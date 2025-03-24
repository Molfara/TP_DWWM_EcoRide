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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avis`
--

LOCK TABLES `avis` WRITE;
/*!40000 ALTER TABLE `avis` DISABLE KEYS */;
INSERT INTO `avis` VALUES (1,'Très bon voyage, conducteur ponctuel',5,'validé',10,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `covoiturage`
--

LOCK TABLES `covoiturage` WRITE;
/*!40000 ALTER TABLE `covoiturage` DISABLE KEYS */;
INSERT INTO `covoiturage` VALUES (1,'Paris','Lyon','2025-02-20','08:00','2025-02-20','12:00',25,3,'en_attente',1,8),(2,'Lyon','Marseille','2025-02-21','14:00','2025-02-21','17:00',20,4,'en_attente',2,9);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'utilisateur'),(2,'passager'),(3,'chauffeur'),(4,'employe'),(5,'administrateur');
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
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateur`
--

LOCK TABLES `utilisateur` WRITE;
/*!40000 ALTER TABLE `utilisateur` DISABLE KEYS */;
INSERT INTO `utilisateur` VALUES (6,'admin1','Admin','Principal','admin@ecoride.fr','hash_password','0123456789','Paris',NULL,NULL,20,NULL),(7,'employe1','Employe','Service','employe@ecoride.fr','hash_password','0123456789','Lyon',NULL,NULL,20,NULL),(8,'conducteur1','Martin','Jean','jean.martin@mail.com','hash_password','0623456789','Paris',NULL,NULL,20,NULL),(9,'conducteur2','Dubois','Marie','marie.dubois@mail.com','hash_password','0634567890','Lyon',NULL,NULL,20,NULL),(10,'utilisateur1','Petit','Pierre','pierre.petit@mail.com','hash_password','0645678901','Marseille',NULL,NULL,20,NULL),(16,'Momoshka',NULL,NULL,'momoshka@gmail.com','$2y$12$3T3BHwqg3/Oe2Qy.ErKRtOJlr8q5GhbMju3SH/WGKjRkYjt2dFXGu',NULL,NULL,NULL,NULL,20,1),(17,'Momo',NULL,NULL,'Momo@gmail.com','$2y$12$rYM7AwxcQZVqo0JAakLrQOlJHAkj5u/I.Op1cb4HDs4Xa642tBOC.',NULL,NULL,NULL,NULL,20,1),(18,'Momomo',NULL,NULL,'momomo@gmail.com','$2y$12$HBjSXaZRuHA5zuB8O.WASeUTRXtOGRKtN.Ev9I4FFEWYL2WhlUSsu',NULL,NULL,NULL,NULL,20,1),(19,'Popo',NULL,NULL,'popo@gmail.com','$2y$12$sMxaw1zJuFGGKkIRt9rqd.eKZciDMDFqmIkujvC6O0yy4nBW/axO6',NULL,NULL,NULL,NULL,20,1),(20,'Gogo',NULL,NULL,'gogo@gmail.com','$2y$12$DHoU5tv5SSIXi.1ONyDpL.Ahwvox9U.UHXbhpuRj61fu3.tysz3Bq',NULL,NULL,NULL,NULL,20,1),(21,'Koko',NULL,NULL,'koko@gmail.com','$2y$12$7GwsrGQpfQkrqDygTCbgJeXgdKDy36UF1yKTKxDr2U4w17ewOMDq2',NULL,NULL,NULL,NULL,20,1),(22,'Hoho',NULL,NULL,'hoho@gmail.com','$2y$12$8Y0iPo1Lkro2Inj590hfKed4OAPWUfSqXt5pkSEvUer1S/xsoeoGq',NULL,NULL,NULL,NULL,20,1),(23,'Murmur',NULL,NULL,'murmur@gmail.com','$2y$12$YJZrhQIQ35Ufk3JN90u2S.TQBiWJkJpeNyZeQISOR9ObSJ6r8JKnq',NULL,NULL,NULL,NULL,20,1),(24,'Roro',NULL,NULL,'roro@gmail.com','$2y$12$Efd5zHhZY9KD8cufsz5iVOivc33zOdQ1ZNjFs2e3CxnAtpUj0wQk2',NULL,NULL,NULL,NULL,20,1),(25,'Fofo',NULL,NULL,'fofo@gmail.com','$2y$12$/xnUQAYt6HJrEr8B/67/E.oc6fk55ccVa28VVf9aJi.tGrfKkt2tS',NULL,NULL,NULL,NULL,20,1),(26,'Dodo',NULL,NULL,'dodd@gmail.com','$2y$12$eZQ8cKCShrbXg40/G3Tczu6BZm1OoMLjUjYpAtc83l5Au.rCCcpNS',NULL,NULL,NULL,NULL,20,1),(27,'Yoyo',NULL,NULL,'yoyo@gmail.com','$2y$12$FJo.RNjMifT5.Wsq7DqZuuna4gzhhSwwxqLrFOTFg.Ya0Dxv5Jtjq',NULL,NULL,NULL,NULL,20,1),(28,'Zozo',NULL,NULL,'zozo@gmail.com','$2y$12$IViZ4fTcc1YehPjjflzfYu3g26ttoUVAM35qNwNDq6zKhoZKnlhzK',NULL,NULL,NULL,NULL,20,2),(29,'Sasa',NULL,NULL,'sasa@gmail.com','$2y$12$humgKk5dyaIfuKm4zwzh1Oi77w5OqpU0uhqEE62eluTvf0a/AJ0hW',NULL,NULL,NULL,NULL,20,3),(30,'QOQO',NULL,NULL,'qoqo@gmail.com','$2y$12$vqHwP9QhWq0VeoUGlpRU6e9Eayk9yHfRhBJavh.NHJFi61eDyRAP2',NULL,NULL,NULL,NULL,20,2),(32,'Nana',NULL,NULL,'nana@gmail.com','$2y$12$6bx.V9wOL4.qQDGCEfYr9Owz6CpAP8Tsnr6IUHZEKTcwtOLrfMwTO',NULL,NULL,NULL,NULL,20,2),(33,'Gigi',NULL,NULL,'gigi@gmail.com','$2y$12$EckA0CJhSCHuHyED51eIB.q7uVju21Z8U8xKHcluFYuve2EnL1LhC',NULL,NULL,NULL,NULL,20,2),(34,'Hihi',NULL,NULL,'hihi@gmail.com','$2y$12$3pSDt8sBkp49YSkjaiBdoOlAhkbAy/kKk.jK2QfdC5kOLAc0Ywj32',NULL,NULL,NULL,NULL,20,2),(35,'Lolo',NULL,NULL,'lolo@gmail.com','$2y$12$m2Lpkp.I1/UDTSXVCInCPu4PpDvB6wKEiLKcXKDgR/2O4s6R0vanS',NULL,NULL,NULL,NULL,20,2),(36,'Fyfy',NULL,NULL,'fyfy@gmail.com','$2y$12$q11VOvpBbVOVQhnoYUfskegcQ0ywbJGjwo0qeuLMFCGIp8kEb84yC',NULL,NULL,NULL,NULL,20,2),(37,'Dede',NULL,NULL,'dede@gmail.com','$2y$12$3belpOSES6Fxuj2SaJYhgej3LBgfsf8c84I6NJGcsRLqvK01tdpsi',NULL,NULL,NULL,NULL,20,2),(38,'Soso',NULL,NULL,'soso@gmail.com','$2y$12$rnyWLE6zx4VSz.qOxE18gudsdV7rTptx8ieF0RROp7mgWJLXhdAUm',NULL,NULL,NULL,NULL,20,2),(39,'Gygy',NULL,NULL,'gygy@gmail.com','$2y$12$KWyZLS8ezjy0.O.bMYQcCemjw8cPZ.VABmjqnSmz71ykhdYgXL8SW',NULL,NULL,NULL,NULL,20,2),(40,'Veve',NULL,NULL,'veve@gmail.com','$2y$12$HHlYQEzSU33xMcqK4B3hwOB.c/Q4WjpoDbQGXLaBx9MLDM0aeDHcy',NULL,NULL,NULL,NULL,20,1),(41,'Coco',NULL,NULL,'coco@gmail.com','$2y$12$YkJRsw4XMdDMAVF119HlBONw6VZZ.YMvQzxAYN6j6WJP2E1JasBzq',NULL,NULL,NULL,NULL,20,1),(42,'Xoxo',NULL,NULL,'xoxo@gmail.com','$2y$12$bb/7fsdMzY6dc/QEUI6Rr.44deomg55tXAS8uWfTDw1WUsxzRQcNa',NULL,NULL,NULL,NULL,20,2),(43,'Fafa',NULL,NULL,'fafa@gmail.com','$2y$12$9g/QCfE840ATTsMKLoFKnONMXaOf.uVBioObtwKMTNex6vRTwHIqy',NULL,NULL,NULL,NULL,20,2),(44,'Fefe',NULL,NULL,'fefe@gmail.com','$2y$12$.PF/4WUTgsp.jNaFG4is5O3Zlt0htqf9D5yvgeBQFKd8/da.ISZta',NULL,NULL,NULL,NULL,20,2),(45,'Eded',NULL,NULL,'eded@gmail.com','$2y$12$q445DPO10MU6T9fGhvka6OfCY37hTbuzq5gEV1uJMnoLqqgRyytIy',NULL,NULL,NULL,NULL,20,2),(46,'Wiwi',NULL,NULL,'wiwi@gmail.com','$2y$12$ehiKOHVYmiZa93nZOfvaCOSK5NIxykZt8KsAnBYVnm6ugQoxcM9vC',NULL,NULL,NULL,NULL,20,1),(47,'nunu',NULL,NULL,'nunu@gmail.com','$2y$12$mWy0pYKzTvkkOmNJlPgU8ulCBAUUuEY7BDaZw76NqVNI8yQBDLL1a',NULL,NULL,NULL,NULL,20,2),(48,'cici',NULL,NULL,'cici@gmail.com','$2y$12$uPjDnv7cRD8CDVWDZnMiJ.z6cLlgjiYInlyWJ.Zia6Y1MJkf0SDtW',NULL,NULL,NULL,NULL,20,2),(49,'rara',NULL,NULL,'rara@gmail.com','$2y$12$Cy0BSH.ClG2WYTATXoHl1.C9JUNXlJa4GqfOVs2l4Y5DohtQVLLXG',NULL,NULL,NULL,NULL,20,2),(50,'vuvu',NULL,NULL,'vuvu@gmail.com','$2y$12$.aUBm5MiYw9Czcuu8SSc0emf8rrrlKyL7uz8Cw0diVvgb0Izv.Cwe',NULL,NULL,NULL,NULL,20,2);
/*!40000 ALTER TABLE `utilisateur` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voiture`
--

LOCK TABLES `voiture` WRITE;
/*!40000 ALTER TABLE `voiture` DISABLE KEYS */;
INSERT INTO `voiture` VALUES (1,'Model 3','AA123BB','électrique','blanc','2023-01-01',4,9,8),(2,'Zoe','CC456DD','électrique','bleu','2022-06-01',4,1,9),(3,'308','EE789FF','essence','noir','2021-12-01',5,2,8);
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

-- Dump completed on 2025-03-24 13:04:50
