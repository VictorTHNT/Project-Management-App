<?php
include '../../includes/connect.php'; // Ajustez le chemin d'accès ici*
include '../../includes/navbar.php'; 


if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['role'] !== 'admin') {
    header('Location: ../../dashboard.php');
    exit;
}

if (isset($_GET['user_id'])) {
    $edit_user_id = $_GET['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$edit_user_id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$edit_user) {
        header('Location: ../../dashboard_admin.php');
        exit;
    }
} else {
    header('Location: ../../dashboard_admin.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $profile_image = $_FILES['profile_image'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $activation = $_POST['activation'];

    // Gérer l'upload de l'image
    if ($profile_image['name']) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($profile_image['type'], $allowed_types)) {
            $target_dir = "../../assets/images/";
            $target_file = $target_dir . basename($profile_image["name"]);
            if (move_uploaded_file($profile_image["tmp_name"], $target_file)) {
                $profile_image_path = "assets/images/" . basename($profile_image["name"]);
            } else {
                $message = 'Erreur lors du téléchargement de l\'image.';
            }
        } else {
            $message = 'Type de fichier non autorisé. Veuillez télécharger une image.';
        }
    } else {
        // Si aucune nouvelle image n'est téléchargée, conserver l'ancienne
        $profile_image_path = $edit_user['profile'];
    }

    // Vérification et mise à jour du mot de passe
    if (!empty($new_password) && !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            $message = 'Les nouveaux mots de passe ne correspondent pas.';
        } else {
            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$new_password_hashed, $edit_user_id]);
            $message = 'Mot de passe mis à jour avec succès.';
        }
    }

    // Mettre à jour les informations de l'utilisateur dans la base de données
    if (empty($message)) {
        $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, profile = ?, role = ?, activation = ? WHERE id = ?");
        if ($stmt->execute([$nom, $prenom, $email, $profile_image_path, $role, $activation, $edit_user_id])) {
            $message = 'Profil mis à jour avec succès';
        } else {
            $message = 'Erreur lors de la mise à jour du profil';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 600px;
            margin-top: 50px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
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
    <h1 class="text-center mb-4">Modifier Utilisateur</h1>
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    <div class="text-center">
        <?php if ($edit_user['profile']): ?>
            <img src="../../<?php echo htmlspecialchars($edit_user['profile']); ?>" alt="Profile Image" class="profile-photo img-thumbnail">
        <?php endif; ?>
    </div>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($edit_user['nom']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($edit_user['prenom']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="profile_image" class="form-label">Photo de Profil</label>
            <input type="file" class="form-control" id="profile_image" name="profile_image">
        </div>
        <hr>
        <div class="mb-3">
            <label for="new_password" class="form-label">Nouveau Mot de Passe</label>
            <input type="password" class="form-control" id="new_password" name="new_password">
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirmer le Nouveau Mot de Passe</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Rôle</label>
            <select class="form-select" id="role" name="role">
                <option value="admin" <?php if ($edit_user['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                <option value="manager" <?php if ($edit_user['role'] === 'manager') echo 'selected'; ?>>Manager</option>
                <option value="member" <?php if ($edit_user['role'] === 'member') echo 'selected'; ?>>Membre</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="activation" class="form-label">Activation</label>
            <select class="form-select" id="activation" name="activation">
                <option value="oui" <?php if ($edit_user['activation'] === 'oui') echo 'selected'; ?>>Oui</option>
                <option value="non" <?php if ($edit_user['activation'] === 'non') echo 'selected'; ?>>Non</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Mettre à jour</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../../includes/footer.php' ?>

</body>
</html>
