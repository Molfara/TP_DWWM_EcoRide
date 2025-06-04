<?php
// Démarrage de la session pour gérer l'authentification de l'utilisateur
// Inclusion du fichier d'authentification pour vérifier les droits d'accès
require_once __DIR__ . '/../middleware/auth.php';

// Vérification si l'utilisateur est connecté et a le rôle de chauffeur
// Si non, redirection vers la page de connexion
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'chauffeur') {
    header('Location: connexion');
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

// Récupération des véhicules de l'utilisateur
$vehicules = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM voiture WHERE utilisateur_id = ?");
    $stmt->execute([$userId]);
    $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des véhicules: " . $e->getMessage();
}

// Récupération des marques disponibles
$marques = [];
try {
    // Utilisez GROUP BY pour éliminer les doublons
    $stmt = $pdo->query("SELECT MIN(marque_id) as marque_id, libelle 
                         FROM marque 
                         GROUP BY libelle 
                         ORDER BY libelle");
    $marques = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Получено уникальных марок: " . count($marques));
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des marques: " . $e->getMessage();
    error_log($error);
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

<main class="container page-proposer-trajet">
    <div class="hero-background driver-hero">
        <div class="chauffeur-content">
            <h1>Espace Chauffeur</h1>
            <a href="role" class="btn btn-white">Changer pour passager</a>
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
    <h2 class="page-title">Un trajet à offrir ? Précisez les détails</h2>

<!-- Conteneur pour placer les sections côte à côte -->
<div class="trip-forms-container">

    <!-- Section Paramètres de covoiturage -->
    <div class="trip-params-section">
        <!-- Header de la section INCLUS dans le bloc blanc -->
        <div class="section-header">
            <h2>Paramètres de covoiturage</h2>
            <p>Complétez les infos de votre prochain trajet</p>
        </div>
        
        <form id="tripForm" method="POST" action="/proposer-trajet">

                <!-- Date et heure de départ -->
                <div class="form-group">
                    <label for="date_depart">Date de départ :</label>
                    <input type="date" id="date_depart" name="date_depart" required>
                </div>
                
                <div class="form-group">
                    <label for="heure_depart">Heure de départ :</label>
                    <input type="time" id="heure_depart" name="heure_depart" required>
                </div>
                
                <!-- Date et heure d'arrivée -->
                <div class="form-group">
                    <label for="date_arrivee">Date d'arrivée :</label>
                    <input type="date" id="date_arrivee" name="date_arrivee" required>
                </div>
                
                <div class="form-group">
                    <label for="heure_arrivee">Heure d'arrivée :</label>
                    <input type="time" id="heure_arrivee" name="heure_arrivee" required>
                </div>
                
                <!-- Lieux de départ et d'arrivée -->
                <div class="form-group full-width">
                    <label for="lieu_depart">Lieu de départ :</label>
                    <input type="text" id="lieu_depart" name="lieu_depart" placeholder="Saisir l'adresse de départ" required>
                </div>
                
                <div class="form-group full-width">
                    <label for="lieu_arrivee">Lieu d'arrivée :</label>
                    <input type="text" id="lieu_arrivee" name="lieu_arrivee" placeholder="Saisir l'adresse d'arrivée" required>
                </div>
                
                <!-- Prix par place -->
<div class="form-group">
    <label for="prix_place">Prix par place* :</label>
    <div class="price-input">
    <div class="price-input" style="max-width: 50%;">
        <input type="text" id="prix_place" name="prix_place" placeholder="0.00" required>
        <span class="currency">Crédits</span>
    </div>
</div>
            </div>
            
            <!-- Note explicative pour le prix -->
            <div class="form-note">
                <p><strong>*</strong> Veuillez noter que 2 crédits sont prélevés par la plateforme pour assurer la qualité du service et la sécurité des transactions.</p>
            </div>

            <!-- Champ caché pour stocker l'ID du véhicule sélectionné - IMPORTANT: déplacé dans le formulaire -->
        <input type="hidden" id="selected_vehicle_id" name="selected_vehicle_id" 
            value="<?php echo !empty($vehicules) ? $vehicules[0]['voiture_id'] : ''; ?>">
        </form>
    </div>

<!-- Bloc de sélection du véhicule avec le nouveau style -->
<div class="vehicle-container">
    <h2 class="vehicle-title">Ma voiture pour trajet</h2>
    <p class="vehicle-subtitle">Vous pouvez gérer les infos de votre véhicule depuis " Mon profil "</p>
    
    <?php
    // Affichage du message de succès pour le changement de véhicule
    if (isset($_SESSION['vehicle_message']) && !empty($_SESSION['vehicle_message'])) {
        echo '<div class="message success">' . htmlspecialchars($_SESSION['vehicle_message']) . '</div>';
        unset($_SESSION['vehicle_message']);
    }
    ?>
    
    <!-- Message de confirmation après changement (pour JavaScript) -->
    <div id="changeConfirmation" class="message success" style="display: none;">
        Véhicule changé avec succès pour ce trajet
    </div>
    
    <!-- Affichage du véhicule sélectionné -->
    <div id="selectedVehicleDisplay" class="vehicle-data">
        <?php if (!empty($vehicules)): ?>
            <?php $selectedVehicle = $vehicules[0]; // Premier véhicule par défaut ?>
            <div class="vehicle-display-item">
                <div class="data-item">
                    <span class="data-label">Marque :</span>
                    <span class="data-value" id="selected-marque">
                        <?php 
                        foreach ($marques as $marque) {
                            if ($marque['marque_id'] == $selectedVehicle['marque_id']) {
                                echo htmlspecialchars($marque['libelle']);
                                break;
                            }
                        }
                        ?>
                    </span>
                </div>
                
                <div class="data-item">
                    <span class="data-label">Modèle :</span>
                    <span class="data-value" id="selected-modele"><?php echo htmlspecialchars($selectedVehicle['modele']); ?></span>
                </div>
                
                <div class="data-item">
                    <span class="data-label">Immatriculation :</span>
                    <span class="data-value" id="selected-immatriculation"><?php echo htmlspecialchars($selectedVehicle['immatriculation']); ?></span>
                </div>
                
                <div class="data-item">
                    <span class="data-label">Énergie :</span>
                    <span class="data-value" id="selected-energie"><?php echo htmlspecialchars(ucfirst($selectedVehicle['energie'])); ?></span>
                </div>
                
                <?php if (!empty($selectedVehicle['couleur'])): ?>
                <div class="data-item">
                    <span class="data-label">Couleur :</span>
                    <span class="data-value" id="selected-couleur"><?php echo htmlspecialchars($selectedVehicle['couleur']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($selectedVehicle['date_premiere_immatriculation'])): ?>
                <div class="data-item">
                    <span class="data-label">Date première immatriculation :</span>
                    <span class="data-value" id="selected-date"><?php echo htmlspecialchars($selectedVehicle['date_premiere_immatriculation']); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="data-item">
                    <span class="data-label">Nombre de places :</span>
                    <span class="data-value" id="selected-places"><?php echo htmlspecialchars($selectedVehicle['nb_places']); ?></span>
                </div>
            </div>
            
            <!-- Bouton pour changer de voiture -->
            <div class="action-buttons">
                <button type="button" id="changeVehicleBtn" class="btn btn-primary" onclick="showVehicleOptions()">
                    Changer la voiture
                </button>
            </div>
            
        <?php else: ?>
            <div class="no-vehicles">
                <p>Aucun véhicule enregistré. Veuillez d'abord ajouter un véhicule dans votre profil.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Liste complète des véhicules pour sélection (cachée par défaut) -->
    <div id="vehicleOptions" class="vehicles-display" style="display: none;">
        
        <?php foreach ($vehicules as $index => $vehicule): ?>
            <div class="vehicle-display-item">
                <div class="vehicle-header">
                    <h3>Véhicule <?php echo $index + 1; ?></h3>
                    <button type="button" class="btn btn-choisir btn-sm"
                       onclick="selectVehicle(<?php echo $index; ?>, <?php echo $vehicule['voiture_id']; ?>)">
                       Choisir
                    </button>
                </div>
                
                <div class="vehicle-details">
                    <div class="data-item">
                        <span class="data-label">Marque :</span>
                        <span class="data-value">
                            <?php 
                            // Trouver le libellé de la marque
                            foreach ($marques as $marque) {
                                if ($marque['marque_id'] == $vehicule['marque_id']) {
                                    echo htmlspecialchars($marque['libelle']);
                                    break;
                                }
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="data-item">
                        <span class="data-label">Modèle :</span>
                        <span class="data-value"><?php echo htmlspecialchars($vehicule['modele']); ?></span>
                    </div>
                    
                    <div class="data-item">
                        <span class="data-label">Immatriculation :</span>
                        <span class="data-value"><?php echo htmlspecialchars($vehicule['immatriculation']); ?></span>
                    </div>
                    
                    <div class="data-item">
                        <span class="data-label">Énergie :</span>
                        <span class="data-value"><?php echo htmlspecialchars(ucfirst($vehicule['energie'])); ?></span>
                    </div>
                    
                    <?php if (!empty($vehicule['couleur'])): ?>
                    <div class="data-item">
                        <span class="data-label">Couleur :</span>
                        <span class="data-value"><?php echo htmlspecialchars($vehicule['couleur']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($vehicule['date_premiere_immatriculation'])): ?>
                    <div class="data-item">
                        <span class="data-label">Date première immatriculation :</span>
                        <span class="data-value"><?php echo htmlspecialchars($vehicule['date_premiere_immatriculation']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="data-item">
                        <span class="data-label">Nombre de places :</span>
                        <span class="data-value"><?php echo htmlspecialchars($vehicule['nb_places']); ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

</div> 

<!-- Bouton centré "Proposer ce trajet" -->
<div class="proposer-trajet-container">
    <button type="submit" class="btn-proposer-trajet" form="tripForm">
        Proposer ce trajet
    </button>
</div>

</main> 
</body>
</html>

<script>
// Données des véhicules pour JavaScript
const vehiculesData = <?php echo json_encode($vehicules); ?>;
const marquesData = <?php echo json_encode($marques); ?>;

function showVehicleOptions() {
    document.getElementById('selectedVehicleDisplay').style.display = 'none';
    document.getElementById('vehicleOptions').style.display = 'block';
}

function selectVehicle(vehicleIndex, vehicleId) {
    const vehicle = vehiculesData[vehicleIndex];
    
    // Trouver la marque
    let marqueLibelle = '';
    marquesData.forEach(marque => {
        if (marque.marque_id == vehicle.marque_id) {
            marqueLibelle = marque.libelle;
        }
    });
    
    // Mettre à jour l'affichage du véhicule sélectionné
    document.getElementById('selected-marque').textContent = marqueLibelle;
    document.getElementById('selected-modele').textContent = vehicle.modele;
    document.getElementById('selected-immatriculation').textContent = vehicle.immatriculation;
    document.getElementById('selected-energie').textContent = vehicle.energie.charAt(0).toUpperCase() + vehicle.energie.slice(1);
    document.getElementById('selected-places').textContent = vehicle.nb_places;
    
// IMPORTANT : D'abord masquer TOUS les champs optionnels
const couleurElement = document.getElementById('selected-couleur');
const dateElement = document.getElementById('selected-date');

// Masquer les champs couleur et date par défaut
if (couleurElement) {
    couleurElement.closest('.data-item').style.display = 'none';
}
if (dateElement) {
    dateElement.closest('.data-item').style.display = 'none';
}

// Ensuite afficher et mettre à jour uniquement les champs qui ont des données pour le véhicule sélectionné

// Gestion du champ Couleur
if (vehicle.couleur && vehicle.couleur.trim() !== '') {
    if (couleurElement) {
        couleurElement.textContent = vehicle.couleur;
        couleurElement.closest('.data-item').style.display = 'flex'; // ou 'block'
    }
}

// Gestion du champ Date première immatriculation
if (vehicle.date_premiere_immatriculation && vehicle.date_premiere_immatriculation.trim() !== '') {
    if (dateElement) {
        dateElement.textContent = vehicle.date_premiere_immatriculation;
        dateElement.closest('.data-item').style.display = 'flex'; // ou 'block'
    }
}
    
    // Mettre à jour le champ caché
    document.getElementById('selected_vehicle_id').value = vehicleId;
    
    // Afficher le message de confirmation
    document.getElementById('changeConfirmation').style.display = 'block';
    
    // Revenir à l'affichage du véhicule sélectionné
    document.getElementById('vehicleOptions').style.display = 'none';
    document.getElementById('selectedVehicleDisplay').style.display = 'block';
    
}
</script>

