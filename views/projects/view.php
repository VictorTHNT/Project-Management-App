<?php
include '../../includes/connect.php';
include '../../includes/navbar.php';
//session_start();

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

try {
    if ($user_role === 'admin') {
        // Récupérer tous les projets pour les administrateurs
        $projectsStmt = $pdo->query("SELECT id, title, description, end_date FROM Projects");
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
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Voir les Projets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <script>
        function getSelectedProject() {
            const selected = document.querySelector('input[name="selected_project"]:checked');
            if (selected) {
                window.location.href = `../index.php?selected_project=${selected.value}`;
            } else {
                alert('Veuillez sélectionner un projet.');
            }
        }
    </script>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Liste des Projets</h1>
    <div class="d-flex justify-content-end mb-4">
        <a href="create.php" class="btn btn-primary me-2">
            <i class="bi bi-plus"></i> Ajouter
        </a>
    </div>
    <form>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Sélectionner</th>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Date de fin</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td>
                            <input type="radio" name="selected_project" value="<?php echo $project['id']; ?>">
                        </td>
                        <td><?php echo htmlspecialchars($project['title']); ?></td>
                        <td><?php echo htmlspecialchars($project['description']); ?></td>
                        <td><?php echo htmlspecialchars($project['end_date']); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $project['id']; ?>" class="btn btn-outline-secondary btn-sm me-2">Editer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-primary" onclick="getSelectedProject()">Entrer</button>
        </div>
    </form>
</div>
<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
