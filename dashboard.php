<?php
session_start();
include 'includes/connect.php';
include 'includes/navbar.php';

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: views/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? '';

// Récupérer les projets en cours
$projectsStmt = $pdo->prepare("
    SELECT p.id, p.title, p.start_date, p.end_date, p.budget, 
           (SELECT COUNT(*) FROM Tasks t WHERE t.project_id = p.id) as total_tasks,
           (SELECT COUNT(*) FROM Tasks t WHERE t.project_id = p.id AND t.status = 'completed') as completed_tasks
    FROM Projects p
    WHERE p.manager_id = :user_id OR EXISTS (
        SELECT 1 FROM User_Team ut WHERE ut.project_id = p.id AND ut.user_id = :user_id
    )
    ORDER BY p.start_date ASC
");
$projectsStmt->execute(['user_id' => $user_id]);
$projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul des indicateurs clés
$totalProjects = count($projects);
$totalProgress = 0;
$totalDelay = 0;
foreach ($projects as $project) {
    $progress = ($project['total_tasks'] > 0) 
        ? ($project['completed_tasks'] / $project['total_tasks']) * 100 
        : 0;
    $totalProgress += $progress;
    if (new DateTime($project['end_date']) < new DateTime()) {
        $totalDelay++;
    }
}
$averageProgress = ($totalProjects > 0) ? $totalProgress / $totalProjects : 0;

// Récupérer les événements du calendrier
$calendarEventsStmt = $pdo->prepare("
    SELECT p.id, p.title as title, DATE_FORMAT(p.start_date, '%Y-%m-%d') as start,
           DATE_FORMAT(p.end_date, '%Y-%m-%d') as end
    FROM Projects p
    WHERE p.manager_id = :user_id OR EXISTS (
        SELECT 1 FROM User_Team ut WHERE ut.project_id = p.id AND ut.user_id = :user_id
    )
");
$calendarEventsStmt->execute(['user_id' => $user_id]);
$calendarEvents = $calendarEventsStmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les tâches en cours
$tasksStmt = $pdo->prepare("
    SELECT t.id, t.title, t.status, p.title as project_title
    FROM Tasks t
    JOIN Projects p ON t.project_id = p.id
    WHERE t.status != 'completed' AND (
        p.manager_id = :user_id OR EXISTS (
            SELECT 1 FROM User_Team ut WHERE ut.project_id = p.id AND ut.user_id = :user_id
        )
    )
");
$tasksStmt->execute(['user_id' => $user_id]);
$tasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.js"></script>
</head>
<body>
<div class="container mt-4">
    <h1 class="text-center">Tableau de Bord</h1>
    
    <!-- Indicateurs clés -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Projets en Cours</h5>
                    <p class="h2"><?php echo $totalProjects; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Avancement Moyen</h5>
                    <p class="h2"><?php echo round($averageProgress, 2); ?>%</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Projets en Retard</h5>
                    <p class="h2"><?php echo $totalDelay; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des projets -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Projets en Cours</h5>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Titre</th>
                    <th>Date de début</th>
                    <th>Date de fin</th>
                    <th>Avancement</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($projects as $project): ?>
                    <?php $progress = ($project['total_tasks'] > 0) 
                        ? ($project['completed_tasks'] / $project['total_tasks']) * 100 
                        : 0; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($project['title']); ?></td>
                        <td><?php echo htmlspecialchars($project['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($project['end_date']); ?></td>
                        <td><?php echo round($progress, 2); ?>%</td>
                        <td><a href="views/index.php?selected_project=<?php echo $project['id']; ?>" class="btn btn-primary btn-sm">Voir</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Liste des tâches -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Tâches à effectuer</h5>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Tâche</th>
                    <th>Projet</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo htmlspecialchars($task['project_title']); ?></td>
                        <td><?php echo htmlspecialchars($task['status']); ?></td>
                        <td><a href="views/tasks/view.php?task_id=<?php echo $task['id']; ?>" class="btn btn-primary btn-sm">Voir</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Calendrier -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Calendrier</h5>
            <div id="calendar"></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        events: <?php echo json_encode($calendarEvents); ?>,
    });
});
</script>
</body>
</html>
