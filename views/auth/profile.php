<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT id, nom, prenom, email, role, profile FROM Users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Utilisateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-photo {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="profile-header">
        <h1>Profil Utilisateur</h1>
        <p>Informations de votre profil</p>
        <?php if ($user['profile']): ?>
            <img src="../../<?php echo htmlspecialchars($user['profile']); ?>" alt="Profile Image" class="profile-photo img-thumbnail">
        <?php else: ?>
            <img src="../../assets/images/default-profile.png" alt="Profile Image" class="profile-photo img-thumbnail">
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label">ID</label>
        <p class="form-control"><?php echo htmlspecialchars($user['id']); ?></p>
    </div>
    <div class="mb-3">
        <label class="form-label">Nom</label>
        <p class="form-control"><?php echo htmlspecialchars($user['nom']); ?></p>
    </div>
    <div class="mb-3">
        <label class="form-label">Prénom</label>
        <p class="form-control"><?php echo htmlspecialchars($user['prenom']); ?></p>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <p class="form-control"><?php echo htmlspecialchars($user['email']); ?></p>
    </div>
    <div class="mb-3">
        <label class="form-label">Rôle</label>
        <p class="form-control"><?php echo htmlspecialchars($user['role']); ?></p>
    </div>
    <a href="profiledit.php" class="btn btn-primary w-100">Modifier mon profil</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
