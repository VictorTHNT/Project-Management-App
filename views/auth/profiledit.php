<?php
include '../../includes/connect.php';
include '../../includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Vérifiez si 'user_role' est défini dans la session
if (isset($_SESSION['user_role'])) {
    $is_admin = $_SESSION['user_role'] === 'admin';
} else {
    $is_admin = false; // Par défaut, considérez que l'utilisateur n'est pas un administrateur
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $profile_image = $_FILES['profile_image'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

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
        $profile_image_path = $_POST['current_profile_image'];
    }

    // Vérification et mise à jour du mot de passe
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            $message = 'Les nouveaux mots de passe ne correspondent pas.';
        } else {
            $stmt = $pdo->prepare("SELECT password FROM Users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if (password_verify($current_password, $user['password'])) {
                $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE Users SET password = ? WHERE id = ?");
                $stmt->execute([$new_password_hashed, $user_id]);
                $message = 'Mot de passe mis à jour avec succès.';
            } else {
                $message = 'Le mot de passe actuel est incorrect.';
            }
        }
    }

    // Mettre à jour les informations de l'utilisateur dans la base de données
    if (empty($message)) {
        if ($is_admin) {
            $stmt = $pdo->prepare("UPDATE Users SET nom = ?, prenom = ?, email = ?, profile = ? WHERE id = ?");
            if ($stmt->execute([$nom, $prenom, $user['email'], $profile_image_path, $user_id])) {
                $message = 'Profil mis à jour avec succès';
                $_SESSION['user_name'] = $nom . ' ' . $prenom;
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_profile'] = $profile_image_path;
            } else {
                $message = 'Erreur lors de la mise à jour du profil';
            }
        } else {
            $stmt = $pdo->prepare("UPDATE Users SET nom = ?, prenom = ?, profile = ? WHERE id = ?");
            if ($stmt->execute([$nom, $prenom, $profile_image_path, $user_id])) {
                $message = 'Profil mis à jour avec succès';
                $_SESSION['user_name'] = $nom . ' ' . $prenom;
                $_SESSION['user_profile'] = $profile_image_path;
            } else {
                $message = 'Erreur lors de la mise à jour du profil';
            }
        }
    }
}

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM Users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
    <h1 class="text-center mb-4">Modifier Profil</h1>
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    <div class="text-center">
        <?php if ($user['profile']): ?>
            <img src="../../<?php echo htmlspecialchars($user['profile']); ?>" alt="Profile Image" class="profile-photo img-thumbnail">
        <?php endif; ?>
    </div>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
        </div>
        <!-- Champ email supprimé -->
        <div class="mb-3">
            <label for="profile_image" class="form-label">Photo de Profil</label>
            <input type="file" class="form-control" id="profile_image" name="profile_image">
            <input type="hidden" name="current_profile_image" value="<?php echo htmlspecialchars($user['profile']); ?>">
        </div>
        <hr>
        <div class="mb-3">
            <label for="current_password" class="form-label">Mot de Passe Actuel</label>
            <input type="password" class="form-control" id="current_password" name="current_password">
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label">Nouveau Mot de Passe</label>
            <input type="password" class="form-control" id="new_password" name="new_password">
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirmer le Nouveau Mot de Passe</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
        </div>
        <button type="submit" class="btn btn-primary w-100">Mettre à jour</button>
    </form>
</div>
<?php include '../../includes/footer.php' ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
