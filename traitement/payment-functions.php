<?php
/**
 * Fonctions pour la gestion des paiements différés
 */

/**
 * Programmer un paiement différé de 15 minutes
 */
function scheduleDelayedPayment($pdo, $tripId, $userId, $gainsChauffeur) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO paiement_planifie (covoiturage_id, utilisateur_id, montant, execute_at, statut) 
            VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE), 'en_attente')
        ");
        $stmt->execute([$tripId, $userId, $gainsChauffeur]);
        
        error_log("Paiement planifié pour le trajet $tripId dans 15 minutes");
        return true;
    } catch (Exception $e) {
        error_log("Erreur lors de la planification du paiement: " . $e->getMessage());
        return false;
    }
}

/**
 * Traiter les paiements différés
 */
function processDelayedPayments($pdo) {
    try {
        $pdo->beginTransaction();
        
        // Récupérer les paiements prêts à être exécutés
        $stmt = $pdo->prepare("
            SELECT pp.*, c.paiement_statut 
            FROM paiement_planifie pp
            JOIN covoiturage c ON pp.covoiturage_id = c.covoiturage_id
            WHERE pp.statut = 'en_attente' 
            AND pp.execute_at <= NOW()
            AND c.paiement_statut = 'en_attente_timer'
        ");
        $stmt->execute();
        $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $processed = 0;
        
        foreach ($paiements as $paiement) {
            // Vérifier la note moyenne actuelle
            $noteCheck = $pdo->prepare("
                SELECT COALESCE(AVG(a.note), 5) as note_actuelle
                FROM participation p
                LEFT JOIN avis a ON p.participation_id = a.participation_id
                WHERE p.covoiturage_id = ?
            ");
            $noteCheck->execute([$paiement['covoiturage_id']]);
            $noteActuelle = $noteCheck->fetchColumn();
            
            if ($noteActuelle >= 3) {
                // CONDITIONS REMPLIES -> EFFECTUER LE PAIEMENT
                
                // Transférer les crédits au chauffeur
                $updateCredits = $pdo->prepare("
                    UPDATE utilisateur 
                    SET credits = credits + ? 
                    WHERE utilisateur_id = ?
                ");
                $updateCredits->execute([$paiement['montant'], $paiement['utilisateur_id']]);
                
                // Marquer le paiement comme effectué dans covoiturage
                $updateStatutTrajet = $pdo->prepare("
                    UPDATE covoiturage 
                    SET paiement_statut = 'paye' 
                    WHERE covoiturage_id = ?
                ");
                $updateStatutTrajet->execute([$paiement['covoiturage_id']]);
                
                // Marquer la tâche comme exécutée
                $updateTache = $pdo->prepare("
                    UPDATE paiement_planifie 
                    SET statut = 'execute' 
                    WHERE paiement_planifie_id = ?
                ");
                $updateTache->execute([$paiement['paiement_planifie_id']]);
                
                $processed++;
                error_log("Paiement automatique effectué - Trajet: {$paiement['covoiturage_id']}, Chauffeur: {$paiement['utilisateur_id']}, Montant: {$paiement['montant']} crédits");
                
            } else {
                // NOTE DEVENUE < 3 -> PASSER EN RÉVISION MANUELLE
                
                $updateStatutTrajet = $pdo->prepare("
                    UPDATE covoiturage 
                    SET paiement_statut = 'en_attente_review' 
                    WHERE covoiturage_id = ?
                ");
                $updateStatutTrajet->execute([$paiement['covoiturage_id']]);
                
                // Marquer la tâche comme annulée
                $updateTache = $pdo->prepare("
                    UPDATE paiement_planifie 
                    SET statut = 'annule' 
                    WHERE paiement_planifie_id = ?
                ");
                $updateTache->execute([$paiement['paiement_planifie_id']]);
                
                error_log("Paiement suspendu pour révision - Trajet: {$paiement['covoiturage_id']}, Note actuelle: {$noteActuelle}");
            }
        }
        
        $pdo->commit();
        return $processed;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erreur lors du traitement des paiements différés: " . $e->getMessage());
        return 0;
    }
}
?>