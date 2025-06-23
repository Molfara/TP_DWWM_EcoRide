<?php
/**
 * Cron job pour les paiements automatiques
 * Ce script doit être exécuté toutes les minutes
 */

// Empêcher l'accès direct depuis le navigateur (optionnel)
if (php_sapi_name() !== 'cli') {
    // Si pas en ligne de commande, vérifier un token secret
    $secret_token = $_GET['token'] ?? '';
    if ($secret_token !== 'votre_token_secret_123') {
        http_response_code(403);
        die('Accès interdit');
    }
}

require_once __DIR__ . '/../config/database.php';

// Log pour debug
$logFile = __DIR__ . '/../logs/payments.log';

function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
    
    // Créer le dossier logs s'il n'existe pas
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

writeLog("=== Début du processus de paiement automatique ===");

try {
    // Rechercher les trajets en attente de paiement depuis plus de 15 minutes
    $sql = "
        SELECT covoiturage_id, utilisateur_id, gains_chauffeur, commission_plateforme,
               lieu_depart, lieu_arrivee, date_depart,
               TIMESTAMPDIFF(MINUTE, updated_at, NOW()) as minutes_ecoulees
        FROM covoiturage 
        WHERE paiement_statut = 'en_attente_timer'
        AND statut = 'terminé'
        AND TIMESTAMPDIFF(MINUTE, updated_at, NOW()) >= 15
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $trajetsAPayer = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($trajetsAPayer)) {
        writeLog("Aucun trajet à payer automatiquement.");
        writeLog("=== Fin du processus ===");
        echo "Aucun paiement à traiter.\n";
        exit;
    }
    
    writeLog("Trajets trouvés à payer: " . count($trajetsAPayer));
    
    foreach ($trajetsAPayer as $trajet) {
        $pdo->beginTransaction();
        
        try {
            $covoiturageId = $trajet['covoiturage_id'];
            $chauffeurId = $trajet['utilisateur_id'];
            $gains = $trajet['gains_chauffeur'];
            
            writeLog("Traitement du trajet ID: {$covoiturageId}, Chauffeur: {$chauffeurId}, Gains: {$gains}");
            
            // Vérifier que le trajet n'a pas déjà été payé (sécurité)
            $stmt = $pdo->prepare("
                SELECT paiement_statut 
                FROM covoiturage 
                WHERE covoiturage_id = ?
            ");
            $stmt->execute([$covoiturageId]);
            $currentStatus = $stmt->fetchColumn();
            
            if ($currentStatus !== 'en_attente_timer') {
                writeLog("ATTENTION: Statut changé pour trajet {$covoiturageId}: {$currentStatus}");
                $pdo->rollBack();
                continue;
            }
            
            // Transférer les crédits au chauffeur
            if ($gains > 0) {
                $gainsEntiers = floor($gains);
                $stmt = $pdo->prepare("
                    UPDATE utilisateur 
                    SET credits = credits + ? 
                    WHERE utilisateur_id = ?
                ");
                $stmt->execute([$gainsEntiers, $chauffeurId]);
                
                writeLog("Crédits transférés: {$gainsEntiers} → Chauffeur {$chauffeurId}");
            }
            
            // Mettre à jour le statut de paiement
            $stmt = $pdo->prepare("
                UPDATE covoiturage 
                SET paiement_statut = 'payé',
                    updated_at = NOW()
                WHERE covoiturage_id = ?
            ");
            $stmt->execute([$covoiturageId]);
            
            $pdo->commit();
            
            writeLog("✅ Paiement automatique réussi pour trajet {$covoiturageId}");
            
        } catch (Exception $e) {
            $pdo->rollBack();
            writeLog("❌ Erreur pour trajet {$covoiturageId}: " . $e->getMessage());
        }
    }
    
    writeLog("=== Fin du processus - " . count($trajetsAPayer) . " trajet(s) traité(s) ===");
    echo "Processus terminé. " . count($trajetsAPayer) . " paiement(s) traité(s).\n";
    
} catch (Exception $e) {
    writeLog("❌ ERREUR CRITIQUE: " . $e->getMessage());
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>