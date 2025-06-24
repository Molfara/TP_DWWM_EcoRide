<?php
// Connexion au pilote PHP MongoDB
require_once __DIR__ . '/../vendor/autoload.php';

// Paramètres de connexion à MongoDB
$mongodb = null;

try {
    // URI MongoDB depuis les variables d'environnement
    $mongoUri = getenv('MONGODB_URI') ?: 'mongodb://localhost:27017';
    
    // Options simples - SANS tlsInsecure qui cause le conflit
    $options = [
        'connectTimeoutMS' => 10000,
        'serverSelectionTimeoutMS' => 5000,
    ];
    
    // Pour MongoDB Atlas, ajouter SSL basique
    if (strpos($mongoUri, 'mongodb+srv') !== false || strpos($mongoUri, 'mongodb.net') !== false) {
        $options['ssl'] = true;
        $options['authSource'] = 'admin';
    }
    
    // Création de la connexion MongoDB
    $mongodb = new MongoDB\Client($mongoUri, $options);
    
    // Test de connexion simple
    $mongodb->listDatabases();
    
    // Log de succès
    error_log("MongoDB connecté avec succès");
    
} catch (Exception $e) {
    // Log de l'erreur
    error_log("Erreur de connexion MongoDB: " . $e->getMessage());
    $mongodb = null;
}
?>