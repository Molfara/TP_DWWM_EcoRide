<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRide</title>
    <link rel="stylesheet" href="/style.css?v=<?php echo time(); ?>">

</head>
<body>
<header>
    <div class="logo">
        <a href="/">
            <img src="/images/logo.png" alt="EcoRide Logo">
        </a>
    </div>

<!-- Bouton du menu burger (visible uniquement sur mobile) -->
    <div class="burger-menu">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <nav>
        <ul class="nav-menu">
            <li><a href="/">Accueil</a></li>
            <li><a href="/covoiturage">Covoiturage</a></li>
            <li><a href="/contact">Contact</a></li>

            <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- Afficher ceci pour les utilisateurs non connectés -->
            <li class="dropdown">
                <span class="dropdown-toggle">Connexion</span>
                <ul class="dropdown-menu">
                    <li><a href="/connexion">Se connecter</a></li>
                    <li><a href="/inscription">Créer un compte</a></li>
                </ul>
            </li>
            <?php else: ?>
            <!-- Afficher ceci pour les utilisateurs connectés -->
            <li class="dropdown user-profile">

            <span class="dropdown-toggle user-avatar">
<?php 
if (isset($_SESSION['user_id'])) {
    // Connexion à la base de données pour vérifier la présence d'un avatar
    require_once __DIR__ . '/../config/database.php';
    
    $current_user_id = $_SESSION['user_id'];
    $current_user_pseudo = $_SESSION['user_pseudo'] ?? 'U';
    
    // Récupération de la photo depuis la base de données
    try {
        $stmt_photo = $pdo->prepare("SELECT photo FROM utilisateur WHERE utilisateur_id = ?");
        $stmt_photo->execute([$current_user_id]);
        $user_photo = $stmt_photo->fetch(PDO::FETCH_ASSOC);
        
        if ($user_photo && !empty($user_photo['photo'])): 
            // Conversion du BLOB en base64 pour l'affichage
            $image_data = base64_encode($user_photo['photo']);
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->buffer($user_photo['photo']);
?>
    <img src="data:<?php echo $mime_type; ?>;base64,<?php echo $image_data; ?>" alt="Profil">
<?php else: ?>
    <div class="avatar-placeholder"><?php echo htmlspecialchars(strtoupper(substr($current_user_pseudo, 0, 1))); ?></div>
<?php 
        endif;
    } catch (PDOException $e) {
        // En cas d'erreur, affichage du placeholder
        error_log("Erreur avatar header: " . $e->getMessage());
?>
    <div class="avatar-placeholder"><?php echo htmlspecialchars(strtoupper(substr($current_user_pseudo, 0, 1))); ?></div>
<?php 
    }
} else { 
?>
    <div class="avatar-placeholder">U</div>
<?php } ?>
</span>
                <!-- Menu différent selon le rôle -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'passager'): ?>
                <!-- Menu pour les passagers -->
                <ul class="dropdown-menu user-menu">
                    <li><a href="/espace-passager">Mon espace</a></li>
                    <li><a href="/profil-passager">Mon profil</a></li>
                    <li><a href="/covoiturage">Covoiturage</a></li>
                    <li><a href="/trajets-passager">Mes trajets</a></li>
                    <li><a href="/deconnexion.php">Déconnecter</a></li>
                </ul>
        
                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'chauffeur'): ?>
                <!-- Menu pour les chauffeurs -->
                <ul class="dropdown-menu user-menu">
                    <li><a href="/espace-chauffeur">Mon espace</a></li>
                    <li><a href="/profil-chauffeur">Mon profil</a></li>
                    <li><a href="/trajets-chauffeur">Mes trajets</a></li>
                    <li><a href="/proposer-trajet">Proposer un trajet</a></li>
                    <li><a href="/deconnexion.php">Déconnecter</a></li>
                </ul>
                
                <?php elseif (isset($_SESSION['temp_role']) && $_SESSION['temp_role'] == 'chauffeur'): ?>
                <!-- Menu pour chauffeur qui doit ajouter une voiture -->
                <ul class="dropdown-menu user-menu">
                    <li><a href="/role/passager">Je suis passager</a></li>
                    <li><a href="/role/chauffeur">Je suis chauffeur</a></li>
                    <li><a href="/deconnexion.php">Déconnecter</a></li>
                </ul>
            
                <?php else: ?>
                <!-- Menu par défaut (choix du rôle) -->
                <ul class="dropdown-menu user-menu">
                    <li><a href="/role/passager">Je suis passager</a></li>
                    <li><a href="/role/chauffeur">Je suis chauffeur</a></li>
                    <li><a href="/deconnexion.php">Déconnecter</a></li>
                </ul>
                <?php endif; ?>
            
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
                    
<!-- JavaScript pour le menu burger et les dropdowns mobiles -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonctionnalité du menu burger
    const burgerMenu = document.querySelector('.burger-menu');
    const navMenu = document.querySelector('.nav-menu');

    burgerMenu.addEventListener('click', function() {
        this.classList.toggle('active');
        navMenu.classList.toggle('active');
    });
                    
    // Fonctionnalité pour les dropdowns en version mobile
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
                
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            // Vérifier si on est en version mobile
            if (window.innerWidth <= 768) {
                e.preventDefault();
                // Trouver le parent dropdown
                const parentDropdown = this.closest('.dropdown');
                parentDropdown.classList.toggle('active');
            }
        }); 
    });
                
    // Fermeture du menu lors d'un clic sur un lien 
    const navLinks = document.querySelectorAll('.nav-menu a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            burgerMenu.classList.remove('active');
            navMenu.classList.remove('active');
            
            // Réinitialiser tous les dropdowns
            const allDropdowns = document.querySelectorAll('.dropdown');
        
           allDropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });     
        });
    }); 

    // Fermer le menu lors d'un clic à l'extérieur
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            const isClickInsideNav = e.target.closest('nav');
            const isClickOnBurger = e.target.closest('.burger-menu');
            const isClickOnDropdownToggle = e.target.closest('.dropdown-toggle');
        
            if (!isClickInsideNav && !isClickOnBurger && navMenu.classList.contains('active') && !isClickOnDropdownToggle) {
                navMenu.classList.remove('active');
                burgerMenu.classList.remove('active');
            }
        }       
    });
        
    // Réinitialiser lors du redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            navMenu.classList.remove('active');
            burgerMenu.classList.remove('active');
                
            const allDropdowns = document.querySelectorAll('.dropdown');
            allDropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            }); 
        }
    });
});
</script>
