<?php
session_start();
include 'includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: views/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

try {
    if ($user_role === 'admin') {
        // Récupérer tous les projets pour les administrateurs
        $projectsStmt = $pdo->query("SELECT id, title, description, end_date FROM Projects");
        $usersStmt = $pdo->query("SELECT id, nom, prenom, email, role FROM Users");
    } else {
        // Récupérer les projets associés à l'utilisateur connecté
        $projectsStmt = $pdo->prepare("
            SELECT Projects.id, Projects.title, Projects.description, Projects.end_date 
            FROM Projects 
            JOIN User_Team ON Projects.id = User_Team.project_id 
            WHERE User_Team.user_id = ?
        ");
        $projectsStmt->execute([$user_id]);
    }
    $projects = $projectsStmt->fetchAll();
    $users = isset($usersStmt) ? $usersStmt->fetchAll() : [];
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">
    <h1 class="text-center">Dashboard</h1>

    <!-- Projects Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h5>Projets</h5>
        </div>
        <div class="card-body">
            <?php if (count($projects) > 0): ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Nom du projet</th>
                            <th scope="col">Description</th>
                            <th scope="col">Date de fin</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['title']); ?></td>
                                <td><?php echo htmlspecialchars($project['description']); ?></td>
                                <td><?php echo htmlspecialchars($project['end_date']); ?></td>
                                <td>
                                    <a href="views/index.php?project_id=<?php echo htmlspecialchars($project['id']); ?>" class="btn btn-info btn-sm">Voir</a>
                                    <?php if ($user_role === 'admin'): ?>
                                        <a href="views/projects/edit.php?id=<?php echo htmlspecialchars($project['id']); ?>" class="btn btn-warning btn-sm">Modifier</a>
                                        <form method="post" action="views/projects/delete.php" class="d-inline">
                                            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['id']); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?');">Supprimer</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    Vous n'êtes associé à aucun projet.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($user_role === 'admin'): ?>
        <!-- Users Section for Admins -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Utilisateurs</h5>
            </div>
            <div class="card-body">
                <?php if (count($users) > 0): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Nom</th>
                                <th scope="col">Prénom</th>
                                <th scope="col">Email</th>
                                <th scope="col">Rôle</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td>
                                        <a href="views/users/edit.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-warning btn-sm">Modifier</a>
                                        <form method="post" action="views/users/delete.php" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        Aucun utilisateur trouvé.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
