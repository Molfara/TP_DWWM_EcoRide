<?php
// Démarrage de la session pour gérer l'authentification de l'utilisateur
// Inclusion du fichier d'authentification pour vérifier les droits d'accès
require_once __DIR__ . '/../middleware/auth.php';

// Vérification si l'utilisateur est connecté et a le rôle de passager
// Si non, redirection vers la page de connexion
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'passager') {
    header('Location: connexion.php');
    exit;
}

// Récupération des informations de l'utilisateur depuis la base de données
require_once __DIR__ . '/../config/database.php';
$userId = $_SESSION['user_id'];
try {
    // Préparation et exécution de la requête pour obtenir les données de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gestion des erreurs de base de données
    $error = "Erreur de base de données: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Passager - CoVoiturage</title>
    <!-- Inclusion de la feuille de style principale -->
    <link rel="stylesheet" href="../public/style.css">
</head>
<body>
    <?php
    // Inclusion de l'en-tête commun du site
    include_once '../public/header.php';
    ?>

<main class="container">
    <div class="hero-background passenger-hero">
        <div class="hero-content passenger-content">
            <h1>Espace Passager</h1>
            <a href="changer_role.php?role=chauffeur" class="btn btn-white">Changer pour chauffeur</a>
        </div>
    </div>
    
    <?php
    // Affichage des messages d'erreur si présents
    if (isset($error)):
    ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php
    endif;
    ?>

<!-- Titre principal de la page -->
    <h2 class="page-title">Gérez vos déplacements en toute simplicité</h2>
    
<!-- Actions principales disponibles pour le passager -->
<div class="passager-action-container">
    <div class="action-block">
        <div class="action-icon">

        <img src="/images/icons/search-icon.png" alt="Rechercher covoiturage">
        </div>
        <p class="action-text">Trouvez rapidement un trajet partagé adapté à votre itinéraire. Comparez les offres, choisissez votre covoitureur et voyagez en toute sérénité. Ensemble, réduisons notre empreinte carbone !</p>
        <a href="recherche_covoiturage.php" class="role-button">Rechercher covoiturage</a>
    </div>
    
    <div class="action-block">
        <div class="action-icon">
            <img src="/images/icons/history-icon.png" alt="Regarder mes trajets">
        </div>
        <p class="action-text">Accédez à votre historique de trajets et vos réservations en un clic. Consultez les détails, gérez vos trajets et planifiez vos prochains déplacements. Chaque trajet partagé, c'est un geste pour la planète !</p>
        <a href="mes_trajets.php" class="role-button">Regarder mes trajets</a>
    </div>
</div>
