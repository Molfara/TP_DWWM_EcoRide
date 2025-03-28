<?php
// Activer la mise en tampon de sortie au début du fichier
ob_start();

// Vérification de la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Journalisation pour le débogage
file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " Début du traitement de la connexion\n", FILE_APPEND);
file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " État de la session : " . session_status() . "\n", FILE_APPEND);

// Inclusion du fichier de configuration de la base de données
require_once __DIR__ . '/../config/database.php';

// Vérification si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Vérification des champs obligatoires
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires";
        header('Location: /connexion');
        exit();
    }
    
    try {
        // Préparation de la requête pour chercher l'utilisateur par email
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérification de l'existence de l'utilisateur et de la validité du mot de passe
        if ($user && password_verify($password, $user['password'])) {
            // Configuration des données de session
            $_SESSION['user_id'] = $user['utilisateur_id'];
            $_SESSION['user_pseudo'] = $user['pseudo'];
            $_SESSION['role'] = 'utilisateur'; // ou une autre valeur de la base de données
            
            // Redirection vers la page de choix de rôle
            header('Location: /role');
            exit();
        } else {
            // Erreur d'authentification
            $_SESSION['error'] = "Email ou mot de passe incorrect";
            header('Location: /connexion');
            exit();
        }
    } catch (PDOException $e) {
        // Journalisation de l'erreur
        error_log("Erreur de connexion : " . $e->getMessage());
        $_SESSION['error'] = "Une erreur est survenue lors de la connexion";
        header('Location: /connexion');
        exit();
    }
}
?>
