# TP_DWWM_EcoRide
Projet pédagogique de développement de la plateforme de covoiturage EcoRide.

## Début de projet

### 1. Configuration de PHP, MySQL et MongoDB :
- PHP 8.4.2 ;
- MySQL  Ver 9.1.0 for macos14 on arm64.
- MongoDB v8.0.4

### 2. Installation de Bootstrap.

### 3. Création d'un repo TP_DWWM_EcoRide sur GitHub.

### 4. Ajout d'une branche "develop".

### 5. Début de la documentation des étapes de développement dans le README.md sur GitHub.

### 6. Clonage d'un repo dans un dossier local.

### 7. Installation de VS Code et Copilot.

### 8. Installation de Composer.

### 9. Création de la structure primaire du projet :

- Les dossiers : `public`, `src`, `config`
- Les fichiers : `public/index.php`, `config/database.php`, `.env`.

### 10. Création de la base de données DB_EcoRide à MySQL via phpMyAdmin.

### 11. Création de la table "utilisateur" dans la base de données DB_EcoRide.

### 12. Démarrage du premier script PHP " Bienvenue dans EcoRide ! " :

- Ajout du code avec " Bienvenue dans EcoRide! " dans public/index.php via VS Code ;
- Lancement du serveur localhost:8080 ;
- Vérification de la page http://localhost:8080 dans le navigateur.

### 13. Routage initiale

Le fichier `public/index.php` gère le routage des pages du site :

```php
'' => 'pages/accueil.php',
    'covoiturage' => 'pages/covoiturage.php',
    'connection' => 'pages/connection.php',
    'contact' => 'pages/contact.php'
];

if (array_key_exists($request, $routes)) {
    require __DIR__ . '/../' . $routes[$request];
} else {
    http_response_code(404);
    echo "Page non trouvée.";
```

## Description des dossiers et fichiers :

- **/public** : Ce dossier contient les fichiers accessibles aux utilisateurs via le navigateur. Il comprend le fichier `index.php`, qui est le point d'entrée pour toutes les requêtes HTTP.

- **/src** : Ce dossier contient le code principal de l'application, y compris la logique métier, les modèles, les contrôleurs et d'autres composants.

- **/config** : Dossier destiné à stocker les fichiers de configuration. Par exemple, le fichier `database.php` contient les paramètres de connexion à la base de données.

- **.env** : Ce fichier contient des variables d'environnement confidentielles, telles que les paramètres de connexion à la base de données, les clés API et d'autres données sensibles qui ne doivent pas être ajoutées au système de contrôle de version.

- **/public** : Ce dossier contient les fichiers accessibles aux utilisateurs via le navigateur. Il comprend le fichier `index.php`, qui es$

- **/src** : Ce dossier contient le code principal de l'application, y compris la logique métier, les modèles, les contrôleurs et d'autres $

- **/config** : Dossier destiné à stocker les fichiers de configuration. Par exemple, le fichier `database.php` contient les paramètres de $

- **.env** : Ce fichier contient des variables d'environnement confidentielles, telles que les paramètres de connexion à la base de données$

## Justification des choix techniques

### Environnement de développement
- **VS Code + Copilot** : Choisi pour sa légèreté, ses nombreuses extensions et l'aide à la programmation via Copilot

### Stack technique

- **PHP 8.4.2** : Dernière version stable offrant les fonctionnalités modernes de PHP comme les types, les attributs et les améliorations de performance

- **PHP 8.4.2** : Dernière version stable offrant les fonctionnalités modernes de PHP comme les types, les attributs et les améliorations d$

- **MySQL 9.1.0** : Choisi pour :
  - Sa fiabilité et ses performances pour les données relationnelles
  - Sa compatibilité avec PHP via PDO
  - Sa facilité d'administration avec phpMyAdmin

- **MongoDB v8.0.4** : Sélectionné pour :
  - Sa flexibilité pour stocker des données non structurées
  - Ses performances pour les opérations de lecture
  - Son utilisation complémentaire avec MySQL

### Structure du projet
- Architecture MVC choisie pour :
  - Séparation claire des responsabilités
  - Facilité de maintenance
  - Evolutivité du code

### Sécurité
- Utilisation de fichier `.env` pour protéger les données sensibles
- Configuration de PDO pour les requêtes préparées
- Séparation des fichiers publics dans le dossier `/public`

## Ajout du Header 

### Structure  
- `public/header.php` : contient le logo et le menu de navigation  
- `public/images/logo.png` : fichier du logo  
- `public/style.css` : styles du header  

### Intégration  
Ajout du header dans les pages avec :  
  
## Ajout du Header

### Structure
- `public/header.php` : contient le logo et le menu de navigation
- `public/images/logo.png` : fichier du logo
- `public/style.css` : styles du header
   
### Intégration
Ajout du header dans les pages avec :
```php
require __DIR__ . '/../public/header.php';
```

### Styles principaux (public/style.css) 
Les styles du header sont définis dans `public/style.css` pour assurer une mise en page cohérente et responsive.  

### Styles principaux (public/style.css)
Les styles du header sont définis dans `public/style.css` pour assurer une mise en page cohérente et responsive.

```php
.logo img {
    height: 40px;
}

nav ul {
    list-style: none;
    display: flex;
    gap: 20px;
    padding: 0;
    margin: 0;
}
```

# Déploiement sur Heroku

## Application : `studiecoride`

### URL déployée : [studiecoride](https://studiecoride-cd8de33217d4.herokuapp.com/)

## Étapes de déploiement

1. **Connexion à Heroku**

2. **Ajout du remote Heroku** 
  
1. **Connexion à Heroku**

2. **Ajout du remote Heroku**

3. **Ajout et commit des modifications**

4. **Push sur la branche `main`**
   
5. **Déploiement sur Heroku**

6. **Accès à l'application**
    Ouvrir [https://studiecoride-cd8de33217d4.herokuapp.com/](https://studiecoride-cd8de33217d4.herokuapp.com/) dans un navigateur.

# Structure de la Base de Données EcoRide

## Description

Ce projet contient la structure de la base de données pour l'application de covoiturage écologique EcoRide. La base de données gère les utilisateurs, les voitures, les trajets et les systèmes de notation.

## Structure

### Tables Principales
* `utilisateur` - Gestion des utilisateurs
* `voiture` - Informations sur les véhicules
* `covoiturage` - Détails des trajets
* `avis` - Système de notation et commentaires

### Tables de Référence
* `role` - Gestion des rôles utilisateurs
* `marque` - Marques de voitures
* `parametre` - Paramètres système
* `configuration` - Configuration système

### Vues (Views)
* `disponible_covoiturages` - Affichage des trajets disponibles
* `conducteur_ratings` - Notation moyenne des conducteurs
* `eco_covoiturages` - Liste des trajets écologiques

### Déclencheurs (Triggers)
* `tr_credits_nouvel_utilisateur` - Attribution des crédits initiaux
* `tr_reservation_covoiturage` - Gestion des crédits lors des réservations
* `tr_confirmation_covoiturage` - Attribution des crédits après trajet

## Installation

1. Créer la base de données :
    ```sql
    CREATE DATABASE DB_EcoRide CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    ```

2. Importer le schéma :
    ```bash
    mysql -u root -p DB_EcoRide < database/schema.sql
    ```

## Développement

Pour contribuer au développement de la base de données :

1. Créer une nouvelle branche :
    ```bash
    git checkout -b feature/database-structure
    ```

2. Exporter les modifications :
    ```bash
    mysqldump -u root -p DB_EcoRide > database/schema.sql
    ```

3. Commiter les changements :
    ```bash
    git add database/schema.sql
    git commit -m "feat: ajout de la structure de la base de données avec tables, index, vues et déclencheurs"
    git push origin feature/database-structure
    ```


## Formulaire d'inscription

1. Création du formulaire dans `pages/inscription.php` avec champs pour pseudo, email et mot de passe2. Implémentation du traitement dans `traitement/inscription.php`
3. Ajout de la validation des données et hachage du mot de passe
4. Configuration de l'insertion en base de données avec 20 crédits initiaux
5. Mise en place de la redirection vers la page de sélection de rôle

## Implémentation de la page de sélection de rôle

La page de sélection de rôle permet aux utilisateurs de choisir s'ils souhaitent utiliser EcoRide en tant que passager ou chauffeur. Cette $

### Fichiers créés/modifiés
* `pages/role.php` - Interface utilisateur
* `traitement/role.php` - Traitement du choix
* Ajout des styles dans `public/style.css`
* Ajout du routage dans `public/index.php`

### Fonctionnement
L'interface présente un message explicatif et deux boutons de choix. La sélection est enregistrée en base de données et détermine les fonctionnalités accessibles à l'utilisateur.

## Formulaire de connexion

1. Création de l'interface dans `pages/connexion.php` avec champs pour email et mot de passe
2. Implémentation de la logique d'authentification dans `traitement/connexion.php`
3. Configuration de la vérification sécurisée des identifiants
4. Mise en place de la redirection vers la page de sélection de rôle après connexion
5. Harmonisation du style avec le formulaire d'inscription

## Implémentation du footer

Le footer a été intégré pour fournir des informations légales et maintenir une cohérence visuelle sur toutes les pages de la plateforme.

### Fichiers créés/modifiés
* `public/footer.php` - Template du footer avec liens et identification de marque
* Ajout des styles dans `public/style.css` pour harmoniser l'apparence avec le header
* Modification de `public/index.php` pour inclure automatiquement le footer sur toutes les pages

### Fonctionnalités
* Affichage des liens vers les informations légales et paramètres de cookies à gauche
* Présentation de l'emblème et du copyright EcoRide à droite
* Style cohérent avec le reste de l'interface (bordures, couleurs, espacement)
* Chargement automatique sur toutes les pages du site via le routeur central

## Espace Passager

L'espace passager représente l'interface principale permettant aux utilisateurs ayant choisi le rôle de passager d'accéder aux fonctionnalités essentielles de covoiturage.

### Fichiers créés/modifiés
* `pages/espace_passager.php` - Interface utilisateur dédiée aux passagers
* Enrichissement du `public/style.css` avec les styles spécifiques
* Ajout d'icônes dans `public/images/icons/` pour l'interface

### Fonctionnalités
* Authentification avec vérification du rôle "passager"
* En-tête personnalisé avec image de fond et bouton de changement de rôle
* Section centrale avec deux fonctionnalités principales :
  * Recherche de covoiturage (avec icône de recherche)
  * Consultation des trajets réservés (avec icône d'historique)
* Mise en page responsive adaptée aux différents formats d'écran

### Aspects techniques
* Sécurisation de l'accès via middleware d'authentification
* Récupération des données utilisateur depuis la base de données
* Design moderne avec une attention particulière aux couleurs et contrastes
* Organisation optimisée du contenu pour une expérience utilisateur intuitive

## Configuration de la base de données DB_EcoRide sur Heroku

### Problème
Heroku ne prend pas en charge MySQL par défaut, il a donc été nécessaire d'utiliser l'addon JawsDB.

### Installation de JawsDB MySQL
Ajout de la base de données via la ligne de commande :
```sh
heroku addons:create jawsdb:kitefin
```
Vérification du statut de la base de données :
```sh
heroku addons:info jawsdb-spherical-15054
```
Obtention de la chaîne de connexion :
```sh
heroku config | grep JAWSDB_URL
```

### Configuration de la connexion en PHP
Le fichier `config/database.php` a été mis à jour pour prendre en charge la variable d'environnement `JAWSDB_URL` :
```php
<?php
$dbUrl = getenv('JAWSDB_URL');
if ($dbUrl) {
    $dbParts = parse_url($dbUrl);
    $host = $dbParts['host'];
    $username = $dbParts['user'];
    $password = $dbParts['pass'];
    $dbname = ltrim($dbParts['path'], '/');
    $port = isset($dbParts['port']) ? $dbParts['port'] : 3306;
} else {
    $host = 'localhost';
    $dbname = 'DB_EcoRide';
    $username = 'root';
    $password = 'Molfarka8';
    $port = '3306';
}
try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
```

### Transfert des données vers JawsDB
Comme Heroku limite les privilèges des utilisateurs de la base de données, seules la structure et les données sans `DEFINER` et `GTID` ont été exportées :
```sh
mysqldump -u root -pMolfarka8 --no-data --skip-triggers DB_EcoRide > ecoride_structure.sql
mysqldump -u root -pMolfarka8 --no-create-info --skip-triggers --complete-insert DB_EcoRide > ecoride_data.sql
```
Modification du fichier `ecoride_structure.sql` pour supprimer les lignes `DEFINER` et `GTID`, puis importation dans JawsDB :
```sh
mysql -h <hostname> -u <username> -p<password> <database_name> < ecoride_structure.sql
mysql -h <hostname> -u <username> -p<password> <database_name> < ecoride_data.sql
```
Vérification de la présence des tables et des données :
```sh
mysql -h <hostname> -u <username> -p<password> <database_name> -e "SHOW TABLES;"
```

### Déploiement sur Heroku
```sh
git add config/database.php
git commit -m "Configuration DB pour Heroku"
git push heroku main
```

### Remarque importante
Les bases de données phpMySQL (locale) et JawsDB (Heroku) **ne sont pas synchronisées automatiquement**. La base de données locale contient des données de test, tandis que JawsDB contient les données de la version en production. Il est important de prendre cela en compte lors des tests et du déploiement des modifications.


## Implémentation du Menu Responsive

### Modification du Header
- Ajout d'un bouton "burger menu" (trois barres horizontales)
- Ajout de la classe `nav-menu` au menu de navigation existant
- Remplacement de `<a href="#">Connexion</a>` par `<span class="dropdown-toggle">Connexion</span>`

### Ajout de CSS Responsive
- Création des styles pour l'icône burger (3 spans avec transitions)
- Ajout de media queries pour les écrans <= 768px:
  - Affichage du burger menu
  - Transformation du menu horizontal en menu vertical
  - Adaptation des dropdowns pour le mobile

### Ajout de JavaScript
- Toggle des classes `.active` lors du clic sur le burger
- Gestion spécifique des dropdowns en version mobile
- Fermeture automatique du menu lors du clic sur un lien

Le résultat final permet une navigation fluide sur tous les appareils : menu horizontal sur desktop, menu burger sur mobile.

# Développement du Header Dynamique

## Fonctionnalités implémentées

### 1. Header adaptatif selon le statut de connexion
- **Utilisateur non connecté**: Affiche les options "Accueil", "Covoiturage", "Contact" et "Connexion"
- **Utilisateur connecté**: Remplace "Connexion" par un avatar utilisateur avec un menu déroulant

### 2. Menu déroulant personnalisé
- **Après l'authentification**: Menu pour choisir un rôle (passager ou chauffeur)
- **Rôle Passager**: Menu adapté avec "Mon profil", "Covoiturage", "Mes trajets" et "Déconnecter"
- **Rôle Chauffeur**: Menu adapté avec options spécifiques aux chauffeurs

### 3. Affichage de l'avatar utilisateur
- Affichage d'une image si l'utilisateur a téléchargé un avatar
- Affichage d'un placeholder avec la première lettre du pseudo si aucun avatar n'est disponible

## Architecture technique

### Structure des fichiers
/public/
├── header.php       # Template du header avec logique conditionnelle
├── index.php        # Routeur principal avec traitement des requêtes
└── style.css        # Styles pour le header et les menus déroulants
/traitement/
└── process-role.php # Traitement du choix de rôle utilisateur
/pages/
└── role.php         # Page de sélection du rôle

### Amélioration du routeur
- Séparation de la logique de traitement et d'affichage
- Traitement des redirections avant l'inclusion du header
- Gestion des routes API qui ne nécessitent pas header/footer

### Gestion des sessions
- Stockage du rôle utilisateur dans la variable de session `$_SESSION['role']`
- Vérification du rôle pour afficher le menu approprié
- Mise à jour de la base de données lors du changement de rôle

## Défis techniques surmontés
- Résolution du problème "headers already sent" en réorganisant l'ordre d'exécution des scripts
- Implémentation de la navigation responsive pour les appareils mobiles
- Personnalisation du menu déroulant selon le contexte utilisateur
