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
$message = "";
$error = "";

// Connexion à la base de données
require_once __DIR__ . '/../config/database.php';

// Débogage
error_log("Début du traitement dans process-car.php");
error_log("Session status: " . session_status());
error_log("Session ID: " . session_id());
error_log("Session user_id: " . $utilisateur_id);
error_log("Données POST reçues: " . print_r($_POST, true));

// Déterminer l'action à effectuer (si elle existe)
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Déterminer la page de retour basée sur le referrer
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$returnUrl = '/profil-chauffeur'; // Par défaut

if (strpos($referer, 'ajouter-voiture') !== false) {
    $returnUrl = '/espace_chauffeur';
} elseif (strpos($referer, 'profil-chauffeur') !== false) {
    $returnUrl = '/profil-chauffeur';
}

// Traitement en fonction de l'action ou du type de requête
if ($action === 'delete_vehicle') {
    // Suppression d'un véhicule
    if (isset($_POST['voiture_id']) && !empty($_POST['voiture_id'])) {
        $voiture_id = $_POST['voiture_id'];
        error_log("Tentative de suppression du véhicule ID: " . $voiture_id);
        
        try {
            // Vérifier que le véhicule appartient bien à l'utilisateur
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM voiture WHERE voiture_id = ? AND utilisateur_id = ?");
            $check_stmt->execute([$voiture_id, $utilisateur_id]);
            
            if ($check_stmt->fetchColumn() > 0) {
                // Le véhicule appartient à l'utilisateur, procéder à la suppression
                $delete_stmt = $pdo->prepare("DELETE FROM voiture WHERE voiture_id = ?");
                $result = $delete_stmt->execute([$voiture_id]);
                
                if ($result) {
                    $_SESSION['message'] = "Véhicule supprimé avec succès!";
                    error_log("Véhicule supprimé: " . $voiture_id);
                } else {
                    $_SESSION['error'] = "Erreur lors de la suppression du véhicule.";
                    error_log("Échec de la suppression. Code d'erreur: " . implode(', ', $delete_stmt->errorInfo()));
                }
            } else {
                $_SESSION['error'] = "Vous n'êtes pas autorisé à supprimer ce véhicule.";
                error_log("Tentative de suppression d'un véhicule non autorisé: " . $voiture_id);
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur: " . $e->getMessage();
            error_log("Erreur PDO lors de la suppression: " . $e->getMessage());
        }
    } else {
        $_SESSION['error'] = "ID de véhicule non spécifié.";
        error_log("Tentative de suppression sans ID de véhicule.");
    }
    
    // Redirection vers la page d'origine
    header("Location: " . $returnUrl);
    exit();
} 
else if ($action === 'update_vehicles') {
    // Mise à jour des véhicules existants
    if (isset($_POST['vehicules']) && is_array($_POST['vehicules'])) {
        error_log("Mise à jour des véhicules existants");
        
        $allUpdatesSuccessful = true;
        
        foreach ($_POST['vehicules'] as $index => $vehicule) {
            if (isset($vehicule['voiture_id']) && !empty($vehicule['voiture_id'])) {
                try {
                    $sql = "UPDATE voiture SET 
                            modele = ?, 
                            immatriculation = ?, 
                            energie = ?, 
                            couleur = ?, 
                            date_premiere_immatriculation = ?, 
                            nb_places = ?, 
                            marque_id = ? 
                            WHERE voiture_id = ? AND utilisateur_id = ?";
                    
                    $stmt = $pdo->prepare($sql);
                    $result = $stmt->execute([
                        $vehicule['modele'],
                        $vehicule['immatriculation'],
                        $vehicule['energie'],
                        $vehicule['couleur'] ?? null,
                        $vehicule['date_premiere_immatriculation'] ?? null,
                        $vehicule['nb_places'],
                        $vehicule['marque_id'],
                        $vehicule['voiture_id'],
                        $utilisateur_id
                    ]);
                    
                    if ($result) {
                        error_log("Véhicule mis à jour avec succès: " . $vehicule['voiture_id']);
                    } else {
                        error_log("Erreur lors de la mise à jour: " . implode(', ', $stmt->errorInfo()));
                        $allUpdatesSuccessful = false;
                    }
                } catch (PDOException $e) {
                    error_log("Erreur PDO: " . $e->getMessage());
                    $_SESSION['error'] = "Erreur: " . $e->getMessage();
                    $allUpdatesSuccessful = false;
                }
            }
        }
        
        if ($allUpdatesSuccessful) {
            $_SESSION['message'] = "Véhicules mis à jour avec succès!";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de certains véhicules.";
        }
    }
    
    // Redirection vers la page d'origine
    header("Location: " . $returnUrl);
    exit();
}
else if ($action === 'add_vehicle') {
    // Ajout d'un nouveau véhicule depuis le profil-chauffeur
    error_log("Traitement de l'ajout d'un véhicule (action: add_vehicle)");
    
    // Vérifier que la connexion à la base de données est établie
    if (!isset($pdo) || $pdo === null) {
        error_log("Erreur: Connexion à la base de données non établie");
        $_SESSION['error'] = "Erreur de connexion à la base de données";
        header("Location: " . $returnUrl);
        exit();
    }

    // Récupération des données du formulaire
    $modele = $_POST['modele'];
    $immatriculation = $_POST['immatriculation'];
    $energie = $_POST['energie'];
    $couleur = isset($_POST['couleur']) ? $_POST['couleur'] : null;
    $date_premiere_immatriculation = isset($_POST['date_premiere_immatriculation']) ? $_POST['date_premiere_immatriculation'] : null;
    $nb_places = $_POST['nb_places'];
    $marque_id = $_POST['marque_id'];

    try {
        // Vérification de l'unicité du numéro d'immatriculation
        error_log("Tentative de vérification d'immatriculation: " . $immatriculation);
        $check_stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM voiture WHERE immatriculation = ?");
        $check_stmt->execute([$immatriculation]);
        $check_row = $check_stmt->fetch();
        error_log("Résultat de la vérification: " . $check_row['count']);

        if ($check_row['count'] > 0) {
            $_SESSION['error'] = "Une voiture avec cette immatriculation existe déjà!";
        } else {
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
                $_SESSION['message'] = "Voiture ajoutée avec succès!";
                
                // Mise à jour du rôle dans la session et la base de données si nécessaire
                if ($_SESSION['role'] !== 'chauffeur') {
                    $_SESSION['role'] = 'chauffeur';
                    
                    try {
                        $updateRole = $pdo->prepare("UPDATE utilisateur SET role_id = 3 WHERE utilisateur_id = ?");
                        $updateRole->execute([$utilisateur_id]);
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la mise à jour du rôle: " . $e->getMessage());
                    }
                }
            } else {
                error_log("Échec de l'insertion. Code d'erreur : " . implode(', ', $stmt->errorInfo()));
                $_SESSION['error'] = "Erreur lors de l'ajout du véhicule. Veuillez réessayer.";
            }
        }
    } catch (PDOException $e) {
        error_log("Erreur PDO: " . $e->getMessage());
        $_SESSION['error'] = "Erreur: " . $e->getMessage();
    }
    
    // Redirection vers la page d'origine
    header("Location: " . $returnUrl);
    exit();
}
else {
    // Traitement standard (ajout d'un nouveau véhicule depuis ajouter-voiture)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        error_log("Traitement de l'ajout d'un véhicule standard");
        
        // Vérifier que la connexion à la base de données est établie
        if (!isset($pdo) || $pdo === null) {
            error_log("Erreur: Connexion à la base de données non établie");
            $error = "Erreur de connexion à la base de données";
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

            try {
                // Vérification de l'unicité du numéro d'immatriculation
                error_log("Tentative de vérification d'immatriculation: " . $immatriculation);
                $check_stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM voiture WHERE immatriculation = ?");
                $check_stmt->execute([$immatriculation]);
                $check_row = $check_stmt->fetch();
                error_log("Résultat de la vérification: " . $check_row['count']);

                if ($check_row['count'] > 0) {
                    $error = "Une voiture avec cette immatriculation existe déjà!";
                    $_SESSION['error'] = $error;
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
                        
                        // Mise à jour du rôle dans la session et la base de données
                        $_SESSION['role'] = 'chauffeur';
                        
                        try {
                            $updateRole = $pdo->prepare("UPDATE utilisateur SET role_id = 3 WHERE utilisateur_id = ?");
                            $updateRole->execute([$utilisateur_id]);
                        } catch (PDOException $e) {
                            error_log("Erreur lors de la mise à jour du rôle: " . $e->getMessage());
                        }
                        
                        // Stockage du message dans la session pour affichage après redirection
                        $_SESSION['vehicle_message'] = $message;

                        // Redirection selon la page d'origine
                        if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'ajouter-voiture') !== false) {
                            error_log("Redirection vers espace_chauffeur (venant d'ajouter-voiture)");
                            header("Location: /espace_chauffeur");
                        } else {
                            error_log("Redirection vers profil-chauffeur (venant de profil-chauffeur)");
                            header("Location: /profil-chauffeur");
                        }
                        exit();
                    } else {
                        error_log("Échec de l'insertion. Code d'erreur : " . implode(', ', $stmt->errorInfo()));
                        $error = "Erreur lors de l'ajout du véhicule. Veuillez réessayer.";
                        $_SESSION['error'] = $error;
                    }
                }
            } catch (PDOException $e) {
                error_log("Erreur PDO: " . $e->getMessage());
                $error = "Erreur: " . $e->getMessage();
                $_SESSION['error'] = $error;
            }
        }
        
        // En cas d'erreur, redirection basée sur l'origine
        if (!empty($error)) {
            if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'ajouter-voiture') !== false) {
                header("Location: /ajouter-voiture");
            } else {
                header("Location: /profil-chauffeur");
            }
            exit();
        }
    }
}

// Si on arrive ici sans redirection, rediriger vers la page d'origine ou profil par défaut
header("Location: " . $returnUrl);
exit();
?>

