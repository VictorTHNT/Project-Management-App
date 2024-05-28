<?php
include '../includes/connect.php';
include '../includes/navbar.php';

if (!isset($_GET['selected_project'])) {
    echo "ID du projet manquant.";
    exit;
}

$project_id = $_GET['selected_project'];

try {
    $projectStmt = $pdo->prepare("SELECT * FROM Projects WHERE id = ?");
    $projectStmt->execute([$project_id]);
    $project = $projectStmt->fetch();

    if (!$project) {
        echo "Projet introuvable.";
        exit;
    }

    $membersStmt = $pdo->prepare("
        SELECT Users.nom, Users.prenom, Users.email, User_Team.post, User_Team.team_name 
        FROM Users 
        JOIN User_Team ON Users.id = User_Team.user_id 
        WHERE User_Team.project_id = ?
    ");
    $membersStmt->execute([$project_id]);
    $members = $membersStmt->fetchAll();

    $tasksStmt = $pdo->prepare("SELECT * FROM Tasks WHERE project_id = ?");
    $tasksStmt->execute([$project_id]);
    $tasks = $tasksStmt->fetchAll();

    $totalTasks = count($tasks);
    $completedTasks = 0;
    foreach ($tasks as $task) {
        if ($task['status'] == 'completed') {
            $completedTasks++;
        }
    }
    $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Projet <?php echo htmlspecialchars($project['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center mb-4">Projet [<?php echo htmlspecialchars($project['title']); ?>]</h1>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <h3 class="card-title">Cahier des Charges</h3>
                    <?php if ($project['cahier_charge']): ?>
                        <a href="<?php echo htmlspecialchars('../assets/upload/' . basename($project['cahier_charge'])); ?>" class="btn btn-primary" download>Télécharger</a>
                    <?php else: ?>
                        <p>Aucun cahier des charges disponible.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body text-center">
                    <h3 class="card-title">Téléverser un fichier</h3>
                    <a href="messages/upload.php?project_id=<?php echo $project_id; ?>" class="btn btn-primary">Téléverser</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <h3 class="card-title">Tâches</h3>
                    <a href="tasks/create.php?project_id=<?php echo $project_id; ?>" class="btn btn-primary">Créer</a>
                    <a href="tasks/view.php?project_id=<?php echo $project_id; ?>" class="btn btn-success">Voir</a>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body text-center">
                    <h3 class="card-title">Progression du Projet</h3>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%;" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo round($progress); ?>%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="card-title">Équipe : <?php echo htmlspecialchars($members[0]['team_name']); ?></h3>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Poste</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['prenom']) . ' ' . htmlspecialchars($member['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                                    <td><?php echo htmlspecialchars($member['post']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <h3 class="card-title">Messagerie</h3>
                    <a href="messages/index.php?project_id=<?php echo $project_id; ?>" class="btn btn-dark">Gérer</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="card-title">Liste des Tâches</h3>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tâche</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
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
                                        <a href="tasks/edit.php?task_id=<?php echo $task['id']; ?>" class="btn btn-primary btn-sm">Modifier</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
