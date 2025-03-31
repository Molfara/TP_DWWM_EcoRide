<?php
// Ne démarre pas une nouvelle session si elle a déjà été lancée dans index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification si l'utilisateur est authentifié
if (!isset($_SESSION['user_id'])) {
    header("Location: /connexion");
    exit();
}

$utilisateur_id = $_SESSION['user_id'];
$message = ""; // Pour les messages d'erreur ou de succès

// Connexion à la base de données
require_once __DIR__ . '/../config/database.php';

// Traitement du formulaire soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug
    error_log("Formulaire soumis");
    error_log("Données POST: " . print_r($_POST, true));

    // Vérifier que la connexion à la base de données est établie
    if (!isset($pdo) || $pdo === null) {
        error_log("Erreur: Connexion à la base de données non établie");
        $message = "Erreur de connexion à la base de données";
    } else {
        error_log("Connexion à la base de données établie");

  // Récupération des données du formulaire
    $modele = $_POST['modele'];
    $immatriculation = $_POST['immatriculation'];
    $energie = $_POST['energie'];
    $couleur = isset($_POST['couleur']) ? $_POST['couleur'] : null;
    $date_premiere_immatriculation = isset($_POST['date_premiere_immatriculation']) ? $_POST['date_premiere_immatriculation'] : null;
    $nb_places = $_POST['nb_places'];
    $marque_id = $_POST['marque_id'];

    // Vérification de l'unicité du numéro d'immatriculation

try {
    error_log("Tentative de vérification d'immatriculation: " . $immatriculation);
    $check_stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM voiture WHERE immatriculation = ?");
    $check_stmt->execute([$immatriculation]);
    $check_row = $check_stmt->fetch();
    error_log("Résultat de la vérification: " . $check_row['count']);

    if ($check_row['count'] > 0) {
        $message = "Une voiture avec cette immatriculation existe déjà!";
    } else {
        // Afficher les valeurs à insérer
        error_log("Tentative d'insertion avec les données:");
        error_log("Modèle: " . $modele);
        error_log("Immatriculation: " . $immatriculation);
        error_log("Énergie: " . $energie);
        error_log("Couleur: " . ($couleur ?: 'non définie'));
        error_log("Date première immatriculation: " . ($date_premiere_immatriculation ?: 'non définie'));
        error_log("Nombre de places: " . $nb_places);
        error_log("Marque ID: " . $marque_id);
        error_log("Utilisateur ID: " . $utilisateur_id);
        
        // Ajout de la voiture dans la base de données
        $sql = "INSERT INTO voiture (modele, immatriculation, energie, couleur,
                 date_premiere_immatriculation, nb_places, marque_id, utilisateur_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $modele,
            $immatriculation,
            $energie,
            $couleur,
            $date_premiere_immatriculation,
            $nb_places,
            $marque_id,
            $utilisateur_id
        ]);
        
if ($result) {
    error_log("Insertion réussie! Lignes affectées: " . $stmt->rowCount());
    $message = "Voiture ajoutée avec succès!";  

    // Débogage pour le dernier ID inséré
    $lastId = $pdo->lastInsertId();
    error_log("Dernier ID inséré : " . $lastId);
                 
    // Vérification des données insérées
    try {
        $verify_stmt = $pdo->prepare("SELECT * FROM voiture WHERE voiture_id = ?");
        $verify_stmt->execute([$lastId]);
        $inserted_car = $verify_stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Données insérées : " . print_r($inserted_car, true));
    } catch (PDOException $ve) {
        error_log("Erreur lors de la vérification des données insérées : " . $ve->getMessage());
    }
            
    // Redirection uniquement en cas d'ajout réussi du véhicule
    header("Location: /espace_chauffeur");
    exit();
} else {
    error_log("Échec de l'insertion. Code d'erreur : " . implode(', ', $stmt->errorInfo()));
    $message = "Erreur lors de l'ajout du véhicule. Veuillez réessayer.";
}

    }
    } catch (PDOException $e) {
    error_log("Erreur PDO: " . $e->getMessage());
    $message = "Erreur: " . $e->getMessage();
    }
  }

}
        
// Récupération de la liste des marques pour le menu déroulant
$marques = [];
try {
    $marque_stmt = $pdo->query("SELECT marque_id, libelle FROM marque");
    $marques = $marque_stmt->fetchAll();
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des marques: " . $e->getMessage();
}
?>  
            

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une voiture - EcoRide</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
                
<div class="container">
        <form method="post" action="/traitement/process-car.php">
        <h1 class="car-form-title">Ajouter une voiture</h1>
        <p class="car-form-description">Pour devenir chauffeur, vous devez enregistrer au moins une voiture.</p>
        
        <?php if (!empty($message)): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>
            
        <div class="form-group">
            <label for="marque_id">Marque<sup>*</sup></label>
            <select name="marque_id" id="marque_id" required>
                <option value="">Sélectionnez une marque</option>
                <?php foreach ($marques as $marque): ?>
                    <option value="<?php echo $marque['marque_id']; ?>"><?php echo $marque['libelle']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

 
            <div class="form-group">
                <label for="modele">Modèle<sup>*</sup></label>
                <input type="text" name="modele" id="modele" required>
            </div>
    
            <div class="form-group">
                <label for="immatriculation">Immatriculation<sup>*</sup></label>
                <input type="text" name="immatriculation" id="immatriculation" required>
            </div>
    
            <div class="form-group">
                <label for="energie">Énergie<sup>*</sup></label>
                <select name="energie" id="energie" required>
                    <option value="">Sélectionnez un type d'énergie</option>
                    <option value="essence">Essence</option>
                    <option value="diesel">Diesel</option>
                    <option value="electrique">Électrique</option>
                    <option value="hybride">Hybride</option>
                </select>
            </div>
    
            <div class="form-group">
                <label for="couleur">Couleur</label>
                <input type="text" name="couleur" id="couleur">
            </div>
        
            <div class="form-group">
                <label for="date_premiere_immatriculation">Date de première immatriculation:</label>
                <input type="date" name="date_premiere_immatriculation" id="date_premiere_immatriculation">
            </div>
            
            <div class="form-group">
                <label for="nb_places">Nombre de places<sup>*</sup></label> 
                <input type="number" name="nb_places" id="nb_places" min="1" max="9" required>
            </div>

            <div class="form-note">
                <p><sup>*</sup> Champs obligatoires</p>
            </div>
                        
            <div class="form-group">
                <button type="submit">Ajouter la voiture</button>
            </div>
        </form>
            
    </div>
</body>
</html>
