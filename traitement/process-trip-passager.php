<?php
session_start();
header('Content-Type: application/json');

// Vérification de l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'passager') {
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

try {
    switch ($action) {
        case 'cancel':
            $participationId = $data['participation_id'] ?? null;
            if (!$participationId) {
                echo json_encode(['success' => false, 'message' => 'ID de participation manquant']);
                exit;
            }
            handleCancelParticipation($pdo, $participationId, $userId);
            break;
        case 'rate':
            $participationId = $data['participation_id'] ?? null;
            $rating = $data['rating'] ?? null;
            $comment = $data['comment'] ?? '';
            
            if (!$participationId || !$rating) {
                echo json_encode(['success' => false, 'message' => 'Données manquantes pour l\'évaluation']);
                exit;
            }
            handleRateTrip($pdo, $participationId, $userId, $rating, $comment);
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
 * Annuler une participation
 */
function handleCancelParticipation($pdo, $participationId, $userId) {
    $pdo->beginTransaction();
    
    try {
        // Vérifier que la participation appartient bien au passager
        $stmt = $pdo->prepare("
            SELECT p.*, c.statut, c.prix_personne 
            FROM participation p
            JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
            WHERE p.participation_id = ? AND p.utilisateur_id = ?
        ");
        $stmt->execute([$participationId, $userId]);
        $participation = $stmt->fetch();
        
        if (!$participation) {
            throw new Exception('Participation non trouvée ou non autorisée');
        }
        
        // Vérifier que le trajet peut être annulé
        if (in_array($participation['statut'], ['terminé', 'annulé'])) {
            throw new Exception('Ce trajet ne peut plus être annulé');
        }
        
        // Rembourser le passager
        $remboursement = floor($participation['prix_personne']);
        $stmt = $pdo->prepare("UPDATE utilisateur SET credits = credits + ? WHERE utilisateur_id = ?");
        $stmt->execute([$remboursement, $userId]);
        
        // Supprimer la participation
        $stmt = $pdo->prepare("DELETE FROM participation WHERE participation_id = ?");
        $stmt->execute([$participationId]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Participation annulée avec succès. Vous avez été remboursé de ' . $remboursement . ' crédits.'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Évaluer un trajet
 */
function handleRateTrip($pdo, $participationId, $userId, $rating, $comment) {
    try {
        // Vérifier que la participation appartient bien au passager et que le trajet est terminé
        $stmt = $pdo->prepare("
            SELECT p.*, c.statut 
            FROM participation p
            JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
            WHERE p.participation_id = ? AND p.utilisateur_id = ? AND c.statut = 'terminé'
        ");
        $stmt->execute([$participationId, $userId]);
        $participation = $stmt->fetch();
        
        if (!$participation) {
            throw new Exception('Participation non trouvée, non autorisée ou trajet non terminé');
        }
        
        // Vérifier que l'évaluation n'existe pas déjà pour cette participation
        $stmt = $pdo->prepare("SELECT avis_id FROM avis WHERE participation_id = ?");
        $stmt->execute([$participationId]);
        if ($stmt->fetch()) {
            throw new Exception('Vous avez déjà évalué ce trajet');
        }
        
        // Enregistrer l'évaluation dans la table avis avec participation_id
        $stmt = $pdo->prepare("
            INSERT INTO avis (participation_id, note, commentaire) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$participationId, $rating, $comment]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Évaluation enregistrée avec succès'
        ]);
        
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Charger plus de trajets (AJAX pagination)
 */
function handleLoadMore($pdo, $userId, $type, $offset, $limit) {
    try {
        if ($type === 'a_venir') {
            // Récupérer les trajets à venir avec offset
            $sql = "SELECT c.*, v.modele, v.immatriculation, m.libelle as marque_nom,
                    u.pseudo as chauffeur_pseudo, u.utilisateur_id as chauffeur_id,
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
                    LIMIT ? OFFSET ?";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $limit, $offset]);
            $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Compter le total restant
            $sqlCount = "SELECT COUNT(p.participation_id) as total
                        FROM participation p
                        JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
                        WHERE p.utilisateur_id = ? 
                        AND c.statut = 'en_attente' 
                        AND CONCAT(c.date_depart, ' ', c.heure_depart) > DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
            $stmtCount = $pdo->prepare($sqlCount);
            $stmtCount->execute([$userId]);
            $totalCount = $stmtCount->fetchColumn();
            
        } elseif ($type === 'historique') {
            // Récupérer l'historique avec offset - CORRIGÉ pour utiliser participation_id
            $sql = "SELECT c.*, v.modele, v.immatriculation, m.libelle as marque_nom,
                    u.pseudo as chauffeur_pseudo, u.utilisateur_id as chauffeur_id,
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
                    LIMIT ? OFFSET ?";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $limit, $offset]);
            $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Compter le total restant
            $sqlCount = "SELECT COUNT(p.participation_id) as total
                        FROM participation p
                        JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
                        WHERE p.utilisateur_id = ? 
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
            <strong>Prix payé :</strong> ' . number_format($trajet['prix_personne'], 2) . ' crédits
        </div>
        
        <div class="trip-info">
            <strong>Chauffeur :</strong> ' . htmlspecialchars($trajet['chauffeur_pseudo']) . '
        </div>
        
        <div class="trip-info">
            <strong>Voiture :</strong> 
            ' . htmlspecialchars($trajet['marque_nom'] . ' ' . $trajet['modele'] . ' (' . $trajet['immatriculation'] . ')') . '
        </div>
        
        <div class="trip-actions">
            <button class="btn btn-danger btn-sm" onclick="cancelParticipation(' . $trajet['participation_id'] . ')">
                Annuler
            </button>
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

function displayStars($note = null) {
    $html = '<div class="rating-stars">';
    for ($i = 1; $i <= 5; $i++) {
        $class = ($note && $i <= $note) ? 'star filled' : 'star';
        $html .= '<span class="' . $class . '" data-rating="' . $i . '">★</span>';
    }
    $html .= '</div>';
    return $html;
}
?>