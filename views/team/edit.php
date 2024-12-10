<?php
// Inclusion des fichiers nécessaires pour la connexion à la base de données et la barre de navigation
include '../../includes/connect.php'; // Connexion à la base de données
include '../../includes/navbar.php'; // Affichage de la barre de navigation

// Vérification si un utilisateur est connecté via la session
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php'); // Redirige vers la page de login si l'utilisateur n'est pas connecté
    exit;
}

// Récupération de l'ID de l'utilisateur connecté depuis la session
$user_id = $_SESSION['user_id'];
// Requête pour obtenir le rôle de l'utilisateur
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérification du rôle de l'utilisateur. Si ce n'est pas un administrateur, redirection vers le tableau de bord.
if ($user['role'] !== 'admin') {
    header('Location: ../../dashboard.php'); // Redirige si l'utilisateur n'est pas un administrateur
    exit;
}

// Vérification si un nom d'équipe est passé en paramètre dans l'URL
if (isset($_GET['team_name'])) {
    $team_name = $_GET['team_name'];
    // Récupère les membres de l'équipe correspondant au nom de l'équipe
    $stmt = $pdo->prepare("SELECT * FROM user_team WHERE team_name = ?");
    $stmt->execute([$team_name]);
    $team = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si l'équipe n'existe pas, redirection vers le tableau de bord admin
    if (!$team) {
        header('Location: ../../dashboard_admin.php');
        exit;
    }
} else {
    // Si aucun nom d'équipe n'est fourni, redirection vers le tableau de bord admin
    header('Location: ../../dashboard_admin.php');
    exit;
}

// Variable pour afficher des messages de succès ou d'erreur
$message = '';

// Traitement du formulaire de mise à jour de l'équipe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère les informations du formulaire
    $new_team_name = htmlspecialchars($_POST['team_name']);
    $members = $_POST['members'];

    // Début de la transaction pour s'assurer que toutes les opérations réussissent
    $pdo->beginTransaction();
    try {
        // Mise à jour du nom de l'équipe dans la table user_team
        $stmt = $pdo->prepare("UPDATE user_team SET team_name = ? WHERE team_name = ?");
        $stmt->execute([$new_team_name, $team_name]);

        // Suppression des anciens membres de l'équipe
        $stmt = $pdo->prepare("DELETE FROM user_team WHERE team_name = ?");
        $stmt->execute([$new_team_name]);

        // Insertion des nouveaux membres dans la table user_team
        $stmt = $pdo->prepare("INSERT INTO user_team (team_name, project_id, user_id, post) VALUES (?, NULL, ?, ?)");
        foreach ($members as $member) {
            if (isset($member['user_id']) && isset($member['post'])) {
                $stmt->execute([$new_team_name, $member['user_id'], $member['post']]);
            }
        }

        // Validation de la transaction si tout a bien fonctionné
        $pdo->commit();
        $message = 'Équipe mise à jour avec succès';
        // Redirige vers le tableau de bord admin après mise à jour
        header("Location: ../../dashboard_admin.php");
        exit;
    } catch (Exception $e) {
        // Si une erreur se produit, on annule la transaction
        $pdo->rollBack();
        $message = 'Erreur lors de la mise à jour de l\'équipe: ' . $e->getMessage();
    }
}

// Récupération de la liste de tous les utilisateurs pour les afficher dans les champs de sélection
$all_users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Équipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Inclusion de Bootstrap -->
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
    <h1 class="text-center mb-4">Modifier Équipe</h1>
    <!-- Affichage du message de succès ou d'erreur -->
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post">
        <!-- Champ pour le nom de l'équipe -->
        <div class="mb-3">
            <label for="team_name" class="form-label">Nom de l'équipe</label>
            <input type="text" class="form-control" id="team_name" name="team_name" value="<?php echo htmlspecialchars($team_name); ?>" required>
        </div>
        <h3>Membres</h3>
        <div id="members">
            <!-- Affichage des membres existants de l'équipe -->
            <?php foreach ($team as $index => $member): ?>
                <div class="member mb-3">
                    <select class="form-select mb-2" name="members[<?php echo $index; ?>][user_id]" required>
                        <?php foreach ($all_users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php if ($user['id'] == $member['user_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" class="form-control mb-2" name="members[<?php echo $index; ?>][post]" value="<?php echo htmlspecialchars($member['post']); ?>" required>
                    <button type="button" class="btn btn-danger btn-sm remove-member">Supprimer</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" id="add-member">Ajouter Membre</button>
        <button type="submit" class="btn btn-primary w-100 mt-3">Mettre à jour</button>
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
<?php include '../../includes/footer.php' ?>

</body>
</html>
