<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include $_SERVER['DOCUMENT_ROOT'].'/Project-Management-App/includes/connect.php';

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/auth/login.php');
    exit;
}

// Récupérez les informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les notifications non lues
$notificationsStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$notificationsStmt->execute([$user_id]);
$notifications = $notificationsStmt->fetchAll();
$notificationCount = count($notifications);

// Définir les variables pour le nom et la photo de profil
$user_name = htmlspecialchars($user['nom'] . ' ' . $user['prenom']);
$profile_image = !empty($user['profile']) ? $user['profile'] : 'assets/images/default-profile.png';

// Vérifier si l'image existe, sinon utiliser une image par défaut
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/Project-Management-App/' . $profile_image)) {
    $profile_image = 'assets/images/default-profile.png';
}

// Définir la variable pour le rôle de l'utilisateur
$user_role = $user['role'];
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/Project-Management-App/index.php">
            <img src="/Project-Management-App/assets/images/logo_white.png" alt="Logo" width="30" height="30" class="d-inline-block align-top">
            VAAL
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/Project-Management-App/dashboard.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Project-Management-App/views/projects/view.php">Projets</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Project-Management-App/views/tasks/view.php">Tâches</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Project-Management-App/views/calendar/view.php">Calendrier</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Notifications <span class="badge bg-danger"><?php echo $notificationCount; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                        <?php if ($notificationCount > 0): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <li>
                                    <a class="dropdown-item" href="/Project-Management-App/views/notifications/handle.php?id=<?php echo $notification['id']; ?>">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><span class="dropdown-item">Aucune notification</span></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <li class="nav-item d-flex align-items-center">
                    <img src="/Project-Management-App/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" width="30" height="30" class="rounded-circle me-2">
                    <span class="navbar-text">
                        <a href="/Project-Management-App/views/auth/profile.php"><?php echo $user_name; ?></a>
                        <?php if ($user_role === 'admin'): ?>
                            <span class="badge bg-warning text-dark ms-2"><a href="../Project-Management-App/dashboard_admin.php" style="text-decoration: none;">Administrateur</a></span>
                        <?php endif; ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Project-Management-App/includes/logout.php">Déconnexion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>