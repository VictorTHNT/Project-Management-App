<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'includes/connect.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_profile = $is_logged_in && !empty($_SESSION['user_profile']) ? $_SESSION['user_profile'] : 'assets/images/default-profile.png';
$user_email = '';

// Récupérer l'email de l'utilisateur connecté
if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT email FROM Users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $user_email = $user['email'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="sidebar" id="sidebar">
    <div class="profile-section">
        <div class="text-center">
            <img src="<?php echo htmlspecialchars($user_profile); ?>" alt="Profile Picture" class="profile-pic">
        </div>
    </div>
    <div class="email-section">
        <h1><?php echo htmlspecialchars($user_email); ?></h1>
    </div>
    <div class="menu-section">
        <a href="#"><i class="bi bi-person"></i> <span class="nav-text">Profile</span></a>
        <a href="#"><i class="bi bi-house"></i> <span class="nav-text">Dashboard</span></a>
        <a href="#"><i class="bi bi-briefcase"></i> <span class="nav-text">Project</span></a>
        <a href="#"><i class="bi bi-list-task"></i> <span class="nav-text">Task</span></a>
        <a href="#"><i class="bi bi-envelope"></i> <span class="nav-text">Message</span></a>
        <a href="#"><i class="bi bi-upload"></i> <span class="nav-text">Upload</span></a>
        <a href="#"><i class="bi bi-box-arrow-left"></i> <span class="nav-text">Log out</span></a>
    </div>
    <button class="expand-btn" id="expand-btn"><i class="fas fa-chevron-right"></i></button>
    <button class="collapse-btn" id="collapse-btn" style="display:none;"><i class="fas fa-chevron-left"></i></button>
</div>

<script>
    document.getElementById('expand-btn').addEventListener('click', function() {
        document.getElementById('sidebar').classList.add('expanded');
        document.querySelector('.email-section').style.opacity = '1';
        document.getElementById('expand-btn').style.display = 'none';
        document.getElementById('collapse-btn').style.display = 'flex';
    });

    document.getElementById('collapse-btn').addEventListener('click', function() {
        document.getElementById('sidebar').classList.remove('expanded');
        document.querySelector('.email-section').style.opacity = '0';
        document.getElementById('expand-btn').style.display = 'flex';
        document.getElementById('collapse-btn').style.display = 'none';
    });
</script>
</body>
</html>
