<?php
// traitement/search-trip.php
session_start();
require_once '../config/database.php';

// Récupérer l'utilisateur actuel (peut être NULL pour les non-autorisés)
$current_user_id = $_SESSION['user_id'] ?? null;
$user_role_string = $_SESSION['role'] ?? null;

// Convertir la chaîne de rôle en nombre (si l'utilisateur est autorisé)
$current_user_role = null;
if ($user_role_string === 'chauffeur') {
    $current_user_role = 3;
} elseif ($user_role_string === 'passager') {
    $current_user_role = 2;
} elseif ($user_role_string === 'utilisateur') {
    $current_user_role = 1;
} elseif ($user_role_string === 'employe') {
    $current_user_role = 4;
} elseif ($user_role_string === 'administrateur') {
    $current_user_role = 5;
}

// Récupérer les paramètres depuis GET (depuis accueil) ou POST (depuis covoiturage)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $lieu_depart = $_GET['depart'] ?? '';
    $lieu_arrivee = $_GET['destination'] ?? '';
    $date_depart = $_GET['date'] ?? '';
    $nb_passagers = intval($_GET['passengers'] ?? 1);
    
    $eco_filter = 0;
    $max_price = 20;
    $max_duration = 720;
    $min_rating = 0;
} else {
    $lieu_depart = $_POST['lieu_depart'] ?? '';
    $lieu_arrivee = $_POST['lieu_arrivee'] ?? '';
    $date_depart = $_POST['date_depart'] ?? '';
    $nb_passagers = intval($_POST['nb_passagers'] ?? 1);
    
    $eco_filter = intval($_POST['eco_filter'] ?? 0);
    $max_price = floatval($_POST['max_price'] ?? 20);
    $max_duration = intval($_POST['max_duration'] ?? 720);
    $min_rating = intval($_POST['min_rating'] ?? 0);
}

// Fonction de recherche de trajets
function searchRides($pdo, $current_user_id, $current_user_role, $lieu_depart, $lieu_arrivee, $date_depart, $nb_passagers, $eco_filter, $max_price, $max_duration, $min_rating) {
    try {
        // RECHERCHE UNIVERSELLE POUR TOUS
        // Afficher tous les trajets disponibles en tenant compte du rôle de l'utilisateur
        
        if ($current_user_role === 3 && $current_user_id) {
            // CHAUFFEUR autorisé - afficher SES propres trajets + les trajets d'autres chauffeurs
            $sql = "SELECT c.*, u.nom, u.prenom, u.pseudo, u.photo, u.utilisateur_id, v.modele, v.immatriculation, v.energie, v.couleur, m.libelle as marque,
                           (c.nb_place - COALESCE(reserved.places_reservees, 0)) as places_disponibles,
                           (TIME_TO_SEC(c.heure_arrivee) - TIME_TO_SEC(c.heure_depart))/60 as duree_minutes,
                           CASE WHEN c.utilisateur_id = ? THEN 'own' ELSE 'other' END as trip_type
                   FROM covoiturage c
                   INNER JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
                   INNER JOIN voiture v ON c.voiture_id = v.voiture_id
                   INNER JOIN marque m ON v.marque_id = m.marque_id
                   INNER JOIN role r ON u.role_id = r.role_id
                   LEFT JOIN (
                       SELECT covoiturage_id, COUNT(*) as places_reservees
                       FROM participation
                       GROUP BY covoiturage_id
                   ) reserved ON c.covoiturage_id = reserved.covoiturage_id
                   WHERE c.statut = 'en_attente'
                   AND r.libelle = 'chauffeur'
                   AND (c.nb_place - COALESCE(reserved.places_reservees, 0)) >= ?";
            
            $params = [$current_user_id, $nb_passagers];
        } else {
            // PASSAGER, NON-AUTORISÉ OU AUTRES RÔLES - afficher les trajets de tous les chauffeurs
            $sql = "SELECT c.*, u.nom, u.prenom, u.pseudo, u.photo, u.utilisateur_id, v.modele, v.immatriculation, v.energie, v.couleur, m.libelle as marque,
                           (c.nb_place - COALESCE(reserved.places_reservees, 0)) as places_disponibles,
                           (TIME_TO_SEC(c.heure_arrivee) - TIME_TO_SEC(c.heure_depart))/60 as duree_minutes,
                           'other' as trip_type
                   FROM covoiturage c
                   INNER JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
                   INNER JOIN voiture v ON c.voiture_id = v.voiture_id
                   INNER JOIN marque m ON v.marque_id = m.marque_id
                   INNER JOIN role r ON u.role_id = r.role_id
                   LEFT JOIN (
                       SELECT covoiturage_id, COUNT(*) as places_reservees
                       FROM participation
                       GROUP BY covoiturage_id
                   ) reserved ON c.covoiturage_id = reserved.covoiturage_id
                   WHERE c.statut = 'en_attente'
                   AND r.libelle = 'chauffeur'
                   AND (c.nb_place - COALESCE(reserved.places_reservees, 0)) >= ?";
            
            // Si l'utilisateur est autorisé, exclure ses propres trajets (sauf pour les chauffeurs)
            if ($current_user_id && $current_user_role !== 3) {
                $sql .= " AND c.utilisateur_id != ?";
                $params = [$nb_passagers, $current_user_id];
            } else {
                $params = [$nb_passagers];
            }
        }
        
        // Ajouter les conditions de recherche
        if (!empty($lieu_depart)) {
            $sql .= " AND c.lieu_depart LIKE ?";
            $params[] = '%' . $lieu_depart . '%';
        }
        
        if (!empty($lieu_arrivee)) {
            $sql .= " AND c.lieu_arrivee LIKE ?";
            $params[] = '%' . $lieu_arrivee . '%';
        }
        
        if (!empty($date_depart)) {
            $sql .= " AND c.date_depart = ?";
            $params[] = $date_depart;
        }
        
        // Filtre véhicule électrique
        if ($eco_filter == 1) {
            $sql .= " AND v.energie = 'Électrique'";
        }
        
        // Filtre prix maximum
        $sql .= " AND c.prix_personne <= ?";
        $params[] = $max_price;
        
        // Tri
        $sql .= " ORDER BY c.date_depart ASC, c.heure_depart ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Traiter les notes et filtrer par note minimale
        foreach ($results as $key => &$covoiturage) {
            // Récupérer la note moyenne du chauffeur
            $stmt = $pdo->prepare("SELECT AVG(note) as note_moyenne FROM avis WHERE utilisateur_id = ?");
            $stmt->execute([$covoiturage['utilisateur_id']]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            $covoiturage['note_moyenne'] = round($note['note_moyenne'] ?: 0, 1);
            
            // Appliquer le filtre de note minimale
            if ($covoiturage['note_moyenne'] < $min_rating) {
                unset($results[$key]);
            }

            // Appliquer le filtre de durée maximale
            if ($covoiturage['duree_minutes'] > $max_duration) {
                unset($results[$key]);
            }
        }
        
        return array_values($results);
        
    } catch (PDOException $e) {
        throw new Exception('Erreur de recherche: ' . $e->getMessage());
    }
}

// Exécuter la recherche
try {
    $search_results = searchRides($pdo, $current_user_id, $current_user_role, $lieu_depart, $lieu_arrivee, $date_depart, $nb_passagers, $eco_filter, $max_price, $max_duration, $min_rating);
    
    // Sauvegarder les résultats et paramètres dans la session
    $_SESSION['search_results'] = $search_results;
    $_SESSION['search_params'] = [
        'lieu_depart' => $lieu_depart,
        'lieu_arrivee' => $lieu_arrivee,
        'date_depart' => $date_depart,
        'nb_passagers' => $nb_passagers,
        'eco_filter' => $eco_filter,
        'max_price' => $max_price,
        'max_duration' => $max_duration,
        'min_rating' => $min_rating,
        'current_user_role' => $current_user_role,
        'current_user_id' => $current_user_id
    ];
    
} catch (Exception $e) {
    $_SESSION['search_error'] = $e->getMessage();
    $_SESSION['search_results'] = [];
    $_SESSION['search_params'] = [];
}

// Rediriger vers la page covoiturage
header('Location: ../covoiturage');
exit;
?>