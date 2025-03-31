<?php
// Démarrer la session si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est authentifié
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
    // Débogage
    error_log("Formulaire soumis");
    error_log("Données POST : " . print_r($_POST, true));

    // Vérifier si la connexion à la base de données est établie
    if (!isset($pdo) || $pdo === null) {
        error_log("Erreur : Connexion à la base de données non établie");
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

        // Vérifier l'unicité du numéro d'immatriculation
        try {
            error_log("Tentative de vérification d'immatriculation : " . $immatriculation);
            $check_stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM voiture WHERE immatriculation = ?");
            $check_stmt->execute([$immatriculation]);
            $check_row = $check_stmt->fetch();
            error_log("Résultat de la vérification : " . $check_row['count']);
            
            if ($check_row['count'] > 0) {
                $message = "Une voiture avec cette immatriculation existe déjà !";
            } else {
                // Affichage des valeurs à insérer
                error_log("Tentative d'insertion avec les données :");
                error_log("Modèle : " . $modele);
                error_log("Immatriculation : " . $immatriculation);
                error_log("Énergie : " . $energie);
                error_log("Couleur : " . ($couleur ?: 'non définie'));
                error_log("Date première immatriculation : " . ($date_premiere_immatriculation ?: 'non définie'));
                error_log("Nombre de places : " . $nb_places);
                error_log("Marque ID : " . $marque_id);
                error_log("Utilisateur ID : " . $utilisateur_id);
            
                // Ajouter la voiture dans la base de données
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
                    error_log("Insertion réussie ! Lignes affectées : " . $stmt->rowCount());
                    $message = "Voiture ajoutée avec succès !";
            
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


// Mise à jour du rôle dans la session
    $_SESSION['role'] = 'chauffeur';
    
    // Mise à jour du rôle dans la base de données
    try {
        $updateRole = $pdo->prepare("UPDATE utilisateur SET role_id = 3 WHERE utilisateur_id = ?");
        $updateRole->execute([$utilisateur_id]);
    } catch (PDOException $e) {
        error_log("Erreur lors de la mise à jour du rôle: " . $e->getMessage());
    }
                    
                    // Redirection après l'ajout réussi du véhicule
                    header("Location: /espace_chauffeur");
                    exit();
                } else {
                    error_log("Échec de l'insertion. Code d'erreur : " . implode(', ', $stmt->errorInfo()));
                    $message = "Erreur lors de l'ajout du véhicule. Veuillez réessayer.";
                }
            }
        } catch (PDOException $e) {
            error_log("Erreur PDO : " . $e->getMessage());
            $message = "Erreur : " . $e->getMessage();
        }
    }
}

// Si nous arrivons ici, une erreur s'est produite ou le formulaire n'a pas été soumis.
// Redirection vers la page d'ajout du véhicule avec un message d'erreur
$_SESSION['message'] = $message;
header("Location: /ajouter-voiture");
exit();
?>

