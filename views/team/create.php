<?php
include '../../includes/connect.php'; // Inclusion du fichier de connexion à la base de données
include '../../includes/navbar.php'; // Inclusion de la barre de navigation

// Vérification si l'utilisateur est connecté. Si non, redirection vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php'); // Redirection si l'utilisateur n'est pas connecté
    exit; // Interruption de l'exécution du script
}

$user_id = $_SESSION['user_id']; // Récupération de l'ID de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?"); // Préparation de la requête pour obtenir le rôle de l'utilisateur
$stmt->execute([$user_id]); // Exécution de la requête avec l'ID de l'utilisateur
$user = $stmt->fetch(PDO::FETCH_ASSOC); // Récupération des résultats de la requête

// Si l'utilisateur n'a pas le rôle d'admin, redirection vers le tableau de bord
if ($user['role'] !== 'admin') {
    header('Location: ../../dashboard.php'); // Redirection si l'utilisateur n'est pas admin
    exit; // Interruption du script
}

$message = ''; // Variable pour stocker un message à afficher (succès ou erreur)

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Si la méthode de requête est POST (formulaire soumis)
    $team_name = htmlspecialchars($_POST['team_name']); // Récupération du nom de l'équipe et sécurisation
    $members = $_POST['members']; // Récupération des membres de l'équipe

    $pdo->beginTransaction(); // Début de la transaction pour garantir la cohérence des données
    try {
        // Parcours des membres pour insérer leurs données dans la table 'user_team'
        foreach ($members as $member) {
            $stmt = $pdo->prepare("INSERT INTO user_team (team_name, project_id, user_id, post) VALUES (?, NULL, ?, ?)"); // Préparation de la requête d'insertion
            $stmt->execute([$team_name, $member['user_id'], $member['post']]); // Exécution de la requête
        }

        $pdo->commit(); // Commit de la transaction si tout s'est bien passé
        $message = 'Équipe créée avec succès'; // Message de succès
        header("Location: ../../dashboard_admin.php"); // Redirection vers le tableau de bord administrateur
        exit; // Interruption du script
    } catch (Exception $e) { // En cas d'erreur
        $pdo->rollBack(); // Annulation de la transaction pour éviter des données incohérentes
        $message = 'Erreur lors de la création de l\'équipe: ' . $e->getMessage(); // Message d'erreur
    }
}

// Récupération de tous les utilisateurs pour les afficher dans la liste des membres possibles
$all_users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC); 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"> <!-- Déclaration du charset UTF-8 pour le site -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Déclaration de la viewport pour un affichage mobile correct -->
    <title>Créer Équipe</title> <!-- Titre de la page -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Lien vers le fichier CSS Bootstrap pour le style -->
    <style>
        .container { /* Style pour la zone principale du formulaire */
            max-width: 600px; /* Largeur maximale de la fenêtre */
            margin-top: 50px; /* Espacement au-dessus de la fenêtre */
            padding: 30px; /* Espacement intérieur */
            border-radius: 10px; /* Coins arrondis */
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); /* Ombre portée */
        }
        .form-label { /* Style pour les labels des champs de formulaire */
            font-weight: bold; /* Met le texte des labels en gras */
        }
        .btn-primary { /* Style pour les boutons de type primaire */
            background-color: #007bff; /* Couleur de fond */
            border-color: #007bff; /* Couleur de bordure */
        }
        .btn-primary:hover { /* Style au survol des boutons */
            background-color: #0056b3; /* Couleur de fond en survol */
            border-color: #0056b3; /* Couleur de bordure en survol */
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center mb-4">Créer Équipe</h1>
    <?php if ($message): ?> <!-- Si un message existe (succès ou erreur) -->
        <div class="alert alert-info"><?php echo $message; ?></div> <!-- Affichage du message dans une boîte d'alerte -->
    <?php endif; ?>
    <form method="post"> <!-- Début du formulaire de création d'équipe -->
        <div class="mb-3"> <!-- Section pour le nom de l'équipe -->
            <label for="team_name" class="form-label">Nom de l'équipe</label> <!-- Label pour le champ du nom -->
            <input type="text" class="form-control" id="team_name" name="team_name" required> <!-- Champ de saisie pour le nom de l'équipe -->
        </div>
        <h3>Membres</h3>
        <div id="members"> <!-- Section pour afficher les membres de l'équipe -->
            <div class="member mb-3"> <!-- Bloc pour un membre -->
                <select class="form-select mb-2" name="members[0][user_id]" required> <!-- Menu déroulant pour sélectionner un membre -->
                    <?php foreach ($all_users as $user): ?> <!-- Boucle pour afficher tous les utilisateurs -->
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?> <!-- Affichage du nom et prénom des utilisateurs -->
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" class="form-control mb-2" name="members[0][post]" placeholder="Poste" required> <!-- Champ pour saisir le poste du membre -->
                <button type="button" class="btn btn-danger btn-sm remove-member">Supprimer</button> <!-- Bouton pour supprimer ce membre -->
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" id="add-member">Ajouter Membre</button> <!-- Bouton pour ajouter un membre -->
        <button type="submit" class="btn btn-primary w-100 mt-3">Créer</button> <!-- Bouton pour soumettre le formulaire -->
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Écouteur d'événement pour le bouton "Ajouter Membre"
document.getElementById('add-member').addEventListener('click', function() {
    var membersDiv = document.getElementById('members'); // Récupération de la section des membres
    var memberCount = membersDiv.getElementsByClassName('member').length; // Compte le nombre de membres déjà ajoutés
    
    var memberDiv = document.createElement('div'); // Création d'un nouvel élément pour un membre
    memberDiv.classList.add('member', 'mb-3'); // Ajout de classes CSS pour le style

    var selectUser = document.createElement('select'); // Création d'un menu déroulant pour le membre
    selectUser.classList.add('form-select', 'mb-2'); // Ajout de classes CSS
    selectUser.name = 'members[' + memberCount + '][user_id]'; // Nom dynamique pour chaque membre
    selectUser.required = true; // Champ requis

    <?php foreach ($all_users as $user): ?> // Boucle pour ajouter les utilisateurs dans le menu déroulant
    var option = document.createElement('option');
    option.value = '<?php echo $user['id']; ?>'; // ID de l'utilisateur
    option.text = '<?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?>'; // Nom complet de l'utilisateur
    selectUser.appendChild(option);
    <?php endforeach; ?>

    var inputPost = document.createElement('input'); // Création d'un champ pour le poste du membre
    inputPost.type = 'text';
    inputPost.classList.add('form-control', 'mb-2'); // Ajout de classes CSS
    inputPost.name = 'members[' + memberCount + '][post]'; // Nom dynamique pour chaque poste
    inputPost.required = true; // Champ requis

    var removeButton = document.createElement('button'); // Création d'un bouton pour supprimer le membre
    removeButton.type = 'button';
    removeButton.classList.add('btn', 'btn-danger', 'btn-sm', 'remove-member');
    removeButton.textContent = 'Supprimer'; // Texte du bouton
    removeButton.addEventListener('click', function() {
        memberDiv.remove(); // Suppression de l'élément du DOM lorsque le bouton est cliqué
    });

    // Ajout des éléments au membre
    memberDiv.appendChild(selectUser);
    memberDiv.appendChild(inputPost);
    memberDiv.appendChild(removeButton);

    membersDiv.appendChild(memberDiv); // Ajout du membre à la section des membres
});

// Gestion de la suppression d'un membre
document.querySelectorAll('.remove-member').forEach(function(button) {
    button.addEventListener('click', function() {
        button.parentElement.remove(); // Suppression de l'élément parent (le membre) lorsqu'on clique sur "Supprimer"
    });
});
</script>

<?php include '../../includes/footer.php'; ?> <!-- Inclusion du pied de page -->
</body>
</html>
