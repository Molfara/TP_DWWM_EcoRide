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

-- Insertion des utilisateurs
INSERT INTO `utilisateur` (`pseudo`, `nom`, `prenom`, `email`, `password`, `telephone`, `adresse`, `date_naissance`, `credits`, `role_id`) VALUES 
('admin', 'Administrateur', 'System', 'admin@ecoride.fr', '$2y$10$XDCXmEzT3RGX7RYENZQuleZn.m3r/Xp1gUw32kVLFfkBQrJLl/jPu', '0123456789', '1 rue de l\'Administration', '1980-01-01', 100, 5),
('jean.dupont', 'Dupont', 'Jean', 'jean.dupont@email.com', '$2y$10$vq7MlHqJCU4JFsKP2DlCMeuRLmFTJOUB1Ez0KvS5GvE6K2MxdFP1q', '0612345678', '15 rue des Lilas', '1985-05-12', 25, 3),
('marie.durand', 'Durand', 'Marie', 'marie.durand@email.com', '$2y$10$9Rr1CGG8MvJmLzO4t.oF2e8KQCQAJzQi0Oyn5KwvvKQvZ1H4Jvnm2', '0645781236', '8 avenue Victor Hugo', '1990-11-23', 30, 2),
('pierre.martin', 'Martin', 'Pierre', 'pierre.martin@email.com', '$2y$10$xLyUh5LwVZq9MWmJ8Oo4bOQeKRXHJWylERNYHRQsxvAXDfK.MghCK', '0798765432', '42 boulevard Général Leclerc', '1978-07-30', 15, 1),
('sophie.bernard', 'Bernard', 'Sophie', 'sophie.bernard@email.com', '$2y$10$3rzHuRdQtWLq5.sA1Qw5EOw4/OV0qWKYhPBcJC6vD.dZX9Qf1b9QO', '0632145698', '27 rue des Fleurs', '1992-03-17', 40, 3);

-- Insertion des voitures
INSERT INTO `voiture` (`modele`, `immatriculation`, `energie`, `couleur`, `date_premiere_immatriculation`, `nb_places`, `marque_id`, `utilisateur_id`) VALUES 
('Zoé', 'AB-123-CD', 'électrique', 'Bleu', '2020-06-15', 4, 1, 2),
('208', 'EF-456-GH', 'essence', 'Rouge', '2019-03-22', 5, 2, 3),
('Model 3', 'IJ-789-KL', 'électrique', 'Noir', '2021-11-05', 5, 9, 5);

-- Insertion des covoiturages
INSERT INTO `covoiturage` (`lieu_depart`, `lieu_arrivee`, `date_depart`, `heure_depart`, `date_arrivee`, `heure_arrivee`, `prix_personne`, `nb_place`, `statut`, `voiture_id`, `conducteur_id`) VALUES 
('Paris', 'Lyon', '2025-04-15', '08:00', '2025-04-15', '11:30', 25.50, 3, 'confirme', 1, 2),
('Marseille', 'Nice', '2025-04-20', '14:00', '2025-04-20', '15:30', 12.00, 4, 'confirme', 3, 5);

-- Insertion des participations
INSERT INTO `participation` (`utilisateur_id`, `covoiturage_id`, `statut`) VALUES 
(3, 1, 'confirme'),
(4, 1, 'confirme');

-- Insertion des avis
INSERT INTO `avis` (`commentaire`, `note`, `statut`, `utilisateur_id`, `covoiturage_id`) VALUES 
('Très bon voyage, conducteur ponctuel et agréable', 5, 'validé', 3, 1),
('Trajet confortable, voiture propre', 4, 'validé', 4, 1);

-- Insertion des paramètres système
INSERT INTO `parametre` (`propriete`, `valeur`) VALUES 
('prix_credit', '0.50'),
('limite_covoiturage_jour', '3'),
('delai_annulation', '24');
