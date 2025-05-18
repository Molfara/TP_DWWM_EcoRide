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

// Vérification pour profil-passager.php
if ($request === 'traitement/profil-passager.php' && $method === 'POST') {
    require __DIR__ . '/../traitement/profil-passager.php';
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


 // Route pour ajouter la première voiture
'ajouter-voiture' => [
    'middleware' => 'checkAuth',  // Vérification de l'authentification
    'handler' => 'pages/ajouter-voiture.php'
],

];
     
// Logique de routage 
if (array_key_exists($request, $routes)) {
   $route = $routes[$request];
 
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
   // Traitement des routes avec méthodes GET/POST
   else if (is_array($route) && !isset($route['handler'])) {
       require __DIR__ . '/../' . $route[$method];
   }
   // Traitement des routes simples
   else {
       require __DIR__ . '/../' . $route;
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

