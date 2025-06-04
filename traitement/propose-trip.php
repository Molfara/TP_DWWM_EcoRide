<?php
// Le démarrage de la session doit être au tout début
session_start();

// Informations de débogage
error_log("=== PROPOSE TRIP DEBUG ===");
error_log("Session ID: " . session_id());
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));
error_log("SESSION data: " . print_r($_SESSION, true));

// Inclusion du middleware pour vérifier l'authentification
require_once __DIR__ . '/../middleware/auth.php';

// Utilisation du middleware pour vérifier le rôle de chauffeur
checkChauffeur(); // Cela redirigera vers /connexion si l'utilisateur n'est pas authentifié ou n'est pas chauffeur

// Vérification de la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Redirection - méthode non POST");
    header('Location: /proposer-trajet');
    exit;
}

// Connexion à la base de données
require_once __DIR__ . '/../config/database.php';

// Validation des données
$errors = [];
$data = [];

// Vérification des champs obligatoires
$required_fields = [
    'date_depart' => 'Date de départ',
    'heure_depart' => 'Heure de départ', 
    'date_arrivee' => 'Date d\'arrivée',
    'heure_arrivee' => 'Heure d\'arrivée',
    'lieu_depart' => 'Lieu de départ',
    'lieu_arrivee' => 'Lieu d\'arrivée',
    'prix_place' => 'Prix par place',
    'selected_vehicle_id' => 'Véhicule'
];

foreach ($required_fields as $field => $label) {
    if (empty($_POST[$field])) {
        $errors[] = "Le champ '$label' est obligatoire.";
        error_log("Champ manquant: $field");
    } else {
        $data[$field] = trim($_POST[$field]);
    }
}

// Vérification du prix
if (!empty($data['prix_place'])) {
    $prix = floatval($data['prix_place']);
    if ($prix <= 0) {
        $errors[] = "Le prix doit être supérieur à 0.";
    } elseif ($prix < 2) {
        $errors[] = "Le prix minimum est de 2 crédits (commission plateforme incluse).";
    }
}

// Vérification des dates
if (!empty($data['date_depart']) && !empty($data['date_arrivee'])) {
    $date_depart = new DateTime($data['date_depart']);
    $date_arrivee = new DateTime($data['date_arrivee']);
    $today = new DateTime();
    
    if ($date_depart < $today->setTime(0, 0, 0)) {
        $errors[] = "La date de départ ne peut pas être dans le passé.";
    }
    
    if ($date_arrivee < $date_depart) {
        $errors[] = "La date d'arrivée ne peut pas être antérieure à la date de départ.";
    }
}

// Vérification du véhicule
if (!empty($data['selected_vehicle_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM voiture WHERE voiture_id = ? AND utilisateur_id = ?");
        $stmt->execute([$data['selected_vehicle_id'], $_SESSION['user_id']]);
        
        if ($stmt->fetchColumn() == 0) {
            $errors[] = "Le véhicule sélectionné n'est pas valide.";
            error_log("Véhicule invalide: " . $data['selected_vehicle_id'] . " pour utilisateur " . $_SESSION['user_id']);
        }
    } catch (PDOException $e) {
        $errors[] = "Erreur lors de la vérification du véhicule.";
        error_log("Erreur véhicule: " . $e->getMessage());
    }
}

// En cas d'erreurs - retour au formulaire
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    error_log("Erreurs de validation: " . print_r($errors, true));
    header('Location: /proposer-trajet');
    exit;
}

// Récupération des informations sur le véhicule
try {
    $stmt = $pdo->prepare("SELECT nb_places FROM voiture WHERE voiture_id = ?");
    $stmt->execute([$data['selected_vehicle_id']]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vehicle) {
        throw new Exception("Véhicule introuvable");
    }
    
    $nb_places = $vehicle['nb_places'];
    error_log("Nombre de places du véhicule: " . $nb_places);
} catch (Exception $e) {
    $_SESSION['form_errors'] = ["Erreur lors de la récupération des informations du véhicule."];
    error_log("Erreur récupération véhicule: " . $e->getMessage());
    header('Location: /proposer-trajet');
    exit;
}

// Ajout de l'enregistrement dans la base de données
try {
    $pdo->beginTransaction();
    
    // Utilisation de utilisateur_id au lieu de conducteur_id, si vous avez modifié la table
    $sql = "INSERT INTO covoiturage (
                lieu_depart, lieu_arrivee, date_depart, heure_depart, 
                date_arrivee, heure_arrivee, prix_personne, nb_place, 
                statut, voiture_id, utilisateur_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $data['lieu_depart'],
        $data['lieu_arrivee'], 
        $data['date_depart'],
        $data['heure_depart'],
        $data['date_arrivee'],
        $data['heure_arrivee'],
        floatval($data['prix_place']),
        $nb_places,
        $data['selected_vehicle_id'],
        $_SESSION['user_id']
    ]);
    
    if (!$result) {
        error_log("Erreur d'exécution de la requête: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Échec de l'exécution de la requête");
    }
    
    $trip_id = $pdo->lastInsertId();
    error_log("Trajet créé avec ID: " . $trip_id);
    
    $pdo->commit();
    
    // Message de succès
    $_SESSION['success_message'] = "Votre trajet a été proposé avec succès ! Il apparaît maintenant dans vos trajets à venir.";
    $_SESSION['new_trip_id'] = $trip_id;
    
    // Redirection vers la page des trajets du chauffeur
    error_log("Redirection vers /trajets-chauffeur");
    header('Location: /trajets-chauffeur');
    exit;
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Erreur insertion trajet: " . $e->getMessage());
    
    $_SESSION['form_errors'] = ["Une erreur est survenue lors de l'enregistrement. Veuillez réessayer."];
    header('Location: /proposer-trajet');
    exit;
}