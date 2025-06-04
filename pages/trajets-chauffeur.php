<?php
// Démarrage de la session pour gérer l'authentification de l'utilisateur
// Inclusion du fichier d'authentification pour vérifier les droits d'accès
require_once __DIR__ . '/../middleware/auth.php';

// Démarrage de la session si ce n'est pas déjà fait
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['user_id'];
$error = '';
$success_message = '';

// Récupération du message de succès s'il existe
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Vérification si l'utilisateur est connecté et a le rôle de chauffeur
// Si non, redirection vers la page de connexion
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'chauffeur') {
    header('Location: /connexion');
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

// Auto-annulation des trajets non commencés (30 minutes après l'heure prévue)
try {
    $sql = "UPDATE covoiturage 
            SET statut = 'annulé' 
            WHERE statut = 'en_attente' 
            AND CONCAT(date_depart, ' ', heure_depart) < DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $annulesAutomatiquement = $stmt->rowCount();
    if ($annulesAutomatiquement > 0) {
        error_log("Auto-annulé $annulesAutomatiquement trajet(s) en retard");
    }
} catch (PDOException $e) {
    error_log("Erreur auto-annulation: " . $e->getMessage());
}

// Récupération des trajets à venir (3 premiers) avec les passagers
$trajets_a_venir = [];
try {
    $sql = "SELECT c.*, v.modele, v.immatriculation, m.libelle as marque_nom,
    COUNT(p.participation_id) as nb_participants
    FROM covoiturage c
    LEFT JOIN voiture v ON c.voiture_id = v.voiture_id
    LEFT JOIN marque m ON v.marque_id = m.marque_id
    LEFT JOIN participation p ON c.covoiturage_id = p.covoiturage_id
    WHERE c.utilisateur_id = ? 
    AND c.statut = 'en_attente' 
    AND CONCAT(c.date_depart, ' ', c.heure_depart) > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
    GROUP BY c.covoiturage_id
    ORDER BY c.date_depart ASC, c.heure_depart ASC
    LIMIT 3";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $trajets_a_venir = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pour chaque trajet, récupérer les passagers
    foreach ($trajets_a_venir as &$trajet) {
        $stmt_passagers = $pdo->prepare("
            SELECT u.utilisateur_id, u.pseudo 
            FROM participation p
            JOIN utilisateur u ON p.utilisateur_id = u.utilisateur_id
            WHERE p.covoiturage_id = ?
            ORDER BY p.participation_id ASC
        ");
        $stmt_passagers->execute([$trajet['covoiturage_id']]);
        $trajet['passagers'] = $stmt_passagers->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($trajet); // Libérer la référence
    
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des trajets à venir: " . $e->getMessage();
}

// Compter le total des trajets à venir pour savoir s'il faut afficher "Afficher plus"
$total_trajets_a_venir = 0;
try {
    $sql = "SELECT COUNT(DISTINCT c.covoiturage_id) as total
    FROM covoiturage c
    WHERE c.utilisateur_id = ? 
    AND c.statut = 'en_attente' 
    AND CONCAT(c.date_depart, ' ', c.heure_depart) > DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $total_trajets_a_venir = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Erreur comptage trajets à venir: " . $e->getMessage());
}

// Récupération du trajet en cours (statut en_route) - SANS vérification du temps
$trajet_en_cours = null;
try {
    $sql = "SELECT c.*, v.modele, v.immatriculation, m.libelle as marque_nom,
            COUNT(p.participation_id) as nb_participants
            FROM covoiturage c
            LEFT JOIN voiture v ON c.voiture_id = v.voiture_id
            LEFT JOIN marque m ON v.marque_id = m.marque_id
            LEFT JOIN participation p ON c.covoiturage_id = p.covoiturage_id
            WHERE c.utilisateur_id = ? 
            AND c.statut = 'en_route'
            GROUP BY c.covoiturage_id
            ORDER BY c.date_depart DESC
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $trajet_en_cours = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Récupérer les passagers du trajet en cours
    if ($trajet_en_cours) {
        $stmt_passagers = $pdo->prepare("
            SELECT u.utilisateur_id, u.pseudo 
            FROM participation p
            JOIN utilisateur u ON p.utilisateur_id = u.utilisateur_id
            WHERE p.covoiturage_id = ?
            ORDER BY p.participation_id ASC
        ");
        $stmt_passagers->execute([$trajet_en_cours['covoiturage_id']]);
        $trajet_en_cours['passagers'] = $stmt_passagers->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération du trajet en cours: " . $e->getMessage();
}

// Récupération de l'historique des trajets (3 derniers)
$historique_trajets = [];
try {
    $sql = "SELECT c.*, v.modele, v.immatriculation, m.libelle as marque_nom
    FROM covoiturage c
    LEFT JOIN voiture v ON c.voiture_id = v.voiture_id
    LEFT JOIN marque m ON v.marque_id = m.marque_id
    WHERE c.utilisateur_id = ? 
    AND (c.statut IN ('terminé', 'annulé') 
        OR CONCAT(c.date_depart, ' ', c.heure_depart) <= DATE_SUB(NOW(), INTERVAL 30 MINUTE))
    ORDER BY c.date_depart DESC, c.heure_depart DESC
    LIMIT 3";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $historique_trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération de l'historique: " . $e->getMessage();
}

// Compter le total de l'historique
$total_historique = 0;
try {
    $sql = "SELECT COUNT(*) as total
    FROM covoiturage c
    WHERE c.utilisateur_id = ? 
    AND (c.statut IN ('terminé', 'annulé') 
        OR CONCAT(c.date_depart, ' ', c.heure_depart) <= DATE_SUB(NOW(), INTERVAL 30 MINUTE))";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $total_historique = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Erreur comptage historique: " . $e->getMessage());
}

// Fonction pour formater la date
function formatDate($date) {
    $dateTime = new DateTime($date);
    return $dateTime->format('d/m/Y');
}

// Fonction pour formater l'heure
function formatTime($time) {
    return substr($time, 0, 5); // HH:MM
}

// Fonction pour obtenir le libellé du statut
function getStatutLabel($statut) {
    switch ($statut) {
        case 'en_attente': return 'En attente';
        case 'en_route': return 'En cours';
        case 'terminé': return 'terminé';
        case 'annulé': return 'annulé';
        default: return ucfirst($statut);
    }
}

// Fonction pour afficher les passagers
function displayPassagers($passagers) {
    if (empty($passagers)) {
        return '<span class="no-passengers">Aucun passager inscrit</span>';
    }
    
    $html = '<div class="passengers-list">';
    foreach ($passagers as $passager) {
        // Utiliser photo.php pour récupérer l'avatar depuis la base de données
        $avatar = '../photo.php?id=' . $passager['utilisateur_id'];
        $pseudo = htmlspecialchars($passager['pseudo']);
        
        $html .= '<div class="passenger-item">';
        $html .= '<img src="' . $avatar . '" alt="Avatar de ' . $pseudo . '" class="passenger-avatar" onerror="this.src=\'../public/images/default-avatar.png\'">';
        $html .= '<span class="passenger-pseudo">' . $pseudo . '</span>';
        $html .= '</div>';
    }
    $html .= '</div>';
    
    return $html;
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

<main class="container">
    <div class="hero-background driver-hero">
        <div class="chauffeur-content">
            <h1>Espace Chauffeur</h1>
            <a href="role" class="btn btn-white">Changer pour passager</a>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Titre principal de la page -->
    <h2 class="page-title">Mes trajets</h2>

    <!-- Bouton pour proposer un nouveau trajet -->
    <div class="nouveau-trajet-container">
        <button class="btn btn-success" onclick="window.location.href='/proposer-trajet'">
            Proposer un nouveau trajet
        </button>
    </div>

    <!-- Section Trajet en cours -->
    <?php if ($trajet_en_cours): ?>
    <div class="form-section">
        <section class="trajets-section">
            <h2 class="section-title">Trajet en cours</h2>
            
            <div class="vehicle-card">
            <div class="trip-header-current">
                 <h3 class="trip-title-current">
                  Je suis en route
                 </h3>
            </div>
                
                <div class="trip-route-current">
                     <div class="lieu-depart"><?= htmlspecialchars($trajet_en_cours['lieu_depart']) ?></div>
                     <div class="lieu-arrivee"><?= htmlspecialchars($trajet_en_cours['lieu_arrivee']) ?></div>
                 </div>
                
                <div class="trip-info">
                    <strong>Départ :</strong> <?= formatTime($trajet_en_cours['heure_depart']) ?>
                </div>
                
                <div class="trip-info">
                    <strong>Arrivée :</strong> <?= formatTime($trajet_en_cours['heure_arrivee']) ?>
                </div>

                <div class="trip-info">
                    <strong>Prix :</strong> <?= number_format($trajet_en_cours['prix_personne'], 2) ?> crédits
                </div>
                
                <div class="trip-info">
                    <strong>Places occupées :</strong> 
                    <?php 
                        $places_occupees = isset($trajet_en_cours['nb_participants']) ? $trajet_en_cours['nb_participants'] : 0;
                        $total_places = $trajet_en_cours['nb_place'];
                        echo $places_occupees . '/' . $total_places;
                    ?>
                </div>
                
                <div class="trip-info">
                    <strong>Passagers :</strong>
                    <?= displayPassagers($trajet_en_cours['passagers']) ?>
                </div>
                
                <div class="trip-info">
                    <strong>Voiture :</strong> 
                    <?= htmlspecialchars($trajet_en_cours['marque_nom'] . ' ' . $trajet_en_cours['modele'] . ' (' . $trajet_en_cours['immatriculation'] . ')') ?>
                </div>
                
                <div class="trip-actions">
                    <button class="btn btn-success" onclick="finishTrip(<?= $trajet_en_cours['covoiturage_id'] ?>)">
                        Terminer le trajet
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="cancelTrip(<?= $trajet_en_cours['covoiturage_id'] ?>)">
                        Annuler
                    </button>
                </div>
            </div>
        </section>
    </div>
    <?php endif; ?>

    <!-- Conteneur pour placer les sections côte à côte -->
    <div class="trip-forms-container">

        <!-- Section Trajets à venir -->
        <div class="form-section">
            <section class="trajets-section">
                <h2 class="section-title">Trajets à venir</h2>
                
                <?php if (!empty($success_message)): ?>
                <div class="message success"><?= htmlspecialchars($success_message) ?></div>
                <?php endif; ?>
                
                <?php if (empty($trajets_a_venir)): ?>
                    <div class="no-trips">
                        <p>Vous n'avez aucun trajet à venir.</p>
                    </div>
                <?php else: ?>
                    <div id="trajets-a-venir-container">
                        <?php foreach ($trajets_a_venir as $trajet): ?>
                            <div class="vehicle-card">
                            <div class="trip-header-upcoming">
                                 <h3 class="trip-title-upcoming">
                                      Trajet prévu le <?= formatDate($trajet['date_depart']) ?>
                                 </h3>
                            </div>
                                
                                <div class="trip-route-upcoming">
                                     <div class="lieu-depart"><?= htmlspecialchars($trajet['lieu_depart']) ?></div>
                                      <div class="lieu-arrivee"><?= htmlspecialchars($trajet['lieu_arrivee']) ?></div>
                                 </div>
                                
                                <div class="trip-info">
                                    <strong>Départ :</strong> <?= formatTime($trajet['heure_depart']) ?>
                                </div>
                                
                                <div class="trip-info">
                                    <strong>Arrivée :</strong> <?= formatTime($trajet['heure_arrivee']) ?>
                                </div>

                                <div class="trip-info">
                                    <strong>Prix :</strong> <?= number_format($trajet['prix_personne'], 2) ?> crédits
                                </div>
                                
                                <div class="trip-info">
                                    <strong>Places occupées :</strong> 
                                    <?php 
                                        // Nombre de participants inscrits
                                        $places_occupees = isset($trajet['nb_participants']) ? $trajet['nb_participants'] : 0;
                                        // Nombre total de places proposées pour ce trajet
                                        $total_places = $trajet['nb_place'];
                                        echo $places_occupees . '/' . $total_places;
                                    ?>
                                </div>
                                
                                <div class="trip-info">
                                    <strong>Passagers :</strong>
                                    <?= displayPassagers($trajet['passagers']) ?>
                                </div>
                                
                                <div class="trip-info">
                                    <strong>Voiture :</strong> 
                                    <?= htmlspecialchars($trajet['marque_nom'] . ' ' . $trajet['modele'] . ' (' . $trajet['immatriculation'] . ')') ?>
                                </div>
                                
                                <div class="trip-actions">
                                    <button class="btn btn-primary" onclick="startTrip(<?= $trajet['covoiturage_id'] ?>)">
                                     Commencer le trajet
                                    </button>
                                    <?php if ($trajet['statut'] === 'en_attente'): ?>
                                       <button class="btn btn-danger btn-sm" onclick="cancelTrip(<?= $trajet['covoiturage_id'] ?>)">
                                         Annuler
                                       </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if ($total_trajets_a_venir > 3): ?>
                            <div class="load-more-container">
                                <button class="btn btn-secondary" onclick="loadMoreTrajets('a_venir', 3)" id="load-more-a-venir">
                                    Afficher plus de trajets (<?= $total_trajets_a_venir - 3 ?> restants)
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- Section Historique -->
        <div class="form-section">
            <section class="trajets-section">
                <h2 class="section-title">Historique des trajets</h2>
                
                <?php if (empty($historique_trajets)): ?>
                    <div class="no-trips">
                        <p>Votre historique est vide.</p>
                    </div>
                <?php else: ?>
                    <div id="historique-container" class="trips-grid">
                    <?php foreach ($historique_trajets as $trajet): ?>
                            <div class="trip-card history">
                                <div class="trip-header-history">
                                    <h3 class="trip-title-history">
                                        Covoiturage <?= getStatutLabel($trajet['statut']) ?> le <?= formatDate($trajet['date_depart']) ?>
                                    </h3>
                                </div>
                                
                                <div class="trip-route-history">
                                    <div class="lieu-depart"><?= htmlspecialchars($trajet['lieu_depart']) ?></div>
                                    <div class="lieu-arrivee"><?= htmlspecialchars($trajet['lieu_arrivee']) ?></div>
                                </div>

                                <div class="trip-info">
                                    <strong>Départ :</strong> <?= formatTime($trajet['heure_depart']) ?>
                                </div>

                                <div class="trip-info">
                                     <strong>Arrivée :</strong> <?= formatTime($trajet['heure_arrivee']) ?>
                                </div>

                                <div class="trip-info">
                                     <strong>Prix :</strong> <?= number_format($trajet['prix_personne'], 2) ?> crédits
                                </div>

                                <div class="trip-info">
                                    <strong>Places occupées :</strong> 
                                    <?php 
                                        $places_occupees = isset($trajet['nb_participants']) ? $trajet['nb_participants'] : 0;
                                        $total_places = $trajet['nb_place'];
                                        echo $places_occupees . '/' . $total_places;
                                    ?>
                                </div>

                                <div class="trip-info">
                                      <strong>Voiture :</strong> 
                                      <?= htmlspecialchars($trajet['marque_nom'] . ' ' . $trajet['modele'] . ' (' . $trajet['immatriculation'] . ')') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($total_historique > 3): ?>
                        <div class="load-more-container">
                            <button class="btn btn-secondary" onclick="loadMoreTrajets('historique', 3)" id="load-more-historique">
                                Afficher plus de trajets (<?= $total_historique - 3 ?> restants)
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        </div>
    </div>

</main>

<script>
// Variables pour la pagination
let offsetAVenir = 3;
let offsetHistorique = 3;

function cancelTrip(tripId) {
    if (confirm('Êtes-vous sûr de vouloir annuler ce trajet ? Les participants seront remboursés.')) {
        processTrip('cancel', tripId);
    }
}

function startTrip(tripId) {
    if (confirm('Voulez-vous commencer ce trajet ?')) {
        processTrip('start', tripId);
    }
}

function finishTrip(tripId) {
    if (confirm('Voulez-vous terminer ce trajet ?')) {
        processTrip('finish', tripId);
    }
}

function processTrip(action, tripId) {
    // Appel AJAX unifié pour toutes les opérations sur les trajets
    fetch('../traitement/process-trip.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            action: action,
            trip_id: tripId 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = data.message;
            
            // Messages spécifiques selon l'action
            if (action === 'finish' && data.gains) {
                message += `\nVous avez gagné ${data.gains} crédits !`;
                if (data.participants) {
                    message += `\n${data.participants} participant(s) - Commission plateforme: ${data.commission} crédits`;
                }
            } else if (action === 'cancel' && data.participants_rembourses) {
                message += `\n${data.participants_rembourses} participant(s) remboursé(s)`;
            }
            
            alert(message);
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue lors de l\'opération');
    });
}

function loadMoreTrajets(type, limit) {
    // Récupérer l'offset approprié selon le type
    const offset = (type === 'a_venir') ? offsetAVenir : offsetHistorique;
    
    // Appel AJAX pour charger plus de trajets
    fetch('../traitement/process-trip.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            action: 'load_more',
            type: type,
            offset: offset,
            limit: limit
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Identifier le conteneur approprié
            const container = document.getElementById(
                type === 'a_venir' ? 'trajets-a-venir-container' : 'historique-container'
            );
            
            // Ajouter le nouveau contenu HTML
            container.insertAdjacentHTML('beforeend', data.html);
            
            // Mettre à jour l'offset pour la prochaine requête
            if (type === 'a_venir') {
                offsetAVenir += limit;
            } else {
                offsetHistorique += limit;
            }
            
            // Gérer l'affichage du bouton "Afficher plus"
            const buttonId = `load-more-${type.replace('_', '-')}`;
            const button = document.getElementById(buttonId);
            
            if (!data.hasMore) {
                // Masquer le bouton s'il n'y a plus de trajets
                button.style.display = 'none';
            } else {
                // Mettre à jour le texte du bouton avec le nombre restant
                button.textContent = `Afficher plus de trajets (${data.remaining} restants)`;
            }
        } else {
            alert('Erreur lors du chargement: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur AJAX:', error);
        alert('Une erreur est survenue lors du chargement des trajets');
    });
}

// Auto-masquer les messages de succès après 5 secondes
setTimeout(function() {
    const successAlert = document.querySelector('.message.success');
    if (successAlert) {
        successAlert.style.opacity = '0';
        setTimeout(() => successAlert.remove(), 300);
    }
}, 5000);
</script>
</body>
</html>