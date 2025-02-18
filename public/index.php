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
       'GET' => 'pages/login.php',
       'POST' => 'traitement/login.php'
   ],
   'inscription' => [
       'GET' => 'pages/inscription.php',
       'POST' => 'traitement/inscription.php'
   ],
   
   // Routes protégées - Espace utilisateur
   'mon-compte' => [
       'middleware' => 'checkAuth',
       'handler' => 'pages/mon-compte.php'
   ],
   'mes-trajets' => [
       'middleware' => 'checkAuth',
       'handler' => 'pages/mes-trajets.php'
   ],
   
   // Routes protégées - Espace administrateur
   'admin/dashboard' => [
       'middleware' => 'checkAdmin',
       'handler' => 'pages/admin/dashboard.php'
   ]
];

// Logique de routage
if (array_key_exists($request, $routes)) {
   $route = $routes[$request];
   
   // Vérification du middleware si présent
   if (isset($route['middleware'])) {
       require_once 'middleware/auth.php';
       $middleware = $route['middleware'];
       $middleware();
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
