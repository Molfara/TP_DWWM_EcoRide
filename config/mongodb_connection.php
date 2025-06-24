<?php
// Connexion au pilote PHP MongoDB
require_once __DIR__ . '/../vendor/autoload.php';

// Paramètres de connexion à MongoDB
$mongodb = null;

try {
    // Détermine l'URI pour la connexion à MongoDB
    // Priorité : variable d'environnement MONGODB_URI, puis MongoDB local
    $mongoUri = getenv('MONGODB_URI') ?: 'mongodb://localhost:27017';
    
    // Pour Heroku, vérifier aussi d'autres variables possibles
    if (!getenv('MONGODB_URI')) {
        // Vérifier d'autres variables courantes pour MongoDB sur Heroku
        $mongoUri = getenv('MONGO_URL') ?: 
                   getenv('MONGOLAB_URI') ?: 
                   getenv('MONGOHQ_URL') ?: 
                   'mongodb://localhost:27017';
    }
    
    // Configuration des options SSL pour Heroku
    $options = [];
    
    // Si c'est une connexion MongoDB Atlas (contient mongodb+srv)
    if (strpos($mongoUri, 'mongodb+srv') !== false) {
        $options = [
            'ssl' => true,
            'tls' => true,
            'tlsAllowInvalidCertificates' => true,
            'tlsAllowInvalidHostnames' => true
        ];
    }
    
    // Création de la connexion à MongoDB
    $mongodb = new MongoDB\Client($mongoUri, $options);
    
    // Vérification de la connexion par ping
    $mongodb->admin->command(['ping' => 1]);
    
    // Journalisation de la connexion réussie (seulement pour le débogage)
    error_log("Connexion MongoDB réussie vers: " . $mongoUri);
    
} catch (Exception $e) {
    // Journalisation de l'erreur dans un fichier pour ne pas l'afficher à l'utilisateur
    error_log("Erreur de connexion MongoDB: " . $e->getMessage() . " | URI: " . ($mongoUri ?? 'undefined'), 0);
    
    // En cas d'erreur, on définit $mongodb à null
    $mongodb = null;
}
?>