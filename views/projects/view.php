<?php
include '../../includes/connect.php';
session_start();

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

try {
    if ($user_role === 'admin') {
        // Récupérer tous les projets pour les administrateurs
        $projectsStmt = $pdo->query("SELECT id, title, description, end_date FROM Projects");
    } else {
        // Récupérer les projets associés à l'utilisateur connecté
        $projectsStmt = $pdo->prepare("
            SELECT Projects.id, Projects.title, Projects.description, Projects.end_date 
            FROM Projects 
            JOIN User_Team ON Projects.id = User_Team.project_id 
            WHERE User_Team.user_id = ?
        ");
        $projectsStmt->execute([$user_id]);
    }
    $projects = $projectsStmt->fetchAll();
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion de projet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Gestion de projet</h1>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Sélectionnez un projet</h2>
        <?php if ($user_role === 'manager' || $user_role === 'admin'): ?>
            <a href="create.php" class="plus-button">
                <i class="bi bi-plus"></i>
            </a>
        <?php endif; ?>
    </div>
    <?php if (count($projects) > 0): ?>
        <form method="POST" action="../index.php">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col">Nom du projet</th>
                        <th scope="col">Description</th>
                        <th scope="col">Date de fin</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr class="project-row">
                            <td>
                                <input class="form-check-input" type="radio" name="selected_project" value="<?php echo htmlspecialchars($project['id']); ?>" required>
                            </td>
                            <td><?php echo htmlspecialchars($project['title']); ?></td>
                            <td><?php echo htmlspecialchars($project['description']); ?></td>
                            <td><?php echo htmlspecialchars($project['end_date']); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo htmlspecialchars($project['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-center">
                <button type="submit" class="btn btn-success btn-lg">Entrer</button>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            Vous n'êtes associé à aucun projet.
        </div>
    <?php endif; ?>
</div>
</body>
</html>
