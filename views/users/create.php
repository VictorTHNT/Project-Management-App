<?php
include '../../includes/connect.php'; // Ajustez le chemin d'accès ici
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

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $profile_image = $_FILES['profile_image'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $activation = 'oui';

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
        $profile_image_path = 'assets/images/default.png'; // image par défaut si aucune image n'est téléchargée
    }

    // Vérification et hachage du mot de passe
    if (!empty($password) && !empty($confirm_password)) {
        if ($password !== $confirm_password) {
            $message = 'Les mots de passe ne correspondent pas.';
        } else {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, profile, role, activation) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$nom, $prenom, $email, $password_hashed, $profile_image_path, $role, $activation])) {
                $message = 'Utilisateur créé avec succès';
            } else {
                $message = 'Erreur lors de la création de l\'utilisateur';
            }
        }
    } else {
        $message = 'Veuillez entrer un mot de passe.';
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer Utilisateur</title>
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
    <h1 class="text-center mb-4">Créer Utilisateur</h1>
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
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
            <label for="profile_image" class="form-label">Photo de Profil</label>
            <input type="file" class="form-control" id="profile_image" name="profile_image">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de Passe</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirmer le Mot de Passe</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Rôle</label>
            <select class="form-select" id="role" name="role">
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Créer</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../../includes/footer.php' ?>
</body>
</html>
