<?php
include '../../includes/connect.php';
include '../../includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $budget = $_POST['budget'];
    $color = $_POST['color'];

    $cahier_charge_path = null;
    if (isset($_FILES['cahier_charge']) && $_FILES['cahier_charge']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/project_files/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_tmp = $_FILES['cahier_charge']['tmp_name'];
        $file_name = basename($_FILES['cahier_charge']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $file_path)) {
            $cahier_charge_path = 'uploads/project_files/' . $file_name;
        } else {
            echo "Erreur lors du téléchargement du fichier.";
            exit;
        }
    }

    $team_option = $_POST['team_option'];
    $team_name = $_POST['team_name'];
    $members = $_POST['members'];

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("INSERT INTO projects (title, description, start_date, end_date, budget, color, cahier_charge, manager_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $start_date, $end_date, $budget, $color, $cahier_charge_path, $user_id]);
        $project_id = $pdo->lastInsertId();

        if ($team_option == 'new') {
            foreach ($members as $member) {
                $email = $member['email'];
                $role = $member['role'];

                $userStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $userStmt->execute([$email]);
                $user = $userStmt->fetch();

                if ($user) {
                    $user_id = $user['id'];
                    $teamStmt = $pdo->prepare("INSERT INTO user_team (team_name, user_id, project_id, post) VALUES (?, ?, ?, ?)");
                    $teamStmt->execute([$team_name, $user_id, $project_id, $role]);
                }
            }
        } elseif ($team_option == 'existing') {
            $existing_team_name = $_POST['existing_team_name'];
            $existingTeamStmt = $pdo->prepare("SELECT user_id, post FROM user_team WHERE team_name = ?");
            $existingTeamStmt->execute([$existing_team_name]);
            $existingTeamMembers = $existingTeamStmt->fetchAll();

            foreach ($existingTeamMembers as $existingMember) {
                $teamStmt = $pdo->prepare("INSERT INTO user_team (team_name, user_id, project_id, post) VALUES (?, ?, ?, ?)");
                $teamStmt->execute([$existing_team_name, $existingMember['user_id'], $project_id, $existingMember['post']]);
            }
        }

        $teamStmt = $pdo->prepare("SELECT user_id FROM user_team WHERE project_id = ?");
        $teamStmt->execute([$project_id]);
        $teamMembers = $teamStmt->fetchAll();

        foreach ($teamMembers as $member) {
            $notificationStmt = $pdo->prepare("INSERT INTO notifications (user_id, project_id, message) VALUES (?, ?, ?)");
            $notificationStmt->execute([$member['user_id'], $project_id, 'New project created: ' . $title]);
        }

        $pdo->commit();
        header("Location: ../index.php?selected_project=$project_id");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un projet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Créer un projet</h1>
    <form method="post" action="create.php" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Titre du projet</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
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
            <label for="budget" class="form-label">Budget</label>
            <input type="number" class="form-control" id="budget" name="budget" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="color" class="form-label">Couleur du projet</label>
            <input type="color" class="form-control" id="color" name="color" required>
        </div>
        <div class="mb-3">
            <label for="cahier_charge" class="form-label">Cahier des charges</label>
            <input type="file" class="form-control" id="cahier_charge" name="cahier_charge" accept=".pdf,.doc,.docx">
        </div>
        <div class="mb-3">
            <label class="form-label">Équipe</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="team_option" id="new_team_option" value="new" checked>
                <label class="form-check-label" for="new_team_option">Créer une nouvelle équipe</label>
            </div>
            <div id="new_team_fields">
                <div class="mb-3">
                    <label for="team_name" class="form-label">Nom de l'équipe</label>
                    <input type="text" class="form-control" id="team_name" name="team_name">
                </div>
                <div id="members_fields">
                    <div class="mb-3">
                        <label for="member_email_1" class="form-label">Email du membre</label>
                        <input type="email" class="form-control" id="member_email_1" name="members[0][email]">
                    </div>
                    <div class="mb-3">
                        <label for="member_role_1" class="form-label">Rôle du membre</label>
                        <input type="text" class="form-control" id="member_role_1" name="members[0][role]">
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" id="add_member">Ajouter un membre</button>
            </div>
            <div class="form-check mt-3">
                <input class="form-check-input" type="radio" name="team_option" id="existing_team_option" value="existing">
                <label class="form-check-label" for="existing_team_option">Utiliser une équipe existante</label>
            </div>
            <div id="existing_team_fields" style="display: none;">
                <div class="mb-3">
                    <label for="existing_team_name" class="form-label">Nom de l'équipe existante</label>
                    <input type="text" class="form-control" id="existing_team_name" name="existing_team_name">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
    </form>
</div>

<script>
document.getElementById('new_team_option').addEventListener('change', function() {
    document.getElementById('new_team_fields').style.display = 'block';
    document.getElementById('existing_team_fields').style.display = 'none';
});

document.getElementById('existing_team_option').addEventListener('change', function() {
    document.getElementById('new_team_fields').style.display = 'none';
    document.getElementById('existing_team_fields').style.display = 'block';
});

document.getElementById('add_member').addEventListener('click', function() {
    const membersFields = document.getElementById('members_fields');
    const index = membersFields.children.length / 2;
    membersFields.insertAdjacentHTML('beforeend', `
        <div class="mb-3">
            <label for="member_email_${index}" class="form-label">Email du membre</label>
            <input type="email" class="form-control" id="member_email_${index}" name="members[${index}][email]">
        </div>
        <div class="mb-3">
            <label for="member_role_${index}" class="form-label">Rôle du membre</label>
            <input type="text" class="form-control" id="member_role_${index}" name="members[${index}][role]">
        </div>
    `);
});
</script>
</body>
</html>
