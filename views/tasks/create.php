<?php
include '../../includes/connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $project_id = $_POST['project_id'];
    $assignee_id = $_POST['assignee_id'];

    try {
        // Insérer la tâche
        $stmt = $pdo->prepare("INSERT INTO Tasks (title, description, status, start_date, end_date, project_id, assignee_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $status, $start_date, $end_date, $project_id, $assignee_id]);
        echo "Tâche créée avec succès!";
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}

// Récupérer les projets pour les afficher dans le champ de sélection
try {
    $projectsStmt = $pdo->query("SELECT id, title FROM Projects");
    $projects = $projectsStmt->fetchAll();
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}

// Récupérer les utilisateurs pour les afficher dans le champ de sélection
try {
    $usersStmt = $pdo->query("SELECT id, nom, prenom FROM Users");
    $users = $usersStmt->fetchAll();
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer une tâche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<div class="container mt-5">
    <h1>Créer une tâche</h1>
    <form method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Titre</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Statut</label>
            <select class="form-select" id="status" name="status" required>
                <option value="pending">En attente</option>
                <option value="in_progress">En cours</option>
                <option value="completed">Terminé</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Date de début</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Date de fin</label>
            <input type="date" class="form-control" id="end_date" name="end_date" required>
        </div>
        <div class="mb-3">
            <label for="project_id" class="form-label">Projet</label>
            <select class="form-select" id="project_id" name="project_id" required>
                <?php foreach ($projects as $project): ?>
                    <option value="<?php echo htmlspecialchars($project['id']); ?>"><?php echo htmlspecialchars($project['title']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="assignee_id" class="form-label">Attribué à</label>
            <select class="form-select" id="assignee_id" name="assignee_id" required>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['id']); ?>"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
    </form>
</div>
</body>
</html>
