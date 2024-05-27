<?php
include 'includes/connect.php'; // Assurez-vous que ce chemin est correct
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les projets en cours
$projectsStmt = $pdo->prepare("
    SELECT p.id, p.title, p.start_date, p.end_date, p.budget, p.manager_id, 
           CONCAT(u.prenom, ' ', u.nom) as manager_name,
           (SELECT COUNT(*) FROM Tasks t WHERE t.project_id = p.id) as total_tasks,
           (SELECT COUNT(*) FROM Tasks t WHERE t.project_id = p.id AND t.status = 'completed') as completed_tasks
    FROM Projects p
    JOIN Users u ON p.manager_id = u.id
    WHERE p.end_date >= NOW()
    ORDER BY p.start_date
");
$projectsStmt->execute();
$projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul des indicateurs clés
$totalProjects = count($projects);
$totalProgress = 0;
$totalDelay = 0;
foreach ($projects as $project) {
    $totalProgress += ($project['total_tasks'] > 0) ? ($project['completed_tasks'] / $project['total_tasks']) * 100 : 0;
    $totalDelay += (new DateTime($project['end_date']) < new DateTime()) ? 1 : 0;
}
$averageProgress = ($totalProjects > 0) ? $totalProgress / $totalProjects : 0;

// Récupérer les tâches en retard
$tasksStmt = $pdo->prepare("
    SELECT t.title, t.end_date, p.title as project_title
    FROM Tasks t
    JOIN Projects p ON t.project_id = p.id
    WHERE t.end_date < NOW() AND t.status != 'completed'
");
$tasksStmt->execute();
$overdueTasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Tableau de Bord</h1>
    <div class="row mb-4">
        <!-- Indicateurs Clés -->
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title">Projets en Cours</h3>
                    <p class="card-text"><?php echo $totalProjects; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title">Avancement Moyen</h3>
                    <p class="card-text"><?php echo round($averageProgress, 2); ?>%</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title">Projets en Retard</h3>
                    <p class="card-text"><?php echo $totalDelay; ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <!-- Projets en cours -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Projets en Cours</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Créateur</th>
                                <th>Date de Début</th>
                                <th>Date de Fin</th>
                                <th>Budget</th>
                                <th>Avancement</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <?php
                                    $progress = ($project['total_tasks'] > 0) ? ($project['completed_tasks'] / $project['total_tasks']) * 100 : 0;
                                    $progressClass = ($progress < 50) ? 'bg-danger' : (($progress < 80) ? 'bg-warning' : 'bg-success');
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($project['title']); ?></td>
                                    <td><?php echo htmlspecialchars($project['manager_name']); ?></td>
                                    <td><?php echo htmlspecialchars($project['start_date']); ?></td>
                                    <td><?php echo htmlspecialchars($project['end_date']); ?></td>
                                    <td><?php echo htmlspecialchars($project['budget']); ?></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar <?php echo $progressClass; ?>" role="progressbar" style="width: <?php echo $progress; ?>%;" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo round($progress); ?>%</div>
                                        </div>
                                    </td>
                                    <td><a href="index.php?selected_project=<?php echo $project['id']; ?>" class="btn btn-primary btn-sm">Voir</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <!-- Calendrier -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Calendrier des Projets</h3>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <!-- Alertes -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Tâches en Retard</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tâche</th>
                                <th>Projet</th>
                                <th>Date d'Échéance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($overdueTasks as $task): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['project_title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['end_date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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
        defaultView: 'month',
        defaultDate: moment().format('YYYY-MM-DD'),
        editable: false,
        eventLimit: true,
        events: <?php echo json_encode($projects); ?>,
        eventClick: function(event) {
            if (event.id) {
                window.location.href = 'index.php?selected_project=' + event.id;
            }
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
