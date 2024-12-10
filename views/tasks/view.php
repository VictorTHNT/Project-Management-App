<?php
include '../../includes/connect.php';
include '../../includes/navbar.php';

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

        // Vérifier si l'utilisateur est assigné à la tâche
        $checkAssigneeStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM tasks 
            WHERE id = ? AND (FIND_IN_SET(?, assignee_id) > 0 OR ? = 'admin')
        ");
        $checkAssigneeStmt->execute([$task_id, $user_id, $user_role]);
        $isAssigned = $checkAssigneeStmt->fetchColumn();

        if ($isAssigned) {
            // Mise à jour du statut de la tâche
            $updateStmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
            $updateStmt->execute([$status, $task_id]);
        } else {
            $error_message = "Vous n'êtes pas assigné à cette tâche.";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view_task_id'])) {
        $task_id = $_POST['view_task_id'];

        // Vérifier si l'utilisateur est assigné à la tâche ou admin
        $checkAssigneeStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM tasks 
            WHERE id = ? AND (FIND_IN_SET(?, assignee_id) > 0 OR ? = 'admin')
        ");
        $checkAssigneeStmt->execute([$task_id, $user_id, $user_role]);
        $isAssigned = $checkAssigneeStmt->fetchColumn();

        if ($isAssigned) {
            // Rediriger vers la page de détails
            header("Location: details.php?task_id=" . $task_id);
            exit;
        } else {
            $error_message = "Vous n'etes pas assigné a cette tache.";
        }
    }

    if ($user_role === 'admin') {
        // Récupérer tous les projets pour les administrateurs
        $projectsStmt = $pdo->query("SELECT id, title, description, end_date FROM projects");
    } else {
        // Récupérer les projets associés à l'utilisateur connecté
        $projectsStmt = $pdo->prepare("
            SELECT projects.id, projects.title, projects.description, projects.end_date 
            FROM projects 
            JOIN user_team ON projects.id = user_team.project_id 
            WHERE user_team.user_id = ?
        ");
        $projectsStmt->execute([$user_id]);
    }
    $projects = $projectsStmt->fetchAll();

    // Récupérer les tâches associées aux projets
    $tasks = [];
    foreach ($projects as $project) {
        $tasksStmt = $pdo->prepare("SELECT tasks.*, projects.title as project_title FROM tasks 
                                    JOIN projects ON tasks.project_id = projects.id 
                                    WHERE tasks.project_id = ?");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Liste des Tâches</h1>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Projet</th>
                <th>Titre</th>
                <th>Description</th>
                <th>Assigné à</th>
                <th>Statut</th>
                <th>Priorité</th>
                <th>Actions</th>
                <th>Voir Détails</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($tasks)): ?>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['project_title']); ?></td>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo htmlspecialchars($task['description']); ?></td>
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
                            <!-- Priorité sous forme de batterie -->
                            <div class="d-flex align-items-center">
                                <?php if ($task['priority'] == 'faible'): ?>
                                    <i class="bi bi-battery text-success" style="font-size: 1.5rem;" title="Faible priorité"></i>
                                <?php elseif ($task['priority'] == 'modéré'): ?>
                                    <i class="bi bi-battery-half text-warning" style="font-size: 1.5rem;" title="Priorité modérée"></i>
                                <?php elseif ($task['priority'] == 'élevé'): ?>
                                    <i class="bi bi-battery-full text-danger" style="font-size: 1.5rem;" title="Haute priorité"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" name="status" value="pending" class="btn btn-outline-danger btn-sm rounded-circle" title="Pending">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" name="status" value="in_progress" class="btn btn-outline-warning btn-sm rounded-circle" title="In Progress">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" name="status" value="completed" class="btn btn-outline-success btn-sm rounded-circle" title="Completed">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            </form>
                        </td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="view_task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" class="btn btn-info btn-sm">
                                    <i class="bi bi-eye"></i> Voir
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">Aucune tâche trouvée.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
