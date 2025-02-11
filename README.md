# TP_DWWM_EcoRide
Projet pédagogique de développement de la plateforme de covoiturage EcoRide.

## Début de projet

### 1. Configuration de PHP et MySQL :
- PHP 8.4.2 ;
- MySQL  Ver 9.1.0 for macos14 on arm64.

### 2. Création d'un repo TP_DWWM_EcoRide sur GitHub.

### 3. Ajout d'une branche "develop".

### 4. Clonage d'un repo dans un dossier local.

### 5. Installation de VS Code et Copilot.

### 6. Création de la structure du projet :

- Les dossiers : `public`, `src`, `config`
- Les fichiers : `public/index.php`, `config/database.php`, `.env`.

### 7. Création de la BD TP_DWWM_EcoRide à MySQL via phpMyAdmin.

### 8. Création de la table "users" dans la base de données TP_DWWM_EcoRide.

### 9. Démarrage du premier script PHP " Bienvenue dans EcoRide ! " :

- Ajout du code avec " Bienvenue dans EcoRide! " dans public/index.php via VS Code ;
- Lancement du serveur localhost:8080 ;
- Vérification de la page http://localhost:8080 dans le navigateur.

### 10. Routage

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

3. **Ajout et commit des modifications**

4. **Push sur la branche `main`**
   
5. **Déploiement sur Heroku**

6. **Accès à l'application**
    Ouvrir [https://studiecoride-cd8de33217d4.herokuapp.com/](https://studiecoride-cd8de33217d4.herokuapp.com/) dans un navigateur.







