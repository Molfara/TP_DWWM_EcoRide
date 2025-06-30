<?php
session_start();
header('Content-Type: application/json');

// Vérification de l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chauffeur') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Récupération des données POST
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? null;
$userId = $_SESSION['user_id'];

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'Action manquante']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/payment-functions.php';

try {
    switch ($action) {
        case 'start':
            $tripId = $data['trip_id'] ?? null;
            if (!$tripId) {
                echo json_encode(['success' => false, 'message' => 'ID du trajet manquant']);
                exit;
            }
            handleStartTrip($pdo, $tripId, $userId);
            break;
        case 'finish':
            $tripId = $data['trip_id'] ?? null;
            if (!$tripId) {
                echo json_encode(['success' => false, 'message' => 'ID du trajet manquant']);
                exit;
            }
            handleFinishTrip($pdo, $tripId, $userId);
            break;
        case 'cancel':
            $tripId = $data['trip_id'] ?? null;
            if (!$tripId) {
                echo json_encode(['success' => false, 'message' => 'ID du trajet manquant']);
                exit;
            }
            handleCancelTrip($pdo, $tripId, $userId);
            break;
        case 'load_more':
            $type = $data['type'] ?? null;
            $offset = $data['offset'] ?? 0;
            $limit = $data['limit'] ?? 3;
            
            if (!$type) {
                echo json_encode(['success' => false, 'message' => 'Type manquant']);
                exit;
            }
            handleLoadMore($pdo, $userId, $type, $offset, $limit);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
            exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}

/**
 * Charger plus de trajets (AJAX pagination)
 */
function handleLoadMore($pdo, $userId, $type, $offset, $limit) {
    try {
        if ($type === 'a_venir') {
            // Récupérer les trajets à venir avec offset
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
                    LIMIT ? OFFSET ?";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $limit, $offset]);
            $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Pour chaque trajet, récupérer les passagers
            foreach ($trajets as &$trajet) {
                $stmt_passagers = $pdo->prepare("
                    SELECT u.utilisateur_id, u.pseudo, u.photo  
                    FROM participation p
                    JOIN utilisateur u ON p.utilisateur_id = u.utilisateur_id
                    WHERE p.covoiturage_id = ?
                    ORDER BY p.participation_id ASC
                ");
                $stmt_passagers->execute([$trajet['covoiturage_id']]);
                $trajet['passagers'] = $stmt_passagers->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($trajet);
            
            // Compter le total restant
            $sqlCount = "SELECT COUNT(DISTINCT c.covoiturage_id) as total
                        FROM covoiturage c
                        WHERE c.utilisateur_id = ? 
                        AND c.statut = 'en_attente' 
                        AND CONCAT(c.date_depart, ' ', c.heure_depart) > DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
            $stmtCount = $pdo->prepare($sqlCount);
            $stmtCount->execute([$userId]);
            $totalCount = $stmtCount->fetchColumn();
            
        } elseif ($type === 'historique') {
            // Récupérer l'historique avec offset
            $sql = "SELECT c.*, v.modele, v.immatriculation, m.libelle as marque_nom
                    FROM covoiturage c
                    LEFT JOIN voiture v ON c.voiture_id = v.voiture_id
                    LEFT JOIN marque m ON v.marque_id = m.marque_id
                    WHERE c.utilisateur_id = ? 
                    AND (c.statut IN ('terminé', 'annulé') 
                        OR CONCAT(c.date_depart, ' ', c.heure_depart) <= DATE_SUB(NOW(), INTERVAL 30 MINUTE))
                    ORDER BY c.date_depart DESC, c.heure_depart DESC
                    LIMIT ? OFFSET ?";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $limit, $offset]);
            $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Compter le total restant
            $sqlCount = "SELECT COUNT(*) as total
                        FROM covoiturage c
                        WHERE c.utilisateur_id = ? 
                        AND (c.statut IN ('terminé', 'annulé') 
                            OR CONCAT(c.date_depart, ' ', c.heure_depart) <= DATE_SUB(NOW(), INTERVAL 30 MINUTE))";
            $stmtCount = $pdo->prepare($sqlCount);
            $stmtCount->execute([$userId]);
            $totalCount = $stmtCount->fetchColumn();
        } else {
            echo json_encode(['success' => false, 'message' => 'Type invalide']);
            return;
        }
        
        // Générer le HTML
        $html = generateTrajetsHTML($trajets, $type);
        
        // Calculer s'il reste des trajets
        $hasMore = ($offset + $limit) < $totalCount;
        $remaining = max(0, $totalCount - ($offset + $limit));
        
        echo json_encode([
            'success' => true,
            'html' => $html,
            'hasMore' => $hasMore,
            'remaining' => $remaining
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
}

/**
 * Générer le HTML pour les trajets
 */
function generateTrajetsHTML($trajets, $type) {
    if (empty($trajets)) {
        return '';
    }
    
    $html = '';
    
    foreach ($trajets as $trajet) {
        if ($type === 'a_venir') {
            $html .= generateTrajetAVenirHTML($trajet);
        } elseif ($type === 'historique') {
            $html .= generateHistoriqueHTML($trajet);
        }
    }
    
    return $html;
}

/**
 * Générer HTML pour un trajet à venir
 */
function generateTrajetAVenirHTML($trajet) {
    $places_occupees = isset($trajet['nb_participants']) ? $trajet['nb_participants'] : 0;
    $total_places = $trajet['nb_place'];
    
    $passagersHtml = displayPassagers($trajet['passagers']);
    
    return '
    <div class="vehicle-card">
        <div class="trip-header-upcoming">
            <h3 class="trip-title-upcoming">
                Trajet prévu le ' . formatDate($trajet['date_depart']) . '
            </h3>
        </div>
        
        <div class="trip-route-upcoming">
            <div class="lieu-depart">' . htmlspecialchars($trajet['lieu_depart']) . '</div>
            <div class="lieu-arrivee">' . htmlspecialchars($trajet['lieu_arrivee']) . '</div>
        </div>
        
        <div class="trip-info">
            <strong>Départ :</strong> ' . formatTime($trajet['heure_depart']) . '
        </div>
        
        <div class="trip-info">
            <strong>Arrivée :</strong> ' . formatTime($trajet['heure_arrivee']) . '
        </div>
        
        <div class="trip-info">
            <strong>Prix :</strong> ' . number_format($trajet['prix_personne'], 2) . ' crédits
        </div>
        
        <div class="trip-info">
            <strong>Places occupées :</strong> ' . $places_occupees . '/' . $total_places . '
        </div>
        
        ' . ($passagersHtml ? '
        <div class="trip-info">
            <strong>Passagers :</strong>
            ' . $passagersHtml . '
        </div>' : '') . '
        
        <div class="trip-info">
            <strong>Voiture :</strong> 
            ' . htmlspecialchars($trajet['marque_nom'] . ' ' . $trajet['modele'] . ' (' . $trajet['immatriculation'] . ')') . '
        </div>
        
        <div class="trip-actions">
            <button class="btn btn-primary" onclick="startTrip(' . $trajet['covoiturage_id'] . ')">
                Commencer le trajet
            </button>
            ' . ($trajet['statut'] === 'en_attente' ? '
            <button class="btn btn-danger btn-sm" onclick="cancelTrip(' . $trajet['covoiturage_id'] . ')">
                Annuler
            </button>' : '') . '
        </div>
    </div>';
}

/**
 * Générer HTML pour l'historique
 */
function generateHistoriqueHTML($trajet) {
    // Récupérer les avis pour ce trajet (dans la fonction handleLoadMore)
    global $pdo;
    $avis_passagers = [];
    if ($trajet['statut'] === 'terminé') {
        $stmt_avis = $pdo->prepare("
            SELECT a.note, a.commentaire, u.pseudo, u.photo, p.participation_id
            FROM avis a
            JOIN participation p ON a.participation_id = p.participation_id
            JOIN utilisateur u ON p.utilisateur_id = u.utilisateur_id
            WHERE p.covoiturage_id = ?
            ORDER BY a.avis_id ASC
        ");
        $stmt_avis->execute([$trajet['covoiturage_id']]);
        $avis_passagers = $stmt_avis->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Section avis
    $avisSection = '';
    if ($trajet['statut'] === 'terminé') {
        if (!empty($avis_passagers)) {
            $avisSection = '
            <div class="trip-reviews-section">
                <div class="trip-info">
                    <strong>Avis des passagers :</strong>
                    ' . displayAvisPassagersInProcess($avis_passagers) . '
                </div>
            </div>';
        } else {
            $avisSection = '
            <div class="trip-reviews-section">
                <div class="trip-info">
                    <strong>Avis des passagers :</strong>
                    <div class="no-reviews">
                        <p style="color: #6c757d; font-style: italic;">Aucun avis reçu pour ce trajet.</p>
                    </div>
                </div>
            </div>';
        }
    }
    
    return '
    <div class="trip-card history">
        <div class="trip-header-history">
            <h3 class="trip-title-history">
                Covoiturage ' . getStatutLabel($trajet['statut']) . ' le ' . formatDate($trajet['date_depart']) . '
            </h3>
        </div>
        
        <div class="trip-route-history">
            <div class="lieu-depart">' . htmlspecialchars($trajet['lieu_depart']) . '</div>
            <div class="lieu-arrivee">' . htmlspecialchars($trajet['lieu_arrivee']) . '</div>
        </div>

        <div class="trip-info">
            <strong>Départ :</strong> ' . formatTime($trajet['heure_depart']) . '
        </div>

        <div class="trip-info">
            <strong>Arrivée :</strong> ' . formatTime($trajet['heure_arrivee']) . '
        </div>

        <div class="trip-info">
            <strong>Prix :</strong> ' . number_format($trajet['prix_personne'], 2) . ' crédits
        </div>

        <div class="trip-info">
            <strong>Places occupées :</strong> ' . $trajet['nb_place'] . '
        </div>

        <div class="trip-info">
            <strong>Voiture :</strong> 
            ' . htmlspecialchars($trajet['marque_nom'] . ' ' . $trajet['modele'] . ' (' . $trajet['immatriculation'] . ')') . '
        </div>
        
        ' . $avisSection . '
    </div>';
}

// Fonctions utilitaires
function formatDate($date) {
    $dateTime = new DateTime($date);
    return $dateTime->format('d/m/Y');
}

function formatTime($time) {
    return substr($time, 0, 5);
}

function getStatutLabel($statut) {
    switch ($statut) {
        case 'en_attente': return 'En attente';
        case 'en_route': return 'En cours';
        case 'terminé': return 'terminé';
        case 'annulé': return 'annulé';
        default: return ucfirst($statut);
    }
}

// Fonction pour afficher les passagers (identique à celle de trajets-chauffeur.php)
function displayPassagers($passagers) {
    if (empty($passagers)) {
        return null;
    }
    
    $html = '<div class="passengers-list">';
    foreach ($passagers as $passager) {
        $pseudo = htmlspecialchars($passager['pseudo']);
        
        $html .= '<div class="passenger-item">';
        
        // Créer une URL de données à partir d’un BLOB ou utiliser un placeholder
        if (!empty($passager['photo'])) {
            $avatar = 'data:image/jpeg;base64,' . base64_encode($passager['photo']);
            $html .= '<img src="' . $avatar . '" alt="Avatar de ' . $pseudo . '" class="passenger-avatar">';
        } else {
            $html .= '<div class="passenger-avatar-placeholder">' . strtoupper(substr($pseudo, 0, 1)) . '</div>';
        }
        
        $html .= '<span class="passenger-pseudo">' . $pseudo . '</span>';
        $html .= '</div>';
    }
    $html .= '</div>';
    
    return $html;
}

/**
 * Démarrer un trajet
 */
function handleStartTrip($pdo, $tripId, $userId) {
    // Vérifier que le trajet appartient bien au chauffeur et est en attente
    $stmt = $pdo->prepare("SELECT * FROM covoiturage WHERE covoiturage_id = ? AND utilisateur_id = ? AND statut = 'en_attente'");
    $stmt->execute([$tripId, $userId]);
    $trajet = $stmt->fetch();
    
    if (!$trajet) {
        echo json_encode(['success' => false, 'message' => 'Trajet non trouvé ou non autorisé']);
        return;
    }
    
    // Vérifier qu'aucun autre trajet n'est en cours
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM covoiturage WHERE utilisateur_id = ? AND statut = 'en_route'");
    $stmt->execute([$userId]);
    $trajetsEnCours = $stmt->fetchColumn();
    
    if ($trajetsEnCours > 0) {
        echo json_encode(['success' => false, 'message' => 'Vous avez déjà un trajet en cours']);
        return;
    }
    
    // Mettre à jour le statut du trajet
    $stmt = $pdo->prepare("UPDATE covoiturage SET statut = 'en_route' WHERE covoiturage_id = ?");
    $stmt->execute([$tripId]);
    
    echo json_encode(['success' => true, 'message' => 'Trajet démarré avec succès']);
}

/**
 * Terminer un trajet avec la nouvelle logique de paiement
 */
function handleFinishTrip($pdo, $tripId, $userId) {
    $pdo->beginTransaction();
    
    try {
        // Vérifier que le trajet appartient bien au chauffeur et est en cours
        $stmt = $pdo->prepare("SELECT * FROM covoiturage WHERE covoiturage_id = ? AND utilisateur_id = ? AND statut = 'en_route'");
        $stmt->execute([$tripId, $userId]);
        $trajet = $stmt->fetch();
        
        if (!$trajet) {
            throw new Exception('Trajet non trouvé ou non en cours');
        }
        
        // Calculer le nombre de participants
        $stmt = $pdo->prepare("SELECT COUNT(*) as nb_participants FROM participation WHERE covoiturage_id = ?");
        $stmt->execute([$tripId]);
        $participants = $stmt->fetch();
        $nbParticipants = $participants['nb_participants'];
        
        // Calculer les gains POTENTIELS (sans les transférer pour le moment)
        $prixTotal = $nbParticipants * $trajet['prix_personne'];
        $commissionPlateforme = $nbParticipants * 2; // 2 crédits par participant pour la plateforme
        $gainsChauffeur = $prixTotal - $commissionPlateforme;
        
        // S'assurer que les gains ne sont pas négatifs
        $gainsChauffeur = max(0, $gainsChauffeur);
        $commissionPlateforme = min($prixTotal, $commissionPlateforme);
        
        // Calculer la note moyenne des avis existants
        $stmt = $pdo->prepare("
            SELECT AVG(a.note) as note_moyenne 
            FROM avis a 
            JOIN participation p ON a.participation_id = p.participation_id 
            WHERE p.covoiturage_id = ?
        ");
        $stmt->execute([$tripId]);
        $resultNote = $stmt->fetch();
        $noteMoyenne = $resultNote['note_moyenne'] ? round($resultNote['note_moyenne'], 1) : null;
        
        // Déterminer le statut de paiement selon la logique:
        // - Pas d'avis OU note >= 3 : paiement dans 15 minutes
        // - Note < 3 : attente de révision manuelle
        if ($noteMoyenne === null || $noteMoyenne >= 3) {
            $paiementStatut = 'en_attente_timer';
            $message = 'Trajet terminé avec succès. ';
            if ($noteMoyenne === null) {
                $message .= 'Paiement prévu dans 15 minutes (aucun avis reçu).';
            } else {
                $message .= "Paiement prévu dans 15 minutes (note moyenne: {$noteMoyenne}/5).";
            }
        } else {
            $paiementStatut = 'en_attente_review';
            $message = "Trajet terminé. Paiement en attente de vérification (note moyenne: {$noteMoyenne}/5).";
        }
        
        // Mettre à jour le trajet avec les gains calculés et le statut de paiement
        $stmt = $pdo->prepare("
            UPDATE covoiturage 
            SET statut = 'terminé', 
                gains_chauffeur = ?, 
                commission_plateforme = ?,
                paiement_statut = ?
            WHERE covoiturage_id = ?
        ");
        $stmt->execute([$gainsChauffeur, $commissionPlateforme, $paiementStatut, $tripId]);

        // PLANIFIER LE PAIEMENT DIFFÉRÉ (si statut = en_attente_timer)
        if ($paiementStatut === 'en_attente_timer') {
         scheduleDelayedPayment($pdo, $tripId, $userId, $gainsChauffeur);
        }
                
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'gains_potentiels' => $gainsChauffeur,
            'participants' => $nbParticipants,
            'commission' => $commissionPlateforme,
            'note_moyenne' => $noteMoyenne,
            'paiement_statut' => $paiementStatut
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Annuler un trajet
 */
function handleCancelTrip($pdo, $tripId, $userId) {
    $pdo->beginTransaction();
    
    try {
        // Vérifier que le trajet appartient bien au chauffeur
        $stmt = $pdo->prepare("SELECT * FROM covoiturage WHERE covoiturage_id = ? AND utilisateur_id = ?");
        $stmt->execute([$tripId, $userId]);
        $trajet = $stmt->fetch();
        
        if (!$trajet) {
            throw new Exception('Trajet non trouvé ou non autorisé');
        }
        
        // Vérifier que le trajet peut être annulé
        if (in_array($trajet['statut'], ['terminé', 'annulé'])) {
            throw new Exception('Ce trajet ne peut plus être annulé');
        }
        
        // Rembourser les participants
        $stmt = $pdo->prepare("
            SELECT p.utilisateur_id 
            FROM participation p 
            WHERE p.covoiturage_id = ?
        ");
        $stmt->execute([$tripId]);
        $participants = $stmt->fetchAll();
        
        foreach ($participants as $participant) {
            $remboursement = floor($trajet['prix_personne']);
            $stmt = $pdo->prepare("UPDATE utilisateur SET credits = credits + ? WHERE utilisateur_id = ?");
            $stmt->execute([$remboursement, $participant['utilisateur_id']]);
        }
        
        // Supprimer les participations
        $stmt = $pdo->prepare("DELETE FROM participation WHERE covoiturage_id = ?");
        $stmt->execute([$tripId]);
        
        // Mettre à jour le statut du trajet et de paiement
        $stmt = $pdo->prepare("UPDATE covoiturage SET statut = 'annulé', paiement_statut = 'annulé' WHERE covoiturage_id = ?");
        $stmt->execute([$tripId]);
        
        $pdo->commit();
        
        $message = ($trajet['statut'] === 'en_route') ? 
                   'Trajet en cours annulé avec succès' : 
                   'Trajet annulé avec succès';
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'participants_rembourses' => count($participants)
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// FONCTION POUR LES AVIS DANS LE FICHIER DE TRAITEMENT

function displayAvisPassagersInProcess($avis_passagers) {
    if (empty($avis_passagers)) {
        return '<div class="no-reviews"><p style="color: #6c757d; font-style: italic;">Aucun avis reçu pour ce trajet.</p></div>';
    }
    
    $html = '<div class="reviews-container">';
    foreach ($avis_passagers as $avis) {
        $pseudo = htmlspecialchars($avis['pseudo']);
        
        $html .= '<div class="review-item">';
        
        // Avatar + pseudo du passager
        $html .= '<div class="reviewer-info">';
        if (!empty($avis['photo'])) {
            $avatar = 'data:image/jpeg;base64,' . base64_encode($avis['photo']);
            $html .= '<img src="' . $avatar . '" alt="Avatar de ' . $pseudo . '" class="reviewer-avatar">';
        } else {
            $html .= '<div class="reviewer-avatar-placeholder">' . strtoupper(substr($pseudo, 0, 1)) . '</div>';
        }
        $html .= '<span class="reviewer-pseudo">' . $pseudo . '</span>';
        $html .= '</div>';
        
        // Note (étoiles)
        $html .= '<div class="review-rating">';
        $html .= displayStarsInProcess($avis['note']);
        $html .= '</div>';
        
        // Commentaire
        if (!empty($avis['commentaire'])) {
            $html .= '<div class="review-comment">';
            $html .= '<p>' . htmlspecialchars($avis['commentaire']) . '</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>'; // fin review-item
    }
    $html .= '</div>'; // fin reviews-container
    
    return $html;
}

// FONCTION POUR AFFICHER LES ÉTOILES DANS LE FICHIER DE TRAITEMENT
function displayStarsInProcess($note = null) {
    $html = '<div class="rating-stars">';
    for ($i = 1; $i <= 5; $i++) {
        $class = ($note && $i <= $note) ? 'star filled' : 'star';
        $html .= '<span class="' . $class . '">★</span>';
    }
    $html .= '</div>';
    return $html;
}
?>