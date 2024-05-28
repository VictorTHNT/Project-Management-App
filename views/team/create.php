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
    $team_name = htmlspecialchars($_POST['team_name']);
    $members = $_POST['members'];

    $pdo->beginTransaction();
    try {
        foreach ($members as $member) {
            $stmt = $pdo->prepare("INSERT INTO user_team (team_name, project_id, user_id, post) VALUES (?, NULL, ?, ?)");
            $stmt->execute([$team_name, $member['user_id'], $member['post']]);
        }

        $pdo->commit();
        $message = 'Équipe créée avec succès';
        header("Location: ../../dashboard_admin.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = 'Erreur lors de la création de l\'équipe: ' . $e->getMessage();
    }
}

$all_users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer Équipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 600px;
            margin-top: 50px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
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
    <h1 class="text-center mb-4">Créer Équipe</h1>
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="team_name" class="form-label">Nom de l'équipe</label>
            <input type="text" class="form-control" id="team_name" name="team_name" required>
        </div>
        <h3>Membres</h3>
        <div id="members">
            <div class="member mb-3">
                <select class="form-select mb-2" name="members[0][user_id]" required>
                    <?php foreach ($all_users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" class="form-control mb-2" name="members[0][post]" placeholder="Poste" required>
                <button type="button" class="btn btn-danger btn-sm remove-member">Supprimer</button>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" id="add-member">Ajouter Membre</button>
        <button type="submit" class="btn btn-primary w-100 mt-3">Créer</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('add-member').addEventListener('click', function() {
    var membersDiv = document.getElementById('members');
    var memberCount = membersDiv.getElementsByClassName('member').length;
    
    var memberDiv = document.createElement('div');
    memberDiv.classList.add('member', 'mb-3');

    var selectUser = document.createElement('select');
    selectUser.classList.add('form-select', 'mb-2');
    selectUser.name = 'members[' + memberCount + '][user_id]';
    selectUser.required = true;
    <?php foreach ($all_users as $user): ?>
    var option = document.createElement('option');
    option.value = '<?php echo $user['id']; ?>';
    option.text = '<?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?>';
    selectUser.appendChild(option);
    <?php endforeach; ?>

    var inputPost = document.createElement('input');
    inputPost.type = 'text';
    inputPost.classList.add('form-control', 'mb-2');
    inputPost.name = 'members[' + memberCount + '][post]';
    inputPost.required = true;

    var removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.classList.add('btn', 'btn-danger', 'btn-sm', 'remove-member');
    removeButton.textContent = 'Supprimer';
    removeButton.addEventListener('click', function() {
        memberDiv.remove();
    });

    memberDiv.appendChild(selectUser);
    memberDiv.appendChild(inputPost);
    memberDiv.appendChild(removeButton);

    membersDiv.appendChild(memberDiv);
});

document.querySelectorAll('.remove-member').forEach(function(button) {
    button.addEventListener('click', function() {
        button.parentElement.remove();
    });
});
</script>
<?php include '../../includes/footer.php'; ?>
</body>
</html>
