<?php
// pages/covoiturage.php
require_once '../config/database.php';

// Récupérer les résultats de recherche depuis la session
$search_results = $_SESSION['search_results'] ?? [];
$search_params = $_SESSION['search_params'] ?? [];
$search_error = $_SESSION['search_error'] ?? '';

// Récupérer les paramètres pour l'affichage dans le formulaire
$lieu_depart = $search_params['lieu_depart'] ?? '';
$lieu_arrivee = $search_params['lieu_arrivee'] ?? '';
$date_depart = $search_params['date_depart'] ?? '';
$nb_passagers = $search_params['nb_passagers'] ?? 1;
$eco_filter = $search_params['eco_filter'] ?? 0;
$max_price = $search_params['max_price'] ?? 10;
$max_duration = $search_params['max_duration'] ?? 240;
$min_rating = $search_params['min_rating'] ?? 3;
$current_user_role = $search_params['current_user_role'] ?? null;
$current_user_id = $search_params['current_user_id'] ?? null;

// Nettoyer les résultats de la session après récupération
unset($_SESSION['search_results'], $_SESSION['search_params'], $_SESSION['search_error']);
?>

<div class="page-covoiturage">

<!-- Section Hero avec arrière-plan, slogan et panneau de recherche -->
<div class="hero-background">
    <div class="hero-content">
        <h1>Covoiturage Simple et Écolo</h1>

        <!-- Panneau de recherche -->
        <form action="../traitement/search-trip.php" method="POST" id="search-form">
            <div class="search-panel">
                <div class="search-field">
                    <input type="text" name="lieu_depart" placeholder="Départ" class="action-text" 
                           value="<?php echo htmlspecialchars($lieu_depart); ?>">
                </div>

                <div class="search-field">
                    <input type="text" name="lieu_arrivee" placeholder="Destination" class="action-text"
                           value="<?php echo htmlspecialchars($lieu_arrivee); ?>">
                </div>

                <div class="search-field date-field">
                     <input type="date" id="trip_date" name="date_depart" class="date-input"
                            value="<?php echo $date_depart; ?>">
                </div>

                <div class="search-field" id="passenger-field">
                    <div class="passenger-display"><?php echo $nb_passagers . ($nb_passagers == 1 ? ' passager' : ' passagers'); ?></div>
                    
                    <!-- Menu déroulant pour les passagers -->
                    <div class="passenger-dropdown" id="passenger-dropdown">
                        <div class="passenger-counter">
                            <div class="passenger-label">Passager</div>
                            <div class="counter-controls">
                                <button type="button" class="counter-btn minus" id="passenger-minus">−</button>
                                <span class="counter-value" id="passenger-count"><?php echo $nb_passagers; ?></span>
                                <button type="button" class="counter-btn plus" id="passenger-plus">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" name="rechercher" class="search-button">Rechercher</button>
            </div>
            
            <!-- Champ caché pour le nombre de passagers -->
            <input type="hidden" name="nb_passagers" id="nb_passagers" value="<?php echo $nb_passagers; ?>">
        </form>
    </div>
</div>

<!-- Section des filtres -->
<div class="container">
    <div class="filters-panel">
        <div class="filter-item">
            <label class="filter-label">
                <input type="checkbox" id="eco-filter" <?php echo $eco_filter ? 'checked' : ''; ?>>
                <span class="checkmark"></span>
                Véhicule électrique
            </label>
        </div>
        
        <div class="filter-item">
            <label class="filter-label">Prix maximum</label>
            <div class="price-slider-container">
                <input type="range" min="0" max="20" value="<?php echo $max_price; ?>" class="price-slider" id="max-price">
                <span class="price-value" id="price-value"><?php echo $max_price; ?> Credits</span>
            </div>
        </div>
        
        <div class="filter-item">
            <label class="filter-label">Durée maximum</label>
            <div class="duration-slider-container">
                <input type="range" min="30" max="720" value="<?php echo $max_duration; ?>" step="30" class="duration-slider" id="max-duration">
                <span class="duration-value" id="duration-value">
                    <?php 
                    $hours = floor($max_duration / 60);
                    $minutes = $max_duration % 60;
                    if ($hours === 0) {
                        echo $minutes . 'min';
                    } else if ($minutes === 0) {
                        echo $hours . 'h';
                    } else {
                        echo $hours . 'h' . $minutes . 'min';
                    }
                    ?>
                </span>
            </div>
        </div>
        
        <div class="filter-item">
            <label class="filter-label">Note minimale</label>
            <div class="rating-filter">
                <div class="stars-container" id="rating-stars">
                    <?php 
                    for ($i = 1; $i <= 5; $i++) {
                        $active_class = $i <= $min_rating ? ' active' : '';
                        echo '<span class="star' . $active_class . '" data-value="' . $i . '">★</span>';
                    }
                    ?>
                </div>
                <span class="rating-value" id="rating-value"><?php echo $min_rating; ?></span>
            </div>
        </div>
        
        <div class="filter-item">
            <button type="button" class="apply-filters-button">Appliquer les filtres</button>  
        </div>
    </div>
</div>

<!-- Section pour afficher les résultats des covoiturages -->
<div class="container rides-section">
    <h2>Covoiturages disponibles</h2>
    <div id="rides-results">
        <?php if ($search_error): ?>
            <div class="no-results-message">Erreur : <?php echo htmlspecialchars($search_error); ?></div>
        <?php elseif (empty($search_results)): ?>
            <div class="no-results-message">
                <?php if (!empty($lieu_depart) || !empty($lieu_arrivee)): ?>
                    Aucun trajet ne correspond à votre recherche.
                <?php else: ?>
                    Utilisez la barre de recherche pour trouver des covoiturages.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($search_results as $covoiturage): ?>
    <div class="ride-card">
        <div class="ride-info">
        <div class="ride-header">
    <span class="ride-date"><?php echo date('d/m/Y', strtotime($covoiturage['date_depart'])); ?></span>
    <span class="ride-time"><?php echo $covoiturage['heure_depart'] . ' - ' . $covoiturage['heure_arrivee']; ?></span>
    <?php if (isset($covoiturage['energie']) && strtolower($covoiturage['energie']) === 'électrique'): ?>
        <img src="images/icons/electric-car-icon.png" alt="Électrique" class="electric-car-icon">
    <?php endif; ?>
</div>
            <div class="ride-route">
                <div class="departure"><?php echo htmlspecialchars($covoiturage['lieu_depart']); ?></div>
                <div class="destination"><?php echo htmlspecialchars($covoiturage['lieu_arrivee']); ?></div>
            </div>
            <div class="ride-details">
                <div class="car-info">
                    <span class="car-brand"><?php echo htmlspecialchars($covoiturage['marque']); ?></span>
                    <span class="car-model"><?php echo htmlspecialchars($covoiturage['modele']); ?></span>
                    <span class="car-immatriculation"><?php echo htmlspecialchars($covoiturage['immatriculation']); ?></span>
                    <?php if (isset($covoiturage['couleur']) && !empty($covoiturage['couleur'])): ?>
                          <span class="car-color"><?php echo htmlspecialchars($covoiturage['couleur']); ?></span>
                    <?php endif; ?>
                    <span class="car-energy"><?php echo htmlspecialchars($covoiturage['energie']); ?></span>
                </div>
            </div>
            <div class="driver-info">
                <?php 
                $is_own_trip = isset($covoiturage['trip_type']) && $covoiturage['trip_type'] === 'own';
                if ($is_own_trip): 
                ?>
                    <div class="driver-avatar">
                        <?php 
                        $photo = $covoiturage['photo'] ?? '';
                        $pseudo = $covoiturage['pseudo'] ?? '';
                        $prenom = $covoiturage['prenom'] ?? '';
                        
                        if (!empty($photo)): 
                            // Conversion du BLOB en base64 pour l'affichage
                            $image_data = base64_encode($photo);
                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                            $mime_type = $finfo->buffer($photo);
                        ?>
                            <img src="data:<?php echo $mime_type; ?>;base64,<?php echo $image_data; ?>" alt="Avatar">
                        <?php else: 
                            $initial = '';
                            if (!empty($pseudo)) {
                                $initial = strtoupper(substr($pseudo, 0, 1));
                            } elseif (!empty($prenom)) {
                                $initial = strtoupper(substr($prenom, 0, 1));
                            }
                        ?>
                            <div class="avatar-placeholder"><?php echo $initial; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="driver-details">
                        <span class="driver-name">Mon trajet</span>
                    </div>
                <?php else: ?>
                    <div class="driver-avatar">
                        <?php 
                        $photo = $covoiturage['photo'] ?? '';
                        $pseudo = $covoiturage['pseudo'] ?? '';
                        $prenom = $covoiturage['prenom'] ?? '';
                        
                        if (!empty($photo)): 
                            // Conversion du BLOB en base64 pour l'affichage
                            $image_data = base64_encode($photo);
                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                            $mime_type = $finfo->buffer($photo);
                        ?>
                            <img src="data:<?php echo $mime_type; ?>;base64,<?php echo $image_data; ?>" alt="Avatar">
                        <?php else: 
                            $initial = '';
                            if (!empty($pseudo)) {
                                $initial = strtoupper(substr($pseudo, 0, 1));
                            } elseif (!empty($prenom)) {
                                $initial = strtoupper(substr($prenom, 0, 1));
                            }
                        ?>
                            <div class="avatar-placeholder"><?php echo $initial; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="driver-details">
                        <span class="driver-name"><?php                                        
                            $prenom = $covoiturage['prenom'] ?? '';
                            $nom = $covoiturage['nom'] ?? '';
                            $pseudo = $covoiturage['pseudo'] ?? '';
                            
                            if (!empty($prenom) && !empty($nom)) {
                                echo htmlspecialchars($prenom . ' ' . substr($nom, 0, 1) . '.');
                            } else {
                                echo htmlspecialchars($pseudo);
                            }
                        ?></span>
                        <div class="driver-rating">
                            <?php 
                            for ($i = 1; $i <= 5; $i++) {
                                $starClass = $i <= $covoiturage['note_moyenne'] ? 'star active' : 'star';
                                echo '<span class="' . $starClass . '">★</span>';
                            }
                            ?>
                            <span class="rating-value">(<?php echo $covoiturage['note_moyenne']; ?>)</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="ride-price">
            <div class="price-amount"><?php echo number_format($covoiturage['prix_personne'], 2); ?> Credits</div>
            <div class="seats-available"><?php echo $covoiturage['places_disponibles']; ?> places disponibles</div>
            
            <?php if (!$current_user_id): ?>
                <!-- Utilisateur non connecté -->
                <a href="connexion" class="connect-button">Se connecter pour réserver</a>
            <?php elseif ($is_own_trip): ?>
                <!-- Trajet personnel -->
                <a href="trajets-chauffeur" class="book-button">Regarder mes trajets</a>
            <?php else: ?>
                <!-- Trajet d'un autre chauffeur -->
                <a href="reservation.php?id=<?php echo $covoiturage['covoiturage_id']; ?>" class="book-button">Réserver</a>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let count = <?php echo $nb_passagers; ?>;
    
    // Code pour la gestion des passagers
    const passengerField = document.getElementById('passenger-field');
    const passengerDropdown = document.getElementById('passenger-dropdown');
    const passengerMinus = document.getElementById('passenger-minus');
    const passengerPlus = document.getElementById('passenger-plus');
    const passengerCount = document.getElementById('passenger-count');
    const passengerDisplay = document.querySelector('.passenger-display');
    const nbPassagersInput = document.getElementById('nb_passagers');
    
    passengerField.addEventListener('click', function(e) {
        e.stopPropagation();
        passengerDropdown.style.display = passengerDropdown.style.display === 'block' ? 'none' : 'block';
    });
    
    document.addEventListener('click', function(e) {
        if (!passengerField.contains(e.target)) {
            passengerDropdown.style.display = 'none';
        }
    });
    
    passengerDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    passengerMinus.addEventListener('click', function() {
        if (count > 1) {
            count--;
            updatePassengerCount();
        }
    });
    
    passengerPlus.addEventListener('click', function() {
        if (count < 8) {
            count++;
            updatePassengerCount();
        }
    });
    
    function updatePassengerCount() {
        passengerCount.textContent = count;
        passengerDisplay.textContent = `${count} ${count === 1 ? 'passager' : 'passagers'}`;
        nbPassagersInput.value = count;
    }

    // Gestion du format de date français
    const dateInput = document.getElementById('trip_date');
    const dateField = dateInput.closest('.date-field');
    
    function checkDateValue() {
        if (dateInput.value) {
            dateField.classList.add('has-value');
        } else {
            dateField.classList.remove('has-value');
        }
    }
    
    
    // Code pour les curseurs et filtres
    const priceSlider = document.getElementById('max-price');
    const priceValue = document.getElementById('price-value');
    
    priceSlider.addEventListener('input', function() {
        priceValue.textContent = this.value + ' Credits';
    });
    
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
    
    // Code pour les étoiles de notation
    const stars = document.querySelectorAll('.star');
    const ratingValue = document.getElementById('rating-value');
    let currentRating = <?php echo $min_rating; ?>;
    
    updateStars(currentRating);
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            currentRating = parseInt(this.getAttribute('data-value'));
            ratingValue.textContent = currentRating;
            updateStars(currentRating);
        });
        
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
    
    // Application des filtres
    const applyFiltersButton = document.querySelector('.apply-filters-button');
    
    applyFiltersButton.addEventListener('click', function() {
        const isEcoVehicle = document.getElementById('eco-filter').checked;
        const maxPrice = priceSlider.value;
        const maxDuration = durationSlider.value;
        const minRating = currentRating;
        
        let form = document.getElementById('search-form');
        
        // Supprimer les anciens champs cachés
        document.querySelectorAll('input[name="eco_filter"], input[name="max_price"], input[name="max_duration"], input[name="min_rating"]').forEach(input => input.remove());
        
        // Ajouter les nouveaux champs cachés
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
        
        form.submit();
    });
});
</script>