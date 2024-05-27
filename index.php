<?php

session_start(); // Démarrer la session

include 'includes/connect.php';
include 'includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Gestion de Projet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .welcome-message {
            margin-top: 20px;
        }
        .user-links a {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
    <?php if (isset($_SESSION['nom']) && isset($_SESSION['prenom'])): ?>
            <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['nom'] . ' ' . $_SESSION['prenom']); ?>!</h1>
            <div class="welcome-message">
                <p>Ravi de vous revoir dans l'application de gestion de projet.</p>
                <div class="user-links">
                    <a href="./includes/logout.php" class="btn btn-primary">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <h1>Bienvenue dans l'application de gestion de projet</h1>
            <div class="user-links">
                <a href="./views/auth/login.php" >Login</a>
                <a href="./views/auth/register.php">Register</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
