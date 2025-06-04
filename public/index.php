<?php

// Début de la mise en tampon de sortie
ob_start();


// Récupérer l'URI de la requête et supprimer les slashes
$request = trim($_SERVER['REQUEST_URI'], '/');

// Récupérer la méthode HTTP utilisée
$method = $_SERVER['REQUEST_METHOD'];

// Vérification de la route spéciale deconnexion.php
if ($request === 'deconnexion.php') {
    session_start();
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    session_destroy();
    header('Location: /');
    exit;
}

// Vérification pour process-role.php
if ($request === 'traitement/process-role.php' && $method === 'POST') {
    require __DIR__ . '/../traitement/process-role.php';
    exit; // Très important - arrête l'exécution ici
}

// Vérification pour process-car.php
if ($request === 'traitement/process-car.php' && $method === 'POST') {
    require __DIR__ . '/../traitement/process-car.php';
    exit; // Très important - arrête l'exécution ici
}

// Vérification pour process-trip.php
if ($request === 'traitement/process-trip.php' && $method === 'POST') {
    require __DIR__ . '/../traitement/process-trip.php';
    exit; // Très important - arrête l'exécution ici
}

// Vérification pour profil-passager.php
if ($request === 'traitement/profil-utilisateur.php' && $method === 'POST') {
    require __DIR__ . '/../traitement/profil-utilisateur.php';
    exit; // Très important - arrête l'exécution ici
}

// Routes API ou de traitement qui ne nécessitent pas header/footer
$api_routes = [
    'traitement/role.php' => 'traitement/role.php',
    // Ajouter d'autres routes de traitement ici si nécessaire
];

if (array_key_exists($request, $api_routes)) {
    require __DIR__ . '/../' . $api_routes[$request];
    exit; // Très important
}

// Gestion spéciale pour la route proposer-trajet en POST
if ($request === 'proposer-trajet' && $method === 'POST') {
    require_once __DIR__ . '/../middleware/auth.php';
    checkAuth(); // Vérifier l'authentification
    require __DIR__ . '/../traitement/propose-trip.php';
    exit; // Important - arrête l'exécution ici
}

// À partir d'ici, on inclut le header pour les routes qui affichent des pages
require 'header.php';

$routes = [
   // Routes publiques
   '' => 'pages/accueil.php',
   'covoiturage' => 'pages/covoiturage.php',
   'contact' => 'pages/contact.php',
   'connexion' => [
       'GET' => 'pages/connexion.php',
       'POST' => 'traitement/connexion.php'
   ],
   'inscription' => [
       'GET' => 'pages/inscription.php',
       'POST' => 'traitement/inscription.php'
   ],
   'role' => [
        'GET' => 'pages/role.php',
        'POST' => 'traitement/role.php'
    ],
   // Route pour espace passager
   'espace-passager' => [
       'middleware' => 'checkAuth',
       'handler' => 'pages/espace_passager.php'
   ],

   // Route pour profil passager
   'profil-passager' => [
       'middleware' => 'checkAuth',
       'handler' => 'pages/profil-passager.php'
   ],
  // Route pour profil chauffeur
   'profil-chauffeur' => [
       'middleware' => 'checkAuth',
       'handler' => 'pages/profil-chauffeur.php'
   ],

  // Route pour espace chauffeur
   'espace-chauffeur' => [
       'middleware' => 'checkAuth',
       'handler' => 'pages/espace_chauffeur.php'
 ],

 // Route pour espace chauffeur alternative
'espace_chauffeur' => [
   'middleware' => 'checkAuth',
   'handler' => 'pages/espace_chauffeur.php'
],

  // Route pour proposer un trajet (seulement GET, POST est géré au-dessus)
  'proposer-trajet' => [
    'middleware' => 'checkAuth',
    'handler' => 'pages/proposer-trajet.php'
],

 // Route pour ajouter la première voiture
'ajouter-voiture' => [
    'middleware' => 'checkAuth',  // Vérification de l'authentification
    'handler' => 'pages/ajouter-voiture.php'
],

  // Route pour les trajets de chauffeur
'trajets-chauffeur' => [
    'middleware' => 'checkAuth',
    'handler' => 'pages/trajets-chauffeur.php'
],

];
     
// Logique de routage 
if (array_key_exists($request, $routes)) {
    $route = $routes[$request];
 
    // Si la route a une structure GET/POST, récupérer la route pour la méthode HTTP
    if (isset($route[$method])) {
        $route = $route[$method];
    }
    
    // Vérification du middleware si présent
    if (isset($route['middleware'])) {
        require_once __DIR__ . '/../middleware/auth.php';
        // Vérifiez le nom du middleware et appelez la fonction correspondante
        if ($route['middleware'] === 'checkAuth') {
            checkAuth();
        } else if ($route['middleware'] === 'checkAdmin') {
            checkAdmin();
        }
        require __DIR__ . '/../' . $route['handler'];
    }
    // Si c'est un string simple (ancien format)
    else if (is_string($route)) {
        require __DIR__ . '/../' . $route;
    }
    // Traitement des routes avec méthodes GET/POST (nouveau format)
    else if (is_array($route) && !isset($route['handler']) && !isset($route['middleware'])) {
        require __DIR__ . '/../' . $route[$method];
    }
    // Traitement direct du handler
    else if (isset($route['handler'])) {
        require __DIR__ . '/../' . $route['handler'];
    }
} else {
   // Route non trouvée
   http_response_code(404);
   echo "Page non trouvée.";
}
        
// Ajouter le footer à la fin
require 'footer.php';

// Fin de la mise en tampon de sortie
ob_end_flush();

