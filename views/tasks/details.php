<?php
include '../../includes/connect.php';
include '../../includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : null;

if (!$task_id) {
    echo "ID de tâche invalide.";
    exit;
}

try {
    // Récupérer les détails de la tâche
    $taskStmt = $pdo->prepare("
        SELECT Tasks.*, Projects.title AS project_title 
        FROM Tasks 
        LEFT JOIN Projects ON Tasks.project_id = Projects.id 
        WHERE Tasks.id = ?
    ");
    $taskStmt->execute([$task_id]);
    $task = $taskStmt->fetch();

    if (!$task) {
        echo "Tâche introuvable.";
        exit;
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
    <title>Détails de la tâche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Détails de la Tâche</h1>
    <div class="card">
        <div class="card-header">
            <h3><?php echo htmlspecialchars($task['title']); ?></h3>
        </div>
        <div class="card-body">
            <p><strong>Description :</strong></p>
            <p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
            <p><strong>Statut :</strong> <?php echo htmlspecialchars($task['status']); ?></p>
            <p><strong>Priorité :</strong> <?php echo htmlspecialchars($task['priority']); ?></p>
            <p><strong>Date de début :</strong> <?php echo htmlspecialchars($task['start_date']); ?></p>
            <p><strong>Date de fin :</strong> <?php echo htmlspecialchars($task['end_date']); ?></p>
            <p><strong>Projet associé :</strong> <?php echo htmlspecialchars($task['project_title']); ?></p>
            <p><strong>Assignés :</strong></p>
            <ul>
                <td>
                    <?php
                    $assigneeIds = explode(',', $task['assignee_id']);
                    $assignees = [];
                    foreach ($assigneeIds as $id) {
                        $assigneeStmt = $pdo->prepare("SELECT CONCAT(prenom, ' ', nom) AS full_name FROM users WHERE id = ?");
                        $assigneeStmt->execute([$id]);
                        $assignee = $assigneeStmt->fetchColumn();
                        if ($assignee) {
                            $assignees[] = htmlspecialchars($assignee);
                        }
                    }
                    echo implode('<br>', $assignees) ?: 'Non assigné';
                    ?>
                </td>
            </ul>
        </div>
        <div class="card-footer text-center">
            <a href="view.php" class="btn btn-primary">Retour à la liste des tâches</a>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
</body>
</html>
