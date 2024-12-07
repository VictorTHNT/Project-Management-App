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

        // Mise à jour du statut de la tâche
        $updateStmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $updateStmt->execute([$status, $task_id]);
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
    
    // Récupérer les tâches associées aux projets avec l'information des utilisateurs assignés
    $tasks = [];
    foreach ($projects as $project) {
        $tasksStmt = $pdo->prepare("
            SELECT tasks.*, CONCAT(users.nom, ' ', users.prenom) AS assignee_name 
            FROM tasks 
            LEFT JOIN users ON tasks.assignee_id = users.id 
            WHERE tasks.project_id = ?
        ");
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
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo htmlspecialchars($task['description']); ?></td>
                        <td>
                            <?php echo !empty($task['assignee_name']) ? htmlspecialchars($task['assignee_name']) : 'Non assigné'; ?>
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
                                    <!-- 1 barre remplie pour faible priorité -->
                                    <i class="bi bi-battery-half text-success" style="font-size: 1.5rem;" title="Faible priorité"></i>
                                    <i class="bi bi-battery text-muted" style="font-size: 1.5rem;" title="Faible priorité"></i>
                                    <i class="bi bi-battery text-muted" style="font-size: 1.5rem;" title="Faible priorité"></i>
                                <?php elseif ($task['priority'] == 'modéré'): ?>
                                    <!-- 2 barres remplies pour priorité modérée -->
                                    <i class="bi bi-battery-full text-warning" style="font-size: 1.5rem;" title="Priorité modérée"></i>
                                    <i class="bi bi-battery-full text-warning" style="font-size: 1.5rem;" title="Priorité modérée"></i>
                                    <i class="bi bi-battery text-muted" style="font-size: 1.5rem;" title="Priorité modérée"></i>
                                <?php elseif ($task['priority'] == 'élevé'): ?>
                                    <!-- 3 barres remplies pour haute priorité -->
                                    <i class="bi bi-battery-full text-danger" style="font-size: 1.5rem;" title="Haute priorité"></i>
                                    <i class="bi bi-battery-full text-danger" style="font-size: 1.5rem;" title="Haute priorité"></i>
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
                            <a href="details.php?task_id=<?php echo $task['id']; ?>" class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i> Voir
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Aucune tâche trouvée.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
