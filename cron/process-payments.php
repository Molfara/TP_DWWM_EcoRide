<?php
/**
 * Script cron pour traiter les paiements différés
 * À exécuter toutes les 5-10 minutes
 */

// Inclure la configuration de base de données
require_once __DIR__ . '/../config/database.php';

// Inclure les fonctions de paiement
require_once __DIR__ . '/../traitement/payment-functions.php';

try {
    // Traiter les paiements en attente
    $processed = processDelayedPayments($pdo);
    
    // Log du résultat
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] Paiements traités: $processed\n";
    
    if ($processed > 0) {
        error_log("Cron paiements: $processed paiements traités avec succès");
    }
    
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] ERREUR: " . $e->getMessage() . "\n";
    error_log("Erreur cron paiements: " . $e->getMessage());
}
?>