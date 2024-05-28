<?php
include '../../includes/connect.php';
include '../../includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

$project_id = $_GET['project_id'];
$user_id = $_SESSION['user_id'];
$uploadDir = '../../assets/upload/';

// Récupérer le nom du projet
$project_name = '';
try {
    $stmt = $pdo->prepare("SELECT title FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($project) {
        $project_name = $project['title'];
    } else {
        $error = "Projet introuvable.";
    }
} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $fileName = basename($_FILES["file"]["name"]);
    $uploadFilePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $uploadFilePath)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Files (project_id, uploader_id, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$project_id, $user_id, $uploadFilePath]);
            $success = "Fichier téléversé avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    } else {
        $error = "Erreur lors du téléversement du fichier.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["comment"]) && isset($_POST["file_id"])) {
    $comment = $_POST["comment"];
    $file_id = $_POST["file_id"];

    try {
        $stmt = $pdo->prepare("INSERT INTO file_comments (file_id, commenter_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$file_id, $user_id, $comment]);
        $success = "Commentaire ajouté avec succès.";
    } catch (PDOException $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM Files WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $files = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Téléverser des Fichiers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Téléverser des Fichiers pour le Projet <?php echo htmlspecialchars($project_name); ?></h1>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="upload.php?project_id=<?php echo $project_id; ?>" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="file" class="form-label">Choisir un fichier</label>
            <input type="file" class="form-control" id="file" name="file" required>
        </div>
        <button type="submit" class="btn btn-primary">Téléverser</button>
    </form>
    <div class="mt-5">
        <h3>Fichiers Téléversés</h3>
        <ul class="list-group">
            <?php foreach ($files as $file): ?>
                <li class="list-group-item">
                    <a href="<?php echo htmlspecialchars($file['file_path']); ?>" target="_blank"><?php echo htmlspecialchars(basename($file['file_path'])); ?></a>
                    <br><small>Téléversé le : <?php echo htmlspecialchars($file['upload_timestamp']); ?></small>
                    <form method="POST" action="upload.php?project_id=<?php echo $project_id; ?>" class="mt-3">
                        <input type="hidden" name="file_id" value="<?php echo htmlspecialchars($file['id']); ?>">
                        <div class="mb-3">
                            <label for="comment" class="form-label">Ajouter un commentaire</label>
                            <textarea class="form-control" id="comment" name="comment" rows="2" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Commenter</button>
                    </form>
                    <?php
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
                        <?php foreach ($comments as $comment): ?>
                            <li class="list-group-item">
                                <strong><?php echo htmlspecialchars($comment['prenom']) . ' ' . htmlspecialchars($comment['nom']); ?>:</strong> <?php echo htmlspecialchars($comment['comment']); ?>
                                <br><small><?php echo htmlspecialchars($comment['comment_timestamp']); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"></script>
</body>
</html>
