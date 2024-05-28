<?php
include '../../includes/connect.php';
include '../../includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $start_date = $_POST['start_date'] . ' ' . $_POST['start_time'];
    $end_date = $_POST['end_date'] . ' ' . $_POST['end_time'];
    $is_shared = isset($_POST['is_shared']) ? 1 : 0;
    $shared_with_users = $is_shared && !empty($_POST['shared_with_users']) ? implode(',', $_POST['shared_with_users']) : NULL;
    $shared_with_groups = $is_shared && !empty($_POST['shared_with_groups']) ? implode(',', $_POST['shared_with_groups']) : NULL;

    // Vérifier si un project_id est fourni
    $project_id = !empty($_POST['project_id']) ? $_POST['project_id'] : NULL;

    try {
        $stmt = $pdo->prepare("INSERT INTO calendar_events (project_id, user_id, title, start_date, end_date, is_shared, shared_with_users, shared_with_groups) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$project_id, $user_id, $title, $start_date, $end_date, $is_shared, $shared_with_users, $shared_with_groups]);

        header('Location: view.php');
        exit;
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}

// Récupérer la liste des projets pour l'utilisateur
$projectsStmt = $pdo->prepare("SELECT id, title FROM projects WHERE manager_id = ?");
$projectsStmt->execute([$user_id]);
$projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT id, nom, prenom FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT DISTINCT team_name FROM user_team");
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Événement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Ajouter un Événement</h2>
    <form method="POST" action="create.php">
        <div class="mb-3">
            <label for="title" class="form-label">Titre</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Date de Début</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>
        <div class="mb-3">
            <label for="start_time" class="form-label">Heure de Début</label>
            <input type="time" class="form-control" id="start_time" name="start_time" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Date de Fin</label>
            <input type="date" class="form-control" id="end_date" name="end_date" required>
        </div>
        <div class="mb-3">
            <label for="end_time" class="form-label">Heure de Fin</label>
            <input type="time" class="form-control" id="end_time" name="end_time" required>
        </div>
        <div class="mb-3">
            <label for="project_id" class="form-label">Projet associé (optionnel)</label>
            <select class="form-control" id="project_id" name="project_id">
                <option value="">Aucun</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?php echo htmlspecialchars($project['id']); ?>"><?php echo htmlspecialchars($project['title']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_shared" name="is_shared">
            <label class="form-check-label" for="is_shared">Partager cet événement</label>
        </div>
        <div id="shareOptions" style="display: none;">
            <div class="mb-3">
                <label for="shared_with_users" class="form-label">Partager avec des utilisateurs</label>
                <select multiple class="form-control" id="shared_with_users" name="shared_with_users[]">
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['id']); ?>"><?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="shared_with_groups" class="form-label">Partager avec des groupes</label>
                <select multiple class="form-control" id="shared_with_groups" name="shared_with_groups[]">
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo htmlspecialchars($group['team_name']); ?>"><?php echo htmlspecialchars($group['team_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Ajouter</button>
    </form>
</div>

<script>
document.getElementById('is_shared').addEventListener('change', function() {
    var shareOptions = document.getElementById('shareOptions');
    if (this.checked) {
        shareOptions.style.display = 'block';
    } else {
        shareOptions.style.display = 'none';
    }
});
</script>
<?php include '../../includes/footer.php' ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
