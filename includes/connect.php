<?php
// Configuration de la connexion à la base de données
$host = 'localhost'; // ou le nom de votre serveur de base de données
$dbname = 'project_management_app'; // le nom de votre base de données
$username = 'root'; // votre nom d'utilisateur MySQL
$password = ''; // votre mot de passe MySQL

try {
    // Création de la connexion PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configuration des options PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Confirmation de la connexion réussie (à des fins de débogage uniquement)
    // echo "Connexion réussie à la base de données";
} catch (PDOException $e) {
    // Gestion des erreurs de connexion
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
