<?php
// traitement/connexion.php
session_start();
require_once __DIR__ . '/../config/database.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    try {
        // Préparation de la requête
        $query = "SELECT utilisateur_id, pseudo, email, password FROM utilisateur WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['utilisateur_id'];
            $_SESSION['pseudo'] = $user['pseudo'];
            
            // Redirection vers la page d'accueil
            header('Location: /');
            exit();
        } else {
            // Échec de la connexion
            $_SESSION['error'] = "Email ou mot de passe incorrect";
            header('Location: /connexion');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de connexion à la base de données";
        header('Location: /connexion');
        exit();
    }
}
?>

