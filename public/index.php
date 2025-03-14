<?php
require 'header.php';

// Récupérer l'URI de la requête et supprimer les slashes
$request = trim($_SERVER['REQUEST_URI'], '/');

// Récupérer la méthode HTTP utilisée
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
   // Routes publiques
   '' => 'pages/accueil.php',
   'covoiturage' => 'pages/covoiturage.php',
   'contact' => 'pages/contact.php',
   'se-connecter' => [
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

// Ajouter la route pour la déconnexion
'deconnexion' => 'traitement/deconnexion.php',

   // Route pour espace passager
   'espace-passager' => [
       'middleware' => 'checkAuth',
       'handler' => 'pages/espace_passager.php'   
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
