<?php
// middleware/auth.php
// Fonctions pour gérer l'authentification et les autorisations des utilisateurs

/**
 * Vérifie si l'utilisateur est authentifié
 * Redirige vers la page de connexion si l'utilisateur n'est pas connecté
 * @return bool True si l'utilisateur est authentifié
 */
function checkAuth() {
    // Démarrage de la session si elle n'est pas déjà active
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vérification de l'authentification
    if (!isset($_SESSION['user_id'])) {
        // Redirection vers la page de connexion
        header('Location: /connexion');
        exit();
    }
    
    return true;
}

/**
 * Vérifie si l'utilisateur possède un rôle spécifique
 * Redirige vers la page de connexion si l'utilisateur n'a pas le rôle requis
 * @param string $role Le rôle à vérifier
 * @return bool True si l'utilisateur a le rôle spécifié
 */
function checkRole($role) {
    // Démarrage de la session si elle n'est pas déjà active
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vérification de l'authentification
    checkAuth();
    
    // Vérification du rôle
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header('Location: /connexion');
        exit();
    }
    
    return true;
}

/**
 * Vérifie si l'utilisateur a le rôle de chauffeur
 * @return bool True si l'utilisateur est un chauffeur
 */
function checkChauffeur() {
    return checkRole('chauffeur');
}

/**
 * Vérifie si l'utilisateur a le rôle de passager
 * @return bool True si l'utilisateur est un passager
 */
function checkPassager() {
    return checkRole('passager');
}

/**
 * Vérifie si l'utilisateur a le rôle d'administrateur
 * Redirige vers la page d'accès refusé si l'utilisateur n'est pas administrateur
 * @return bool True si l'utilisateur est un administrateur
 */
function checkAdmin() {
    // Démarrage de la session si elle n'est pas déjà active
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vérification de l'authentification
    checkAuth();
    
    // Vérification du rôle d'administrateur
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
        header('Location: /acces-refuse');
        exit();
    }
    
    return true;
}
?>
