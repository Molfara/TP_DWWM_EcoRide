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

// Vérification si l'utilisateur est connecté et a le rôle de passager
// Si non, redirection vers la page de connexion
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'passager') {
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

// Récupération des trajets à venir (3 premiers) - participations du passager
$trajets_a_venir = [];
try {
    $sql = "SELECT c.*, v.modele, v.immatriculation, m.libelle as marque_nom,
            u.pseudo as chauffeur_pseudo, u.utilisateur_id as chauffeur_id, u.photo as chauffeur_photo,
            p.participation_id
            FROM participation p
            JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
            LEFT JOIN voiture v ON c.voiture_id = v.voiture_id
            LEFT JOIN marque m ON v.marque_id = m.marque_id
            JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
            WHERE p.utilisateur_id = ? 
            AND c.statut = 'en_attente' 
            AND CONCAT(c.date_depart, ' ', c.heure_depart) > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ORDER BY c.date_depart ASC, c.heure_depart ASC
            LIMIT 3";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $trajets_a_venir = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des trajets à venir: " . $e->getMessage();
}

// Compter le total des trajets à venir pour savoir s'il faut afficher "Afficher plus"
$total_trajets_a_venir = 0;
try {
    $sql = "SELECT COUNT(p.participation_id) as total
            FROM participation p
            JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
            WHERE p.utilisateur_id = ? 
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
            u.pseudo as chauffeur_pseudo, u.utilisateur_id as chauffeur_id, u.photo as chauffeur_photo,
            p.participation_id
            FROM participation p
            JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
            LEFT JOIN voiture v ON c.voiture_id = v.voiture_id
            LEFT JOIN marque m ON v.marque_id = m.marque_id
            JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
            WHERE p.utilisateur_id = ? 
            AND c.statut = 'en_route'
            ORDER BY c.date_depart DESC
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $trajet_en_cours = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération du trajet en cours: " . $e->getMessage();
}

// Récupération de l'historique des trajets (3 derniers) - CORRIGÉ
$historique_trajets = [];
try {
    $sql = "SELECT c.*, v.modele, v.immatriculation, m.libelle as marque_nom,
            u.pseudo as chauffeur_pseudo, u.utilisateur_id as chauffeur_id, u.photo as chauffeur_photo,
            p.participation_id, a.note, a.commentaire
            FROM participation p
            JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
            LEFT JOIN voiture v ON c.voiture_id = v.voiture_id
            LEFT JOIN marque m ON v.marque_id = m.marque_id
            JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
            LEFT JOIN avis a ON p.participation_id = a.participation_id
            WHERE p.utilisateur_id = ? 
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
    $sql = "SELECT COUNT(p.participation_id) as total
            FROM participation p
            JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
            WHERE p.utilisateur_id = ? 
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

// Fonction pour afficher les étoiles de notation
function displayStars($note = null) {
    $html = '<div class="rating-stars">';
    for ($i = 1; $i <= 5; $i++) {
        $class = ($note && $i <= $note) ? 'star filled' : 'star';
        $html .= '<span class="' . $class . '" data-rating="' . $i . '">★</span>';
    }
    $html .= '</div>';
    return $html;
}

// Fonction pour afficher l'avatar et le pseudo du chauffeur
function displayChauffeurAvatar($chauffeur) {
    if (empty($chauffeur) || empty($chauffeur['chauffeur_pseudo'])) {
        return '';
    }
    
    $pseudo = htmlspecialchars($chauffeur['chauffeur_pseudo']);
    
    $html = '<div class="chauffeur-info">';
    $html .= '<div class="chauffeur-avatar-container">';
    
    // Créer une URL de données à partir d'un BLOB ou utiliser un placeholder
    if (!empty($chauffeur['chauffeur_photo'])) {
        $avatar = 'data:image/jpeg;base64,' . base64_encode($chauffeur['chauffeur_photo']);
        $html .= '<img src="' . $avatar . '" alt="Avatar de ' . $pseudo . '" class="chauffeur-avatar">';
    } else {
        $html .= '<div class="chauffeur-avatar-placeholder">' . strtoupper(substr($pseudo, 0, 1)) . '</div>';
    }
    
    $html .= '<span class="chauffeur-pseudo">' . $pseudo . '</span>';
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Passager - CoVoiturage</title>
    <!-- Inclusion de la feuille de style principale -->
    <link rel="stylesheet" href="../public/style.css">
</head>
<body class="page-trajets-passager"> 
    <?php
    // Inclusion de l'en-tête commun du site
    include_once '../public/header.php';
    ?>

<main class="container">
    <div class="hero-background passenger-hero">
        <div class="hero-content passenger-content">
            <h1>Espace Passager</h1>
            <a href="role" class="btn btn-white">Changer pour chauffeur</a>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Titre principal de la page -->
    <h2 class="page-title">Mes trajets</h2>

    <!-- Bouton pour trouver un nouveau covoiturage -->
    <div class="nouveau-trajet-container">
        <button class="btn btn-success" onclick="window.location.href='/covoiturage'">
            Trouver un nouveau covoiturage
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
                  Trajet en cours
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
                    <strong>Prix payé :</strong> <?= number_format($trajet_en_cours['prix_personne'], 2) ?> crédits
                </div>

                <div class="trip-info">
                    <strong>Voiture :</strong> 
                    <?= htmlspecialchars($trajet_en_cours['marque_nom'] . ' ' . $trajet_en_cours['modele'] . ' (' . $trajet_en_cours['immatriculation'] . ')') ?>
                </div>
                
                <div class="trip-info">
                    <strong>Chauffeur :</strong>
                    <?= displayChauffeurAvatar($trajet_en_cours) ?>
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
                                    <strong>Prix payé :</strong> <?= number_format($trajet['prix_personne'], 2) ?> crédits
                                </div>

                                <div class="trip-info">
                                    <strong>Voiture :</strong> 
                                    <?= htmlspecialchars($trajet['marque_nom'] . ' ' . $trajet['modele'] . ' (' . $trajet['immatriculation'] . ')') ?>
                                </div>
                                
                                <div class="trip-info">
                                    <strong>Chauffeur :</strong> 
                                    <?= displayChauffeurAvatar($trajet) ?>
                                </div>
                                
                               
                                
                                <div class="trip-actions">
                                    <button class="btn btn-danger btn-sm" onclick="cancelParticipation(<?= $trajet['participation_id'] ?>)">
                                        Annuler
                                    </button>
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
                                     <strong>Prix payé :</strong> <?= number_format($trajet['prix_personne'], 2) ?> crédits
                                </div>

                                <div class="trip-info">
                                      <strong>Voiture :</strong> 
                                      <?= htmlspecialchars($trajet['marque_nom'] . ' ' . $trajet['modele'] . ' (' . $trajet['immatriculation'] . ')') ?>
                                </div>

                                <div class="trip-info">
                                    <strong>Chauffeur :</strong> 
                                    <?= displayChauffeurAvatar($trajet) ?>
                                </div>

                                <?php if ($trajet['statut'] === 'terminé'): ?>
                                    <?php if ($trajet['note'] && $trajet['commentaire']): ?>
                                        <div class="trip-info rating-display">
                                            <strong>Votre évaluation :</strong>
                                            <?= displayStars($trajet['note']) ?>
                                            <p class="comment-display"><?= htmlspecialchars($trajet['commentaire']) ?></p>
                                        </div>
                                    <?php else: ?>
                                        <div class="rating-section" id="rating-<?= $trajet['participation_id'] ?>">
                                            <div class="trip-info">
                                                <strong>Laisser un avis :</strong>
                                                <textarea 
                                                    class="rating-comment" 
                                                    id="comment-<?= $trajet['participation_id'] ?>" 
                                                    placeholder="Votre commentaire (max 300 caractères)" 
                                                    maxlength="300"></textarea>
                                                <div class="char-count">0/300</div>
                                            </div>
                                            <div class="trip-info">
                                                <strong>Note :</strong>
                                                <div class="rating-stars clickable" data-participation="<?= $trajet['participation_id'] ?>">
                                                    <span class="star" data-rating="1">★</span>
                                                    <span class="star" data-rating="2">★</span>
                                                    <span class="star" data-rating="3">★</span>
                                                    <span class="star" data-rating="4">★</span>
                                                    <span class="star" data-rating="5">★</span>
                                                </div>
                                            </div>
                                            <div class="trip-actions">
                                                <button class="btn btn-primary btn-sm" onclick="submitRating(<?= $trajet['participation_id'] ?>)">
                                                    Évaluer le trajet
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
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
let selectedRatings = {}; // Pour stocker les notes sélectionnées

function cancelParticipation(participationId) {
    if (confirm('Êtes-vous sûr de vouloir annuler votre participation à ce trajet ? Vous serez remboursé.')) {
        processPassengerTrip('cancel', participationId);
    }
}

function processPassengerTrip(action, participationId) {
    // Appel AJAX unifié pour toutes les opérations sur les trajets passager
    fetch('../traitement/process-trip-passager.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            action: action,
            participation_id: participationId 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
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
    fetch('../traitement/process-trip-passager.php', {
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
            
            // Réactiver les événements pour les nouveaux éléments
            initializeRatingEvents();
            
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

function submitRating(participationId) {
    const rating = selectedRatings[participationId] || 0;
    const comment = document.getElementById(`comment-${participationId}`).value.trim();
    
    if (rating === 0) {
        alert('Veuillez sélectionner une note');
        return;
    }
    
    // Appel AJAX pour soumettre la note et le commentaire
    fetch('../traitement/process-trip-passager.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            action: 'rate',
            participation_id: participationId,
            rating: rating,
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Évaluation enregistrée avec succès !');
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue lors de l\'enregistrement de l\'évaluation');
    });
}

// Fonction pour initialiser les événements de notation
function initializeRatingEvents() {
    // Gestion des étoiles de notation
    document.querySelectorAll('.rating-stars.clickable').forEach(function(starsContainer) {
        // Éviter de dupliquer les événements
        if (starsContainer.dataset.initialized) return;
        starsContainer.dataset.initialized = 'true';
        
        const participationId = starsContainer.dataset.participation;
        const stars = starsContainer.querySelectorAll('.star');
        
        stars.forEach(function(star, index) {
            star.addEventListener('click', function() {
                const rating = parseInt(star.dataset.rating);
                selectedRatings[participationId] = rating;
                
                // Mettre à jour l'affichage des étoiles
                stars.forEach(function(s, i) {
                    if (i < rating) {
                        s.classList.add('filled');
                    } else {
                        s.classList.remove('filled');
                    }
                });
            });
            
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(star.dataset.rating);
                stars.forEach(function(s, i) {
                    if (i < rating) {
                        s.classList.add('hover');
                    } else {
                        s.classList.remove('hover');
                    }
                });
            });
        });
        
        starsContainer.addEventListener('mouseleave', function() {
            stars.forEach(function(s) {
                s.classList.remove('hover');
            });
        });
    });
    
    // Compteur de caractères pour les commentaires
    document.querySelectorAll('.rating-comment').forEach(function(textarea) {
        // Éviter de dupliquer les événements
        if (textarea.dataset.initialized) return;
        textarea.dataset.initialized = 'true';
        
        const charCount = textarea.nextElementSibling;
        
        textarea.addEventListener('input', function() {
            const count = textarea.value.length;
            charCount.textContent = `${count}/300`;
            
            if (count > 280) {
                charCount.style.color = '#e74c3c';
            } else {
                charCount.style.color = '#666';
            }
        });
    });
}

// Gestion des étoiles cliquables
document.addEventListener('DOMContentLoaded', function() {
    initializeRatingEvents();
});

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