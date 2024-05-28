<?php
include '../../includes/connect.php';
include '../../includes/navbar.php';

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_project'])) {
        $project_id = $_POST['project_id'];

        try {
            $pdo->beginTransaction();

            // Supprimer les commentaires sur les fichiers associés au projet
            $stmt = $pdo->prepare("DELETE FROM file_comments WHERE file_id IN (SELECT id FROM files WHERE project_id = ?)");
            $stmt->execute([$project_id]);

            // Supprimer les fichiers associés au projet
            $stmt = $pdo->prepare("DELETE FROM files WHERE project_id = ?");
            $stmt->execute([$project_id]);

            // Supprimer les notifications associées au projet
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE project_id = ?");
            $stmt->execute([$project_id]);

            // Supprimer les messages associés au projet
            $stmt = $pdo->prepare("DELETE FROM messages WHERE project_id = ?");
            $stmt->execute([$project_id]);

            // Supprimer les tâches associées au projet
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE project_id = ?");
            $stmt->execute([$project_id]);

            // Supprimer les membres de l'équipe associés au projet
            $stmt = $pdo->prepare("DELETE FROM user_team WHERE project_id = ?");
            $stmt->execute([$project_id]);

            // Supprimer les événements de calendrier associés au projet
            $stmt = $pdo->prepare("DELETE FROM calendar_events WHERE project_id = ?");
            $stmt->execute([$project_id]);

            // Supprimer les cahiers de charge associés au projet
            $stmt = $pdo->prepare("SELECT cahier_charge FROM projects WHERE id = ?");
            $stmt->execute([$project_id]);
            $cahier_charge = $stmt->fetchColumn();
            if ($cahier_charge) {
                unlink('../../assets/upload/' . $cahier_charge);
            }

            // Supprimer le projet
            $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$project_id]);

            $pdo->commit();
            header("Location: view.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    } else if (isset($_POST['add_member'])) {
        $project_id = $_POST['project_id'];
        $email = $_POST['email'];
        $post = $_POST['post'];

        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $stmt = $pdo->prepare("INSERT INTO user_team (project_id, user_id, post) VALUES (?, ?, ?)");
                $stmt->execute([$project_id, $user['id'], $post]);
            } else {
                echo "Utilisateur non trouvé.";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    } else if (isset($_POST['remove_member'])) {
        $project_id = $_POST['project_id'];
        $user_id = $_POST['user_id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM user_team WHERE project_id = ? AND user_id = ?");
            $stmt->execute([$project_id, $user_id]);
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    } else {
        $project_id = $_POST['project_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $budget = $_POST['budget'];
        $color = $_POST['color'];
        $old_cahier_charge = $_POST['old_cahier_charge'];
        $cahier_charge = $_FILES['cahier_charge']['name'];

        try {
            $pdo->beginTransaction();

            // Mise à jour des informations du projet
            $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ?, start_date = ?, end_date = ?, budget = ?, color = ? WHERE id = ?");
            $stmt->execute([$title, $description, $start_date, $end_date, $budget, $color, $project_id]);

            // Gestion du cahier de charge
            if ($cahier_charge) {
                // Supprimer l'ancien cahier de charge s'il existe
                if ($old_cahier_charge) {
                    unlink('../../assets/upload/' . $old_cahier_charge);
                }

                // Enregistrer le nouveau cahier de charge
                $target_dir = "../../assets/upload/";
                $target_file = $target_dir . basename($_FILES["cahier_charge"]["name"]);
                move_uploaded_file($_FILES["cahier_charge"]["tmp_name"], $target_file);

                // Mettre à jour la base de données avec le nom du nouveau fichier
                $stmt = $pdo->prepare("UPDATE projects SET cahier_charge = ? WHERE id = ?");
                $stmt->execute([$cahier_charge, $project_id]);
            }

            $pdo->commit();
            header("Location: view.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }
}

// Récupérer les détails du projet pour l'affichage dans le formulaire
if (isset($_GET['id'])) {
    $project_id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $project = $stmt->fetch();

        if (!$project) {
            throw new Exception("Projet introuvable");
        }

        // Récupérer les membres de l'équipe
        $stmt = $pdo->prepare("
            SELECT users.id, users.email, user_team.post 
            FROM users 
            JOIN user_team ON users.id = user_team.user_id 
            WHERE user_team.project_id = ?
        ");
        $stmt->execute([$project_id]);
        $team_members = $stmt->fetchAll();

    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
        exit;
    }
} else {
    echo "ID de projet manquant.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un Projet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Modifier un Projet</h1>
    <form method="POST" action="edit.php?id=<?php echo htmlspecialchars($project['id']); ?>" enctype="multipart/form-data">
        <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['id']); ?>">
        <div class="mb-3">
            <label for="title" class="form-label">Titre du Projet</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($project['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($project['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Date de Début</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($project['start_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Date de Fin</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($project['end_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="budget" class="form-label">Budget</label>
            <input type="number" class="form-control" id="budget" name="budget" step="0.01" value="<?php echo htmlspecialchars($project['budget']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="color" class="form-label">Couleur du Projet</label>
            <input type="color" class="form-control" id="color" name="color" value="<?php echo htmlspecialchars($project['color']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="cahier_charge" class="form-label">Cahier des Charges</label>
            <?php if ($project['cahier_charge']): ?>
                <div>
                    <a href="../../assets/upload/<?php echo htmlspecialchars($project['cahier_charge']); ?>" target="_blank">Voir le Cahier des Charges actuel</a>
                </div>
                <div>
                    <label for="delete_cahier_charge" class="form-label">Supprimer le cahier des charges existant</label>
                    <input type="checkbox" id="delete_cahier_charge" name="delete_cahier_charge" value="1">
                </div>
            <?php endif; ?>
            <input type="file" class="form-control" id="cahier_charge" name="cahier_charge">
            <input type="hidden" name="old_cahier_charge" value="<?php echo htmlspecialchars($project['cahier_charge']); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>

    <div class="mt-5">
        <h3>Membres de l'équipe</h3>
        <ul class="list-group">
            <?php foreach ($team_members as $member): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?php echo htmlspecialchars($member['email']); ?> (<?php echo htmlspecialchars($member['post']); ?>)
                    <form method="POST" action="edit.php?id=<?php echo htmlspecialchars($project['id']); ?>" class="d-inline">
                        <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['id']); ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($member['id']); ?>">
                        <button type="submit" name="remove_member" class="btn btn-danger btn-sm">Retirer</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <form method="POST" action="edit.php?id=<?php echo htmlspecialchars($project['id']); ?>" class="mt-3">
            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['id']); ?>">
            <div class="mb-3">
                <label for="email" class="form-label">Email du Membre</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="post" class="form-label">Poste</label>
                <input type="text" class="form-control" id="post" name="post" required>
            </div>
            <button type="submit" name="add_member" class="btn btn-primary">Ajouter Membre</button>
        </form>
        
        <form method="POST" action="edit.php?id=<?php echo htmlspecialchars($project['id']); ?>" class="mt-3">
            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['id']); ?>">
            <button type="submit" name="delete_project" class="btn btn-danger">Supprimer le projet</button>
        </form>        
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../../includes/footer.php'; ?>
</body>
</html>
