<?php
include 'includes/connect.php';
include 'includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: views/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Récupérer les projets en cours
$projectsStmt = $pdo->prepare("
    SELECT p.id, p.title, p.start_date, p.end_date, p.budget, p.manager_id, 
           CONCAT(u.prenom, ' ', u.nom) as manager_name,
           (SELECT COUNT(*) FROM Tasks t WHERE t.project_id = p.id) as total_tasks,
           (SELECT COUNT(*) FROM Tasks t WHERE t.project_id = p.id AND t.status = 'completed') as completed_tasks
    FROM Projects p
    JOIN Users u ON p.manager_id = u.id
    WHERE p.manager_id = ? OR EXISTS (SELECT 1 FROM User_Team ut WHERE ut.project_id = p.id AND ut.user_id = ?)
    ORDER BY p.start_date
");
$projectsStmt->execute([$user_id, $user_id]);
$projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les événements du calendrier
$calendarEventsStmt = $pdo->prepare("
    SELECT p.id, p.title as title, DATE_FORMAT(p.start_date, '%Y-%m-%d') as start, 
           DATE_FORMAT(p.end_date, '%Y-%m-%d') as end, p.color
    FROM projects p
    WHERE p.manager_id = :user_id OR EXISTS (SELECT 1 FROM User_Team ut WHERE ut.project_id = p.id AND ut.user_id = :user_id)
");
$calendarEventsStmt->execute(['user_id' => $user_id]);
$calendarEvents = $calendarEventsStmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul des indicateurs clés
$totalProjects = count($projects);
$totalProgress = 0;
$totalDelay = 0;
foreach ($projects as $project) {
    $totalProgress += (isset($project['total_tasks']) && $project['total_tasks'] > 0) ? ($project['completed_tasks'] / $project['total_tasks']) * 100 : 0;
    $totalDelay += (new DateTime($project['end_date']) < new DateTime()) ? 1 : 0;
}
$averageProgress = ($totalProjects > 0) ? $totalProgress / $totalProjects : 0;

// Récupérer les tâches en retard
$tasksStmt = $pdo->prepare("
    SELECT t.title, t.end_date, p.title as project_title
    FROM Tasks t
    JOIN Projects p ON t.project_id = p.id
    WHERE t.end_date < NOW() AND t.status != 'completed' AND (p.manager_id = ? OR EXISTS (SELECT 1 FROM User_Team ut WHERE ut.project_id = p.id AND ut.user_id = ?))
");
$tasksStmt->execute([$user_id, $user_id]);
$overdueTasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les tâches à effectuer
$tasksToDoStmt = $pdo->prepare("
    SELECT t.id, t.title, t.status, p.title as project_title
    FROM Tasks t
    JOIN Projects p ON t.project_id = p.id
    WHERE t.status != 'completed' AND (p.manager_id = ? OR EXISTS (SELECT 1 FROM User_Team ut WHERE ut.project_id = p.id AND ut.user_id = ?))
");
$tasksToDoStmt->execute([$user_id, $user_id]);
$tasksToDo = $tasksToDoStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f5f7;
        }
        .container {
            margin-top: 20px;
        }
        .card {
            border: none;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .card h3 {
            margin-bottom: 20px;
        }
        .progress-bar {
            background-color: #36b37e;
            transition: width 1s ease-in-out;
        }
        .badge {
            padding: 10px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-primary, .btn-success, .btn-dark {
            background-color: #0747a6;
            border: none;
            transition: background-color 0.3s;
        }
        .btn-primary:hover, .btn-success:hover, .btn-dark:hover {
            background-color: #053e85;
        }
        .progress-bar {
            transition: width 0.6s ease;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center mb-4">Tableau de Bord</h1>
    <?php if ($user_role == 'admin'): ?>
        <div class="d-flex justify-content-end mb-4">
            <a href="dashboard_admin.php" class="btn btn-danger">Admin</a>
        </div>
    <?php endif; ?>
    <div class="row mb-4">
        <!-- Indicateurs Clés -->
        <div class="col-md-4" data-aos="fade-up">
            <div class="card text-center animate__animated animate__fadeIn">
                <div class="card-body">
                    <h3 class="card-title">Projets en Cours</h3>
                    <p class="card-text"><?php echo $totalProjects; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up">
            <div class="card text-center animate__animated animate__fadeIn">
                <div class="card-body">
                    <h3 class="card-title">Avancement Moyen</h3>
                    <p class="card-text"><?php echo round($averageProgress, 2); ?>%</p>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up">
            <div class="card text-center animate__animated animate__fadeIn">
                <div class="card-body">
                    <h3 class="card-title">Projets en Retard</h3>
                    <p class="card-text"><?php echo $totalDelay; ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <!-- Projets en cours -->
        <div class="col-md-12" data-aos="fade-up">
            <div class="card animate__animated animate__fadeIn">
                <div class="card-body">
                    <h3 class="card-title">Projets en Cours <button class="btn btn-sm btn-primary float-end" id="toggleFilters">Filtres</button></h3>
                    <div id="filters" class="mb-3" style="display: none;">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-4">
                                <label for="filterEndDate" class="form-label">Date de Fin</label>
                                <input type="date" class="form-control form-control-sm" id="filterEndDate">
                            </div>
                            <div class="col-md-4">
                                <label for="filterBudget" class="form-label">Budget</label>
                                <input type="number" class="form-control form-control-sm" id="filterBudget" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label for="filterProgress" class="form-label">Avancement (%)</label>
                                <input type="number" class="form-control form-control-sm" id="filterProgress" step="0.01">
                            </div>
                            <div class="col-md-12">
                                <button type="button" class="btn btn-sm btn-primary mt-2" id="applyFilters">Appliquer</button>
                                <button type="button" class="btn btn-sm btn-secondary mt-2" id="resetFilters">Réinitialiser</button>
                            </div>
                        </form>
                    </div>
                    <table class="table mt-3" id="projectsTable">
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
                                    $progress = (isset($project['total_tasks']) && $project['total_tasks'] > 0) ? ($project['completed_tasks'] / $project['total_tasks']) * 100 : 0;
                                    $progressClass = ($progress < 50) ? 'bg-danger' : (($progress < 80) ? 'bg-warning' : 'bg-success');
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($project['title']); ?></td>
                                    <td><?php echo htmlspecialchars(isset($project['manager_name']) ? $project['manager_name'] : ''); ?></td>
                                    <td><?php echo htmlspecialchars(isset($project['start_date']) ? $project['start_date'] : ''); ?></td>
                                    <td><?php echo htmlspecialchars($project['end_date']); ?></td>
                                    <td><?php echo htmlspecialchars(isset($project['budget']) ? $project['budget'] : ''); ?></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar <?php echo $progressClass; ?>" role="progressbar" style="width: <?php echo $progress; ?>%;" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo round($progress); ?>%</div>
                                        </div>
                                    </td>
                                    <td><a href="views/index.php?selected_project=<?php echo $project['id']; ?>" class="btn btn-primary btn-sm">Voir</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <!-- Tâches à effectuer -->
        <div class="col-md-12" data-aos="fade-up">
            <div class="card animate__animated animate__fadeIn">
                <div class="card-body">
                    <h3 class="card-title">Tâches à Effectuer</h3>
                    <table class="table mt-3">
                        <thead>
                            <tr>
                                <th>Tâche</th>
                                <th>Projet</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasksToDo as $task): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['project_title']); ?></td>
                                    <td>
                                        <?php if ($task['status'] == 'pending'): ?>
                                            <span class="badge bg-danger">Pending</span>
                                        <?php elseif ($task['status'] == 'in_progress'): ?>
                                            <span class="badge bg-warning text-dark">In Progress</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><a href="views/tasks/view.php?task_id=<?php echo $task['id']; ?>" class="btn btn-primary btn-sm">Voir</a></td>
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
        <div class="col-md-12" data-aos="fade-up">
            <div class="card animate__animated animate__fadeIn">
                <div class="card-body">
                    <h3 class="card-title">Calendrier des Projets</h3>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <!-- Tâches en retard -->
        <div class="col-md-12" data-aos="fade-up">
            <div class="card animate__animated animate__fadeIn">
                <div class="card-body">
                    <h3 class="card-title">Tâches en Retard</h3>
                    <table class="table mt-3">
                        <thead>
                            <tr>
                                <th>Tâche</th>
                                <th>Projet</th>
                                <th>Date de Fin</th>
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
    AOS.init();

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
        events: <?php echo json_encode($calendarEvents); ?>,
        eventClick: function(event) {
            if (event.id) {
                window.location.href = 'views/index.php?selected_project=' + event.id;
            }
        }
    });

    $('#toggleFilters').click(function() {
        $('#filters').toggle();
    });

    $('#applyFilters').click(function() {
        const endDate = $('#filterEndDate').val();
        const budget = $('#filterBudget').val();
        const progress = $('#filterProgress').val();

        $('#projectsTable tbody tr').each(function() {
            const projectEndDate = $(this).find('td:eq(3)').text();
            const projectBudget = $(this).find('td:eq(4)').text();
            const projectProgress = $(this).find('.progress-bar').attr('aria-valuenow');

            let showRow = true;

            if (endDate && projectEndDate !== endDate) {
                showRow = false;
            }

            if (budget && projectBudget !== budget) {
                showRow = false;
            }

            if (progress && projectProgress !== progress) {
                showRow = false;
            }

            if (showRow) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $('#resetFilters').click(function() {
        $('#filterForm')[0].reset();
        $('#projectsTable tbody tr').show();
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
