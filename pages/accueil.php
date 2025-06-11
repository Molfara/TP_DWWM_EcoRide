<div class="page-accueil">
    
<div class="hero-background">
    <div class="hero-content">
        <h1>Covoiturage Simple et Écolo</h1>

        <!-- Section de recherche avec formulaire -->
        <form action="/traitement/search-trip.php" method="GET" class="search-form">
            <div class="search-panel">
                <div class="search-field">
                    <input type="text" name="depart" id="depart-input" placeholder="Départ" class="action-text">
                </div>

                <div class="search-field">
                    <input type="text" name="destination" id="destination-input" placeholder="Destination" class="action-text">
                </div>

                <div class="search-field date-field">
                    <input type="date" name="date" id="trip_date" class="date-input">
                </div>

                <div class="search-field" id="passenger-field">
                    <div class="passenger-display">1 passager</div>
                    
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

                <input type="hidden" name="passengers" id="passengers-hidden" value="1">
                <button type="submit" class="search-button">Rechercher</button>
            </div>
        </form>
    </div>
</div>

<!-- Section des valeurs -->
<div class="values-container">
    <h2 class="page-title">Valeurs que nous partageons</h2>
    
    <div class="value-row">
        <div class="value-image">
            <img src="images/Image1.png" alt="Électromobilité partagée">
        </div>
        <div class="value-content">
            <h3 class="value-title">Communautaire & Efficace</h3>
            <p class="value-text">Rejoignez notre communauté de conducteurs et passagers pour une mobilité collaborative. Ensemble, nous fluidifions le trafic et créons des connections entre personnes partageant le même trajet.</p>
        </div>
    </div>
    
    <div class="value-row">
        <div class="value-content second-content">
            <h3 class="value-title">Économique & Pratique</h3>
            <p class="value-text">Partagez vos frais de trajet tout en optimisant vos déplacements quotidiens. Chaque covoiturage représente une économie pour votre portefeuille et moins de véhicules sur nos routes.</p>
        </div>
        <div class="value-image">
            <img src="images/Image2.png" alt="Économie de covoiturage">
        </div>
    </div>
    
    <div class="value-row">
        <div class="value-image">
            <img src="images/Image3.png" alt="Communauté de covoiturage">
        </div>
        <div class="value-content">
            <h3 class="value-title">Simple & Écologique</h3>
            <p class="value-text">Optez pour l'électromobilité partagée et réduisez considérablement votre empreinte carbone. Notre plateforme valorise les conducteurs de véhicules électriques, contribuant ensemble à un avenir plus vert.</p>
        </div>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du dropdown des passagers
    const passengerField = document.getElementById('passenger-field');
    const passengerDropdown = document.getElementById('passenger-dropdown');
    const passengerMinus = document.getElementById('passenger-minus');
    const passengerPlus = document.getElementById('passenger-plus');
    const passengerCount = document.getElementById('passenger-count');
    const passengerDisplay = document.querySelector('.passenger-display');
    const passengersHidden = document.getElementById('passengers-hidden');

    // Ouverture du dropdown
    passengerField.addEventListener('click', function(e) {
        e.stopPropagation();
        passengerDropdown.style.display = passengerDropdown.style.display === 'block' ? 'none' : 'block';
    });

    // Fermeture du dropdown
    document.addEventListener('click', function(e) {
        if (!passengerField.contains(e.target)) {
            passengerDropdown.style.display = 'none';
        }
    });

    passengerDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Gestion du compteur
    let count = 1;

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
        passengersHidden.value = count; // Mettre à jour l'input caché
    }
});
</script>