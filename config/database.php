<?php
// Paramètres de connexion à la base de données
$host = 'localhost';     // Hôte de la base de données
$dbname = 'DB_EcoRide';    // Nom de votre base de données
$username = 'root';     // Nom d'utilisateur de la base de données
$password = 'Molfarka8';         // Mot de passe de la base de données
$port = '3306';         // Port par défaut pour MySQL

try {
    // Création de la connexion PDO
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // En cas d'erreur, afficher le message et arrêter le script
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
