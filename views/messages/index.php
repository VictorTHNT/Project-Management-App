<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : null;

if ($project_id === null || $project_id == 0) {
    echo "ID du projet manquant.";
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message_body'])) {
        $message_body = $_POST['message_body'];

        try {
            $stmt = $pdo->prepare("INSERT INTO Messages (sender_id, project_id, body, timestamp) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$user_id, $project_id, $message_body]);
        } catch (Exception $e) {
            $message = "Erreur lors de l'envoi du message : " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_message_id'])) {
        $delete_message_id = intval($_POST['delete_message_id']);

        try {
            $stmt = $pdo->prepare("DELETE FROM Messages WHERE id = ? AND sender_id = ?");
            $stmt->execute([$delete_message_id, $user_id]);
        } catch (Exception $e) {
            $message = "Erreur lors de la suppression du message : " . $e->getMessage();
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT Messages.*, Users.nom, Users.prenom FROM Messages JOIN Users ON Messages.sender_id = Users.id WHERE Messages.project_id = ? ORDER BY Messages.timestamp ASC");
    $stmt->execute([$project_id]);
    $messages = $stmt->fetchAll();
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messagerie du Projet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .message {
            padding: 10px;
            margin: 5px 0;
            border-radius: 10px;
            position: relative;
            max-width: 70%; /* Set a maximum width for messages */
            word-wrap: break-word; /* Ensure long words break and wrap */
        }
        .message.left {
            background-color: #f1f1f1;
            text-align: left;
        }
        .message.right {
            background-color: #d1e7dd;
            text-align: right;
            margin-left: auto;
        }
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: background-color 0.3s, transform 0.3s;
        }
        .delete-btn i {
            font-size: 12px; /* Adjust the size of the icon */
        }
        .delete-btn:hover {
            background-color: darkred;
            transform: scale(1.1);
        }
        .message-content {
            margin-right: 30px; /* Adjusted margin for the delete button */
        }
        .messages-container {
            max-height: 400px; /* Set the max height of the message container */
            overflow-y: auto; /* Enable vertical scroll */
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Messagerie du Projet</h1>
    <?php if ($message): ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Messages</h5>
            <div class="list-group list-group-flush messages-container" id="messages-container">
                <?php foreach ($messages as $msg): ?>
                    <div class="list-group-item message <?php echo ($msg['sender_id'] == $user_id) ? 'right' : 'left'; ?>">
                        <?php if ($msg['sender_id'] == $user_id): ?>
                            <form method="POST" action="" class="delete-form">
                                <input type="hidden" name="delete_message_id" value="<?php echo $msg['id']; ?>">
                                <button type="submit" class="delete-btn"><i class="fas fa-times"></i></button>
                            </form>
                        <?php endif; ?>
                        <div class="message-content">
                            <strong><?php echo htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']); ?></strong><br>
                            <?php echo htmlspecialchars($msg['body']); ?><br>
                            <small class="text-muted"><?php echo $msg['timestamp']; ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card-footer">
            <form method="POST" action="">
                <div class="input-group">
                    <input type="text" class="form-control" name="message_body" placeholder="Votre message" required>
                    <button class="btn btn-primary" type="submit">Envoyer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"></script>
<script>
    function scrollToBottom() {
        var messagesContainer = document.getElementById('messages-container');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Scroll to bottom on page load
    document.addEventListener('DOMContentLoaded', scrollToBottom);

    // Scroll to bottom on form submit
    var form = document.querySelector('form');
    form.addEventListener('submit', function() {
        setTimeout(scrollToBottom, 100);
    });
</script>
</body>
</html>
