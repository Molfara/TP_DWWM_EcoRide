<?php
// Activation des rapports d'erreurs pour le développement
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclusion du fichier de configuration de la base de données
require_once __DIR__ . '/../config/database.php';

// Journalisation des données pour le débogage
file_put_contents('debug_inscription.log', date('Y-m-d H:i:s') . " - Début du script\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   // Validation des champs obligatoires
   if (empty($_POST['pseudo']) || empty($_POST['email']) || empty($_POST['password'])) {
       $_SESSION['error'] = "Tous les champs sont obligatoires";
       header('Location: /inscription');
       exit();
   }

   // Nettoyage et validation des données
   $pseudo = filter_input(INPUT_POST, 'pseudo', FILTER_SANITIZE_STRING);
   $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
   $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
   $credits = 20; // Crédits initiaux
   
   // Définir un role_id temporaire (vous devez avoir une valeur correspondante dans votre table des rôles)
   $role_id = 1; // Supposons que 1 est l'ID pour un rôle 'pending' ou 'user'

   try {
       // Vérification si l'email existe déjà
       $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email");
       $stmt->execute(['email' => $email]);

       if ($stmt->fetchColumn() > 0) {
           $_SESSION['error'] = "Cet email est déjà utilisé";
           header('Location: /inscription');
           exit();
       }

       // Début de la transaction
       $pdo->beginTransaction();

       // Insertion du nouvel utilisateur
       $stmt = $pdo->prepare("
           INSERT INTO utilisateur (pseudo, email, password, role_id, credits)
           VALUES (:pseudo, :email, :password, :role_id, :credits)
       ");

       $stmt->execute([
           'pseudo' => $pseudo,
           'email' => $email,
           'password' => $password,
           'role_id' => $role_id,
           'credits' => $credits
       ]);

       $userId = $pdo->lastInsertId();
       
       // Validation de la transaction
       $pdo->commit();
   
       // Initialisation de la session
       $_SESSION['user_id'] = $userId;
       $_SESSION['user_role'] = $role_id; // Stocker role_id au lieu de role
       $_SESSION['user_pseudo'] = $pseudo;
   
       // Redirection vers la page de sélection de rôle
       header('Location: /role');
       exit();
       
   } catch (PDOException $e) {
       // Journalisation de l'erreur
       file_put_contents('debug_inscription.log', "Erreur PDO : " . $e->getMessage() . "\n", FILE_APPEND);
           
       // Annulation de la transaction
       $pdo->rollBack();
        
       // Enregistrement de l'erreur
       error_log("Erreur d'inscription : " . $e->getMessage());
       
       // Message d'erreur pour l'utilisateur
       $_SESSION['error'] = "Une erreur est survenue lors de l'inscription";
       header('Location: /inscription');
       exit();
   }
}
?>
