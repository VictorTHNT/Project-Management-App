<?php
include '../../includes/connect.php';
session_start();

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Vérifier si l'utilisateur a le rôle de "manager" ou "admin"
if ($user_role !== 'manager' && $user_role !== 'admin') {
    echo "Accès refusé. Vous n'avez pas les autorisations nécessaires pour créer un projet.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $budget = $_POST['budget'];
    $emails = $_POST['emails'];

    try {
        // Insérer le projet avec l'utilisateur connecté comme manager
        $stmt = $pdo->prepare("INSERT INTO Projects (title, description, start_date, end_date, budget, manager_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $start_date, $end_date, $budget, $user_id]);

        // Récupérer l'ID du projet inséré
        $project_id = $pdo->lastInsertId();

        // Insérer les membres du projet dans la table User_Team
        foreach ($emails as $email) {
            $stmt = $pdo->prepare("SELECT id FROM Users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                $stmt = $pdo->prepare("INSERT INTO User_Team (project_id, user_id) VALUES (?, ?)");
                $stmt->execute([$project_id, $user['id']]);
            }
        }

        echo "Projet créé avec succès!";
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un projet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<div class="container mt-5">
    <h1>Créer un projet</h1>
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
            <label for="start_date" class="form-label">Date de début</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Date de fin</label>
            <input type="date" class="form-control" id="end_date" name="end_date" required>
        </div>
        <div class="mb-3">
            <label for="budget" class="form-label">Budget</label>
            <input type="number" step="0.01" class="form-control" id="budget" name="budget" required>
        </div>
        <div class="mb-3">
            <label for="emails" class="form-label">Membres du projet</label>
            <div id="email-fields">
                <input type="email" class="form-control mb-2" name="emails[]" placeholder="Email du membre" required>
            </div>
            <button type="button" class="btn btn-secondary" onclick="addEmailField()">Ajouter un membre</button>
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
    </form>
</div>

<script>
function addEmailField() {
    const emailFields = document.getElementById('email-fields');
    const newField = document.createElement('input');
    newField.type = 'email';
    newField.className = 'form-control mb-2';
    newField.name = 'emails[]';
    newField.placeholder = 'Email du membre';
    newField.required = true;
    emailFields.appendChild(newField);
}
</script>
</body>
</html>
