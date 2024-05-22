<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : (isset($_POST['selected_project']) ? intval($_POST['selected_project']) : null);

if ($project_id === null || $project_id == 0) {
    echo "ID du projet manquant.";
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM Tasks WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $tasks = $stmt->fetchAll();
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id']) && isset($_POST['status'])) {
    $task_id = intval($_POST['task_id']);
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE Tasks SET status = ? WHERE id = ?");
        $stmt->execute([$status, $task_id]);
        header("Location: view.php?project_id=" . $project_id);
        exit;
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
        exit;
    }
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
                            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                            <button type="submit" name="status" value="pending" class="btn btn-outline-secondary btn-sm rounded-circle" title="Pending">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </form>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                            <button type="submit" name="status" value="completed" class="btn btn-outline-success btn-sm rounded-circle" title="Completed">
                                <i class="bi bi-check-circle"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
