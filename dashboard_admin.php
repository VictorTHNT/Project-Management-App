<?php
// Inclusion des fichiers nécessaires
include 'includes/connect.php'; // Connexion à la base de données
include 'includes/navbar.php';  // Barre de navigation

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header('Location: views/auth/login.php'); // Si l'utilisateur n'est pas connecté, redirection vers la page de connexion
    exit;
}

$user_id = $_SESSION['user_id']; // Récupération de l'ID de l'utilisateur
// Requête pour récupérer le rôle de l'utilisateur dans la base de données
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l'utilisateur n'est pas un administrateur, il est redirigé
if ($user['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Activation d'un utilisateur
if (isset($_GET['action']) && $_GET['action'] === 'activate' && isset($_GET['user_id'])) {
    $activate_user_id = $_GET['user_id'];
    $stmt = $pdo->prepare("UPDATE users SET activation = 'oui' WHERE id = ?");
    if ($stmt->execute([$activate_user_id])) {
        $activation_message = 'Utilisateur activé avec succès.';
    } else {
        $activation_message = 'Erreur lors de l\'activation de l\'utilisateur.';
    }
}

// Suppression d'un utilisateur
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['user_id'])) {
    $delete_user_id = $_GET['user_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$delete_user_id])) {
        $deletion_message = 'Utilisateur supprimé avec succès.';
    } else {
        $deletion_message = 'Erreur lors de la suppression de l\'utilisateur.';
    }
}

// Suppression d'un groupe
if (isset($_GET['action']) && $_GET['action'] === 'delete_group' && isset($_GET['team_name'])) {
    $team_name = $_GET['team_name'];
    $stmt = $pdo->prepare("DELETE FROM user_team WHERE team_name = ?");
    if ($stmt->execute([$team_name])) {
        $deletion_message = 'Équipe supprimée avec succès.';
    } else {
        $deletion_message = 'Erreur lors de la suppression de l\'équipe.';
    }
}

// Récupération des utilisateurs et groupes pour gestion
$usersStmt = $pdo->query("SELECT * FROM users");
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

$groupsStmt = $pdo->query("SELECT DISTINCT team_name FROM user_team");
$groups = $groupsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord Admin</title>
    <!-- Inclusion de Bootstrap pour le style -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Tableau de Bord Admin</h1>

    <!-- Boutons pour ajouter un utilisateur ou une équipe -->
    <div class="mb-4 text-end">
        <a href="views/users/create.php" class="btn btn-success btn-sm">+ Ajouter Utilisateur</a>
        <a href="views/team/create.php" class="btn btn-success btn-sm">+ Ajouter Équipe</a>
    </div>

    <!-- Affichage des messages d'activation ou de suppression -->
    <?php if (isset($activation_message)): ?>
        <div class="alert alert-info"><?php echo $activation_message; ?></div>
    <?php endif; ?>

    <?php if (isset($deletion_message)): ?>
        <div class="alert alert-info"><?php echo $deletion_message; ?></div>
    <?php endif; ?>

    <!-- Gestion des utilisateurs -->
    <h2>Gérer les Utilisateurs</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Activation</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['nom']); ?></td>
                    <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo htmlspecialchars($user['activation']); ?></td>
                    <td>
                        <!-- Liens pour modifier ou supprimer l'utilisateur -->
                        <a href="views/users/edit.php?user_id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <a href="dashboard_admin.php?action=delete&user_id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                        <?php if ($user['activation'] === 'non'): ?>
                            <a href="dashboard_admin.php?action=activate&user_id=<?php echo $user['id']; ?>" class="btn btn-success btn-sm">Activer</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Gestion des groupes -->
    <h2>Gérer les Groupes</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nom du groupe</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($groups as $group): ?>
                <tr>
                    <td><?php echo htmlspecialchars($group['team_name']); ?></td>
                    <td>
                        <!-- Liens pour modifier ou supprimer un groupe -->
                        <a href="views/team/edit.php?team_name=<?php echo urlencode($group['team_name']); ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <a href="dashboard_admin.php?action=delete_group&team_name=<?php echo urlencode($group['team_name']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Tableau pour activer les nouveaux comptes -->
    <h2>Activer les Nouveaux Comptes</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <?php if ($user['activation'] === 'non'): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['nom']); ?></td>
                        <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <a href="dashboard_admin.php?action=activate&user_id=<?php echo $user['id']; ?>" class="btn btn-success btn-sm">Activer</a>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
<!-- Inclusion de Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
