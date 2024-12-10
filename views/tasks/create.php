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
        $priority = $_POST['priority'];
        $assignees = $_POST['assignees']; // Tableau d'IDs sélectionnés
        $selected_project_id = $project_id ?: $_POST['project_id'];

        // Convertir le tableau d'IDs en chaîne séparée par des virgules
        $assignee_ids = implode(',', $assignees);

        $pdo->beginTransaction();
        try {
            // Insérer la tâche dans la table Tasks
            $stmt = $pdo->prepare("INSERT INTO Tasks (title, description, status, start_date, end_date, priority, project_id, assignee_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $status, $start_date, $end_date, $priority, $selected_project_id, $assignee_ids]);

            // Récupérer l'ID de la tâche nouvellement créée
            $task_id = $pdo->lastInsertId();

            // Ajouter une notification pour chaque utilisateur assigné
            foreach ($assignees as $assignee_id) {
                $message = "Nouvelle tâche assignée : '$title'. À terminer avant le $end_date.";
                $notificationStmt = $pdo->prepare("INSERT INTO Notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
                $notificationStmt->execute([$assignee_id, $message]);
            }

            $pdo->commit();
            header("Location: view.php?project_id=" . $selected_project_id);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Erreur lors de la création de la tâche : " . $e->getMessage();
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
            <label for="assignees" class="form-label">Assigner à</label>
            <select class="form-select" id="assignees" name="assignees[]" multiple required>
                <?php
                $membersStmt = $pdo->prepare("
                    SELECT Users.id, Users.nom, Users.prenom 
                    FROM Users 
                    JOIN User_Team ON Users.id = User_Team.user_id 
                    WHERE User_Team.project_id = ?
                ");
                $project_id_to_query = $project_id ?: ($projects[0]['id'] ?? null);
                if ($project_id_to_query) {
                    $membersStmt->execute([$project_id_to_query]);
                    $members = $membersStmt->fetchAll();
                    foreach ($members as $member) {
                        echo '<option value="' . $member['id'] . '">' . htmlspecialchars($member['prenom']) . ' ' . htmlspecialchars($member['nom']) . '</option>';
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
