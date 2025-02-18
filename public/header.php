<?php
// public/header.php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRide</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">

</head>
<body>
<header>
    <div class="logo">
        <a href="/">
            <img src="images/logo.png" alt="EcoRide Logo">
        </a>
    </div>
    <nav>
        <ul>
            <li><a href="/">Accueil</a></li>
            <li><a href="/covoiturage">Covoiturage</a></li>


<li class="dropdown">
    <a href="#">Connexion</a>
    <ul class="dropdown-menu">
        <li><a href="/connexion">Se connecter</a></li>
        <li><a href="/inscription">Créer un compte</a></li>
    </ul>
</li>

            <li><a href="/contact">Contact</a></li>
        </ul>
    </nav>
</header>

