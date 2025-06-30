-- --------------------------------------------------------
-- Fichier d'insertion de données pour la base EcoRide
-- Projet de covoiturage
-- --------------------------------------------------------

-- Insertion des rôles utilisateur
INSERT INTO `role` (`libelle`) VALUES 
('utilisateur'),
('passager'),
('chauffeur'),
('employe'),
('administrateur');

-- Insertion des marques de voitures
INSERT INTO `marque` (`libelle`) VALUES 
('Renault'),
('Peugeot'),
('Citroën'),
('Volkswagen'),
('Toyota'),
('BMW'),
('Mercedes'),
('Audi'),
('Tesla'),
('Hyundai'),
('Kia'),
('Ford'),
('Opel'),
('Fiat'),
('Nissan');

