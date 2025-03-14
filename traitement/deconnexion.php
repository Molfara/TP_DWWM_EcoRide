<?php
// traitement/deconnexion.php

// Démarrer la session
session_start();

// Supprimer toutes les données de session
$_SESSION = array();

// Détruire le cookie de session s'il existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil
header('Location: /');
exit;
?>
