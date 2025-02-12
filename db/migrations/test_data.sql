-- Test data for EcoRide carpooling project
-- Date of creation: 2024-02-12
-- Purpose: Demonstration of initial database structure and test data

-- Insert test users
INSERT INTO utilisateur (pseudo, nom, prenom, email, password, telephone, adresse, credits) VALUES 
('admin1', 'Admin', 'Principal', 'admin@ecoride.fr', 'hash_password', '0123456789', 'Paris', 20),
('employe1', 'Employe', 'Service', 'employe@ecoride.fr', 'hash_password', '0123456789', 'Lyon', 20),
('conducteur1', 'Martin', 'Jean', 'jean.martin@mail.com', 'hash_password', '0623456789', 'Paris', 20),
('conducteur2', 'Dubois', 'Marie', 'marie.dubois@mail.com', 'hash_password', '0634567890', 'Lyon', 20),
('utilisateur1', 'Petit', 'Pierre', 'pierre.petit@mail.com', 'hash_password', '0645678901', 'Marseille', 20);

-- Insert test brands
INSERT INTO marque (libelle) VALUES 
('Tesla'),
('Renault'),
('Peugeot');

-- Insert test vehicles
INSERT INTO voiture (modele, immatriculation, energie, couleur, date_premiere_immatriculation, nb_places, marque_id, utilisateur_id) VALUES
('Model 3', 'AA123BB', 'électrique', 'blanc', '2023-01-01', 4, 
 (SELECT marque_id FROM marque WHERE libelle = 'Tesla'),
 (SELECT utilisateur_id FROM utilisateur WHERE pseudo = 'conducteur1')),
('Zoe', 'CC456DD', 'électrique', 'bleu', '2022-06-01', 4,
 (SELECT marque_id FROM marque WHERE libelle = 'Renault'),
 (SELECT utilisateur_id FROM utilisateur WHERE pseudo = 'conducteur2')),
('308', 'EE789FF', 'essence', 'noir', '2021-12-01', 5,
 (SELECT marque_id FROM marque WHERE libelle = 'Peugeot'),
 (SELECT utilisateur_id FROM utilisateur WHERE pseudo = 'conducteur1'));

-- Insert test carpooling trips
INSERT INTO covoiturage (lieu_depart, lieu_arrivee, date_depart, heure_depart, date_arrivee, heure_arrivee, prix_personne, nb_place, statut, voiture_id, conducteur_id) VALUES
('Paris', 'Lyon', '2025-02-20', '08:00', '2025-02-20', '12:00', 25.00, 3, 'en_attente',
 (SELECT voiture_id FROM voiture WHERE immatriculation = 'AA123BB'),
 (SELECT utilisateur_id FROM utilisateur WHERE pseudo = 'conducteur1')),
('Lyon', 'Marseille', '2025-02-21', '14:00', '2025-02-21', '17:00', 20.00, 4, 'en_attente',
 (SELECT voiture_id FROM voiture WHERE immatriculation = 'CC456DD'),
 (SELECT utilisateur_id FROM utilisateur WHERE pseudo = 'conducteur2'));

-- Insert test reviews
INSERT INTO avis (commentaire, note, statut, utilisateur_id, covoiturage_id) VALUES
('Très bon voyage, conducteur ponctuel', 5, 'validé',
 (SELECT utilisateur_id FROM utilisateur WHERE pseudo = 'utilisateur1'),
 (SELECT covoiturage_id FROM covoiturage WHERE conducteur_id = (SELECT utilisateur_id FROM utilisateur WHERE pseudo = 'conducteur1') LIMIT 1));
