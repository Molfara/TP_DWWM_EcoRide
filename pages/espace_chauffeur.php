<?php
// Démarrage de la session pour gérer l'authentification de l'utilisateur
// Inclusion du fichier d'authentification pour vérifier les droits d'accès
require_once __DIR__ . '/../middleware/auth.php';

// Vérification si l'utilisateur est connecté et a le rôle de chauffeur
// Si non, redirection vers la page de connexion
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'chauffeur') {
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
    <title>Espace Chauffeur - CoVoiturage</title>
    <!-- Inclusion de la feuille de style principale -->
    <link rel="stylesheet" href="../public/style.css">
</head>
<body>
    <?php
    // Inclusion de l'en-tête commun du site
    include_once '../public/header.php';
    ?>

<main class="container">
    <div class="hero-background driver-hero">
        <div class="chauffeur-content">
            <h1>Espace Chauffeur</h1>
            <a href="changer_role.php?role=passager" class="btn btn-white">Changer pour passager</a>
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
    <h2 class="page-title">Optimisez vos déplacements, réduisez les émissions de CO₂</h2>

<!-- Actions principales disponibles pour le chauffeur -->
    <div class="action-container">
        <div class="action-row">
            <div class="action-block">
                <p class="action-text">Publiez facilement votre trajet et trouvez des passagers en quelques clics. Optimisez vos déplacements, partagez les frais et contribuez à une mobilité plus durable.</p>
                <div class="button-container">
                    <a href="proposer_covoiturage.php" class="role-button">Proposer un covoiturage</a>
                </div>
            </div>
            
            <div class="action-block">
                <p class="action-text">Retrouvez l'historique de vos trajets et vos réservations en un instant. Gérez vos covoiturages, suivez vos passagers et ajustez vos plans selon vos besoins.</p>
                <div class="button-container">
                    <a href="mes_trajets.php" class="role-button">Regarder mes trajets</a>
                </div>
            </div>
        </div>
        
        <div class="action-row">
            <div class="action-block">
                <p class="action-text">Consultez vos gains et suivez l'évolution de votre solde en temps réel. Profitez de vos crédits pour vos futurs trajets ou transférez-les selon vos préférences.</p>
                <div class="button-container">
                    <a href="mes_credits.php" class="role-button">Vérifier mes crédits</a>
                </div>
            </div>
            
            <div class="action-block">
                <p class="action-text">Ajoutez et gérez plusieurs véhicules en toute simplicité. Indiquez les caractéristiques, la capacité et soyez prêt à proposer de nouveaux trajets en quelques secondes.</p>
                <div class="button-container">
                    <a href="gestion_vehicules.php" class="role-button">Ajouter une voiture</a>
                </div>
            </div>
        </div>
    </div>
</main>

