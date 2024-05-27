<?php
include '../../includes/connect.php';
session_start();

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

// Fetch existing teams
try {
    $teamsStmt = $pdo->query("SELECT DISTINCT team_name FROM User_Team");
    $existingTeams = $teamsStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $budget = $_POST['budget'];
    $color = $_POST['color'];
    $team_option = $_POST['team_option'];
    $team_name = isset($_POST['team_name']) ? $_POST['team_name'] : '';
    $new_team_name = $_POST['new_team_name'];
    $emails = isset($_POST['emails']) ? $_POST['emails'] : [];
    $posts = isset($_POST['posts']) ? $_POST['posts'] : [];

    // Handle file upload
    $uploadDir = 'upload/';
    $cahierCharge = $_FILES['cahier_charge'];
    $cahierChargePath = '';

    if ($cahierCharge['error'] === UPLOAD_ERR_OK) {
        $cahierChargeName = basename($cahierCharge['name']);
        $cahierChargePath = $uploadDir . $cahierChargeName;
        if (!move_uploaded_file($cahierCharge['tmp_name'], $cahierChargePath)) {
            echo "Erreur lors du téléversement du fichier.";
            exit;
        }
    } else {
        echo "Erreur lors du téléversement du fichier.";
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Insérer le projet
        $stmt = $pdo->prepare("INSERT INTO Projects (title, description, start_date, end_date, budget, color, cahier_charge, manager_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $start_date, $end_date, $budget, $color, $cahierChargePath, $user_id]);
        $project_id = $pdo->lastInsertId();

        if ($team_option == 'existing' && $team_name) {
            // Utiliser une équipe existante et lier les membres existants au nouveau projet
            $stmt = $pdo->prepare("INSERT INTO User_Team (team_name, project_id, user_id, post) 
                                   SELECT team_name, ?, user_id, post FROM User_Team 
                                   WHERE team_name = ?");
            $stmt->execute([$project_id, $team_name]);
        } elseif ($team_option == 'new' && $new_team_name) {
            // Créer une nouvelle équipe
            for ($i = 0; $i < count($emails); $i++) {
                $email = $emails[$i];
                $post = $posts[$i];

                // Récupérer l'ID de l'utilisateur par email
                $userStmt = $pdo->prepare("SELECT id FROM Users WHERE email = ?");
                $userStmt->execute([$email]);
                $user = $userStmt->fetch();

                if ($user) {
                    $stmt = $pdo->prepare("INSERT INTO User_Team (team_name, project_id, user_id, post) 
                                           VALUES (?, ?, ?, ?)");
                    $stmt->execute([$new_team_name, $project_id, $user['id'], $post]);
                }
            }
        }

        $pdo->commit();
        header("Location: ../index.php?selected_project=" . $project_id);
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un Projet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Créer un Projet</h1>
    <form method="POST" action="create.php" id="projectForm" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Titre du Projet</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Date de Début</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Date de Fin</label>
            <input type="date" class="form-control" id="end_date" name="end_date" required>
        </div>
        <div class="mb-3">
            <label for="budget" class="form-label">Budget</label>
            <input type="number" class="form-control" id="budget" name="budget" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="color" class="form-label">Couleur du Projet</label>
            <input type="color" class="form-control" id="color" name="color" required>
        </div>
        <div class="mb-3">
            <label for="cahier_charge" class="form-label">Cahier des Charges</label>
            <input type="file" class="form-control" id="cahier_charge" name="cahier_charge" accept=".pdf,.doc,.docx" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Option d'équipe</label>
            <div>
                <input type="radio" id="existingTeamOption" name="team_option" value="existing" required>
                <label for="existingTeamOption">Utiliser une équipe existante</label>
            </div>
            <div>
                <input type="radio" id="newTeamOption" name="team_option" value="new" required>
                <label for="newTeamOption">Créer une nouvelle équipe</label>
            </div>
        </div>
        <div id="existingTeamSection" style="display:none;">
            <div class="mb-3">
                <label class="form-label">Nom de l'Équipe</label>
                <select class="form-select" id="team_name" name="team_name">
                    <option value="">Sélectionnez une équipe</option>
                    <?php foreach ($existingTeams as $team): ?>
                        <option value="<?php echo htmlspecialchars($team); ?>"><?php echo htmlspecialchars($team); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div id="newTeamSection" style="display:none;">
            <div class="mb-3">
                <label class="form-label">Nom de la Nouvelle Équipe</label>
                <input type="text" class="form-control" id="new_team_name" name="new_team_name">
            </div>
            <h4>Ajouter des Membres à la Nouvelle Équipe</h4>
            <div id="teamMembers">
                <div class="mb-3">
                    <label class="form-label">Email du Membre</label>
                    <input type="email" class="form-control email-input" name="emails[]" required>
                    <label class="form-label">Poste</label>
                    <input type="text" class="form-control" name="posts[]" required>
                </div>
            </div>
            <button type="button" class="btn btn-secondary" id="addMember">Ajouter un Membre</button>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary">Créer le Projet</button>
        </div>
    </form>
</div>

<script>
document.getElementById('existingTeamOption').addEventListener('click', function() {
    document.getElementById('existingTeamSection').style.display = 'block';
    document.getElementById('newTeamSection').style.display = 'none';
});

document.getElementById('newTeamOption').addEventListener('click', function() {
    document.getElementById('existingTeamSection').style.display = 'none';
    document.getElementById('newTeamSection').style.display = 'block';
});

document.getElementById('addMember').addEventListener('click', function() {
    var memberDiv = document.createElement('div');
    memberDiv.classList.add('mb-3');
    memberDiv.innerHTML = `
        <label class="form-label">Email du Membre</label>
        <input type="email" class="form-control email-input" name="emails[]" required>
        <label class="form-label">Poste</label>
        <input type="text" class="form-control" name="posts[]" required>
    `;
    document.getElementById('teamMembers').appendChild(memberDiv);
});
</script>
</body>
</html>
