<?php
// process-role.php
session_start();

// Вся логика обработки роли
if (!isset($_SESSION['user_id'])) {
  header('Location: /connexion');
  exit;
}

if (isset($_POST['role'])) {
  $role_id = $_POST['role'];

  if ($role_id === '2' || $role_id === '3') {
    $role_name = ($role_id === '2') ? 'passager' : 'chauffeur';
    $_SESSION['role'] = $role_name;

    require_once __DIR__ . '/../config/database.php';
    $userId = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("UPDATE utilisateur SET role_id = ? WHERE utilisateur_id = ?");
        $stmt->execute([(int)$role_id, $userId]);
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la mise à jour du rôle: " . $e->getMessage();
        header('Location: /role');
        exit;
    }


// Si le rôle est chauffeur, vérifier la présence d'une voiture
if ($role_id === '3') {
    // Vérifier si l'utilisateur a déjà une voiture
    try {
        $check_sql = "SELECT COUNT(*) AS car_count FROM voiture WHERE utilisateur_id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$userId]);
        $check_row = $check_stmt->fetch();
        
        if ($check_row['car_count'] > 0) {
            // L'utilisateur a au moins une voiture, redirection vers la page chauffeur
            header("Location: /espace_chauffeur");
        } else {
            // L'utilisateur n'a pas de voiture, redirection vers la page d'ajout
            header("Location: /ajouter-voiture");
        }
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la vérification des véhicules: " . $e->getMessage();
        header('Location: /role');
        exit;
    }

} else {
    // Si le rôle est passager, rediriger directement
    header('Location: /espace-' . $role_name);
    exit;
}



  }

  $_SESSION['error'] = "Rôle invalide. Veuillez sélectionner un rôle valide.";
  header('Location: /role');
  exit;
} else {
  $_SESSION['error'] = "Veuillez sélectionner un rôle.";
  header('Location: /role');
  exit;
}

