<?php
// Activation des rapports d'erreurs pour le développement
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Inclusion du fichier de configuration de la base de données
require_once __DIR__ . '/../config/database.php';

// Journalisation des données pour le débogage
file_put_contents('../public/debug_connexion.log', date('Y-m-d H:i:s') . " - Début du script connexion\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Journalisation pour débogage
    file_put_contents('../public/debug_connexion.log', "Email: $email\n", FILE_APPEND);
    
    try {
        // Préparation de la requête
        $query = "SELECT utilisateur_id, pseudo, email, password, role_id FROM utilisateur WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['utilisateur_id'];
            $_SESSION['pseudo'] = $user['pseudo'];
            
            // Journalisation du succès
            file_put_contents('../public/debug_connexion.log', "Connexion réussie pour utilisateur: {$user['utilisateur_id']}\n", FILE_APPEND);
            
            // Redirection simple vers la page de rôle
            file_put_contents('../public/debug_connexion.log', "Redirection vers /role\n", FILE_APPEND);
            header('Location: /role');
            exit();
        } else {
            // Échec de la connexion
            file_put_contents('../public/debug_connexion.log', "Échec de connexion - Identifiants incorrects\n", FILE_APPEND);
            $_SESSION['error'] = "Email ou mot de passe incorrect";
            header('Location: /se-connecter');
            exit();
        }
    } catch (PDOException $e) {
        // Journalisation de l'erreur
        file_put_contents('../public/debug_connexion.log', "Erreur PDO: " . $e->getMessage() . "\n", FILE_APPEND);
        
        $_SESSION['error'] = "Erreur de connexion à la base de données";
        header('Location: /se-connecter');
        exit();
    }
}
