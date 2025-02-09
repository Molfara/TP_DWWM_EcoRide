
<?php
$request = trim($_SERVER['REQUEST_URI'], '/');

$routes = [
    '' => 'pages/accueil.php',
    'covoiturage' => 'pages/covoiturage.php',
    'connection' => 'pages/connection.php',
    'contact' => 'pages/contact.php'
];

if (array_key_exists($request, $routes)) {
    require __DIR__ . '/../' . $routes[$request];
} else {
    http_response_code(404);
    echo "Page non trouvée.";
}

