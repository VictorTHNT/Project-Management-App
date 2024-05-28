<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$notification_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($notification_id) {
    // Récupérer la notification
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notification_id, $user_id]);
    $notification = $stmt->fetch();

    if ($notification) {
        // Marquer la notification comme lue
        $updateStmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $updateStmt->execute([$notification_id]);

        // Rediriger vers le projet, la tâche ou le fichier concerné
        if ($notification['project_id']) {
            header('Location: ../projects/view.php?id=' . $notification['project_id']);
        } elseif ($notification['task_id']) {
            header('Location: ../tasks/view.php?id=' . $notification['task_id']);
        } elseif ($notification['file_id']) {
            header('Location: ../files/view.php?id=' . $notification['file_id']);
        } else {
            header('Location: ../../dashboard.php');
        }
        exit;
    }
}

// Rediriger vers le tableau de bord si la notification est introuvable
header('Location: ../../dashboard.php');
exit;
?>
