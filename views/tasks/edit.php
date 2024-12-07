<?php
include '../../includes/connect.php';
include '../../includes/navbar.php';

if (!isset($_GET['task_id'])) {
    echo "ID de tâche manquant.";
    exit;
}

$task_id = $_GET['task_id'];

try {
    // Récupérer les informations de la tâche
    $taskStmt = $pdo->prepare("SELECT * FROM Tasks WHERE id = ?");
    $taskStmt->execute([$task_id]);
    $task = $taskStmt->fetch();

    if (!$task) {
        echo "Tâche introuvable.";
        exit;
    }

    // Récupérer les utilisateurs pour la sélection d'assignation
    $usersStmt = $pdo->query("SELECT id, nom, prenom FROM Users");
    $users = $usersStmt->fetchAll();

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        try {
            // Supprimer la tâche
            $deleteStmt = $pdo->prepare("DELETE FROM Tasks WHERE id = ?");
            $deleteStmt->execute([$task_id]);

            echo "Tâche supprimée avec succès.";
            header("Location: ../index.php?selected_project=" . $task['project_id']); // Rediriger vers la page du projet
            exit;
        } catch (Exception $e) {
            echo "Erreur lors de la suppression de la tâche : " . $e->getMessage();
        }
    } else {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $assignee_id = $_POST['assignee_id'];

        try {
            // Mettre à jour les informations de la tâche
            $updateStmt = $pdo->prepare("UPDATE Tasks SET title = ?, description = ?, status = ?, start_date = ?, end_date = ?, assignee_id = ? WHERE id = ?");
            $updateStmt->execute([$title, $description, $status, $start_date, $end_date, $assignee_id, $task_id]);

            echo "Tâche mise à jour avec succès.";
            header("Location: ../../dashboard_admin.php?selected_project=" . $task['project_id']); // Rediriger vers la page du projet
            exit;
        } catch (Exception $e) {
            echo "Erreur lors de la mise à jour de la tâche : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Tâche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Modifier Tâche</h1>

    <!-- Formulaire de modification -->
    <form method="post">
        <div class="mb-3">
            <label for="title" class="form-label">Titre de la Tâche</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($task['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Statut</label>
            <select class="form-select" id="status" name="status" required>
                <option value="pending" <?php if ($task['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                <option value="in_progress" <?php if ($task['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                <option value="completed" <?php if ($task['status'] == 'completed') echo 'selected'; ?>>Completed</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Date de Début</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($task['start_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Date de Fin</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($task['end_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="assignee_id" class="form-label">Assigner à</label>
            <select class="form-select" id="assignee_id" name="assignee_id" required>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>" <?php if ($user['id'] == $task['assignee_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>

    <!-- Formulaire de suppression (placé tout en bas) -->
    <form method="post" action="edit.php?task_id=<?php echo $task_id; ?>" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?');">
        <button type="submit" name="delete" class="btn btn-danger mt-3">Supprimer la Tâche</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../../includes/footer.php'; ?>

</body>
</html>
