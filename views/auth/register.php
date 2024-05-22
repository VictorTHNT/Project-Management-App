<?php
include '../../includes/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $profile = $_FILES['profile']['name'] ? 'assets/images/' . basename($_FILES['profile']['name']) : 'assets/images/default-profile.png';
    if ($profile !== 'assets/images/default-profile.png') {
        move_uploaded_file($_FILES['profile']['tmp_name'], $profile);
    }

    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'member';

    try {
        $stmt = $pdo->prepare("INSERT INTO Users (profile, nom, prenom, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$profile, $nom, $prenom, $email, $password, $role]);
        header("Location: login.php");
        exit;
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<div class="container mt-5">
    <h1>Inscription</h1>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="profile" class="form-label">Photo de profil</label>
            <input type="file" class="form-control" id="profile" name="profile">
        </div>
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" required>
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="prenom" name="prenom" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Inscription</button>
    </form>
    <p class="mt-3">Déjà inscrit ? <a href="login.php">Connectez-vous ici</a>.</p>
</div>
</body>
</html>
