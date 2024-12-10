<?php
include '../../includes/connect.php';  // Inclusion du fichier de connexion à la base de données
include '../../includes/navbar.php';  // Inclusion du fichier pour la barre de navigation

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');  // Redirection vers la page de connexion si non connecté
    exit;
}

// Récupération de l'ID du projet depuis l'URL
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : null;

// Vérification que l'ID du projet est valide
if ($project_id === null || $project_id == 0) {
    echo "ID du projet manquant.";  // Message d'erreur si l'ID est invalide
    exit;
}

// Récupération de l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$message = '';  // Initialisation d'une variable pour les messages d'erreur ou de succès

// Traitement du formulaire (envoi ou suppression de messages)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si un message a été envoyé
    if (isset($_POST['message_body'])) {
        $message_body = $_POST['message_body'];  // Récupération du contenu du message

        try {
            // Insertion du message dans la base de données
            $stmt = $pdo->prepare("INSERT INTO Messages (sender_id, project_id, body, timestamp) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$user_id, $project_id, $message_body]);
        } catch (Exception $e) {
            // Message d'erreur en cas d'échec de l'envoi
            $message = "Erreur lors de l'envoi du message : " . $e->getMessage();
        }
    // Si un message doit être supprimé
    } elseif (isset($_POST['delete_message_id'])) {
        $delete_message_id = intval($_POST['delete_message_id']);  // ID du message à supprimer

        try {
            // Suppression du message dans la base de données
            $stmt = $pdo->prepare("DELETE FROM Messages WHERE id = ? AND sender_id = ?");
            $stmt->execute([$delete_message_id, $user_id]);
        } catch (Exception $e) {
            // Message d'erreur en cas d'échec de la suppression
            $message = "Erreur lors de la suppression du message : " . $e->getMessage();
        }
    }
}

// Récupération des messages associés au projet depuis la base de données
try {
    $stmt = $pdo->prepare("SELECT Messages.*, Users.nom, Users.prenom FROM Messages JOIN Users ON Messages.sender_id = Users.id WHERE Messages.project_id = ? ORDER BY Messages.timestamp ASC");
    $stmt->execute([$project_id]);
    $messages = $stmt->fetchAll();  // Récupération de tous les messages
} catch (Exception $e) {
    // Message d'erreur en cas d'échec de la récupération des messages
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messagerie du Projet</title>
    <!-- Inclusion de la feuille de style Bootstrap pour les styles de base du site -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Inclusion des icônes Font Awesome pour les boutons et icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Styles pour les messages dans la messagerie */
        .message {
            padding: 10px;
            margin: 5px 0;
            border-radius: 10px;
            position: relative;
            max-width: 70%; 
            word-wrap: break-word; 
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
        /* Style du bouton de suppression du message */
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
            font-size: 12px; 
        }
        .delete-btn:hover {
            background-color: darkred;
            transform: scale(1.1);
        }
        /* Styles pour le contenu des messages et la zone de texte */
        .message-content {
            margin-right: 30px;
        }
        .messages-container {
            max-height: 400px; 
            overflow-y: auto; 
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <!-- Titre de la messagerie du projet -->
    <h1 class="text-center">Messagerie du Projet</h1>
    <!-- Affichage d'un message d'erreur si une erreur a eu lieu pendant l'envoi ou la suppression d'un message -->
    <?php if ($message): ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <!-- Carte contenant les messages -->
    <div class="card">
        <div class="card-body">
            <!-- Titre des messages -->
            <h5 class="card-title">Messages</h5>
            
            <!-- Conteneur des messages avec défilement automatique -->
            <div class="list-group list-group-flush messages-container" id="messages-container">
                <!-- Boucle pour afficher tous les messages récupérés depuis la base de données -->
                <?php foreach ($messages as $msg): ?>
                    <div class="list-group-item message <?php echo ($msg['sender_id'] == $user_id) ? 'right' : 'left'; ?>">
                        <!-- Si l'utilisateur est l'expéditeur du message, on affiche un bouton de suppression -->
                        <?php if ($msg['sender_id'] == $user_id): ?>
                            <form method="POST" action="" class="delete-form">
                                <input type="hidden" name="delete_message_id" value="<?php echo $msg['id']; ?>">
                                <button type="submit" class="delete-btn"><i class="fas fa-times"></i></button>
                            </form>
                        <?php endif; ?>
                        
                        <!-- Contenu du message -->
                        <div class="message-content">
                            <!-- Nom et prénom de l'expéditeur -->
                            <strong><?php echo htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']); ?></strong><br>
                            <!-- Corps du message -->
                            <?php echo htmlspecialchars($msg['body']); ?><br>
                            <!-- Horodatage du message -->
                            <small class="text-muted"><?php echo $msg['timestamp']; ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Formulaire pour envoyer un nouveau message -->
        <div class="card-footer">
            <form method="POST" action="">
                <div class="input-group">
                    <!-- Champ de texte pour saisir le message -->
                    <input type="text" class="form-control" name="message_body" placeholder="Votre message" required>
                    <!-- Bouton d'envoi -->
                    <button class="btn btn-primary" type="submit">Envoyer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Inclusion du script Bootstrap pour gérer l'interactivité (comme les modales) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"></script>

<script>
    // Fonction pour faire défiler la fenêtre vers le bas lorsqu'un message est ajouté
    function scrollToBottom() {
        var messagesContainer = document.getElementById('messages-container');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Défilement automatique des messages lors du chargement de la page
    document.addEventListener('DOMContentLoaded', scrollToBottom);

    // Après l'envoi d'un message, la fenêtre se déplace vers le bas pour afficher le dernier message
    var form = document.querySelector('form');
    form.addEventListener('submit', function() {
        setTimeout(scrollToBottom, 100);
    });
</script>

<?php include '../../includes/footer.php'; ?> <!-- Inclusion du pied de page -->

</body>
</html>
