<?php
include '../../includes/connect.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Récupérer les tâches dont la date de fin est aujourd'hui ou dépassée
    $stmt = $pdo->prepare("
        SELECT Tasks.id, Tasks.title, Tasks.end_date, Tasks.assignee_id, Tasks.project_id, Projects.title AS project_title
        FROM Tasks
        JOIN Projects ON Tasks.project_id = Projects.id
        WHERE Tasks.end_date <= CURDATE() AND FIND_IN_SET(?, Tasks.assignee_id)
        ORDER BY Tasks.end_date ASC
    ");
    $stmt->execute([$user_id]);
    $tasks = $stmt->fetchAll();

    if (empty($tasks)) {
        echo "<p>Aucune notification pour le moment.</p>";
    } else {
        echo "<h2>Notifications</h2>";
        echo "<ul class='list-group'>";
        foreach ($tasks as $task) {
            $date = htmlspecialchars($task['end_date']);
            $title = htmlspecialchars($task['title']);
            $project = htmlspecialchars($task['project_title']);
            echo "<li class='list-group-item'>";
            echo "<strong>Projet :</strong> $project<br>";
            echo "<strong>Tâche :</strong> $title<br>";
            echo "<strong>Date de fin :</strong> $date<br>";
            echo "</li>";
        }
        echo "</ul>";
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
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <?php include '../../includes/navbar.php'; ?>
    <h1 class="text-center">Mes Notifications</h1>
    <div>
        <?php if (!empty($tasks)): ?>
            <div class="alert alert-warning">
                <strong>Attention :</strong> Vous avez des tâches à gérer.
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
</body>
</html>
