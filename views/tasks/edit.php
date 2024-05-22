<?php
include '../../includes/connect.php';
session_start();

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

// Vérification de l'ID de la tâche
if (!isset($_GET['id'])) {
    echo "ID de tâche manquant.";
    exit;
}

$task_id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $assignee_id = $_POST['assignee_id'];

    try {
        $stmt = $pdo->prepare("UPDATE Tasks SET title = ?, description = ?, status = ?, start_date = ?, end_date = ?, assignee_id = ? WHERE id = ?");
        $stmt->execute([$title, $description, $status, $start_date, $end_date, $assignee_id, $task_id]);
        header("Location: view.php");
        exit;
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
} else {
    try {
        $stmt = $pdo->prepare("SELECT * FROM Tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        $task = $stmt->fetch();

        $usersStmt = $pdo->query("SELECT id, nom, prenom FROM Users");
        $users = $usersStmt->fetchAll();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier la tâche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<div class="container mt-5">
    <h1>Modifier la tâche</h1>
    <form method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Titre</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($task['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Statut</label>
            <select class="form-select" id="status" name="status" required>
                <option value="pending" <?php if ($task['status'] == 'pending') echo 'selected'; ?>>En attente</option>
                <option value="in_progress" <?php if ($task['status'] == 'in_progress') echo 'selected'; ?>>En cours</option>
                <option value="completed" <?php if ($task['status'] == 'completed') echo 'selected'; ?>>Terminé</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Date de début</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($task['start_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Date de fin</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($task['end_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="assignee_id" class="form-label">Attribué à</label>
            <select class="form-select" id="assignee_id" name="assignee_id" required>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['id']); ?>" <?php if ($user['id'] == $task['assignee_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
</body>
</html>
