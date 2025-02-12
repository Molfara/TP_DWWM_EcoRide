-- MySQL dump 10.13  Distrib 9.1.0, for macos14 (arm64)
--
-- Host: localhost    Database: DB_EcoRide
-- ------------------------------------------------------
-- Server version	9.1.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `avis`
--

DROP TABLE IF EXISTS `avis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avis` (
  `avis_id` int NOT NULL AUTO_INCREMENT,
  `commentaire` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` int DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'en_attente',
  `utilisateur_id` int DEFAULT NULL,
  `covoiturage_id` int DEFAULT NULL,
  PRIMARY KEY (`avis_id`),
  KEY `covoiturage_id` (`covoiturage_id`),
  KEY `idx_avis_user` (`utilisateur_id`,`statut`),
  CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`),
  CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`covoiturage_id`) REFERENCES `covoiturage` (`covoiturage_id`),
  CONSTRAINT `avis_chk_1` CHECK ((`note` between 1 and 5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avis`
--

LOCK TABLES `avis` WRITE;
/*!40000 ALTER TABLE `avis` DISABLE KEYS */;
/*!40000 ALTER TABLE `avis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `conducteur_ratings`
--

DROP TABLE IF EXISTS `conducteur_ratings`;
/*!50001 DROP VIEW IF EXISTS `conducteur_ratings`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `conducteur_ratings` AS SELECT 
 1 AS `utilisateur_id`,
 1 AS `pseudo`,
 1 AS `moyenne_notes`,
 1 AS `nombre_avis`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuration` (
  `id_configuration` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id_configuration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuration`
--

LOCK TABLES `configuration` WRITE;
/*!40000 ALTER TABLE `configuration` DISABLE KEYS */;
/*!40000 ALTER TABLE `configuration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `covoiturage`
--

DROP TABLE IF EXISTS `covoiturage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `covoiturage` (
  `covoiturage_id` int NOT NULL AUTO_INCREMENT,
  `lieu_depart` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lieu_arrivee` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_depart` date NOT NULL,
  `heure_depart` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_arrivee` date NOT NULL,
  `heure_arrivee` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prix_personne` float NOT NULL,
  `nb_place` int NOT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'en_attente',
  `voiture_id` int DEFAULT NULL,
  `conducteur_id` int DEFAULT NULL,
  PRIMARY KEY (`covoiturage_id`),
  KEY `voiture_id` (`voiture_id`),
  KEY `conducteur_id` (`conducteur_id`),
  KEY `idx_covoiturage_search` (`lieu_depart`,`lieu_arrivee`,`date_depart`),
  CONSTRAINT `covoiturage_ibfk_1` FOREIGN KEY (`voiture_id`) REFERENCES `voiture` (`voiture_id`),
  CONSTRAINT `covoiturage_ibfk_2` FOREIGN KEY (`conducteur_id`) REFERENCES `utilisateur` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `covoiturage`
--

LOCK TABLES `covoiturage` WRITE;
/*!40000 ALTER TABLE `covoiturage` DISABLE KEYS */;
/*!40000 ALTER TABLE `covoiturage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `disponible_covoiturages`
--

DROP TABLE IF EXISTS `disponible_covoiturages`;
/*!50001 DROP VIEW IF EXISTS `disponible_covoiturages`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `disponible_covoiturages` AS SELECT 
 1 AS `covoiturage_id`,
 1 AS `lieu_depart`,
 1 AS `lieu_arrivee`,
 1 AS `date_depart`,
 1 AS `heure_depart`,
 1 AS `date_arrivee`,
 1 AS `heure_arrivee`,
 1 AS `prix_personne`,
 1 AS `nb_place`,
 1 AS `statut`,
 1 AS `voiture_id`,
 1 AS `conducteur_id`,
 1 AS `conducteur_pseudo`,
 1 AS `conducteur_photo`,
 1 AS `energie`,
 1 AS `places_restantes`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `eco_covoiturages`
--

DROP TABLE IF EXISTS `eco_covoiturages`;
/*!50001 DROP VIEW IF EXISTS `eco_covoiturages`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `eco_covoiturages` AS SELECT 
 1 AS `covoiturage_id`,
 1 AS `lieu_depart`,
 1 AS `lieu_arrivee`,
 1 AS `date_depart`,
 1 AS `heure_depart`,
 1 AS `date_arrivee`,
 1 AS `heure_arrivee`,
 1 AS `prix_personne`,
 1 AS `nb_place`,
 1 AS `statut`,
 1 AS `voiture_id`,
 1 AS `conducteur_id`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `marque`
--

DROP TABLE IF EXISTS `marque`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marque` (
  `marque_id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`marque_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marque`
--

LOCK TABLES `marque` WRITE;
/*!40000 ALTER TABLE `marque` DISABLE KEYS */;
INSERT INTO `marque` VALUES (1,'Renault'),(2,'Peugeot'),(3,'Citroën'),(4,'Volkswagen'),(5,'Toyota'),(6,'BMW'),(7,'Mercedes'),(8,'Audi'),(9,'Tesla'),(10,'Nissan'),(11,'Hyundai'),(12,'Kia'),(13,'Ford'),(14,'Fiat'),(15,'Opel');
/*!40000 ALTER TABLE `marque` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parametre`
--

DROP TABLE IF EXISTS `parametre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parametre` (
  `parametre_id` int NOT NULL AUTO_INCREMENT,
  `propriete` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valeur` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`parametre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parametre`
--

LOCK TABLES `parametre` WRITE;
/*!40000 ALTER TABLE `parametre` DISABLE KEYS */;
/*!40000 ALTER TABLE `parametre` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participation`
--

DROP TABLE IF EXISTS `participation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `participation` (
  `participation_id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int DEFAULT NULL,
  `covoiturage_id` int DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'confirme',
  PRIMARY KEY (`participation_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `covoiturage_id` (`covoiturage_id`),
  CONSTRAINT `participation_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`),
  CONSTRAINT `participation_ibfk_2` FOREIGN KEY (`covoiturage_id`) REFERENCES `covoiturage` (`covoiturage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participation`
--

LOCK TABLES `participation` WRITE;
/*!40000 ALTER TABLE `participation` DISABLE KEYS */;
/*!40000 ALTER TABLE `participation` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `tr_reservation_covoiturage` AFTER INSERT ON `participation` FOR EACH ROW BEGIN
   UPDATE utilisateur 
   SET credits = credits - (
       SELECT prix_personne + 2 
       FROM covoiturage 
       WHERE covoiturage_id = NEW.covoiturage_id
   )
   WHERE utilisateur_id = NEW.utilisateur_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `tr_confirmation_covoiturage` AFTER UPDATE ON `participation` FOR EACH ROW BEGIN
   IF NEW.statut = 'termine' AND OLD.statut != 'termine' THEN
       UPDATE utilisateur u
       SET u.credits = u.credits + (
           SELECT prix_personne 
           FROM covoiturage 
           WHERE covoiturage_id = NEW.covoiturage_id
       )
       WHERE u.utilisateur_id = (
           SELECT conducteur_id 
           FROM covoiturage 
           WHERE covoiturage_id = NEW.covoiturage_id
       );
   END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'visiteur'),(2,'utilisateur'),(3,'conducteur'),(4,'employe'),(5,'administrateur');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilisateur` (
  `utilisateur_id` int NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_naissance` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` blob,
  `credits` int DEFAULT '20',
  `role_id` int DEFAULT NULL,
  PRIMARY KEY (`utilisateur_id`),
  UNIQUE KEY `pseudo` (`pseudo`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  KEY `idx_utilisateur_email` (`email`),
  CONSTRAINT `utilisateur_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateur`
--

LOCK TABLES `utilisateur` WRITE;
/*!40000 ALTER TABLE `utilisateur` DISABLE KEYS */;
/*!40000 ALTER TABLE `utilisateur` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `tr_credits_nouvel_utilisateur` AFTER INSERT ON `utilisateur` FOR EACH ROW BEGIN
   UPDATE utilisateur 
   SET credits = 20 
   WHERE utilisateur_id = NEW.utilisateur_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `voiture`
--

DROP TABLE IF EXISTS `voiture`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voiture` (
  `voiture_id` int NOT NULL AUTO_INCREMENT,
  `modele` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `immatriculation` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `energie` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `couleur` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_premiere_immatriculation` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_places` int NOT NULL,
  `marque_id` int DEFAULT NULL,
  `utilisateur_id` int DEFAULT NULL,
  PRIMARY KEY (`voiture_id`),
  UNIQUE KEY `immatriculation` (`immatriculation`),
  KEY `marque_id` (`marque_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `idx_voiture_immatriculation` (`immatriculation`),
  KEY `idx_voiture_energie` (`energie`),
  CONSTRAINT `voiture_ibfk_1` FOREIGN KEY (`marque_id`) REFERENCES `marque` (`marque_id`),
  CONSTRAINT `voiture_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voiture`
--

LOCK TABLES `voiture` WRITE;
/*!40000 ALTER TABLE `voiture` DISABLE KEYS */;
/*!40000 ALTER TABLE `voiture` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `conducteur_ratings`
--

/*!50001 DROP VIEW IF EXISTS `conducteur_ratings`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `conducteur_ratings` AS select `u`.`utilisateur_id` AS `utilisateur_id`,`u`.`pseudo` AS `pseudo`,avg(`a`.`note`) AS `moyenne_notes`,count(`a`.`avis_id`) AS `nombre_avis` from ((`utilisateur` `u` left join `covoiturage` `c` on((`u`.`utilisateur_id` = `c`.`conducteur_id`))) left join `avis` `a` on((`c`.`covoiturage_id` = `a`.`covoiturage_id`))) where (`u`.`role_id` = (select `role`.`role_id` from `role` where (`role`.`libelle` = 'conducteur'))) group by `u`.`utilisateur_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `disponible_covoiturages`
--

/*!50001 DROP VIEW IF EXISTS `disponible_covoiturages`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `disponible_covoiturages` AS select `c`.`covoiturage_id` AS `covoiturage_id`,`c`.`lieu_depart` AS `lieu_depart`,`c`.`lieu_arrivee` AS `lieu_arrivee`,`c`.`date_depart` AS `date_depart`,`c`.`heure_depart` AS `heure_depart`,`c`.`date_arrivee` AS `date_arrivee`,`c`.`heure_arrivee` AS `heure_arrivee`,`c`.`prix_personne` AS `prix_personne`,`c`.`nb_place` AS `nb_place`,`c`.`statut` AS `statut`,`c`.`voiture_id` AS `voiture_id`,`c`.`conducteur_id` AS `conducteur_id`,`u`.`pseudo` AS `conducteur_pseudo`,`u`.`photo` AS `conducteur_photo`,`v`.`energie` AS `energie`,(`c`.`nb_place` - count(`p`.`utilisateur_id`)) AS `places_restantes` from (((`covoiturage` `c` join `utilisateur` `u` on((`c`.`conducteur_id` = `u`.`utilisateur_id`))) join `voiture` `v` on((`c`.`voiture_id` = `v`.`voiture_id`))) left join `participation` `p` on((`c`.`covoiturage_id` = `p`.`covoiturage_id`))) where (`c`.`date_depart` >= curdate()) group by `c`.`covoiturage_id` having (`places_restantes` > 0) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `eco_covoiturages`
--

/*!50001 DROP VIEW IF EXISTS `eco_covoiturages`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `eco_covoiturages` AS select `c`.`covoiturage_id` AS `covoiturage_id`,`c`.`lieu_depart` AS `lieu_depart`,`c`.`lieu_arrivee` AS `lieu_arrivee`,`c`.`date_depart` AS `date_depart`,`c`.`heure_depart` AS `heure_depart`,`c`.`date_arrivee` AS `date_arrivee`,`c`.`heure_arrivee` AS `heure_arrivee`,`c`.`prix_personne` AS `prix_personne`,`c`.`nb_place` AS `nb_place`,`c`.`statut` AS `statut`,`c`.`voiture_id` AS `voiture_id`,`c`.`conducteur_id` AS `conducteur_id` from (`covoiturage` `c` join `voiture` `v` on((`c`.`voiture_id` = `v`.`voiture_id`))) where (`v`.`energie` = 'électrique') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-02-12 17:36:16
