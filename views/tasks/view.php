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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status']) && isset($_POST['task_id'])) {
        $status = $_POST['status'];
        $task_id = $_POST['task_id'];

        // Mise à jour du statut de la tâche
        $updateStmt = $pdo->prepare("UPDATE Tasks SET status = ? WHERE id = ?");
        $updateStmt->execute([$status, $task_id]);
    }

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
    
    // Récupérer les tâches associées aux projets récupérés
    $tasks = [];
    foreach ($projects as $project) {
        $tasksStmt = $pdo->prepare("SELECT * FROM Tasks WHERE project_id = ?");
        $tasksStmt->execute([$project['id']]);
        $projectTasks = $tasksStmt->fetchAll();
        $tasks = array_merge($tasks, $projectTasks);
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Voir les Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Liste des Tâches</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Description</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($tasks)): ?>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo htmlspecialchars($task['description']); ?></td>
                        <td>
                            <?php if ($task['status'] == 'pending'): ?>
                                <span class="badge bg-danger">Pending</span>
                            <?php elseif ($task['status'] == 'in_progress'): ?>
                                <span class="badge bg-warning text-dark">In Progress</span>
                            <?php elseif ($task['status'] == 'completed'): ?>
                                <span class="badge bg-success">Completed</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <input type="hidden" name="project_id" value="<?php echo $task['project_id']; ?>">
                                <button type="submit" name="status" value="pending" class="btn btn-outline-danger btn-sm rounded-circle" title="Pending">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <input type="hidden" name="project_id" value="<?php echo $task['project_id']; ?>">
                                <button type="submit" name="status" value="in_progress" class="btn btn-outline-warning btn-sm rounded-circle" title="In Progress">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <input type="hidden" name="project_id" value="<?php echo $task['project_id']; ?>">
                                <button type="submit" name="status" value="completed" class="btn btn-outline-success btn-sm rounded-circle" title="Completed">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">Aucune tâche trouvée.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
