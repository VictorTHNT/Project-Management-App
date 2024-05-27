<?php
session_start();
require_once '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$event_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM calendar_events WHERE id = ? AND user_id = ?");
$stmt->execute([$event_id, $user_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die('Événement non trouvé ou accès non autorisé.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $is_shared = isset($_POST['is_shared']) ? 1 : 0;
    $shared_with_users = $is_shared && !empty($_POST['shared_with_users']) ? implode(',', $_POST['shared_with_users']) : NULL;
    $shared_with_groups = $is_shared && !empty($_POST['shared_with_groups']) ? implode(',', $_POST['shared_with_groups']) : NULL;

    $stmt = $pdo->prepare("UPDATE calendar_events SET title = ?, start_date = ?, end_date = ?, is_shared = ?, shared_with_users = ?, shared_with_groups = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$title, $start_date, $end_date, $is_shared, $shared_with_users, $shared_with_groups, $event_id, $user_id]);

    header('Location: view.php');
    exit;
}

$stmt = $pdo->query("SELECT id, nom, prenom FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT DISTINCT team_name FROM user_team");
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un Événement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Modifier un Événement</h2>
    <form method="POST" action="edit.php?id=<?php echo htmlspecialchars($event['id']); ?>">
        <div class="mb-3">
            <label for="title" class="form-label">Titre</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Date de Début</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($event['start_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Date de Fin</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($event['end_date']); ?>" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_shared" name="is_shared" <?php echo $event['is_shared'] ? 'checked' : ''; ?>>
            <label class="form-check-label" for="is_shared">Partager cet événement</label>
        </div>
        <div id="shareOptions" style="<?php echo $event['is_shared'] ? 'display: block;' : 'display: none;'; ?>">
            <div class="mb-3">
                <label for="shared_with_users" class="form-label">Partager avec des utilisateurs</label>
                <select multiple class="form-control" id="shared_with_users" name="shared_with_users[]">
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['id']); ?>" <?php echo in_array($user['id'], explode(',', $event['shared_with_users'])) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="shared_with_groups" class="form-label">Partager avec des groupes</label>
                <select multiple class="form-control" id="shared_with_groups" name="shared_with_groups[]">
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo htmlspecialchars($group['team_name']); ?>" <?php echo in_array($group['team_name'], explode(',', $event['shared_with_groups'])) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($group['team_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Modifier</button>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
