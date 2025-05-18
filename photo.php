<?php

// Для отладки - выводим параметры
error_log("photo.php accessed with ID: " . ($_GET['id'] ?? 'not set'));

session_start();
require_once 'config/database.php';

// Vérification de la présence de l'ID utilisateur
if (!isset($_GET['id'])) {
    http_response_code(404);
    exit();
}

$user_id = (int)$_GET['id'];

try {
    // Récupération de la photo depuis la base de données
    $stmt = $pdo->prepare("SELECT photo FROM utilisateur WHERE utilisateur_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && !empty($result['photo'])) {
        // Détection du type MIME de l'image
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($result['photo']);

        // Configuration des en-têtes HTTP
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . strlen($result['photo']));
        header('Cache-Control: public, max-age=3600');

        // Envoi de l'image au navigateur
        echo $result['photo'];
    } else {
        // Si aucune photo n'est trouvée, retourner une erreur 404
        http_response_code(404);
        header('Content-Type: text/plain');
        echo 'Photo not found';
    }
} catch (PDOException $e) {
    // Gestion des erreurs de base de données
    http_response_code(500);
    echo 'Database error';
}
?>
