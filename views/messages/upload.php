<?php
// Inclusion des fichiers de connexion à la base de données et de la barre de navigation
include '../../includes/connect.php';
include '../../includes/navbar.php';

// Vérification si l'utilisateur est bien connecté (session active)
if (!isset($_SESSION['user_id'])) {
    // Redirection vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location: ../../views/auth/login.php");
    exit;
}

// Récupération de l'ID du projet passé en paramètre dans l'URL
$project_id = $_GET['project_id'];
$user_id = $_SESSION['user_id'];
$uploadDir = '../../assets/upload/';

// Initialisation de la variable pour le nom du projet
$project_name = '';
try {
    // Requête pour récupérer le nom du projet à partir de son ID
    $stmt = $pdo->prepare("SELECT title FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si le projet est trouvé, assignation de son nom
    if ($project) {
        $project_name = $project['title'];
    } else {
        // Si le projet n'est pas trouvé, message d'erreur
        $error = "Projet introuvable.";
    }
} catch (PDOException $e) {
    // Gestion d'erreur si la requête échoue
    $error = "Erreur : " . $e->getMessage();
}

// Gestion du téléversement de fichier
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    // Récupération du nom du fichier et préparation du chemin de stockage
    $fileName = basename($_FILES["file"]["name"]);
    $uploadFilePath = $uploadDir . $fileName;

    // Tentative de téléversement du fichier sur le serveur
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $uploadFilePath)) {
        try {
            // Si le fichier est téléversé avec succès, insertion de l'information dans la base de données
            $stmt = $pdo->prepare("INSERT INTO Files (project_id, uploader_id, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$project_id, $user_id, $uploadFilePath]);
            $success = "Fichier téléversé avec succès.";
        } catch (PDOException $e) {
            // Gestion d'erreur si l'insertion dans la base de données échoue
            $error = "Erreur : " . $e->getMessage();
        }
    } else {
        // Si le téléversement du fichier échoue, message d'erreur
        $error = "Erreur lors du téléversement du fichier.";
    }
}

// Gestion de l'ajout de commentaires pour un fichier
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["comment"]) && isset($_POST["file_id"])) {
    // Récupération du commentaire et de l'ID du fichier
    $comment = $_POST["comment"];
    $file_id = $_POST["file_id"];

    try {
        // Insertion du commentaire dans la base de données
        $stmt = $pdo->prepare("INSERT INTO file_comments (file_id, commenter_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$file_id, $user_id, $comment]);
        $success = "Commentaire ajouté avec succès.";
    } catch (PDOException $e) {
        // Gestion d'erreur si l'insertion du commentaire échoue
        $error = "Erreur : " . $e->getMessage();
    }
}

// Récupération de la liste des fichiers téléversés pour le projet
try {
    $stmt = $pdo->prepare("SELECT * FROM Files WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $files = $stmt->fetchAll();
} catch (PDOException $e) {
    // Gestion d'erreur si la récupération des fichiers échoue
    $error = "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Définition du charset pour garantir la gestion correcte des caractères spéciaux -->
    <meta charset="UTF-8">
    <!-- Titre de la page affiché dans l'onglet du navigateur -->
    <title>Téléverser des Fichiers</title>

    <!-- Lien vers la feuille de style Bootstrap pour la mise en page -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Lien vers la feuille de style des icônes Bootstrap pour les icônes de l'interface -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Lien vers le fichier CSS personnalisé (style.css) -->
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- Titre principal de la page affichant le nom du projet -->
        <h1 class="text-center">Téléverser des Fichiers pour le Projet <?php echo htmlspecialchars($project_name); ?></h1>

        <!-- Affichage d'un message de succès ou d'erreur selon le résultat des actions -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulaire pour téléverser un fichier -->
        <form method="POST" action="upload.php?project_id=<?php echo $project_id; ?>" enctype="multipart/form-data">
            <div class="mb-3">
                <!-- Label et champ de saisie pour sélectionner un fichier à téléverser -->
                <label for="file" class="form-label">Choisir un fichier</label>
                <input type="file" class="form-control" id="file" name="file" required>
            </div>
            <!-- Bouton pour soumettre le formulaire et téléverser le fichier -->
            <button type="submit" class="btn btn-primary">Téléverser</button>
        </form>

        <!-- Section affichant les fichiers déjà téléversés pour ce projet -->
        <div class="mt-5">
            <h3>Fichiers Téléversés</h3>
            <ul class="list-group">
                <?php foreach ($files as $file): ?>
                    <li class="list-group-item">
                        <!-- Lien vers le fichier pour pouvoir le télécharger -->
                        <a href="<?php echo htmlspecialchars($file['file_path']); ?>" target="_blank"><?php echo htmlspecialchars(basename($file['file_path'])); ?></a>
                        <br><small>Téléversé le : <?php echo htmlspecialchars($file['upload_timestamp']); ?></small>

                        <!-- Formulaire pour ajouter un commentaire à un fichier -->
                        <form method="POST" action="upload.php?project_id=<?php echo $project_id; ?>" class="mt-3">
                            <!-- Champ caché pour identifier le fichier auquel le commentaire est associé -->
                            <input type="hidden" name="file_id" value="<?php echo htmlspecialchars($file['id']); ?>">
                            <div class="mb-3">
                                <!-- Label et champ de saisie pour le commentaire -->
                                <label for="comment" class="form-label">Ajouter un commentaire</label>
                                <textarea class="form-control" id="comment" name="comment" rows="2" required></textarea>
                            </div>
                            <!-- Bouton pour soumettre le commentaire -->
                            <button type="submit" class="btn btn-primary">Commenter</button>
                        </form>

                        <!-- Affichage des commentaires associés à ce fichier -->
                        <?php
                        // Requête pour récupérer les commentaires associés à ce fichier
                        $commentsStmt = $pdo->prepare("
                            SELECT file_comments.*, Users.nom, Users.prenom 
                            FROM file_comments 
                            JOIN Users ON file_comments.commenter_id = Users.id 
                            WHERE file_comments.file_id = ?
                        ");
                        $commentsStmt->execute([$file['id']]);
                        $comments = $commentsStmt->fetchAll();
                        ?>
                        <ul class="list-group mt-3">
                            <!-- Liste des commentaires associés au fichier -->
                            <?php foreach ($comments as $comment): ?>
                                <li class="list-group-item">
                                    <!-- Nom de l'utilisateur ayant posté le commentaire et le commentaire lui-même -->
                                    <strong><?php echo htmlspecialchars($comment['prenom']) . ' ' . htmlspecialchars($comment['nom']); ?>:</strong> <?php echo htmlspecialchars($comment['comment']); ?>
                                    <br><small><?php echo htmlspecialchars($comment['comment_timestamp']); ?></small> <!-- Date et heure du commentaire -->
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Inclusion du footer -->
    <?php include '../../includes/footer.php'; ?>

    <!-- Inclusion de Bootstrap JS pour gérer les fonctionnalités interactives -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"></script>
</body>
</html>
