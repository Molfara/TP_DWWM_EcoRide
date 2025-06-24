<?php
// Connexion au pilote PHP MongoDB
require_once __DIR__ . '/../vendor/autoload.php';

// Paramètres de connexion à MongoDB
$mongodb = null;

try {
    // Récupération de l'URI MongoDB depuis les variables d'environnement
    $mongoUri = getenv('MONGODB_URI') ?: getenv('MONGO_URL') ?: getenv('MONGOLAB_URI') ?: getenv('MONGOHQ_URL');
    
    // Si aucune URI n'est trouvée, utiliser une connexion locale pour le développement
    if (!$mongoUri) {
        $mongoUri = 'mongodb://localhost:27017';
        error_log("Aucune URI MongoDB trouvée dans les variables d'environnement, utilisation de localhost");
    }
    
    // Configuration des options
    $options = [
        'connectTimeoutMS' => 10000,
        'socketTimeoutMS' => 30000,
        'serverSelectionTimeoutMS' => 10000,
    ];
    
    // Pour MongoDB Atlas, ajouter les options TLS
    if (strpos($mongoUri, 'mongodb.net') !== false || strpos($mongoUri, 'mongodb+srv') !== false) {
        $options['tls'] = true;
        error_log("Configuration Atlas détectée");
    }
    
    error_log("Tentative de connexion MongoDB avec URI: " . preg_replace('/\/\/[^@]+@/', '//***:***@', $mongoUri));
    error_log("Options de connexion: " . json_encode($options));
    
    // Création de la connexion à MongoDB
    $mongodb = new MongoDB\Client($mongoUri, $options);
    
    // Test de connexion
    $mongodb->admin->command(['ping' => 1]);
    error_log("Connexion MongoDB réussie");
    
} catch (\Exception $e) {
    error_log("Erreur de connexion MongoDB: " . $e->getMessage());
    $mongodb = null;
}
?>
