<?php
// Démarrage de la mise en tampon de sortie dès le début du fichier
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
  header('Location: /connexion');
  exit;
}

// Récupération du rôle sélectionné depuis la requête POST
if (isset($_POST['role'])) {
  $role_id = $_POST['role'];
  
  // Vérification de la validité du rôle (2=passager, 3=chauffeur)
  if ($role_id === '2' || $role_id === '3') {
    // Conversion du role_id en nom de rôle pour la session
    $role_name = ($role_id === '2') ? 'passager' : 'chauffeur';
    
    // Sauvegarde du rôle dans la base de données
    require_once __DIR__ . '/../config/database.php';
    $userId = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE utilisateur SET role_id = ? WHERE utilisateur_id = ?");
        $stmt->execute([(int)$role_id, $userId]);
        
        // Si l'utilisateur choisit le rôle "chauffeur", vérification de la présence d'un véhicule
        if ($role_id === '3') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM voiture WHERE utilisateur_id = ?");
            $stmt->execute([$userId]);
            $hasVehicle = $stmt->fetchColumn() > 0;
            
            // Si le chauffeur n'a pas encore de véhicule
            if (!$hasVehicle) {
                // Enregistrement temporaire du choix du rôle dans la session
                $_SESSION['temp_role'] = 'chauffeur';
                // Redirection vers la page d'ajout de véhicule
                header('Location: /ajouter-voiture');
                ob_end_flush();
                exit;
            }
        }
        
        // Enregistrement du rôle dans la session uniquement si tout s'est bien passé
        $_SESSION['role'] = $role_name;
        
    } catch (PDOException $e) {
        // Gestion des erreurs
        $_SESSION['error'] = "Erreur lors de la mise à jour du rôle : " . $e->getMessage();
        header('Location: /role');
        ob_end_flush();
        exit;
    }
    
    // Redirection vers l'espace correspondant
    header('Location: /espace-' . $role_name);
    ob_end_flush();
    exit;
  }

  // Si le rôle est invalide
  $_SESSION['error'] = "Rôle invalide. Veuillez sélectionner un rôle valide.";
  header('Location: /role');
  ob_end_flush();
  exit;
} else {
  // Si aucun rôle n'a été sélectionné
  $_SESSION['error'] = "Veuillez sélectionner un rôle.";
  header('Location: /role');
  ob_end_flush();
  exit;
}

ob_end_flush();
?>

