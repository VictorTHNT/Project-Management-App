<?php
include '../includes/connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_project'])) {
    $project_id = $_POST['selected_project'];

    // Fetch project details
    try {
        $projectStmt = $pdo->prepare("SELECT title FROM Projects WHERE id = ?");
        $projectStmt->execute([$project_id]);
        $project = $projectStmt->fetch();

        if (!$project) {
            throw new Exception("Project not found");
        }

        $project_title = $project['title'];

        // Fetch project members
        $membersStmt = $pdo->prepare("
            SELECT Users.nom, Users.prenom, Users.email, Users.role 
            FROM Users 
            JOIN User_Team ON Users.id = User_Team.user_id 
            WHERE User_Team.project_id = ?
        ");
        $membersStmt->execute([$project_id]);
        $members = $membersStmt->fetchAll();

        // Fetch project tasks
        $tasksStmt = $pdo->prepare("SELECT title, status FROM Tasks WHERE project_id = ?");
        $tasksStmt->execute([$project_id]);
        $tasks = $tasksStmt->fetchAll();
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
        exit;
    }
} else {
    echo "Aucun projet sélectionné.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Projet [<?php echo htmlspecialchars($project_title); ?>]</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Projet [<?php echo htmlspecialchars($project_title); ?>]</h1>
    <div class="row mt-4">
        <!-- Tasks Section -->
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="card-title">Task</h3>
                    <a href="tasks/create.php" class="btn btn-primary mt-2">Create</a>
                    <a href="tasks/view.php" class="btn btn-success mt-2">View</a>
                </div>
            </div>
        </div>
        <!-- Members Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Member</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['prenom']) . ' ' . htmlspecialchars($member['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                                    <td><?php echo htmlspecialchars($member['role']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
                    <a href="messages/index.php" class="btn btn-dark mt-2">Manage</a>
                </div>
            </div>
        </div>
        <!-- Tasks List Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Task</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Task</th>
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
