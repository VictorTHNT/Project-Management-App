<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $profile_image = $_FILES['profile_image'];

    // Gérer l'upload de l'image
    if ($profile_image['name']) {
        $target_dir = "../../assets/images/";
        $target_file = $target_dir . basename($profile_image["name"]);
        move_uploaded_file($profile_image["tmp_name"], $target_file);
        $profile_image_path = "assets/images/" . basename($profile_image["name"]);
    } else {
        // Si aucune nouvelle image n'est téléchargée, conserver l'ancienne
        $profile_image_path = $_POST['current_profile_image'];
    }

    // Mettre à jour les informations de l'utilisateur dans la base de données
    $stmt = $pdo->prepare("UPDATE Users SET nom = ?, prenom = ?, email = ?, profile = ? WHERE id = ?");
    if ($stmt->execute([$nom, $prenom, $email, $profile_image_path, $user_id])) {
        $message = 'Profil mis à jour avec succès';
        $_SESSION['user_name'] = $nom . ' ' . $prenom;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_profile'] = $profile_image_path;
    } else {
        $message = 'Erreur lors de la mise à jour du profil';
    }
}

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM Users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<div class="container mt-5">
    <h1>Modifier Profil</h1>
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="profile_image" class="form-label">Photo de Profil</label>
            <input type="file" class="form-control" id="profile_image" name="profile_image">
            <input type="hidden" name="current_profile_image" value="<?php echo htmlspecialchars($user['profile']); ?>">
            <?php if ($user['profile']): ?>
                <img src="../../<?php echo htmlspecialchars($user['profile']); ?>" alt="Profile Image" class="img-thumbnail mt-2" width="150">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
