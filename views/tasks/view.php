<?php
include '../../includes/connect.php';
session_start();

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT Tasks.id, Tasks.title, Tasks.status, Tasks.start_date, Tasks.end_date, Users.prenom, Users.nom 
        FROM Tasks 
        JOIN Users ON Tasks.assignee_id = Users.id
    ");
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Voir toutes les tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<div class="container mt-5">
    <h1>Toutes les tâches</h1>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Statut</th>
                <th>Date de début</th>
                <th>Date de fin</th>
                <th>Attribué à</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                    <td>
                        <?php if ($task['status'] == 'pending'): ?>
                            <span class="badge bg-danger">En attente</span>
                        <?php elseif ($task['status'] == 'in_progress'): ?>
                            <span class="badge bg-warning text-dark">En cours</span>
                        <?php elseif ($task['status'] == 'completed'): ?>
                            <span class="badge bg-success">Terminé</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($task['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($task['end_date']); ?></td>
                    <td><?php echo htmlspecialchars($task['prenom'] . ' ' . $task['nom']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo htmlspecialchars($task['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
