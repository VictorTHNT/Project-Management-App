<?php
include '../../includes/connect.php';
include '../../includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : null;

try {
    // Récupérer les projets associés à l'utilisateur connecté
    $projectsStmt = $pdo->prepare("
        SELECT Projects.id, Projects.title 
        FROM Projects 
        JOIN User_Team ON Projects.id = User_Team.project_id 
        WHERE User_Team.user_id = ?
    ");
    $projectsStmt->execute([$user_id]);
    $projects = $projectsStmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $assignee_id = $_POST['assignee_id'];
        $selected_project_id = $project_id ?: $_POST['project_id'];

        $pdo->beginTransaction();
        
        try {
            $stmt = $pdo->prepare("INSERT INTO Tasks (title, description, status, start_date, end_date, priority, project_id, assignee_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $status, $start_date, $end_date, $_POST['priority'], $selected_project_id, $assignee_id]);
            // Récupérer l'ID de la tâche créée
            $task_id = $pdo->lastInsertId();

            // Ajouter une notification pour chaque membre du projet
            $teamStmt = $pdo->prepare("SELECT user_id FROM User_Team WHERE project_id = ?");
            $teamStmt->execute([$selected_project_id]);
            $teamMembers = $teamStmt->fetchAll();

            
            $pdo->commit();
            
            header("Location: view.php?project_id=" . $selected_project_id);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer une Tâche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Créer une Tâche</h1>
    <form method="POST" action="">
        <?php if (!$project_id): ?>
            <div class="mb-3">
                <label for="project_id" class="form-label">Projet</label>
                <select class="form-select" id="project_id" name="project_id" required>
                    <option value="">Sélectionnez un projet</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="mb-3">
            <label for="title" class="form-label">Titre</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Statut</label>
            <select class="form-select" id="status" name="status" required>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Date de début</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Date de fin</label>
            <input type="date" class="form-control" id="end_date" name="end_date" required>
        </div>
        <div class="mb-3">
            <label for="assignee_id" class="form-label">Assigner à</label>
            <select class="form-select" id="assignee_id" name="assignee_id" required>
                <?php
                // Récupérer les membres du projet sélectionné ou de tous les projets auxquels l'utilisateur est affecté
                $membersStmt = $pdo->prepare("
                    SELECT Users.id, Users.nom, Users.prenom 
                    FROM Users 
                    JOIN User_Team ON Users.id = User_Team.user_id 
                    WHERE User_Team.project_id = ?
                ");
                if ($project_id) {
                    // Récupérer les membres du projet spécifique
                    $membersStmt->execute([$project_id]);
                    $members = $membersStmt->fetchAll();
                    foreach ($members as $member) {
                        echo '<option value="' . $member['id'] . '">' . htmlspecialchars($member['prenom']) . ' ' . htmlspecialchars($member['nom']) . '</option>';
                    }
                } else {
                    // Récupérer les membres de tous les projets de l'utilisateur
                    foreach ($projects as $project) {
                        $membersStmt->execute([$project['id']]);
                        $members = $membersStmt->fetchAll();
                        foreach ($members as $member) {
                            echo '<option value="' . $member['id'] . '">' . htmlspecialchars($member['prenom']) . ' ' . htmlspecialchars($member['nom']) . '</option>';
                        }
                    }
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="priority" class="form-label">Priorité</label>
            <select class="form-select" id="priority" name="priority" required>
                <option value="faible">Faible</option>
                <option value="modéré">Modéré</option>
                <option value="élevé">Élevé</option>
            </select>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-success">Créer</button>
        </div>
    </form>
</div>
<?php include '../../includes/footer.php'; ?>
</body>
</html>
