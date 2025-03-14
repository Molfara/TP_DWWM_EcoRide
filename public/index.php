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
   
   // Nouvelle route pour la sélection de rôle
   'role' => [
       'middleware' => 'auth',
       'handler' => 'pages/role.php'
   ],
   
   // Nouvelle route pour le traitement de la sélection de rôle
   'traitement/role' => [
       'middleware' => 'auth',
       'handler' => 'traitement/role.php'
   ],

   // Routes protégées - Espace utilisateur
   'mon-compte' => [
       'middleware' => 'auth',
       'handler' => 'pages/mon-compte.php'
   ],
   'mes-trajets' => [
       'middleware' => 'auth',
       'handler' => 'pages/mes-trajets.php'
   ],

   // Routes protégées - Espace administrateur
   'admin/dashboard' => [
       'middleware' => 'admin',
       'handler' => 'pages/admin/dashboard.php'
   ]
];

// Logique de routage
if (array_key_exists($request, $routes)) {
   $route = $routes[$request];

   // Vérification du middleware si présent
   if (isset($route['middleware'])) {
       // Inclure le fichier d'authentification et la fonction middleware correspondante
       include_once __DIR__ . '/../middleware/auth.php';
       
       // Vérifier le type de middleware requis
       if ($route['middleware'] === 'auth') {
           // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
           if (!isset($_SESSION['user_id'])) {
               header('Location: /se-connecter');
               exit();
           }
       } else if ($route['middleware'] === 'admin') {
           // Si l'utilisateur n'est pas admin, rediriger vers la page d'accès refusé
           if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
               header('Location: /acces-refuse');
               exit();
           }
       }
       
       // Si l'authentification est réussie, charger la page demandée
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
