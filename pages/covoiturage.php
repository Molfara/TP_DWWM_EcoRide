<?php
// Session déjà démarrée dans header.php
require_once '../config/database.php'; // Chemin vers votre fichier de configuration de base de données
?>


<div class="hero-background">
    <div class="hero-content">
        <h1>Covoiturage Simple et Écolo</h1>

        <!-- Section de recherche -->
        <form method="POST" action="" id="search-form">
            <div class="search-panel">
                <div class="search-field">
                    <input type="text" name="lieu_depart" placeholder="Départ" class="action-text" value="<?php echo isset($_POST['lieu_depart']) ? htmlspecialchars($_POST['lieu_depart']) : ''; ?>">
                </div>

                <div class="search-field">
                    <input type="text" name="lieu_arrivee" placeholder="Destination" class="action-text" value="<?php echo isset($_POST['lieu_arrivee']) ? htmlspecialchars($_POST['lieu_arrivee']) : ''; ?>">
                </div>

                <!-- Division avec le calendrier simple -->
                <div class="search-field date-field">
                    <input type="date" id="trip_date" name="date_depart" class="date-input" value="<?php echo isset($_POST['date_depart']) ? htmlspecialchars($_POST['date_depart']) : ''; ?>">
                </div>

                <!-- Champ pour le nombre de passagers avec menu déroulant -->
                <div class="search-field" id="passenger-field">
                    <div class="passenger-display">1 passager</div>
                    <input type="hidden" name="nb_passagers" id="nb_passagers" value="1">

                    <!-- Dropdown pour les passagers -->
                    <div class="passenger-dropdown" id="passenger-dropdown">
                        <div class="passenger-counter">
                            <div class="passenger-label">Passager</div>
                            <div class="counter-controls">
                                <button type="button" class="counter-btn minus" id="passenger-minus">−</button>
                                <span class="counter-value" id="passenger-count">1</span>
                                <button type="button" class="counter-btn plus" id="passenger-plus">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" name="rechercher" class="search-button">Rechercher</button>
            </div>
        </form>

        <!-- Section des filtres -->
        <div class="filters-panel">
            <div class="filter-item">
                <label class="filter-label">
                    <input type="checkbox" id="eco-filter">
                    <span class="checkmark"></span>
                    Véhicule électrique
                </label>
            </div>
            <div class="filter-item">
                <label class="filter-label">Prix maximum</label>
                <div class="price-slider-container">
                    <input type="range" min="0" max="20" value="10" class="price-slider" id="max-price">
                    <span class="price-value" id="price-value">10  Credits</span>
                </div>
            </div>
            <div class="filter-item">
                <label class="filter-label">Durée maximum</label>
                <div class="duration-slider-container">
                    <input type="range" min="30" max="720" value="240" step="30" class="duration-slider" id="max-duration">
                    <span class="duration-value" id="duration-value">4h</span>
                </div>
            </div>
            <div class="filter-item">
                <label class="filter-label">Note minimale</label>
                <div class="rating-filter">
                    <div class="stars-container" id="rating-stars">
                        <span class="star" data-value="1">★</span>
                        <span class="star" data-value="2">★</span>
                        <span class="star" data-value="3">★</span>
                        <span class="star" data-value="4">★</span>
                        <span class="star" data-value="5">★</span>
                    </div>
                    <span class="rating-value" id="rating-value">3</span>
                </div>
            </div>
            <div class="filter-item">
                <button class="apply-filters-button">Appliquer les filtres</button>  
            </div>
        </div>
    </div>
</div>
            
<!-- Section pour afficher les résultats des covoiturages -->
<div class="container rides-section">
    <h2>Covoiturages disponibles</h2>
    <div id="rides-results">
        <?php
        // Traitement de la recherche si le formulaire a été soumis
        if (isset($_POST['rechercher'])) {
            // Récupération des valeurs soumises
            $lieu_depart = isset($_POST['lieu_depart']) ? $_POST['lieu_depart'] : '';
            $lieu_arrivee = isset($_POST['lieu_arrivee']) ? $_POST['lieu_arrivee'] : '';
            $date_depart = isset($_POST['date_depart']) ? $_POST['date_depart'] : '';
            $nb_passagers = isset($_POST['nb_passagers']) ? intval($_POST['nb_passagers']) : 1;
            
            // Préparation de la requête SQL
            $sql = "SELECT c.*, u.nom, u.prenom, v.modele, v.energie, v.couleur, m.libelle as marque
                   FROM covoiturage c
                   INNER JOIN utilisateur u ON c.conducteur_id = u.utilisateur_id
                   INNER JOIN voiture v ON c.voiture_id = v.voiture_id
                   INNER JOIN marque m ON v.marque_id = m.marque_id
                   WHERE 1=1";
            
            $params = [];
            
            // Ajout des conditions de filtrage
            if (!empty($lieu_depart)) {
                $sql .= " AND c.lieu_depart = ?";
                $params[] = $lieu_depart;
            }
            
            if (!empty($lieu_arrivee)) {
                $sql .= " AND c.lieu_arrivee = ?";
                $params[] = $lieu_arrivee;
            }
            
            if (!empty($date_depart)) {
                $sql .= " AND c.date_depart = ?";
                $params[] = $date_depart;
            }
            
            // Filtre sur le nombre de places disponibles
            $sql .= " AND c.nb_place >= ?";
            $params[] = $nb_passagers;
            
            // Ordonner par date et heure de départ
            $sql .= " ORDER BY c.date_depart ASC, c.heure_depart ASC";
            
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $covoiturages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($covoiturages) == 0) {
                    echo '<div class="no-results-message">Aucun trajet ne correspond à votre recherche.</div>';
                } else {
                    // Affichage des résultats
                    foreach ($covoiturages as $covoiturage) {
                        // Calculer la note moyenne du conducteur
                        $stmt = $pdo->prepare("SELECT AVG(note) as note_moyenne FROM avis WHERE utilisateur_id = ?");
                        $stmt->execute([$covoiturage['conducteur_id']]);
                        $note = $stmt->fetch(PDO::FETCH_ASSOC);
                        $note_moyenne = round($note['note_moyenne'] ?: 0, 1);
                        
                        // Afficher chaque covoiturage
                        ?>
                        <div class="ride-card">
                            <div class="ride-info">
                                <div class="ride-header">
                                    <span class="ride-date"><?php echo date('d/m/Y', strtotime($covoiturage['date_depart'])); ?></span>
                                    <span class="ride-time"><?php echo $covoiturage['heure_depart']; ?></span>
                                </div>
                                <div class="ride-route">
                                    <div class="departure"><?php echo htmlspecialchars($covoiturage['lieu_depart']); ?></div>
                                    <div class="destination"><?php echo htmlspecialchars($covoiturage['lieu_arrivee']); ?></div>
                                </div>
                                <div class="ride-details">
                                    <div class="car-info">
                                        <span class="car-brand"><?php echo htmlspecialchars($covoiturage['marque']); ?></span>
                                        <span class="car-model"><?php echo htmlspecialchars($covoiturage['modele']); ?></span>
                                        <span class="car-energy"><?php echo htmlspecialchars($covoiturage['energie']); ?></span>
                                    </div>
                                    <div class="driver-info">
                                        <span class="driver-name"><?php echo htmlspecialchars($covoiturage['prenom'] . ' ' . substr($covoiturage['nom'], 0, 1) . '.'); ?></span>
                                        <div class="driver-rating">
                                            <?php 
                                            for ($i = 1; $i <= 5; $i++) {
                                                $starClass = $i <= $note_moyenne ? 'star active' : 'star';
                                                echo '<span class="' . $starClass . '">★</span>';
                                            }
                                            ?>
                                            <span class="rating-value">(<?php echo $note_moyenne; ?>)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="ride-price">
                                <div class="price-amount"><?php echo number_format($covoiturage['prix_personne'], 2); ?> Credits</div>
                                <div class="seats-available"><?php echo $covoiturage['nb_place']; ?> places disponibles</div>
                                <a href="reservation.php?id=<?php echo $covoiturage['covoiturage_id']; ?>" class="book-button">Réserver</a>
                            </div>
                        </div>
                        <?php
                    }
                }
            } catch (PDOException $e) {
                echo '<div class="no-results-message">Erreur lors de la recherche : ' . $e->getMessage() . '</div>';
            }
        } else {
            // Aucune recherche effectuée
            echo '<div class="no-results-message">Utilisez la barre de recherche pour trouver des covoiturages.</div>';
        }
        ?>
    </div>
</div>
                    
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du menu dropdown des passagers
    const passengerField = document.getElementById('passenger-field');
    const passengerDropdown = document.getElementById('passenger-dropdown');
    const passengerMinus = document.getElementById('passenger-minus');
    const passengerPlus = document.getElementById('passenger-plus');
    const passengerCount = document.getElementById('passenger-count');
    const passengerDisplay = document.querySelector('.passenger-display');
    const nbPassagersInput = document.getElementById('nb_passagers');
                
    // Ouverture du dropdown au clic
    passengerField.addEventListener('click', function(e) {
        e.stopPropagation();
        passengerDropdown.style.display = passengerDropdown.style.display === 'block' ? 'none' : 'block';
    });
                    
    // Fermeture du dropdown quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!passengerField.contains(e.target)) {
            passengerDropdown.style.display = 'none';
        }
    });
                        
    // Empêcher la fermeture lors des clics à l'intérieur du dropdown
    passengerDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
                    
    // Gestion du compteur de passagers
    let count = 1;
            
    passengerMinus.addEventListener('click', function() {
        if (count > 1) {
            count--;
            updatePassengerCount();
        } 
    });
            
    passengerPlus.addEventListener('click', function() {
        if (count < 8) { // Maximum 8 passagers
            count++;
            updatePassengerCount();
        }
    });
    
    function updatePassengerCount() {
        passengerCount.textContent = count;
        passengerDisplay.textContent = `${count} ${count === 1 ? 'passager' : 'passagers'}`;
        nbPassagersInput.value = count; // Mise à jour de l'input caché
    }
    
    // Gestion du slider pour le prix
    const priceSlider = document.getElementById('max-price');
    const priceValue = document.getElementById('price-value');
    
    priceSlider.addEventListener('input', function() {
        priceValue.textContent = this.value + ' Credits';
    });         
    
    // Gestion du slider pour la durée
    const durationSlider = document.getElementById('max-duration');
    const durationValue = document.getElementById('duration-value');
       
    durationSlider.addEventListener('input', function() {
        const hours = Math.floor(this.value / 60);   
        const minutes = this.value % 60;
        
        if (hours === 0) {
            durationValue.textContent = `${minutes}min`;
        } else if (minutes === 0) {
            durationValue.textContent = `${hours}h`;
        } else {
            durationValue.textContent = `${hours}h${minutes}min`;
        }
    });
                    
    // Gestion des étoiles pour la notation
    const stars = document.querySelectorAll('.star');
    const ratingValue = document.getElementById('rating-value');
    let currentRating = 3; // Valeur par défaut
        
    updateStars(currentRating);
            
    stars.forEach(star => {
        star.addEventListener('click', function() {
            currentRating = parseInt(this.getAttribute('data-value'));
            ratingValue.textContent = currentRating;
            updateStars(currentRating);
        });
            
        // Effet hover
        star.addEventListener('mouseover', function() {
            const hoverValue = parseInt(this.getAttribute('data-value'));
            highlightStars(hoverValue);
        });
        star.addEventListener('mouseout', function() {
            highlightStars(currentRating);
        });
    });
    
    function updateStars(value) {
        stars.forEach(star => {
            const starValue = parseInt(star.getAttribute('data-value'));
            if (starValue <= value) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }
        
    function highlightStars(value) {
        stars.forEach(star => {
            const starValue = parseInt(star.getAttribute('data-value'));
            if (starValue <= value) {
                star.classList.add('highlighted');
            } else {
                star.classList.remove('highlighted');
            }
        });
    }  
                    
    // Action du bouton d'application des filtres
    const applyFiltersButton = document.querySelector('.apply-filters-button');
    
    applyFiltersButton.addEventListener('click', function() {
        // Récupération des valeurs des filtres
        const isEcoVehicle = document.getElementById('eco-filter').checked;
        const maxPrice = priceSlider.value;
        const maxDuration = durationSlider.value;
        const minRating = currentRating;
            
        // Créer des inputs cachés pour les filtres
        let form = document.getElementById('search-form');
        
        // Supprimer les anciens inputs cachés s'ils existent
        document.querySelectorAll('input[name="eco_filter"], input[name="max_price"], input[name="max_duration"], input[name="min_rating"]').forEach(input => input.remove());
        
        // Créer et ajouter les nouveaux inputs
        let ecoInput = document.createElement('input');
        ecoInput.type = 'hidden';
        ecoInput.name = 'eco_filter';
        ecoInput.value = isEcoVehicle ? '1' : '0';
        form.appendChild(ecoInput);
        
        let priceInput = document.createElement('input');
        priceInput.type = 'hidden';
        priceInput.name = 'max_price';
        priceInput.value = maxPrice;
        form.appendChild(priceInput);
        
        let durationInput = document.createElement('input');
        durationInput.type = 'hidden';
        durationInput.name = 'max_duration';
        durationInput.value = maxDuration;
        form.appendChild(durationInput);
        
        let ratingInput = document.createElement('input');
        ratingInput.type = 'hidden';
        ratingInput.name = 'min_rating';
        ratingInput.value = minRating;
        form.appendChild(ratingInput);
        
        // Soumettre le formulaire
        form.submit();
    });
}); 
</script>
