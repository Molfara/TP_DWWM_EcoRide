<?php
// Démarrage de la session pour gérer l'authentification de l'utilisateur
session_start();

// Inclusion du fichier d'authentification pour vérifier les droits d'accès
require_once __DIR__ . '/../middleware/auth.php';

// Vérification si l'utilisateur est connecté (sans vérification de rôle)
if (!isset($_SESSION['user_id'])) {
    header('Location: /connexion.php');
    exit;
}

// Récupération des informations de l'utilisateur depuis la base de données
require_once __DIR__ . '/../config/database.php';
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? '';

// Initialisation des variables de messages
$message = '';
$error = '';

// Débogage
error_log("Traitement du profil utilisateur. ID: " . $userId . ", Rôle: " . $userRole);

// TRAITEMENT DE LA MISE À JOUR DES DONNÉES PERSONNELLES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_personal_data'])) {
    // Debug - log des données reçues
    error_log("Traitement de la mise à jour des données personnelles pour utilisateur ID: " . $userId);
    error_log("Données POST: " . print_r($_POST, true));
    
    // Récupération des données du formulaire
    $pseudo = isset($_POST['pseudo']) ? trim($_POST['pseudo']) : null;
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : null;
    $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;
    $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : null;
    $adresse = isset($_POST['adresse']) ? trim($_POST['adresse']) : null;
    $date_naissance = isset($_POST['date_naissance']) ? trim($_POST['date_naissance']) : null;
    
    // Validation des champs obligatoires
    $errors = [];
    if (empty($pseudo)) {
        $errors[] = "Le pseudo est obligatoire.";
    }
    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    }
    
    // Vérification si l'email existe déjà pour un autre utilisateur
    if (!empty($email)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ? AND utilisateur_id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Cet email est déjà utilisé par un autre compte.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la vérification de l'email: " . $e->getMessage();
            error_log("Erreur SQL: " . $e->getMessage());
        }
    }
    
    // Vérification si le pseudo existe déjà pour un autre utilisateur
    if (!empty($pseudo)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE pseudo = ? AND utilisateur_id != ?");
            $stmt->execute([$pseudo, $userId]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Ce pseudo est déjà utilisé par un autre compte.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la vérification du pseudo: " . $e->getMessage();
            error_log("Erreur SQL: " . $e->getMessage());
        }
    }
    
    // Si pas d'erreur, mise à jour des données
    if (empty($errors)) {
        try {
            // Préparation de la requête de mise à jour
            $sql = "UPDATE utilisateur SET 
                    pseudo = ?, 
                    nom = ?, 
                    prenom = ?, 
                    email = ?, 
                    telephone = ?,
                    adresse = ?,
                    date_naissance = ?";
            
            $params = [$pseudo, $nom, $prenom, $email, $telephone, $adresse, $date_naissance];
            
            // Ajout du mot de passe à la requête s'il a été modifié
            if (!empty($password)) {
                $sql .= ", password = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            $sql .= " WHERE utilisateur_id = ?";
            $params[] = $userId;
            
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $message = "Vos données personnelles ont été mises à jour avec succès!";
            
            // Mise à jour de la session
            $_SESSION['pseudo'] = $pseudo;
            
            // Stockage du message dans la session pour affichage après redirection
            $_SESSION['personal_message'] = $message;
            
        } catch (PDOException $e) {
            $error = "Erreur lors de la mise à jour des données: " . $e->getMessage();
            error_log("Erreur SQL: " . $e->getMessage());
            $_SESSION['personal_error'] = $error;
        }
    } else {
        // Stockage des erreurs dans la session pour affichage après redirection
        $_SESSION['personal_error'] = implode("<br>", $errors);
    }
    
    // Redirection vers la page du profil en fonction du rôle
    if ($userRole === 'chauffeur') {
        header('Location: /profil-chauffeur');
    } else {
        header('Location: /profil-passager');
    }
    exit;
}

// TRAITEMENT DES ACTIONS SPÉCIFIQUES (SUPPRIMER UN CHAMP)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $field = isset($_POST['field']) ? $_POST['field'] : '';
    $action = $_POST['action'];
    
    error_log("Action demandée: " . $action . " pour le champ: " . $field);
    
    if ($action === 'delete' && !empty($field)) {
        // Vérification des champs obligatoires qui ne peuvent pas être supprimés
        if ($field === 'pseudo' || $field === 'email' || $field === 'password') {
            $_SESSION['personal_error'] = "Les champs obligatoires ne peuvent pas être supprimés.";
        } else {
            // Vérification que le champ existe dans la table (sécurité)
            $allowedFields = ['nom', 'prenom', 'telephone', 'adresse', 'date_naissance'];
            
            if (in_array($field, $allowedFields)) {
                try {
                    $sql = "UPDATE utilisateur SET $field = NULL WHERE utilisateur_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$userId]);
                    
                    $_SESSION['personal_message'] = "Le champ a été supprimé avec succès.";
                    
                } catch (PDOException $e) {
                    $_SESSION['personal_error'] = "Erreur lors de la suppression du champ: " . $e->getMessage();
                    error_log("Erreur SQL: " . $e->getMessage());
                }
            } else {
                $_SESSION['personal_error'] = "Opération non autorisée sur ce champ.";
                error_log("Tentative de suppression d'un champ non autorisé: " . $field);
            }
        }
    }
    
    // Redirection vers la page du profil en fonction du rôle
    if ($userRole === 'chauffeur') {
        header('Location: /profil-chauffeur');
    } else {
        header('Location: /profil-passager');
    }
    exit;
}

// Si on arrive ici, c'est qu'on n'a pas traité de formulaire, redirection
if ($userRole === 'chauffeur') {
    header('Location: /profil-chauffeur');
} else {
    header('Location: /profil-passager');
}
exit;
?>
