-- --------------------------------------------------------
-- Fichier de structure de base de données EcoRide
-- Projet de covoiturage
-- --------------------------------------------------------

-- Création et sélection de la base de données
CREATE DATABASE `DB_EcoRide` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `DB_EcoRide`;

-- Table des rôles utilisateur
CREATE TABLE role (
    role_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
);

-- Table des marques de voitures
CREATE TABLE marque (
    marque_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
);

-- Table des utilisateurs
CREATE TABLE utilisateur (
    utilisateur_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(50),
    adresse VARCHAR(50),
    date_naissance VARCHAR(50),
    photo BLOB,
    credits INT DEFAULT 20,
    role_id INT,
    FOREIGN KEY (role_id) REFERENCES role(role_id)
);

-- Table des voitures
CREATE TABLE voiture (
    voiture_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    modele VARCHAR(50) NOT NULL,
    immatriculation VARCHAR(50) NOT NULL UNIQUE,
    energie VARCHAR(50) NOT NULL,
    couleur VARCHAR(50),
    date_premiere_immatriculation VARCHAR(50),
    nb_places INT NOT NULL,
    marque_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    FOREIGN KEY (marque_id) REFERENCES marque(marque_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
    INDEX idx_voiture_energie (energie)
);

-- Table des covoiturages
CREATE TABLE covoiturage (
    covoiturage_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    lieu_depart VARCHAR(50) NOT NULL,
    lieu_arrivee VARCHAR(50) NOT NULL,
    date_depart DATE NOT NULL,
    heure_depart VARCHAR(50) NOT NULL,
    date_arrivee DATE NOT NULL,
    heure_arrivee VARCHAR(50) NOT NULL,
    prix_personne FLOAT NOT NULL,
    nb_place INT NOT NULL,
    statut VARCHAR(50) DEFAULT 'en_attente',
    voiture_id INT,
    conducteur_id INT,
    FOREIGN KEY (voiture_id) REFERENCES voiture(voiture_id),
    FOREIGN KEY (conducteur_id) REFERENCES utilisateur(utilisateur_id),
    INDEX idx_covoiturage_search (lieu_depart, lieu_arrivee, date_depart)
);

-- Table des participations aux covoiturages
CREATE TABLE participation (
    participation_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    covoiturage_id INT,
    statut VARCHAR(50) DEFAULT 'confirme',
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
    FOREIGN KEY (covoiturage_id) REFERENCES covoiturage(covoiturage_id)
);

-- Table des avis sur les covoiturages
CREATE TABLE avis (
    avis_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    commentaire VARCHAR(255),
    note INT,
    statut VARCHAR(50) DEFAULT 'en_attente',
    utilisateur_id INT,
    covoiturage_id INT,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
    FOREIGN KEY (covoiturage_id) REFERENCES covoiturage(covoiturage_id),
    INDEX idx_avis_user (utilisateur_id, statut),
    CHECK (note BETWEEN 1 AND 5)
);

-- Table de configuration du système
CREATE TABLE configuration (
    id_configuration INT NOT NULL AUTO_INCREMENT PRIMARY KEY
);

-- Table des paramètres système
CREATE TABLE parametre (
    parametre_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    propriete VARCHAR(50) NOT NULL,
    valeur VARCHAR(50)
);

-- Vue des évaluations des conducteurs
CREATE VIEW conducteur_ratings AS 
SELECT 
    u.utilisateur_id, 
    u.pseudo, 
    AVG(a.note) AS moyenne_notes, 
    COUNT(a.avis_id) AS nombre_avis
FROM 
    utilisateur u
LEFT JOIN 
    covoiturage c ON u.utilisateur_id = c.conducteur_id
LEFT JOIN 
    avis a ON c.covoiturage_id = a.covoiturage_id
WHERE 
    a.note IS NOT NULL
GROUP BY 
    u.utilisateur_id, u.pseudo;

-- Vue des covoiturages disponibles avec places restantes
CREATE VIEW disponible_covoiturages AS 
SELECT 
    c.covoiturage_id, 
    c.lieu_depart, 
    c.lieu_arrivee, 
    c.date_depart, 
    c.heure_depart, 
    c.date_arrivee, 
    c.heure_arrivee, 
    c.prix_personne, 
    c.nb_place, 
    c.statut, 
    c.voiture_id, 
    c.conducteur_id,
    u.pseudo AS conducteur_pseudo, 
    u.photo AS conducteur_photo, 
    v.energie,
    (c.nb_place - COALESCE(COUNT(p.participation_id), 0)) AS places_restantes
FROM 
    covoiturage c
JOIN 
    utilisateur u ON c.conducteur_id = u.utilisateur_id
JOIN 
    voiture v ON c.voiture_id = v.voiture_id
LEFT JOIN 
    participation p ON c.covoiturage_id = p.covoiturage_id AND p.statut = 'confirme'
WHERE 
    c.statut = 'confirme' 
    AND c.date_depart >= CURDATE()
GROUP BY 
    c.covoiturage_id, c.lieu_depart, c.lieu_arrivee, c.date_depart, c.heure_depart, 
    c.date_arrivee, c.heure_arrivee, c.prix_personne, c.nb_place, c.statut, 
    c.voiture_id, c.conducteur_id, u.pseudo, u.photo, v.energie
HAVING 
    places_restantes > 0;

-- Vue des covoiturages écologiques (énergie propre)
CREATE VIEW eco_covoiturages AS 
SELECT 
    c.*
FROM 
    covoiturage c
JOIN 
    voiture v ON c.voiture_id = v.voiture_id
WHERE 
    v.energie IN ('électrique', 'hybride', 'hydrogène')
    AND c.statut = 'confirme';
