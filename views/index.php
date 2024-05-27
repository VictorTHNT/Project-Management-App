<?php
include '../includes/connect.php';
session_start();

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

    // Calcul de la progression des tâches
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
    <title>Projet [<?php echo htmlspecialchars($project['title']); ?>]</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Projet [<?php echo htmlspecialchars($project['title']); ?>]</h1>
    <div class="row mt-4">
        <!-- Cahier des Charges Section -->
        <div class="col-md-6 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title">Cahier des Charges</h3>
                    <?php if ($project['cahier_charge']): ?>
                        <a href="<?php echo htmlspecialchars('../assets/upload/' . basename($project['cahier_charge'])); ?>" class="btn btn-primary mt-2" download>Télécharger</a>
                    <?php else: ?>
                        <p>Aucun cahier des charges disponible.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Upload Section -->
        <div class="col-md-6 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title">Téléverser un fichier</h3>
                    <a href="messages/upload.php?project_id=<?php echo $project_id; ?>" class="btn btn-primary mt-2">Téléverser</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <!-- Tasks Section -->
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title">Tâches</h3>
                    <a href="tasks/create.php?project_id=<?php echo $project_id; ?>" class="btn btn-primary mt-2">Créer</a>
                    <a href="tasks/view.php?project_id=<?php echo $project_id; ?>" class="btn btn-success mt-2">Voir</a>
                </div>
            </div>
        </div>
        <!-- Members Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Équipe : <?php echo htmlspecialchars($members[0]['team_name']); ?></h3>
                    <table class="table">
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
    <div class="row mt-4">
        <!-- Progress Bar Section -->
        <div class="col-md-12">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title">Progression du Projet</h3>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%;" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo round($progress); ?>%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <!-- Messagerie Section -->
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title">Messagerie</h3>
                    <a href="messages/index.php?project_id=<?php echo $project_id; ?>" class="btn btn-dark mt-2">Gérer</a>
                </div>
            </div>
        </div>
        <!-- Tasks List Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Liste des Tâches</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tâche</th>
                                <th>Statut</th>
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
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
