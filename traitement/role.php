<?php
// traitement/role.php
// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
  header('Location: /se-connecter');
  exit;
}
// Récupération du rôle choisi depuis la requête POST
if (isset($_POST['role'])) {
  $role_id = $_POST['role'];

  // Validation du rôle (valeurs autorisées: 2=passager, 3=chauffeur)
  if ($role_id === '2' || $role_id === '3') {
    // Conversion du role_id en nom de rôle pour la session
    $role_name = ($role_id === '2') ? 'passager' : 'chauffeur';
    
    // Enregistrement du rôle dans la session
    $_SESSION['role'] = $role_name;

    // Sauvegarde du rôle dans la base de données
    require_once __DIR__ . '/../config/database.php';
    $userId = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("UPDATE utilisateur SET role_id = ? WHERE utilisateur_id = ?");
        $stmt->execute([(int)$role_id, $userId]);
    } catch (PDOException $e) {
        // Gestion des erreurs
        $_SESSION['error'] = "Erreur lors de la mise à jour du rôle: " . $e->getMessage();
        header('Location: /role');
        exit;
    }

    // Redirection de l'utilisateur vers la page correspondante
    header('Location: /espace-' . $role_name);
    exit;
  }

  // Si le rôle n'est pas valide
  $_SESSION['error'] = "Rôle invalide. Veuillez sélectionner un rôle valide.";
  header('Location: /role');
  exit;
} else {
  // Si le rôle n'a pas été transmis
  $_SESSION['error'] = "Veuillez sélectionner un rôle.";
  header('Location: /role');
  exit;
}
?>
