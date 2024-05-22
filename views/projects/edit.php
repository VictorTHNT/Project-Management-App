<?php
include '../../includes/connect.php';
session_start();

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $budget = $_POST['budget'];

    if (isset($_POST['delete'])) {
        // Suppression des enregistrements associés dans User_Team
        try {
            $stmt = $pdo->prepare("DELETE FROM User_Team WHERE project_id = ?");
            $stmt->execute([$project_id]);

            // Suppression du projet
            $stmt = $pdo->prepare("DELETE FROM Projects WHERE id = ?");
            $stmt->execute([$project_id]);
            header("Location: view.php");
            exit;
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    } else {
        // Mise à jour du projet
        try {
            $stmt = $pdo->prepare("UPDATE Projects SET title = ?, description = ?, start_date = ?, end_date = ?, budget = ? WHERE id = ?");
            $stmt->execute([$title, $description, $start_date, $end_date, $budget, $project_id]);
            header("Location: view.php");
            exit;
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
} else {
    $project_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM Projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $project = $stmt->fetch();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le projet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<div class="container mt-5">
    <h1>Modifier le projet</h1>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($project['id']); ?>">
        <div class="mb-3">
            <label for="title" class="form-label">Titre</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($project['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($project['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Date de début</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($project['start_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Date de fin</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($project['end_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="budget" class="form-label">Budget</label>
            <input type="number" step="0.01" class="form-control" id="budget" name="budget" value="<?php echo htmlspecialchars($project['budget']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
        <button type="submit" name="delete" class="btn btn-danger">Supprimer</button>
    </form>
</div>
</body>
</html>
