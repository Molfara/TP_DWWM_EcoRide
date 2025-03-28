<?php
// process-role.php
session_start();

// Вся логика обработки роли
if (!isset($_SESSION['user_id'])) {
  header('Location: /se-connecter');
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

    header('Location: /espace-' . $role_name);
    exit;
  }

  $_SESSION['error'] = "Rôle invalide. Veuillez sélectionner un rôle valide.";
  header('Location: /role');
  exit;
} else {
  $_SESSION['error'] = "Veuillez sélectionner un rôle.";
  header('Location: /role');
  exit;
}

