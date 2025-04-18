<div class="hero-background">
    <div class="hero-content">
        <h1>Covoiturage Simple et Écolo</h1>

        <!-- Nouvelle section de recherche -->
        <div class="search-panel">
            <div class="search-field">
                <input type="text" placeholder="Départ" class="action-text">
            </div>

            <div class="search-field">
                <input type="text" placeholder="Destination" class="action-text">
            </div>

            <!-- Division avec le calendrier simple -->
            <div class="search-field date-field">
                <input type="date" id="trip_date" name="trip_date" class="date-input">
            </div>

            <!-- Champ pour le nombre de passagers avec menu déroulant -->
            <div class="search-field" id="passenger-field">
                <div class="passenger-display">1 passager</div>
                
                <!-- Dropdown pour les passagers -->
                <div class="passenger-dropdown" id="passenger-dropdown">
                    <div class="passenger-counter">
                        <div class="passenger-label">Passager</div>
                        <div class="counter-controls">
                            <button class="counter-btn minus" id="passenger-minus">−</button>
                            <span class="counter-value" id="passenger-count">1</span>
                            <button class="counter-btn plus" id="passenger-plus">+</button>
                        </div>
                    </div>
                </div>
            </div>

            <button class="search-button">Rechercher</button>
        </div>
    </div>
</div>

    
<!-- Section des valeurs -->
<div class="values-container">
    <h2 class="page-title">Valeurs que nous partageons</h2>
    
    <!-- Première valeur avec titre et texte de la troisième section -->
    <div class="value-row">
        <div class="value-image">
            <img src="images/Image1.png" alt="Électromobilité partagée">
        </div>
        <div class="value-content">
            <h3 class="value-title">Communautaire & Efficace</h3>
            <p class="value-text">Rejoignez notre communauté de conducteurs et passagers pour une mobilité collaborative. Ensemble, nous fluidifions le trafic et créons des connections entre personnes partageant le même trajet.</p>
        </div>
    </div>
    
    <!-- Deuxième valeur: inchangée -->
    <div class="value-row">
        <div class="value-content second-content">
            <h3 class="value-title">Économique & Pratique</h3>
            <p class="value-text">Partagez vos frais de trajet tout en optimisant vos déplacements quotidiens. Chaque covoiturage représente une économie pour votre portefeuille et moins de véhicules sur nos routes.</p>
        </div>
        <div class="value-image">
            <img src="images/Image2.png" alt="Économie de covoiturage">
        </div>
    </div>
    
    <!-- Troisième valeur avec titre et texte de la première section -->
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



<script>
document.addEventListener('DOMContentLoaded', function() {
    const passengerField = document.getElementById('passenger-field');
    const passengerDropdown = document.getElementById('passenger-dropdown');
    const passengerMinus = document.getElementById('passenger-minus');
    const passengerPlus = document.getElementById('passenger-plus');
    const passengerCount = document.getElementById('passenger-count');
    const passengerDisplay = document.querySelector('.passenger-display');

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
    }
});
</script>
