-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mctbs
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `additional_charges`
--

DROP TABLE IF EXISTS `additional_charges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `additional_charges` (
  `additional_charge_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`additional_charge_id`),
  KEY `additional_charges_booking_id_foreign` (`booking_id`),
  KEY `additional_charges_created_by_foreign` (`created_by`),
  CONSTRAINT `additional_charges_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`),
  CONSTRAINT `additional_charges_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `additional_charges`
--

LOCK TABLES `additional_charges` WRITE;
/*!40000 ALTER TABLE `additional_charges` DISABLE KEYS */;
INSERT INTO `additional_charges` VALUES (1,3,6,'Broken Plate',4,200.00,800.00,'2026-10-12 06:26:50','2026-10-12 06:26:50'),(2,3,6,'Missing Towels',2,599.90,1199.80,'2026-10-12 06:47:33','2026-10-12 06:47:33'),(3,4,6,'Broken Plate',10,500.00,5000.00,'2026-10-13 19:37:52','2026-10-13 19:37:52');
/*!40000 ALTER TABLE `additional_charges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amenities`
--

DROP TABLE IF EXISTS `amenities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amenities` (
  `amenity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`amenity_id`),
  UNIQUE KEY `amenities_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amenities`
--

LOCK TABLES `amenities` WRITE;
/*!40000 ALTER TABLE `amenities` DISABLE KEYS */;
/*!40000 ALTER TABLE `amenities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_addons`
--

DROP TABLE IF EXISTS `booking_addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_addons` (
  `booking_addon_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  PRIMARY KEY (`booking_addon_id`),
  KEY `booking_addons_booking_id_foreign` (`booking_id`),
  CONSTRAINT `booking_addons_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_addons`
--

LOCK TABLES `booking_addons` WRITE;
/*!40000 ALTER TABLE `booking_addons` DISABLE KEYS */;
INSERT INTO `booking_addons` VALUES (1,15,'Electric stove',3500.00,1),(2,15,'Kaserola',400.00,1),(3,16,'Electric stove',3500.00,1),(4,16,'Pitcher',450.00,2),(5,16,'Electric stove',3500.00,3),(6,16,'Electric kettle',1000.00,5),(7,16,'Kaserola',400.00,1),(8,16,'Pitcher',450.00,1),(9,16,'Electric kettle',1000.00,1),(10,16,'Kaserola',400.00,1),(11,17,'ewanch',1200.00,1),(12,10,'Electric stove',3500.00,11),(13,10,'Electric kettle',1000.00,1),(14,18,'Electric stove',3500.00,10),(15,18,'Electric kettle',1000.00,19),(16,18,'Kaserola',400.00,12),(17,21,'Electric Kettle',500.00,1),(18,21,'Toothpaste',19.90,3),(19,21,'Mineral Water',39.81,2),(20,26,'Towel',50.00,13),(21,26,'Mineral Water',40.00,2),(22,27,'Toothpaste',20.00,12);
/*!40000 ALTER TABLE `booking_addons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_damages`
--

DROP TABLE IF EXISTS `booking_damages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_damages` (
  `booking_damage_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  PRIMARY KEY (`booking_damage_id`),
  KEY `booking_damages_booking_id_foreign` (`booking_id`),
  CONSTRAINT `booking_damages_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_damages`
--

LOCK TABLES `booking_damages` WRITE;
/*!40000 ALTER TABLE `booking_damages` DISABLE KEYS */;
INSERT INTO `booking_damages` VALUES (1,15,'Spoons',10.00,13),(2,16,'Spoons',10.00,12),(3,16,'Spatula',40.00,13),(4,16,'Knife',70.00,11),(5,16,'Mugs',30.00,15),(6,16,'Spoons',10.00,12),(7,16,'Spatula',40.00,10),(10,26,'Plate',40.00,10);
/*!40000 ALTER TABLE `booking_damages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_discounts`
--

DROP TABLE IF EXISTS `booking_discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_discounts` (
  `discount_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `booking_guest_id` int(10) unsigned NOT NULL,
  `verified_by` int(10) unsigned DEFAULT NULL,
  `discount_type` varchar(30) NOT NULL,
  `id_reference` varchar(100) NOT NULL,
  `verification_status` varchar(30) NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `eligible_amount` decimal(10,2) NOT NULL,
  `discount_rate` decimal(5,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `final_amount` decimal(10,2) NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `refund_status` varchar(30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_reference_key` varchar(64) DEFAULT NULL,
  `verification_note` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`discount_id`),
  KEY `booking_discounts_booking_id_foreign` (`booking_id`),
  KEY `booking_discounts_booking_guest_id_foreign` (`booking_guest_id`),
  KEY `booking_discounts_verified_by_foreign` (`verified_by`),
  CONSTRAINT `booking_discounts_booking_guest_id_foreign` FOREIGN KEY (`booking_guest_id`) REFERENCES `booking_guests` (`booking_guest_id`) ON DELETE CASCADE,
  CONSTRAINT `booking_discounts_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  CONSTRAINT `booking_discounts_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_discounts`
--

LOCK TABLES `booking_discounts` WRITE;
/*!40000 ALTER TABLE `booking_discounts` DISABLE KEYS */;
INSERT INTO `booking_discounts` VALUES (1,2,3,NULL,'Senior Citizen','123456789','Pending',NULL,1120.00,20.00,224.00,896.00,0.00,'None','2026-10-09 03:14:15','2026-10-09 03:14:15','15e2b0d3c33891ebb0f1ef609ec419420c20e320ce94c65fbc8c3312448eb225',NULL),(2,2,4,NULL,'Senior Citizen','321456','Pending',NULL,1120.00,20.00,224.00,896.00,0.00,'None','2026-10-09 03:14:15','2026-10-09 03:14:15','3af80ab0454995c52ebb1f4704fff7b71e61e39f2dc428d9fdef762b669ec811',NULL),(3,3,8,6,'Senior Citizen','1234567','Verified','2026-10-12 14:16:14',1000.00,20.00,200.00,800.00,0.00,'None','2026-10-12 01:02:40','2026-10-12 06:16:14','b0c6db8428b5fcd2911617bbced3fce429622538aff5d877013f5b7b9e28e21d',NULL),(4,3,9,6,'Senior Citizen','1234567','Verified','2026-10-12 14:16:17',1000.00,20.00,200.00,800.00,0.00,'None','2026-10-12 01:02:40','2026-10-12 06:16:17','3af3d4d9d9972786469adbbc68ff514233c8ddceba38fee0c0115f5e954a5df1',NULL),(5,3,10,6,'Senior Citizen','1234567','Verified','2026-10-12 14:16:19',1000.00,20.00,200.00,800.00,0.00,'None','2026-10-12 01:02:40','2026-10-12 06:16:19','58775d78f0b3a24f79c3f693c52f9a305b6aeea67ab4116ea44f9d349ab0f8db',NULL),(6,3,11,6,'Senior Citizen','1234567','Verified','2026-10-12 14:16:22',1000.00,20.00,200.00,800.00,0.00,'None','2026-10-12 01:02:40','2026-10-12 06:16:22','af658fcb5c0136d6ab011e470eacdaeef80387037e4b6db79a90f283f22cecc7',NULL),(7,5,24,NULL,'Senior Citizen','123456','Pending',NULL,1000.00,20.00,200.00,800.00,0.00,'None','2026-10-13 20:36:13','2026-10-13 20:36:13','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92',NULL),(8,6,29,7,'Senior Citizen','1234567','Verified','2026-10-14 07:52:05',8000.00,20.00,1600.00,6400.00,0.00,'None','2026-10-13 23:29:52','2026-10-13 23:52:05','7112b992c971ab9792d2104d0f968ecf4b8e1a27e2de51dbf0c424a72beb2eb4',NULL),(9,6,30,7,'PWD','1234567','Verified','2026-10-14 07:52:09',8000.00,20.00,1600.00,6400.00,0.00,'None','2026-10-13 23:29:52','2026-10-13 23:52:09','12bf94ee6039b7e54aa44a77b93887b1dc56f9c49e951c92adadf7e2f5870a08',NULL),(10,7,36,7,'Senior Citizen','12','Verified','2026-10-15 01:24:18',1000.00,20.00,200.00,800.00,0.00,'None','2026-10-14 16:38:28','2026-10-14 17:24:18','6b51d431df5d7f141cbececcf79edf3dd861c3b4069f0b11661a3eefacbba918',NULL),(11,7,37,7,'PWD','1','Verified','2026-10-15 01:24:15',1000.00,20.00,200.00,800.00,0.00,'None','2026-10-14 16:38:28','2026-10-14 17:24:15','6b86b273ff34fce19d6b804eff5a3f5747ada4eaa22f1d49c01e52ddb7875b4b',NULL),(12,8,56,NULL,'Senior Citizen','098543','Pending',NULL,2500.00,20.00,500.00,2000.00,0.00,'None','2026-10-14 16:41:42','2026-10-14 16:41:42','675466b37a783fea0e9c47fd86bcd8bd6bf92a41377e771aa020812daac5f167',NULL),(13,9,60,NULL,'PWD','1000','Pending',NULL,10000.00,20.00,2000.00,8000.00,0.00,'None','2026-10-14 17:18:36','2026-10-14 17:18:36','40510175845988f13f6162ed8526f0b09f73384467fa855e1e79b44a56562a58',NULL),(14,10,62,7,'PWD','10000','Verified','2026-10-25 10:55:35',40000.00,20.00,8000.00,32000.00,0.00,'None','2026-10-14 17:20:23','2026-10-25 02:55:35','39e5b4830d4d9c14db7368a95b65d5463ea3d09520373723430c03a5a453b5df',NULL),(15,11,63,9,'PWD','300','Verified','2026-10-15 02:11:18',4500.00,20.00,900.00,3600.00,900.00,'Refunded','2026-10-14 18:01:43','2026-10-14 18:38:53','983bd614bb5afece5ab3b6023f71147cd7b6bc2314f9d27af7422541c6558389',NULL),(16,12,65,9,'PWD','14','Verified','2026-10-15 03:46:50',872.73,20.00,174.55,698.18,0.00,'None','2026-10-14 19:15:07','2026-10-14 19:46:50','8527a891e224136950ff32ca212b45bc93f69fbb801c3b1ebedac52775f99e61',NULL),(17,13,76,NULL,'Senior Citizen','13243241','Pending',NULL,3000.00,20.00,600.00,2400.00,0.00,'None','2026-10-23 21:00:55','2026-10-23 21:00:55',NULL,NULL),(18,14,86,NULL,'Senior Citizen','12121','Pending',NULL,1500.00,20.00,300.00,1200.00,0.00,'None','2026-10-23 21:09:17','2026-10-23 21:09:17',NULL,NULL),(19,15,96,7,'Senior Citizen','1212121212121','Verified','2026-10-24 06:23:28',1000.00,20.00,200.00,800.00,0.00,'None','2026-10-23 22:09:22','2026-10-23 22:23:28',NULL,NULL),(20,16,106,7,'Senior Citizen','12','Verified','2026-10-24 10:09:07',1866.67,20.00,373.33,1493.34,0.00,'None','2026-10-24 01:26:22','2026-10-24 02:09:07',NULL,NULL),(21,18,121,7,'Senior Citizen','123456','Verified','2026-10-25 10:50:29',1815.38,20.00,363.08,1452.30,0.00,'None','2026-10-25 02:16:36','2026-10-25 02:50:29',NULL,NULL),(22,20,146,2,'Senior Citizen','12','Invalid','2026-11-03 17:14:51',2250.00,20.00,450.00,1800.00,0.00,'None','2026-11-03 08:36:58','2026-11-03 09:14:51',NULL,'not real'),(23,21,148,11,'Senior Citizen','12121','Verified','2026-11-03 17:47:37',1800.00,20.00,360.00,1440.00,0.00,'None','2026-11-03 09:44:45','2026-11-03 09:47:37',NULL,NULL),(24,23,153,NULL,'Senior Citizen','1212','Pending',NULL,3600.00,20.00,720.00,2880.00,0.00,'None','2026-11-03 10:13:14','2026-11-03 10:13:14',NULL,NULL),(25,23,154,NULL,'PWD','3121','Pending',NULL,3600.00,20.00,720.00,2880.00,0.00,'None','2026-11-03 10:13:14','2026-11-03 10:13:14',NULL,NULL),(26,24,168,NULL,'Senior Citizen','1212','Pending',NULL,3600.00,20.00,720.00,2880.00,0.00,'None','2026-11-03 10:15:46','2026-11-03 10:15:46',NULL,NULL),(27,24,169,NULL,'PWD','3121','Pending',NULL,3600.00,20.00,720.00,2880.00,0.00,'None','2026-11-03 10:15:46','2026-11-03 10:15:46',NULL,NULL),(28,25,183,NULL,'Senior Citizen','1212','Pending',NULL,3600.00,20.00,720.00,2880.00,0.00,'None','2026-11-03 10:17:38','2026-11-03 10:17:38',NULL,NULL),(29,25,184,NULL,'PWD','321321','Pending',NULL,3600.00,20.00,720.00,2880.00,0.00,'None','2026-11-03 10:17:38','2026-11-03 10:17:38',NULL,NULL),(30,26,198,2,'Senior Citizen','12122','Verified','2026-11-03 18:25:56',3600.00,20.00,720.00,2880.00,0.00,'None','2026-11-03 10:24:27','2026-11-03 10:25:56',NULL,NULL),(31,26,199,2,'PWD','31231','Invalid','2026-11-03 18:25:54',3600.00,20.00,720.00,2880.00,0.00,'None','2026-11-03 10:24:27','2026-11-03 10:25:54',NULL,'not real!'),(32,29,225,2,'Senior Citizen','2121212','Verified','2026-11-11 21:48:02',2400.00,20.00,480.00,1920.00,0.00,'None','2026-11-11 13:41:42','2026-11-11 13:48:02',NULL,NULL),(33,29,226,2,'Senior Citizen','1212121','Verified','2026-11-11 21:48:06',2400.00,20.00,480.00,1920.00,0.00,'None','2026-11-11 13:41:42','2026-11-11 13:48:06',NULL,NULL);
/*!40000 ALTER TABLE `booking_discounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_edits`
--

DROP TABLE IF EXISTS `booking_edits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_edits` (
  `edit_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `edited_by` int(10) unsigned NOT NULL,
  `old_check_in` datetime NOT NULL,
  `old_check_out` datetime NOT NULL,
  `new_check_in` datetime NOT NULL,
  `new_check_out` datetime NOT NULL,
  `old_total_price` decimal(10,2) NOT NULL,
  `new_total_price` decimal(10,2) NOT NULL,
  `edit_reason` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`edit_id`),
  KEY `booking_edits_edited_by_foreign` (`edited_by`),
  KEY `booking_edits_booking_id_created_at_index` (`booking_id`,`created_at`),
  CONSTRAINT `booking_edits_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`),
  CONSTRAINT `booking_edits_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_edits`
--

LOCK TABLES `booking_edits` WRITE;
/*!40000 ALTER TABLE `booking_edits` DISABLE KEYS */;
INSERT INTO `booking_edits` VALUES (1,4,6,'2026-10-23 00:00:00','2026-10-28 00:00:00','2026-10-23 00:00:00','2026-10-31 00:00:00',31500.00,43500.00,'Updated the dates and added a sleeping set','2026-10-13 19:31:15','2026-10-13 19:31:15'),(2,5,6,'2026-11-06 00:00:00','2026-11-07 00:00:00','2026-11-06 00:00:00','2026-11-07 00:00:00',5000.00,9000.00,'added 4 sets of sleeping set','2026-10-13 22:33:54','2026-10-13 22:33:54');
/*!40000 ALTER TABLE `booking_edits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_guest_events`
--

DROP TABLE IF EXISTS `booking_guest_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_guest_events` (
  `booking_guest_event_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `booking_guest_id` int(10) unsigned DEFAULT NULL,
  `related_booking_guest_id` int(10) unsigned DEFAULT NULL,
  `event_type` varchar(40) NOT NULL,
  `performed_by` int(10) unsigned DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `event_at` datetime NOT NULL,
  `before_state` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_state`)),
  `after_state` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_state`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`booking_guest_event_id`),
  KEY `booking_guest_events_booking_guest_id_foreign` (`booking_guest_id`),
  KEY `booking_guest_events_related_booking_guest_id_foreign` (`related_booking_guest_id`),
  KEY `booking_guest_events_performed_by_foreign` (`performed_by`),
  KEY `booking_guest_events_booking_id_event_at_index` (`booking_id`,`event_at`),
  CONSTRAINT `booking_guest_events_booking_guest_id_foreign` FOREIGN KEY (`booking_guest_id`) REFERENCES `booking_guests` (`booking_guest_id`) ON DELETE SET NULL,
  CONSTRAINT `booking_guest_events_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`),
  CONSTRAINT `booking_guest_events_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `booking_guest_events_related_booking_guest_id_foreign` FOREIGN KEY (`related_booking_guest_id`) REFERENCES `booking_guests` (`booking_guest_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_guest_events`
--

LOCK TABLES `booking_guest_events` WRITE;
/*!40000 ALTER TABLE `booking_guest_events` DISABLE KEYS */;
INSERT INTO `booking_guest_events` VALUES (1,29,226,NULL,'Arrival',2,NULL,'2026-11-11 21:44:40','{\"attendance_status\":\"Expected\"}','{\"attendance_status\":\"Arrived\"}','2026-11-11 13:44:40','2026-11-11 13:44:40'),(2,29,226,NULL,'Identity verification',2,NULL,'2026-11-11 21:44:45','{\"identity_verification_status\":\"Unverified\",\"identity_verified_by\":null,\"identity_verified_at\":null,\"identity_exception_reason\":null}','{\"identity_verification_status\":\"Verified\",\"identity_verified_by\":2,\"identity_verified_at\":\"2026-11-11 21:44:45\",\"identity_exception_reason\":null}','2026-11-11 13:44:45','2026-11-11 13:44:45'),(3,29,239,NULL,'No-show',2,NULL,'2026-11-11 21:48:14','{\"attendance_status\":\"Expected\"}','{\"attendance_status\":\"No-show\"}','2026-11-11 13:48:14','2026-11-11 13:48:14');
/*!40000 ALTER TABLE `booking_guest_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_guests`
--

DROP TABLE IF EXISTS `booking_guests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_guests` (
  `booking_guest_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `full_name` varchar(200) NOT NULL,
  `guest_type` varchar(30) NOT NULL,
  `age` tinyint(3) unsigned DEFAULT NULL,
  `is_free_child` tinyint(1) NOT NULL DEFAULT 0,
  `share_amount` decimal(10,2) NOT NULL,
  `is_primary_guest` tinyint(4) NOT NULL DEFAULT 0,
  `attendance_status` varchar(20) DEFAULT NULL,
  `replaces_booking_guest_id` int(10) unsigned DEFAULT NULL,
  `identity_verification_status` varchar(20) DEFAULT NULL,
  `identity_verified_by` int(10) unsigned DEFAULT NULL,
  `identity_verified_at` datetime DEFAULT NULL,
  `identity_exception_reason` text DEFAULT NULL,
  `classification_overridden` tinyint(1) DEFAULT NULL,
  `classification_override_reason` text DEFAULT NULL,
  `classification_overridden_by` int(10) unsigned DEFAULT NULL,
  `classification_overridden_at` datetime DEFAULT NULL,
  `roster_status` varchar(20) NOT NULL DEFAULT 'Active',
  `removed_at` datetime DEFAULT NULL,
  `removed_by` int(10) unsigned DEFAULT NULL,
  `removal_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`booking_guest_id`),
  KEY `booking_guests_user_id_foreign` (`user_id`),
  KEY `booking_guests_removed_by_foreign` (`removed_by`),
  KEY `booking_guests_booking_id_roster_status_index` (`booking_id`,`roster_status`),
  KEY `booking_guests_replaces_booking_guest_id_foreign` (`replaces_booking_guest_id`),
  KEY `booking_guests_identity_verified_by_foreign` (`identity_verified_by`),
  KEY `booking_guests_classification_overridden_by_foreign` (`classification_overridden_by`),
  CONSTRAINT `booking_guests_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  CONSTRAINT `booking_guests_classification_overridden_by_foreign` FOREIGN KEY (`classification_overridden_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `booking_guests_identity_verified_by_foreign` FOREIGN KEY (`identity_verified_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `booking_guests_removed_by_foreign` FOREIGN KEY (`removed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `booking_guests_replaces_booking_guest_id_foreign` FOREIGN KEY (`replaces_booking_guest_id`) REFERENCES `booking_guests` (`booking_guest_id`),
  CONSTRAINT `booking_guests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=250 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_guests`
--

LOCK TABLES `booking_guests` WRITE;
/*!40000 ALTER TABLE `booking_guests` DISABLE KEYS */;
INSERT INTO `booking_guests` VALUES (1,1,4,'Karl Marteja','adult',NULL,0,4500.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-08 19:15:55','2026-10-08 19:15:55'),(2,1,NULL,'Angelo','adult',NULL,0,4500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-08 19:15:55','2026-10-08 19:15:55'),(3,2,4,'Karlos','adult',NULL,0,1120.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-09 03:14:15','2026-10-09 03:14:15'),(4,2,NULL,'erin','adult',NULL,0,1120.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-09 03:14:15','2026-10-09 03:14:15'),(5,2,NULL,'brad','adult',NULL,0,1120.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-09 03:14:15','2026-10-09 03:14:15'),(6,2,NULL,'lili','adult',NULL,0,1120.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-09 03:14:15','2026-10-09 03:14:15'),(7,2,NULL,'loli','adult',NULL,0,1120.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-09 03:14:15','2026-10-09 03:14:15'),(8,3,4,'Senior Citizen 1','adult',NULL,0,1000.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-12 01:02:40','2026-10-12 01:02:40'),(9,3,NULL,'Senior Citizen 2','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-12 01:02:40','2026-10-12 01:02:40'),(10,3,NULL,'Senior Citizen 3','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-12 01:02:40','2026-10-12 01:02:40'),(11,3,NULL,'Senior Citizen 4','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-12 01:02:40','2026-10-12 01:02:40'),(12,3,NULL,'Adult 5','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-12 01:02:40','2026-10-12 01:02:40'),(13,3,NULL,'Adult 6','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-12 01:02:40','2026-10-12 01:02:40'),(14,3,NULL,'Adult 7','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-12 01:02:40','2026-10-12 01:02:40'),(15,3,NULL,'Adult 8','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-12 01:02:40','2026-10-12 01:02:40'),(16,3,NULL,'Adult 9','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-12 01:02:40','2026-10-12 01:02:40'),(17,3,NULL,'Adult 10','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-12 01:02:40','2026-10-12 01:02:40'),(18,4,4,'Adult 1','adult',NULL,0,8000.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 19:03:12','2026-10-13 19:31:15'),(19,4,NULL,'Child 1','child',9,0,8000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 19:03:12','2026-10-13 19:31:15'),(20,4,NULL,'Child 2','child',8,0,8000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 19:03:12','2026-10-13 19:31:15'),(21,4,NULL,'Child 3','child',6,0,8000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 19:03:12','2026-10-13 19:31:15'),(22,4,NULL,'Child 4','child',4,0,8000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 19:03:12','2026-10-13 19:31:15'),(23,4,NULL,'Child 5','child',1,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 19:03:12','2026-10-13 19:03:12'),(24,5,4,'Senior Citizen 1','adult',NULL,0,1000.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 20:36:13','2026-10-13 20:36:13'),(25,5,NULL,'Adult 2','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 20:36:13','2026-10-13 20:36:13'),(26,5,NULL,'Adult 3','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 20:36:13','2026-10-13 20:36:13'),(27,5,NULL,'Adult 4','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 20:36:13','2026-10-13 20:36:13'),(28,5,NULL,'Adult 5','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 20:36:13','2026-10-13 20:36:13'),(29,6,4,'Senior Citizen 1','adult',NULL,0,8000.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 23:29:52','2026-10-13 23:29:52'),(30,6,NULL,'PWD 1','adult',NULL,0,8000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 23:29:52','2026-10-13 23:29:52'),(31,6,NULL,'Child 1','child',1,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 23:29:52','2026-10-13 23:29:52'),(32,6,NULL,'Child 2','child',2,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 23:29:53','2026-10-13 23:29:53'),(33,6,NULL,'Child 3','child',6,0,8000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 23:29:53','2026-10-13 23:29:53'),(34,6,NULL,'Child 4','child',7,0,8000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 23:29:53','2026-10-13 23:29:53'),(35,6,NULL,'Child 5','child',8,0,8000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-13 23:29:53','2026-10-13 23:29:53'),(36,7,4,'Senior Citizen 1','adult',NULL,0,1000.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(37,7,NULL,'PWD 1','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(38,7,NULL,'Adult 3','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(39,7,NULL,'Adult 4','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(40,7,NULL,'Adult 5','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(41,7,NULL,'Adult 6','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(42,7,NULL,'Adult 7','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(43,7,NULL,'Adult 8','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(44,7,NULL,'Adult 9','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(45,7,NULL,'Adult 10','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(46,7,NULL,'Child 1','child',NULL,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(47,7,NULL,'Child 2','child',NULL,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(48,7,NULL,'Child 3','child',NULL,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(49,7,NULL,'Child 4','child',NULL,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(50,7,NULL,'Child 5','child',NULL,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(51,7,NULL,'Child 6','child',NULL,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(52,7,NULL,'Child 7','child',NULL,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(53,7,NULL,'Child 8','child',NULL,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(54,7,NULL,'Child 9','child',NULL,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(55,7,NULL,'Child 10','child',NULL,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 16:38:28'),(56,8,4,'Senior Citizen 1','adult',NULL,0,2500.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:41:42','2026-10-14 16:41:42'),(57,8,NULL,'Adult 2','adult',NULL,0,2500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:41:42','2026-10-14 16:41:42'),(58,8,NULL,'Adult 3','adult',NULL,0,2500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:41:42','2026-10-14 16:41:42'),(59,8,NULL,'Adult 4','adult',NULL,0,2500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 16:41:42','2026-10-14 16:41:42'),(60,9,8,'PWD 1','adult',NULL,0,10000.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 17:18:36','2026-10-14 17:18:36'),(61,9,NULL,'Adult 2','adult',NULL,0,10000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 17:18:36','2026-10-14 17:18:36'),(62,10,8,'PWD 1','adult',NULL,0,40000.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 17:20:23','2026-10-14 17:20:23'),(63,11,8,'PWD 1','adult',NULL,0,4500.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 18:01:43','2026-10-14 18:01:43'),(64,11,NULL,'Adult 2','adult',NULL,0,4500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 18:01:43','2026-10-14 18:01:43'),(65,12,8,'PWD 1','adult',NULL,0,872.73,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 19:15:07','2026-10-14 19:15:07'),(66,12,NULL,'Adult 2','adult',NULL,0,872.73,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 19:15:07','2026-10-14 19:15:07'),(67,12,NULL,'Adult 3','adult',NULL,0,872.73,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 19:15:07','2026-10-14 19:15:07'),(68,12,NULL,'Adult 4','adult',NULL,0,872.73,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 19:15:07','2026-10-14 19:15:07'),(69,12,NULL,'Adult 5','adult',NULL,0,872.73,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 19:15:07','2026-10-14 19:15:07'),(70,12,NULL,'Adult 6','adult',NULL,0,872.73,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 19:15:07','2026-10-14 19:15:07'),(71,12,NULL,'Adult 7','adult',NULL,0,872.73,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 19:15:07','2026-10-14 19:15:07'),(72,12,NULL,'Adult 8','adult',NULL,0,872.73,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 19:15:07','2026-10-14 19:15:07'),(73,12,NULL,'Adult 9','adult',NULL,0,872.73,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 19:15:07','2026-10-14 19:15:07'),(74,12,NULL,'Adult 10','adult',NULL,0,872.73,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 19:15:07','2026-10-14 19:15:07'),(75,12,NULL,'Adult 11','adult',NULL,0,872.73,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-14 19:15:07','2026-10-14 19:15:07'),(76,13,4,'Senior Citizen 1','adult',NULL,0,3000.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:00:55','2026-10-23 21:00:55'),(77,13,NULL,'Adult 2','adult',NULL,0,3000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:00:55','2026-10-23 21:00:55'),(78,13,NULL,'Adult 3','adult',NULL,0,3000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:00:55','2026-10-23 21:00:55'),(79,13,NULL,'Adult 4','adult',NULL,0,3000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:00:55','2026-10-23 21:00:55'),(80,13,NULL,'Adult 5','adult',NULL,0,3000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:00:55','2026-10-23 21:00:55'),(81,13,NULL,'Adult 6','adult',NULL,0,3000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:00:55','2026-10-23 21:00:55'),(82,13,NULL,'Adult 7','adult',NULL,0,3000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:00:55','2026-10-23 21:00:55'),(83,13,NULL,'Adult 8','adult',NULL,0,3000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:00:55','2026-10-23 21:00:55'),(84,13,NULL,'Adult 9','adult',NULL,0,3000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:00:55','2026-10-23 21:00:55'),(85,13,NULL,'Adult 10','adult',NULL,0,3000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:00:55','2026-10-23 21:00:55'),(86,14,4,'Senior Citizen 1','adult',NULL,0,1500.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:09:17','2026-10-23 21:09:17'),(87,14,NULL,'Adult 2','adult',NULL,0,1500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:09:17','2026-10-23 21:09:17'),(88,14,NULL,'Adult 3','adult',NULL,0,1500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:09:17','2026-10-23 21:09:17'),(89,14,NULL,'Adult 4','adult',NULL,0,1500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:09:17','2026-10-23 21:09:17'),(90,14,NULL,'Adult 5','adult',NULL,0,1500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:09:17','2026-10-23 21:09:17'),(91,14,NULL,'Adult 6','adult',NULL,0,1500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:09:17','2026-10-23 21:09:17'),(92,14,NULL,'Adult 7','adult',NULL,0,1500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:09:17','2026-10-23 21:09:17'),(93,14,NULL,'Adult 8','adult',NULL,0,1500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:09:17','2026-10-23 21:09:17'),(94,14,NULL,'Adult 9','adult',NULL,0,1500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:09:17','2026-10-23 21:09:17'),(95,14,NULL,'Adult 10','adult',NULL,0,1500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 21:09:17','2026-10-23 21:09:17'),(96,15,4,'Senior Citizen 1','adult',NULL,0,1000.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 22:09:22','2026-10-23 22:09:22'),(97,15,NULL,'Adult 2','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 22:09:22','2026-10-23 22:09:22'),(98,15,NULL,'Adult 3','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 22:09:22','2026-10-23 22:09:22'),(99,15,NULL,'Adult 4','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 22:09:22','2026-10-23 22:09:22'),(100,15,NULL,'Adult 5','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 22:09:22','2026-10-23 22:09:22'),(101,15,NULL,'Adult 6','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 22:09:22','2026-10-23 22:09:22'),(102,15,NULL,'Adult 7','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 22:09:22','2026-10-23 22:09:22'),(103,15,NULL,'Adult 8','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 22:09:22','2026-10-23 22:09:22'),(104,15,NULL,'Adult 9','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 22:09:22','2026-10-23 22:09:22'),(105,15,NULL,'Adult 10','adult',NULL,0,1000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-23 22:09:22','2026-10-23 22:09:22'),(106,16,4,'Senior Citizen 1','adult',NULL,0,1866.67,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 01:26:22','2026-10-24 01:26:22'),(107,16,NULL,'Adult 2','adult',NULL,0,1866.67,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 01:26:22','2026-10-24 01:26:22'),(108,16,NULL,'Adult 3','adult',NULL,0,1866.67,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 01:26:22','2026-10-24 01:26:22'),(109,16,NULL,'Adult 4','adult',NULL,0,1866.67,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 01:26:22','2026-10-24 01:26:22'),(110,16,NULL,'Adult 5','adult',NULL,0,1866.67,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 01:26:22','2026-10-24 01:26:22'),(111,16,NULL,'Adult 6','adult',NULL,0,1866.67,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 01:26:22','2026-10-24 01:26:22'),(112,16,NULL,'Adult 7','adult',NULL,0,1866.67,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 01:26:22','2026-10-24 01:26:22'),(113,16,NULL,'Adult 8','adult',NULL,0,1866.67,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 01:26:22','2026-10-24 01:26:22'),(114,16,NULL,'Adult 9','adult',NULL,0,1866.67,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 01:26:22','2026-10-24 01:26:22'),(115,16,NULL,'Adult 10','adult',NULL,0,1866.67,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 01:26:22','2026-10-24 01:26:22'),(116,16,NULL,'Adult 11','adult',NULL,0,1866.67,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 01:26:22','2026-10-24 01:26:22'),(117,16,NULL,'Adult 12','adult',NULL,0,1866.67,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 01:26:22','2026-10-24 01:26:22'),(118,17,4,'Adult 1','adult',NULL,0,1533.33,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 03:12:04','2026-10-24 03:12:04'),(119,17,NULL,'Adult 2','adult',NULL,0,1533.33,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 03:12:04','2026-10-24 03:12:04'),(120,17,NULL,'Adult 3','adult',NULL,0,1533.33,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-24 03:12:04','2026-10-24 03:12:04'),(121,18,4,'Senior Citizen 1','adult',NULL,0,1815.38,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 02:16:36'),(122,18,NULL,'Adult 2','adult',NULL,0,1815.38,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 02:16:36'),(123,18,NULL,'Adult 3','adult',NULL,0,1815.38,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 02:16:36'),(124,18,NULL,'Adult 4','adult',NULL,0,1815.38,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 02:16:36'),(125,18,NULL,'Adult 5','adult',NULL,0,1815.38,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 02:16:36'),(126,18,NULL,'Adult 6','adult',NULL,0,1815.38,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 02:16:36'),(127,18,NULL,'Adult 7','adult',NULL,0,1815.38,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 02:16:36'),(128,18,NULL,'Adult 8','adult',NULL,0,1815.38,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 02:16:36'),(129,18,NULL,'Adult 9','adult',NULL,0,1815.38,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 02:16:36'),(130,18,NULL,'Adult 10','adult',NULL,0,1815.38,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 02:16:36'),(131,18,NULL,'Adult 11','adult',NULL,0,1815.38,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 02:16:36'),(132,18,NULL,'Adult 12','adult',NULL,0,1815.38,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 02:16:36'),(133,18,NULL,'Adult 13','adult',NULL,0,1815.38,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 02:16:36'),(134,19,NULL,'Karlos','adult',NULL,0,2000.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53'),(135,19,NULL,'Adult 2','adult',NULL,0,2000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53'),(136,19,NULL,'Adult 3','adult',NULL,0,2000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53'),(137,19,NULL,'Adult 4','adult',NULL,0,2000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53'),(138,19,NULL,'Adult 5','adult',NULL,0,2000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53'),(139,19,NULL,'Adult 6','adult',NULL,0,2000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53'),(140,19,NULL,'Adult 7','adult',NULL,0,2000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53'),(141,19,NULL,'Adult 8','adult',NULL,0,2000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53'),(142,19,NULL,'Adult 9','adult',NULL,0,2000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53'),(143,19,NULL,'Adult 10','adult',NULL,0,2000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53'),(144,19,NULL,'Child 1','child',NULL,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53'),(145,19,NULL,'Child 2','child',NULL,1,0.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53'),(146,20,4,'Senior Citizen 1','adult',NULL,0,2250.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 08:36:58','2026-11-03 08:36:58'),(147,20,NULL,'Adult 2','adult',NULL,0,2250.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 08:36:58','2026-11-03 08:36:58'),(148,21,10,'Senior Citizen 1','adult',NULL,0,1800.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 09:44:45','2026-11-03 09:44:45'),(149,21,NULL,'Adult 2','adult',NULL,0,1800.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 09:44:45','2026-11-03 09:44:45'),(150,21,NULL,'Adult 3','adult',NULL,0,1800.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 09:44:45','2026-11-03 09:44:45'),(151,22,NULL,'Walk-In Guest','adult',NULL,0,600.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 09:52:08','2026-11-03 09:52:08'),(152,22,NULL,'Adult 2','adult',NULL,0,600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 09:52:08','2026-11-03 09:52:08'),(153,23,13,'Senior Citizen 1','adult',NULL,0,3600.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(154,23,NULL,'PWD 1','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(155,23,NULL,'Adult 3','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(156,23,NULL,'Adult 4','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(157,23,NULL,'Adult 5','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(158,23,NULL,'Adult 6','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(159,23,NULL,'Adult 7','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(160,23,NULL,'Adult 8','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(161,23,NULL,'Adult 9','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(162,23,NULL,'Adult 10','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(163,23,NULL,'Adult 11','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(164,23,NULL,'Adult 12','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(165,23,NULL,'Adult 13','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(166,23,NULL,'Adult 14','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(167,23,NULL,'Adult 15','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:13:14','2026-11-03 10:13:14'),(168,24,13,'Senior Citizen 1','adult',NULL,0,3600.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(169,24,NULL,'PWD 1','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(170,24,NULL,'Adult 3','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(171,24,NULL,'Adult 4','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(172,24,NULL,'Adult 5','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(173,24,NULL,'Adult 6','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(174,24,NULL,'Adult 7','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(175,24,NULL,'Adult 8','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(176,24,NULL,'Adult 9','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(177,24,NULL,'Adult 10','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(178,24,NULL,'Adult 11','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(179,24,NULL,'Adult 12','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(180,24,NULL,'Adult 13','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(181,24,NULL,'Adult 14','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(182,24,NULL,'Adult 15','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:15:46','2026-11-03 10:15:46'),(183,25,13,'Senior Citizen 1','adult',NULL,0,3600.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(184,25,NULL,'PWD 1','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(185,25,NULL,'Adult 3','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(186,25,NULL,'Adult 4','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(187,25,NULL,'Adult 5','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(188,25,NULL,'Adult 6','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(189,25,NULL,'Adult 7','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(190,25,NULL,'Adult 8','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(191,25,NULL,'Adult 9','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(192,25,NULL,'Adult 10','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(193,25,NULL,'Adult 11','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(194,25,NULL,'Adult 12','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(195,25,NULL,'Adult 13','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(196,25,NULL,'Adult 14','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(197,25,NULL,'Adult 15','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:17:38','2026-11-03 10:17:38'),(198,26,13,'Senior Citizen 1','adult',NULL,0,3600.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(199,26,NULL,'PWD 1','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(200,26,NULL,'Adult 3','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(201,26,NULL,'Adult 4','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(202,26,NULL,'Adult 5','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(203,26,NULL,'Adult 6','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(204,26,NULL,'Adult 7','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(205,26,NULL,'Adult 8','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(206,26,NULL,'Adult 9','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(207,26,NULL,'Adult 10','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(208,26,NULL,'Adult 11','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(209,26,NULL,'Adult 12','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(210,26,NULL,'Adult 13','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(211,26,NULL,'Adult 14','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(212,26,NULL,'Adult 15','adult',NULL,0,3600.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:24:27'),(213,27,NULL,'Walk-In Guest','adult',NULL,0,1350.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:35:57'),(214,27,NULL,'Adult 2','adult',NULL,0,1350.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:35:57'),(215,27,NULL,'Adult 3','adult',NULL,0,1350.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:35:57'),(216,27,NULL,'Adult 4','adult',NULL,0,1350.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:35:57'),(217,27,NULL,'Adult 5','adult',NULL,0,1350.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:35:57'),(218,27,NULL,'Adult 6','adult',NULL,0,1350.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:35:57'),(219,27,NULL,'Adult 7','adult',NULL,0,1350.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:35:57'),(220,27,NULL,'Adult 8','adult',NULL,0,1350.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:35:57'),(221,27,NULL,'Adult 9','adult',NULL,0,1350.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:35:57'),(222,27,NULL,'Adult 10','adult',NULL,0,1350.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:35:57'),(223,27,NULL,'Adult 11','adult',NULL,0,1350.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:35:57'),(224,27,NULL,'Adult 12','adult',NULL,0,1350.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:35:57'),(225,29,4,'Senior Citizen 1','adult',NULL,0,2400.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:41:42'),(226,29,NULL,'Senior Citizen 2','adult',NULL,0,2400.00,0,'Arrived',NULL,'Verified',2,'2026-11-11 21:44:45',NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:44:45'),(227,29,NULL,'Adult 3','adult',NULL,0,2400.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:41:42'),(228,29,NULL,'Adult 4','adult',NULL,0,2400.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:41:42'),(229,29,NULL,'Adult 5','adult',NULL,0,2400.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:41:42'),(230,29,NULL,'Adult 6','adult',NULL,0,2400.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:41:42'),(231,29,NULL,'Adult 7','adult',NULL,0,2400.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:41:42'),(232,29,NULL,'Adult 8','adult',NULL,0,2400.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:41:42'),(233,29,NULL,'Adult 9','adult',NULL,0,2400.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:41:42'),(234,29,NULL,'Adult 10','adult',NULL,0,2400.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:41:42'),(235,29,NULL,'Adult 11','adult',NULL,0,2400.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:41:42'),(236,29,NULL,'Adult 12','adult',NULL,0,2400.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:41:42'),(237,29,NULL,'Adult 13','adult',NULL,0,2400.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:41:42'),(238,29,NULL,'Adult 14','adult',NULL,0,2400.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:41:42'),(239,29,NULL,'Adult 15','adult',NULL,0,2400.00,0,'No-show',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 13:48:14'),(240,30,NULL,'Walk-In Guest','adult',NULL,0,6000.00,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 14:35:13','2026-11-11 14:35:13'),(241,30,NULL,'Adult 2','adult',NULL,0,6000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 14:35:13','2026-11-11 14:35:13'),(242,30,NULL,'Adult 3','adult',NULL,0,6000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 14:35:13','2026-11-11 14:35:13'),(243,30,NULL,'Adult 4','adult',NULL,0,6000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 14:35:13','2026-11-11 14:35:13'),(244,30,NULL,'Adult 5','adult',NULL,0,6000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 14:35:13','2026-11-11 14:35:13'),(245,30,NULL,'Adult 6','adult',NULL,0,6000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 14:35:13','2026-11-11 14:35:13'),(246,30,NULL,'Adult 7','adult',NULL,0,6000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 14:35:13','2026-11-11 14:35:13'),(247,30,NULL,'Adult 8','adult',NULL,0,6000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 14:35:13','2026-11-11 14:35:13'),(248,30,NULL,'Adult 9','adult',NULL,0,6000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 14:35:13','2026-11-11 14:35:13'),(249,30,NULL,'Adult 10','adult',NULL,0,6000.00,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,'2026-11-11 14:35:13','2026-11-11 14:35:13');
/*!40000 ALTER TABLE `booking_guests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_receipts`
--

DROP TABLE IF EXISTS `booking_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_receipts` (
  `receipt_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `receipt_number` varchar(40) NOT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`receipt_id`),
  UNIQUE KEY `booking_receipts_booking_id_unique` (`booking_id`),
  UNIQUE KEY `booking_receipts_receipt_number_unique` (`receipt_number`),
  CONSTRAINT `booking_receipts_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_receipts`
--

LOCK TABLES `booking_receipts` WRITE;
/*!40000 ALTER TABLE `booking_receipts` DISABLE KEYS */;
INSERT INTO `booking_receipts` VALUES (1,3,'MCTBS-RCP-00000003','2026-10-12 06:49:51'),(2,4,'MCTBS-RCP-00000004','2026-10-13 19:39:28'),(3,6,'MCTBS-RCP-00000006','2026-10-13 23:58:24'),(4,7,'MCTBS-RCP-00000007','2026-10-14 17:24:47'),(5,11,'MCTBS-RCP-00000011','2026-10-14 18:38:09'),(6,12,'MCTBS-RCP-00000012','2026-10-14 19:48:14'),(7,9,'MCTBS-RCP-00000009','2026-10-23 18:16:32'),(8,15,'MCTBS-RCP-00000015','2026-10-23 22:57:04'),(9,16,'MCTBS-RCP-00000016','2026-10-24 02:36:14'),(10,17,'MCTBS-RCP-00000017','2026-10-25 02:15:50'),(11,18,'MCTBS-RCP-00000018','2026-10-25 10:26:49'),(12,20,'MCTBS-RCP-00000020','2026-11-03 09:15:36'),(13,26,'MCTBS-RCP-00000026','2026-11-03 10:28:57'),(14,27,'MCTBS-RCP-00000027','2026-11-03 10:36:46');
/*!40000 ALTER TABLE `booking_receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `booking_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `walk_in_phone` varchar(30) DEFAULT NULL,
  `walk_in_email` varchar(191) DEFAULT NULL,
  `unit_id` int(10) unsigned NOT NULL,
  `check_in` datetime NOT NULL,
  `check_out` datetime NOT NULL,
  `adult_count` int(11) NOT NULL,
  `child_count` int(11) NOT NULL,
  `child_free_through_age` tinyint(3) unsigned DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `nightly_rate` decimal(10,2) DEFAULT NULL,
  `room_subtotal` decimal(10,2) DEFAULT NULL,
  `extra_guest_count` int(10) unsigned NOT NULL DEFAULT 0,
  `extra_guest_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `extra_guest_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) NOT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `payment_deadline` datetime DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `booking_reference` varchar(32) DEFAULT NULL,
  `feedback_token` varchar(100) DEFAULT NULL,
  `feedback_token_expires_at` timestamp NULL DEFAULT NULL,
  `checked_in_at` datetime DEFAULT NULL,
  `checked_out_at` datetime DEFAULT NULL,
  `pricing_mode` varchar(20) NOT NULL DEFAULT 'Per Unit',
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `bookings_booking_reference_unique` (`booking_reference`),
  UNIQUE KEY `bookings_feedback_token_unique` (`feedback_token`),
  KEY `bookings_unit_id_check_in_check_out_index` (`unit_id`,`check_in`,`check_out`),
  KEY `bookings_user_id_foreign` (`user_id`),
  CONSTRAINT `bookings_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`),
  CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (1,4,NULL,NULL,1,'2026-10-11 00:00:00','2026-10-17 00:00:00',2,0,NULL,9000.00,1500.00,NULL,0,0.00,0.00,'Cancelled',NULL,NULL,'Payment was not submitted within 24 hours of acceptance.',NULL,'2026-10-08 19:15:55','2026-10-09 21:03:37','MCTBS-000001',NULL,NULL,NULL,NULL,'Per Unit'),(2,4,NULL,NULL,2,'2026-10-24 00:00:00','2026-10-26 00:00:00',5,0,NULL,5600.00,2800.00,NULL,0,0.00,0.00,'Cancelled',NULL,NULL,'maintenance',NULL,'2026-10-09 03:14:15','2026-10-09 03:21:37','MCTBS-000002',NULL,NULL,NULL,NULL,'Per Unit'),(3,4,NULL,NULL,3,'2026-10-15 00:00:00','2026-10-17 00:00:00',10,0,NULL,9200.00,5000.00,10000.00,0,0.00,0.00,'Completed','2026-10-12 09:10:26','2026-10-13 09:10:26',NULL,NULL,'2026-10-12 01:02:40','2026-10-12 06:49:51','MCTBS-000003',NULL,NULL,NULL,NULL,'Per Unit'),(4,4,NULL,NULL,3,'2026-10-23 00:00:00','2026-10-31 00:00:00',1,5,NULL,40000.00,5000.00,40000.00,0,600.00,0.00,'Completed','2026-10-14 03:15:00','2026-10-15 03:15:00',NULL,NULL,'2026-10-13 19:03:12','2026-10-13 19:39:28','MCTBS-000004',NULL,NULL,NULL,NULL,'Per Unit'),(5,4,NULL,NULL,3,'2026-11-06 00:00:00','2026-11-07 00:00:00',5,0,NULL,5000.00,5000.00,5000.00,0,600.00,0.00,'Cancelled','2026-10-14 06:23:18','2026-10-15 06:23:18','no payment',NULL,'2026-10-13 20:36:13','2026-10-23 21:45:06','MCTBS-000005',NULL,NULL,NULL,NULL,'Per Unit'),(6,4,NULL,NULL,4,'2026-10-20 00:00:00','2026-10-24 00:00:00',2,5,NULL,36800.00,10000.00,40000.00,0,600.00,0.00,'Completed','2026-10-14 07:30:58','2026-10-15 07:30:58',NULL,NULL,'2026-10-13 23:29:52','2026-10-13 23:58:24','MCTBS-000006',NULL,NULL,NULL,NULL,'Per Unit'),(7,4,NULL,NULL,4,'2026-10-30 00:00:00','2026-10-31 00:00:00',10,10,NULL,9600.00,10000.00,10000.00,0,600.00,0.00,'Completed','2026-10-15 01:12:27',NULL,NULL,NULL,'2026-10-14 16:38:28','2026-10-14 17:24:47','MCTBS-000007',NULL,NULL,NULL,NULL,'Per Unit'),(8,4,NULL,NULL,3,'2026-10-31 00:00:00','2026-11-02 00:00:00',4,0,NULL,10000.00,5000.00,10000.00,0,600.00,0.00,'Cancelled','2026-10-15 00:47:34',NULL,'Cancelled by guest: ayaw q na',NULL,'2026-10-14 16:41:42','2026-10-23 21:46:08','MCTBS-000008',NULL,NULL,NULL,NULL,'Per Unit'),(9,8,NULL,NULL,3,'2026-11-02 00:00:00','2026-11-06 00:00:00',2,0,NULL,20000.00,5000.00,20000.00,0,600.00,0.00,'Completed','2026-10-15 01:32:53',NULL,NULL,NULL,'2026-10-14 17:18:36','2026-10-23 18:16:32','MCTBS-000009',NULL,NULL,NULL,NULL,'Per Unit'),(10,8,NULL,NULL,4,'2026-11-02 00:00:00','2026-11-06 00:00:00',1,0,NULL,32000.00,10000.00,40000.00,0,600.00,0.00,'Checked-in','2026-10-15 01:23:12',NULL,NULL,NULL,'2026-10-14 17:20:23','2026-10-25 02:55:35','MCTBS-000010',NULL,NULL,'2026-10-25 10:50:40',NULL,'Per Unit'),(11,8,NULL,NULL,5,'2026-10-17 00:00:00','2026-10-18 00:00:00',2,0,NULL,8100.00,9000.00,9000.00,0,600.00,0.00,'Completed','2026-10-15 02:05:36',NULL,NULL,NULL,'2026-10-14 18:01:43','2026-10-14 18:38:09','MCTBS-000011',NULL,NULL,NULL,NULL,'Per Unit'),(12,8,NULL,NULL,5,'2026-10-24 00:00:00','2026-10-25 00:00:00',11,0,NULL,9425.45,9000.00,9000.00,1,600.00,600.00,'Completed','2026-10-15 03:16:20',NULL,NULL,NULL,'2026-10-14 19:15:07','2026-10-14 19:48:14','MCTBS-000012',NULL,NULL,NULL,NULL,'Per Unit'),(13,4,NULL,NULL,6,'2026-10-28 00:00:00','2026-10-31 00:00:00',10,0,NULL,30000.00,10000.00,30000.00,0,600.00,0.00,'Pending',NULL,NULL,NULL,NULL,'2026-10-23 21:00:55','2026-10-23 21:00:55',NULL,NULL,NULL,NULL,NULL,'Per Unit'),(14,4,NULL,NULL,3,'2026-11-07 00:00:00','2026-11-10 00:00:00',10,0,NULL,15000.00,5000.00,15000.00,0,600.00,0.00,'Confirmed','2026-10-24 05:10:34',NULL,NULL,NULL,'2026-10-23 21:09:17','2026-10-23 21:28:53','MCTBS-000014',NULL,NULL,NULL,NULL,'Per Unit'),(15,4,NULL,NULL,4,'2026-10-25 00:00:00','2026-10-26 00:00:00',10,0,NULL,9800.00,10000.00,10000.00,0,600.00,0.00,'Checked-out','2026-10-24 06:09:32',NULL,NULL,NULL,'2026-10-23 22:09:22','2026-10-23 22:57:04','MCTBS-000015','71e79930a615520d292de00ce83fb2203180372d00988fe013beff0a2f395ed5','2026-10-30 22:23:11','2026-10-24 06:20:08','2026-10-24 06:57:04','Per Unit'),(16,4,NULL,NULL,4,'2026-11-07 00:00:00','2026-11-09 00:00:00',12,0,NULL,22026.67,10000.00,20000.00,2,600.00,2400.00,'Checked-out','2026-10-24 09:31:24',NULL,NULL,NULL,'2026-10-24 01:26:22','2026-10-24 02:36:14','MCTBS-000016','2ff035de888161b2c8a0be01af2aab30eab1afeb2130b55b2bc9781cfa071bfd','2026-10-31 02:09:34','2026-10-24 10:08:45','2026-10-24 10:36:14','Per Unit'),(17,4,NULL,NULL,7,'2026-10-25 00:00:00','2026-10-27 00:00:00',3,0,NULL,4600.00,2000.00,4000.00,1,300.00,600.00,'Checked-out','2026-10-24 11:14:02',NULL,NULL,NULL,'2026-10-24 03:12:04','2026-10-25 02:15:50','MCTBS-000017',NULL,NULL,'2026-10-25 10:14:53','2026-10-25 10:15:50','Per Unit'),(18,4,NULL,NULL,4,'2026-11-14 00:00:00','2026-11-16 00:00:00',13,0,NULL,23236.92,10000.00,20000.00,3,600.00,3600.00,'Checked-out','2026-10-25 10:16:48',NULL,NULL,NULL,'2026-10-25 02:16:36','2026-10-25 10:26:49','MCTBS-000018','5c9b0fe8c5c59f77ffd28717426d355311eb8c62d2946d9e5ce8415e6dd14492','2026-11-01 10:25:38','2026-10-25 10:50:12','2026-10-25 18:26:49','Per Unit'),(19,NULL,'09192169029',NULL,4,'2026-11-18 00:00:00','2026-11-20 00:00:00',10,2,NULL,20000.00,10000.00,20000.00,0,600.00,0.00,'Confirmed',NULL,NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53',NULL,NULL,NULL,NULL,NULL,'Per Unit'),(20,4,NULL,NULL,1,'2026-11-04 00:00:00','2026-11-07 00:00:00',2,0,NULL,4500.00,1500.00,4500.00,0,0.00,0.00,'Checked-out','2026-11-03 16:37:23',NULL,NULL,NULL,'2026-11-03 08:36:58','2026-11-03 09:15:36','MCTBS-000020',NULL,NULL,'2026-11-03 17:14:30','2026-11-03 17:15:36','Per Unit'),(21,10,NULL,NULL,9,'2026-11-04 00:00:00','2026-11-07 00:00:00',3,0,NULL,5040.00,600.00,5400.00,0,0.00,0.00,'Checked-in','2026-11-03 17:45:08',NULL,NULL,NULL,'2026-11-03 09:44:45','2026-11-03 09:48:21','MCTBS-000021','da0e672e584267ef1dffaeb62f87a1582327ed9d9d9791318eb796a78936b437','2026-11-10 09:48:21','2026-11-03 17:47:07',NULL,'Per Head'),(22,NULL,'0921212121',NULL,9,'2026-11-08 00:00:00','2026-11-09 00:00:00',2,0,NULL,1200.00,600.00,1200.00,0,0.00,0.00,'Cancelled',NULL,NULL,'Scam People!!!!',NULL,'2026-11-03 09:52:08','2026-11-03 09:52:20',NULL,NULL,NULL,NULL,NULL,'Per Head'),(23,13,NULL,NULL,10,'2026-11-04 00:00:00','2026-11-07 00:00:00',15,0,NULL,54000.00,15000.00,45000.00,5,600.00,9000.00,'Rejected',NULL,NULL,NULL,'TRY - BCOS YOU ARE SCAMMER WE JUST KNOW IT!!!','2026-11-03 10:13:14','2026-11-03 10:14:27',NULL,NULL,NULL,NULL,NULL,'Per Unit'),(24,13,NULL,NULL,10,'2026-11-04 00:00:00','2026-11-07 00:00:00',15,0,NULL,54000.00,15000.00,45000.00,5,600.00,9000.00,'Cancelled','2026-11-03 18:16:08',NULL,'ops you are not allowed here!!',NULL,'2026-11-03 10:15:46','2026-11-03 10:16:28',NULL,NULL,NULL,NULL,NULL,'Per Unit'),(25,13,NULL,NULL,10,'2026-11-04 00:00:00','2026-11-07 00:00:00',15,0,NULL,54000.00,15000.00,45000.00,5,600.00,9000.00,'Cancelled','2026-11-03 18:17:48',NULL,'Cancelled by guest: i dont wanna go na pala...',NULL,'2026-11-03 10:17:38','2026-11-03 10:19:07','MCTBS-000025',NULL,NULL,NULL,NULL,'Per Unit'),(26,13,NULL,NULL,10,'2026-11-04 00:00:00','2026-11-07 00:00:00',15,0,NULL,53280.00,15000.00,45000.00,5,600.00,9000.00,'Checked-out','2026-11-03 18:24:39',NULL,NULL,NULL,'2026-11-03 10:24:27','2026-11-03 10:28:57','MCTBS-000026',NULL,NULL,'2026-11-03 18:25:38','2026-11-03 18:28:57','Per Unit'),(27,NULL,'0921212121',NULL,10,'2026-11-20 00:00:00','2026-11-21 00:00:00',12,0,NULL,16200.00,15000.00,15000.00,2,600.00,1200.00,'Checked-out',NULL,NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:36:46',NULL,NULL,NULL,'2026-11-03 18:36:07','2026-11-03 18:36:46','Per Unit'),(29,4,NULL,NULL,10,'2026-11-14 00:00:00','2026-11-16 00:00:00',15,0,NULL,35040.00,15000.00,30000.00,5,600.00,6000.00,'Checked-in','2026-11-11 21:42:21',NULL,NULL,NULL,'2026-11-11 13:41:42','2026-11-11 14:18:16','MCTBS-000029','1747d12a7575481b46c301625448b22888f628669715c3d3ae75df6610c3da26','2026-11-18 14:18:16','2026-11-11 21:47:50',NULL,'Per Unit'),(30,NULL,'0921212121',NULL,10,'2026-11-21 00:00:00','2026-11-25 00:00:00',10,0,NULL,60000.00,15000.00,60000.00,0,600.00,0.00,'Checked-in',NULL,NULL,NULL,NULL,'2026-11-11 14:35:13','2026-11-11 14:37:02',NULL,NULL,NULL,'2026-11-11 22:37:02',NULL,'Per Unit');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversation_participants`
--

DROP TABLE IF EXISTS `conversation_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversation_participants` (
  `participant_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`participant_id`),
  UNIQUE KEY `conversation_participants_conversation_id_user_id_unique` (`conversation_id`,`user_id`),
  KEY `conversation_participants_user_id_foreign` (`user_id`),
  CONSTRAINT `conversation_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE,
  CONSTRAINT `conversation_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversation_participants`
--

LOCK TABLES `conversation_participants` WRITE;
/*!40000 ALTER TABLE `conversation_participants` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversation_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversations` (
  `conversation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`conversation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversations`
--

LOCK TABLES `conversations` WRITE;
/*!40000 ALTER TABLE `conversations` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `damage_reports`
--

DROP TABLE IF EXISTS `damage_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `damage_reports` (
  `damage_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `unit_id` int(10) unsigned NOT NULL,
  `unit_damage_item_id` int(10) unsigned DEFAULT NULL,
  `reported_by` int(10) unsigned NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `status` varchar(30) NOT NULL,
  `approval_status` varchar(30) DEFAULT NULL,
  `approval_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`approval_history`)),
  `reported_at` datetime NOT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `resolved_by` int(10) unsigned DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  PRIMARY KEY (`damage_id`),
  KEY `damage_reports_booking_id_foreign` (`booking_id`),
  KEY `damage_reports_unit_id_foreign` (`unit_id`),
  KEY `damage_reports_reported_by_foreign` (`reported_by`),
  KEY `damage_reports_unit_damage_item_id_foreign` (`unit_damage_item_id`),
  KEY `damage_reports_resolved_by_foreign` (`resolved_by`),
  CONSTRAINT `damage_reports_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`),
  CONSTRAINT `damage_reports_reported_by_foreign` FOREIGN KEY (`reported_by`) REFERENCES `users` (`user_id`),
  CONSTRAINT `damage_reports_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `damage_reports_unit_damage_item_id_foreign` FOREIGN KEY (`unit_damage_item_id`) REFERENCES `unit_damage_items` (`unit_damage_item_id`) ON DELETE SET NULL,
  CONSTRAINT `damage_reports_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `damage_reports`
--

LOCK TABLES `damage_reports` WRITE;
/*!40000 ALTER TABLE `damage_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `damage_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_verifications`
--

DROP TABLE IF EXISTS `email_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_verifications` (
  `verification_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`verification_id`),
  UNIQUE KEY `email_verifications_token_unique` (`token`),
  KEY `email_verifications_user_id_foreign` (`user_id`),
  CONSTRAINT `email_verifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_verifications`
--

LOCK TABLES `email_verifications` WRITE;
/*!40000 ALTER TABLE `email_verifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_verifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `feedback_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `unit_id` int(10) unsigned NOT NULL,
  `rating` int(11) NOT NULL,
  `review` text DEFAULT NULL,
  `status` varchar(30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`feedback_id`),
  UNIQUE KEY `feedback_booking_id_unique` (`booking_id`),
  KEY `feedback_user_id_foreign` (`user_id`),
  KEY `feedback_unit_id_foreign` (`unit_id`),
  CONSTRAINT `feedback_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`),
  CONSTRAINT `feedback_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`),
  CONSTRAINT `feedback_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `house_amenities`
--

DROP TABLE IF EXISTS `house_amenities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `house_amenities` (
  `house_amenity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `house_id` int(10) unsigned NOT NULL,
  `amenity_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`house_amenity_id`),
  UNIQUE KEY `house_amenities_house_id_amenity_id_unique` (`house_id`,`amenity_id`),
  KEY `house_amenities_amenity_id_foreign` (`amenity_id`),
  CONSTRAINT `house_amenities_amenity_id_foreign` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`amenity_id`) ON DELETE CASCADE,
  CONSTRAINT `house_amenities_house_id_foreign` FOREIGN KEY (`house_id`) REFERENCES `transient_houses` (`house_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `house_amenities`
--

LOCK TABLES `house_amenities` WRITE;
/*!40000 ALTER TABLE `house_amenities` DISABLE KEYS */;
/*!40000 ALTER TABLE `house_amenities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invitations`
--

DROP TABLE IF EXISTS `invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invitations` (
  `invitation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invited_by` int(10) unsigned NOT NULL,
  `house_id` int(10) unsigned DEFAULT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `email` varchar(191) NOT NULL,
  `token` varchar(255) NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reminder_sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`invitation_id`),
  UNIQUE KEY `invitations_token_unique` (`token`),
  KEY `invitations_invited_by_foreign` (`invited_by`),
  KEY `invitations_house_id_foreign` (`house_id`),
  KEY `invitations_role_id_foreign` (`role_id`),
  CONSTRAINT `invitations_house_id_foreign` FOREIGN KEY (`house_id`) REFERENCES `transient_houses` (`house_id`) ON DELETE SET NULL,
  CONSTRAINT `invitations_invited_by_foreign` FOREIGN KEY (`invited_by`) REFERENCES `users` (`user_id`),
  CONSTRAINT `invitations_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invitations`
--

LOCK TABLES `invitations` WRITE;
/*!40000 ALTER TABLE `invitations` DISABLE KEYS */;
INSERT INTO `invitations` VALUES (1,2,1,3,'staff@test.com','5b5f828a-a300-4bf6-ba15-5c3d66a11979','2026-10-09 03:02:32','2026-10-15 19:02:32',NULL),(2,1,NULL,2,'owner2@gmail.com','u6xCMfuj3kfa05ugvrGCBhtE488OFYuUGCIc7MCzQnaMw5zPERXnFr5bvQwaJl71','2026-10-12 07:19:29','2026-10-12 07:19:29',NULL),(3,1,NULL,2,'karlangelomarteja2006@gmail.com','Y2O1pEZTB8FoCavNYN8udWygriXw9LnrzufKy4Q7ADkdYWwtemyVqoLpPYI8I7Eb','2026-10-14 06:58:11','2026-10-14 06:58:11',NULL),(4,1,NULL,2,'MTH@gmail.com','uYHVxjR9DkhypolTO3gt0UKGuk0u1GqFZ1unjCTrohcnw13D98Ya8Jy7MKi21Gua','2026-10-15 01:37:38','2026-10-15 01:37:38',NULL),(5,1,NULL,2,'cortezjayjayme@gmail.com','jCSUfCzsBjQ3KPsrfk3ZIS2i6r5YE4DHqNC6SRTjZdV4zIjMCUUFsrAGOyUf6ssw',NULL,'2026-10-15 18:20:43',NULL),(6,1,NULL,2,'owner67@gmail.com','TkGJnmj9tW6D6ZOAbo3IbFkXvuuSn5qfAmULSHO5bjP95YWJHhuGTkeFyNqUaiOI','2026-11-03 17:38:27','2026-11-03 09:38:27',NULL),(7,11,6,3,'staff1@gmail.com','KBpOSE6rkkfqyhO98EqMbWa7eh9psZgOEjtEwHIzObtW6jUiG4UxJ8PGfM51BcYl','2026-11-03 17:55:18','2026-11-03 09:55:18',NULL),(8,2,7,3,'staffexample@gmail.com','YGqni4jyxEg8YNyvsU7sKcli04YTYgVCFmzMLU7VyFqXxQwA24clZ8MTAXmQTyHs','2026-11-03 18:38:03','2026-11-03 10:38:03',NULL);
/*!40000 ALTER TABLE `invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `message_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` int(10) unsigned NOT NULL,
  `sender_id` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`message_id`),
  KEY `messages_conversation_id_foreign` (`conversation_id`),
  KEY `messages_sender_id_foreign` (`sender_id`),
  CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_10_08_133824_create_mctbs_schema',1),(2,'2026_10_09_021057_create_unit_photos_table',1),(3,'2026_10_09_103222_add_rejection_reason_to_bookings_table',2),(4,'2026_10_09_105555_create_booking_edits_table',2),(5,'2026_10_09_105555_create_booking_receipts_table',2),(6,'2026_10_12_045124_add_acceptance_deadline_to_bookings_table',3),(7,'2026_10_12_055128_add_child_free_age_policy_to_units_and_booking_guests',4),(8,'2026_10_12_074800_add_over_capacity_pricing_and_unit_rentals',5),(9,'2026_10_12_134607_add_rental_changes_to_booking_edits_table',6),(10,'2026_10_14_041450_add_reference_number_key_to_payments',7),(11,'2026_10_14_041450_add_social_links_to_transient_houses',7),(12,'2026_10_14_094558_add_normalized_reference_key_to_booking_discounts_table',8),(13,'2026_10_14_102956_add_unique_booking_id_to_feedback_table',9),(14,'2026_10_14_112313_add_reminder_sent_at_to_invitations_table',9),(15,'2026_10_24_030037_revise_bookings_and_add_unit_addon_tables',10),(16,'2026_10_24_042128_add_unique_feedback_token_index_to_bookings_table',11),(17,'2026_10_24_055214_remove_stale_cancelled_booking_calendar_holds',12),(18,'2026_10_24_122042_add_unit_pricing_and_check_times_and_booking_pricing_mode',12),(19,'2026_10_25_100707_add_verification_note_to_booking_discounts_table',13),(20,'2026_10_25_103319_add_walk_in_guest_support_to_bookings_and_reviews_table',13),(21,'2026_10_25_110609_add_registered_at_to_users_table',14),(22,'2026_11_10_231029_add_roster_membership_to_booking_guests_table',15),(23,'2026_11_10_215214_add_guest_lifecycle_fields_to_booking_guests_and_bookings_table',16),(24,'2026_11_10_215215_create_booking_guest_events_table',16),(25,'2026_11_11_200236_add_approval_workflow_to_damage_reports_table',16);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `notification_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `booking_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  KEY `notifications_booking_id_foreign` (`booking_id`),
  CONSTRAINT `notifications_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL,
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=245 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,2,1,'new_booking','New Booking Request','Test Guest requested booking #1 for Room 1.',1,'2026-10-09 03:15:55','2026-10-08 19:57:54'),(2,3,1,'new_booking','New Booking Request','Test Guest requested booking #1 for Room 1.',0,'2026-10-09 03:15:55',NULL),(3,4,1,'booking_accepted','Booking request accepted','Submit at least 50% of the booking total by Oct 10, 2026 3:58 AM (within 24 hours). Your booking is confirmed after payment is verified.',1,'2026-10-08 19:58:10','2026-10-09 02:10:22'),(4,2,1,'booking_accepted','Booking accepted; payment pending','Booking #1 was accepted. Payment is due within 24 hours.',1,'2026-10-08 19:58:10','2026-10-09 02:09:23'),(5,3,1,'booking_accepted','Booking accepted; payment pending','Booking #1 was accepted. Payment is due within 24 hours.',0,'2026-10-08 19:58:10',NULL),(6,4,2,'booking_submitted','Booking request submitted','Your request for Family Room is pending Owner/Staff review. You are the first request for these dates.',1,'2026-10-09 03:14:15','2026-10-12 06:51:20'),(7,2,2,'booking_requested','New booking request','Test Guest requested Family Room. You are the first request for these dates.',1,'2026-10-09 03:14:15','2026-10-11 21:08:10'),(8,3,2,'booking_requested','New booking request','Test Guest requested Family Room. You are the first request for these dates.',0,'2026-10-09 03:14:15',NULL),(9,4,2,'booking_accepted','Booking request accepted','Submit at least 50% of the booking total by Oct 10, 2026 11:15 AM (within 24 hours). Your booking is confirmed after payment is verified.',1,'2026-10-09 03:15:37','2026-10-09 03:16:03'),(10,2,2,'booking_accepted','Booking accepted; payment pending','Booking #2 was accepted. Payment is due within 24 hours.',1,'2026-10-09 03:15:37','2026-10-11 21:08:10'),(11,3,2,'booking_accepted','Booking accepted; payment pending','Booking #2 was accepted. Payment is due within 24 hours.',0,'2026-10-09 03:15:37',NULL),(12,4,2,'booking_cancelled','Booking cancelled by Owner/Staff','Your booking was cancelled. Reason: maintenance',1,'2026-10-09 03:21:37','2026-10-12 06:51:20'),(13,2,2,'booking_cancelled','Booking cancelled','Booking #2 was cancelled by Test Owner.',1,'2026-10-09 03:21:37','2026-10-11 21:08:10'),(14,3,2,'booking_cancelled','Booking cancelled','Booking #2 was cancelled by Test Owner.',0,'2026-10-09 03:21:37',NULL),(15,4,1,'booking_payment_expired','Booking hold expired','The 24-hour payment window ended without a valid payment submission. The request was cancelled.',1,'2026-10-09 21:03:37','2026-10-12 06:51:20'),(16,2,1,'booking_payment_expired','Booking hold expired','Booking #1 expired. The next overlapping request can now be reviewed.',1,'2026-10-09 21:03:37','2026-10-11 21:08:10'),(17,3,1,'booking_payment_expired','Booking hold expired','Booking #1 expired. The next overlapping request can now be reviewed.',0,'2026-10-09 21:03:37',NULL),(18,4,3,'booking_submitted','Booking request submitted','Your request for Delux is pending Owner/Staff review. You are the first request for these dates.',1,'2026-10-12 01:02:40','2026-10-12 06:51:20'),(19,6,3,'booking_requested','New booking request','Test Guest requested Delux. You are the first request for these dates.',1,'2026-10-12 01:02:40','2026-10-12 01:04:31'),(20,4,3,'booking_accepted','Booking request accepted','Submit at least 50% of the booking total by Oct 13, 2026 9:10 AM (within 24 hours). Your booking is confirmed after payment is verified.',1,'2026-10-12 01:10:26','2026-10-12 06:51:20'),(21,6,3,'booking_accepted','Booking accepted; payment pending','Booking #3 was accepted. Payment is due within 24 hours.',1,'2026-10-12 01:10:26','2026-10-12 01:11:20'),(22,6,3,'payment_submitted','Payment submitted for review','Booking #3 submitted a Bank Transfer payment of ₱5,000.00.',1,'2026-10-12 04:49:48','2026-10-12 04:50:24'),(23,4,3,'payment_under_review','Payment under review','Your payment was submitted. The Owner/Staff will notify you after checking it.',1,'2026-10-12 04:49:48','2026-10-12 06:51:20'),(24,4,3,'booking_confirmed','Booking confirmed','Your payment was verified and booking #3 is confirmed.',1,'2026-10-12 04:51:35','2026-10-12 04:52:03'),(25,6,3,'booking_confirmed','Booking confirmed','Payment for booking #3 was verified; the calendar dates are now reserved.',1,'2026-10-12 04:51:35','2026-10-12 06:51:29'),(26,6,3,'payment_submitted','Payment submitted for review','Booking #3 submitted a GCash payment of ₱5,000.00.',1,'2026-10-12 06:15:32','2026-10-12 06:51:29'),(27,4,3,'payment_under_review','Payment under review','Your payment was submitted. The Owner/Staff will notify you after checking it.',1,'2026-10-12 06:15:32','2026-10-12 06:51:20'),(28,4,3,'discount_Verified','Discount ID verified','The Senior Citizen ID for Senior Citizen 1 was verified at checkout.',1,'2026-10-12 06:16:14','2026-10-12 06:51:20'),(29,4,3,'discount_Verified','Discount ID verified','The Senior Citizen ID for Senior Citizen 2 was verified at checkout.',1,'2026-10-12 06:16:17','2026-10-12 06:51:20'),(30,4,3,'discount_Verified','Discount ID verified','The Senior Citizen ID for Senior Citizen 3 was verified at checkout.',1,'2026-10-12 06:16:19','2026-10-12 06:51:20'),(31,4,3,'discount_Verified','Discount ID verified','The Senior Citizen ID for Senior Citizen 4 was verified at checkout.',1,'2026-10-12 06:16:22','2026-10-12 06:28:55'),(32,4,3,'payment_verified','Payment verified','Payment review result: Verified.',1,'2026-10-12 06:47:07','2026-10-12 06:51:20'),(33,6,3,'payment_reviewed','Payment review updated','Payment for booking #3 was marked Verified by Owner Ako.',1,'2026-10-12 06:47:07','2026-10-12 06:51:29'),(34,6,3,'payment_submitted','Payment submitted for review','Booking #3 submitted a GCash payment of ₱1,199.80.',1,'2026-10-12 06:49:29','2026-10-12 06:51:29'),(35,4,3,'payment_under_review','Payment under review','Your payment was submitted. The Owner/Staff will notify you after checking it.',1,'2026-10-12 06:49:29','2026-10-12 06:51:20'),(36,4,3,'payment_verified','Payment verified','Payment review result: Verified.',1,'2026-10-12 06:49:40','2026-10-12 06:51:20'),(37,6,3,'payment_reviewed','Payment review updated','Payment for booking #3 was marked Verified by Owner Ako.',1,'2026-10-12 06:49:40','2026-10-12 06:51:29'),(38,4,3,'booking_completed','Booking completed','Your stay at Delux is complete. Your receipt is ready.',1,'2026-10-12 06:49:51','2026-10-12 06:51:20'),(39,4,4,'booking_submitted','Booking request submitted','Your request for Delux is pending Owner/Staff review. You are the first request for these dates.',1,'2026-10-13 19:03:12','2026-10-13 19:12:05'),(40,6,4,'booking_requested','New booking request','Test Guest requested Delux. You are the first request for these dates.',1,'2026-10-13 19:03:12','2026-10-13 20:32:35'),(41,4,4,'booking_accepted','Booking request accepted','Submit at least 50% of the booking total, including selected rentals, by Oct 15, 2026 3:15 AM (within 24 hours). Your booking is confirmed after payment is verified.',1,'2026-10-13 19:15:00','2026-10-14 03:19:29'),(42,6,4,'booking_accepted','Booking accepted; payment pending','Booking #4 was accepted. Payment is due within 24 hours.',1,'2026-10-13 19:15:00','2026-10-13 19:23:56'),(43,6,4,'payment_submitted','Payment submitted for review','Booking #4 submitted a GCash payment of ₱25,000.00.',1,'2026-10-13 19:22:26','2026-10-13 20:32:35'),(44,4,4,'payment_under_review','Payment under review','Your payment was submitted. The Owner/Staff will notify you after checking it.',1,'2026-10-13 19:22:26','2026-10-14 03:19:29'),(45,4,4,'booking_confirmed','Booking confirmed','Your payment was verified and booking #4 is confirmed.',1,'2026-10-13 19:26:29','2026-10-13 19:27:35'),(46,6,4,'booking_confirmed','Booking confirmed','Payment for booking #4 was verified; the calendar dates are now reserved.',1,'2026-10-13 19:26:29','2026-10-13 20:32:35'),(47,4,4,'booking_updated','Booking updated','Booking #4 was updated by Owner/Staff. The revised total is ₱43,500.00. Reason: Updated the dates and added a sleeping set',1,'2026-10-13 19:31:15','2026-10-13 19:31:59'),(48,6,4,'payment_submitted','Payment submitted for review','Booking #4 submitted a GCash payment of ₱18,500.00.',1,'2026-10-13 19:35:34','2026-10-13 19:36:07'),(49,4,4,'payment_under_review','Payment under review','Your payment was submitted. The Owner/Staff will notify you after checking it.',1,'2026-10-13 19:35:34','2026-10-14 03:19:29'),(50,4,4,'payment_verified','Payment verified','Payment review result: Verified.',1,'2026-10-13 19:36:16','2026-10-14 03:19:29'),(51,6,4,'payment_reviewed','Payment review updated','Payment for booking #4 was marked Verified by Owner Ako.',1,'2026-10-13 19:36:16','2026-10-13 20:32:35'),(52,4,4,'checkout_payment_recorded','Checkout payment recorded','A Cash payment of ₱5,000.00 was recorded for booking #4.',1,'2026-10-13 19:39:22','2026-10-14 03:19:29'),(53,4,4,'booking_completed','Booking completed','Your stay at Delux is complete. Your receipt is ready.',1,'2026-10-13 19:39:29','2026-10-14 03:19:29'),(54,4,5,'booking_submitted','Booking request submitted','Your request for Delux is pending Owner/Staff review. You are the first request for these dates.',1,'2026-10-13 20:36:13','2026-10-14 03:19:29'),(55,6,5,'booking_requested','New booking request','Test Guest requested Delux. You are the first request for these dates.',1,'2026-10-13 20:36:13','2026-10-14 00:50:59'),(56,4,5,'booking_accepted','Booking request accepted','Submit at least 50% of the booking total, including selected rentals, by Oct 15, 2026 6:23 AM (within 24 hours). Your booking is confirmed after payment is verified.',1,'2026-10-13 22:23:18','2026-10-14 03:19:29'),(57,6,5,'booking_accepted','Booking accepted; payment pending','Booking #5 was accepted. Payment is due within 24 hours.',1,'2026-10-13 22:23:18','2026-10-14 00:50:59'),(58,4,5,'booking_updated','Booking updated','Booking #5 was updated by Owner/Staff. The revised total is ₱9,000.00. Reason: added 4 sets of sleeping set',1,'2026-10-13 22:33:54','2026-10-14 03:19:29'),(59,4,6,'booking_submitted','Booking request submitted','Your request for Big Room is pending Owner/Staff review. You are the first request for these dates.',1,'2026-10-13 23:29:53','2026-10-14 03:19:29'),(60,7,6,'booking_requested','New booking request','Test Guest requested Big Room. You are the first request for these dates.',1,'2026-10-13 23:29:53','2026-10-13 23:30:28'),(61,4,6,'booking_accepted','Booking request accepted','Submit at least 50% of the booking total, including selected rentals, by Oct 15, 2026 7:30 AM (within 24 hours). Your booking is confirmed after payment is verified.',1,'2026-10-13 23:30:58','2026-10-14 03:19:29'),(62,7,6,'booking_accepted','Booking accepted; payment pending','Booking #6 was accepted. Payment is due within 24 hours.',1,'2026-10-13 23:30:58','2026-10-23 22:08:44'),(63,7,6,'payment_submitted','Payment submitted for review','Booking #6 submitted a GCash payment of ₱21,500.00.',1,'2026-10-13 23:41:14','2026-10-13 23:41:34'),(64,4,6,'payment_under_review','Payment under review','Your payment was submitted. The Owner/Staff will notify you after checking it.',1,'2026-10-13 23:41:14','2026-10-14 03:19:29'),(65,4,6,'booking_confirmed','Booking confirmed','Your payment was verified and booking #6 is confirmed.',1,'2026-10-13 23:42:05','2026-10-14 03:19:29'),(66,7,6,'booking_confirmed','Booking confirmed','Payment for booking #6 was verified; the calendar dates are now reserved.',1,'2026-10-13 23:42:05','2026-10-23 22:08:44'),(67,4,6,'discount_Verified','Discount ID verified','The Senior Citizen ID for Senior Citizen 1 was verified at checkout.',1,'2026-10-13 23:52:05','2026-10-14 03:19:29'),(68,4,6,'discount_Verified','Discount ID verified','The PWD ID for PWD 1 was verified at checkout.',1,'2026-10-13 23:52:09','2026-10-14 03:19:29'),(69,4,6,'checkout_payment_recorded','Checkout payment recorded','A Cash payment of ₱18,300.00 was recorded for booking #6.',1,'2026-10-13 23:58:06','2026-10-14 03:19:29'),(70,4,6,'booking_completed','Booking completed','Your stay at Big Room is complete. Your receipt is ready.',1,'2026-10-13 23:58:24','2026-10-14 03:19:29'),(71,4,7,'booking_submitted','Booking request submitted','Your request for Big Room is pending Owner/Staff review. You are the first request for these dates.',1,'2026-10-14 16:38:28','2026-10-23 16:50:34'),(72,7,7,'booking_requested','New booking request','Test Guest requested Big Room. You are the first request for these dates.',1,'2026-10-14 16:38:28','2026-10-23 22:08:44'),(73,4,8,'booking_submitted','Booking request submitted','Your request for Delux is pending Owner/Staff review. You are the first request for these dates.',1,'2026-10-14 16:41:42','2026-10-23 16:50:34'),(74,6,8,'booking_requested','New booking request','Test Guest requested Delux. You are the first request for these dates.',1,'2026-10-14 16:41:42','2026-10-14 16:47:28'),(75,4,8,'booking_confirmed','Booking confirmed','Your booking #8 is confirmed and the dates are reserved. No payment is due until checkout.',1,'2026-10-14 16:47:34','2026-10-23 16:50:34'),(76,6,8,'booking_confirmed','Booking confirmed','Booking #8 was accepted and its dates are now reserved.',1,'2026-10-14 16:47:34','2026-10-23 16:52:28'),(77,4,7,'booking_accepted','Booking accepted — downpayment required','Your booking #7 was accepted. Submit the ₱6,000.00 downpayment to confirm your reservation.',1,'2026-10-14 17:12:27','2026-10-23 16:50:34'),(78,7,7,'booking_accepted','Booking accepted','Booking #7 was accepted and its dates are reserved pending the guest\'s downpayment.',1,'2026-10-14 17:12:27','2026-10-23 22:08:44'),(79,8,9,'booking_submitted','Booking request submitted','Your request for Delux is pending Owner/Staff review. You are the first request for these dates.',1,'2026-10-14 17:18:36','2026-10-14 18:02:53'),(80,6,9,'booking_requested','New booking request','Juan Chu requested Delux. You are the first request for these dates.',1,'2026-10-14 17:18:36','2026-10-23 16:52:28'),(81,8,10,'booking_submitted','Booking request submitted','Your request for Big Room is pending Owner/Staff review. You are the first request for these dates.',1,'2026-10-14 17:20:23','2026-10-14 18:02:53'),(82,7,10,'booking_requested','New booking request','Juan Chu requested Big Room. You are the first request for these dates.',1,'2026-10-14 17:20:23','2026-10-14 17:23:09'),(83,7,7,'payment_submitted','Payment submitted','A payment of ₱6,000.00 was submitted for booking #7 and is awaiting review.',1,'2026-10-14 17:22:27','2026-10-14 17:22:35'),(84,4,7,'booking_confirmed','Booking confirmed','The payment for booking #7 was verified. The dates are confirmed and any remaining balance is due at checkout.',1,'2026-10-14 17:22:46','2026-10-23 16:50:34'),(85,7,7,'booking_confirmed','Booking confirmed','The downpayment for booking #7 was verified and the booking is confirmed.',1,'2026-10-14 17:22:46','2026-10-14 17:23:26'),(86,8,10,'booking_accepted','Booking accepted — downpayment required','Your booking #10 was accepted. Submit the ₱20,000.00 downpayment to confirm your reservation.',1,'2026-10-14 17:23:12','2026-10-14 18:02:53'),(87,7,10,'booking_accepted','Booking accepted','Booking #10 was accepted and its dates are reserved pending the guest\'s downpayment.',1,'2026-10-14 17:23:12','2026-10-23 22:08:44'),(88,4,7,'discount_Verified','Discount ID verified','The PWD ID for PWD 1 was verified at checkout.',1,'2026-10-14 17:24:15','2026-10-23 16:50:34'),(89,4,7,'discount_Verified','Discount ID verified','The Senior Citizen ID for Senior Citizen 1 was verified at checkout.',1,'2026-10-14 17:24:18','2026-10-23 16:50:34'),(90,7,10,'payment_submitted','Payment submitted','A payment of ₱20,000.00 was submitted for booking #10 and is awaiting review.',1,'2026-10-14 17:24:23','2026-10-14 17:25:16'),(91,4,7,'checkout_payment_recorded','Checkout payment recorded','A Cash payment of ₱5,600.00 was recorded for booking #7.',1,'2026-10-14 17:24:34','2026-10-23 16:50:34'),(92,4,7,'booking_completed','Booking completed','Your stay at Big Room is complete. Your receipt is ready.',1,'2026-10-14 17:24:47','2026-10-23 16:50:34'),(93,8,10,'booking_confirmed','Booking confirmed','The payment for booking #10 was verified. The dates are confirmed and any remaining balance is due at checkout.',1,'2026-10-14 17:25:41','2026-10-14 17:25:55'),(94,7,10,'booking_confirmed','Booking confirmed','The downpayment for booking #10 was verified and the booking is confirmed.',1,'2026-10-14 17:25:41','2026-10-14 17:32:15'),(95,8,9,'booking_accepted','Booking accepted — downpayment required','Your booking #9 was accepted. Submit the ₱10,500.00 downpayment to confirm your reservation.',1,'2026-10-14 17:32:53','2026-10-14 18:02:53'),(96,6,9,'booking_accepted','Booking accepted','Booking #9 was accepted and its dates are reserved pending the guest\'s downpayment.',1,'2026-10-14 17:32:53','2026-10-23 16:52:28'),(97,6,9,'payment_submitted','Payment submitted','A payment of ₱10,500.00 was submitted for booking #9 and is awaiting review.',1,'2026-10-14 17:36:16','2026-10-14 17:36:47'),(98,8,9,'booking_confirmed','Booking confirmed','The payment for booking #9 was verified. The dates are confirmed and any remaining balance is due at checkout.',1,'2026-10-14 17:36:51','2026-10-14 18:02:53'),(99,6,9,'booking_confirmed','Booking confirmed','The downpayment for booking #9 was verified and the booking is confirmed.',1,'2026-10-14 17:36:51','2026-10-23 16:52:28'),(100,8,11,'booking_submitted','Booking request submitted','Your request for Family Room is pending Owner/Staff review. You are the first request for these dates.',1,'2026-10-14 18:01:43','2026-10-14 18:02:53'),(101,9,11,'booking_requested','New booking request','Juan Chu requested Family Room. You are the first request for these dates.',0,'2026-10-14 18:01:43',NULL),(102,8,11,'booking_accepted','Booking accepted — downpayment required','Your booking #11 was accepted. Submit the ₱5,000.00 downpayment to confirm your reservation.',1,'2026-10-14 18:05:36','2026-10-14 18:14:23'),(103,9,11,'booking_accepted','Booking accepted','Booking #11 was accepted and its dates are reserved pending the guest\'s downpayment.',0,'2026-10-14 18:05:36',NULL),(104,9,11,'payment_submitted','Payment submitted','A payment of ₱10,000.00 was submitted for booking #11 and is awaiting review.',1,'2026-10-14 18:08:59','2026-10-14 18:09:11'),(105,8,11,'booking_confirmed','Booking confirmed','The payment for booking #11 was verified. The dates are confirmed and any remaining balance is due at checkout.',1,'2026-10-14 18:09:40','2026-10-14 18:09:51'),(106,9,11,'booking_confirmed','Booking confirmed','The downpayment for booking #11 was verified and the booking is confirmed.',0,'2026-10-14 18:09:41',NULL),(107,8,11,'discount_Verified','Discount ID verified','The PWD ID for PWD 1 was verified at checkout.',1,'2026-10-14 18:11:18','2026-10-14 18:13:10'),(108,8,11,'booking_completed','Booking completed','Your stay at Family Room is complete. Your receipt is ready. A discount refund of ₱900.00 is pending.',1,'2026-10-14 18:38:09','2026-10-14 19:00:42'),(109,8,11,'refund_issued','Refund issued','A refund of ₱900.00 for booking #11 has been issued.',1,'2026-10-14 18:38:53','2026-10-14 19:00:42'),(110,8,12,'booking_submitted','Booking request submitted','Your request for Family Room is pending Owner/Staff review. You are the first request for these dates.',0,'2026-10-14 19:15:07',NULL),(111,9,12,'booking_requested','New booking request','Juan Chu requested Family Room. You are the first request for these dates.',0,'2026-10-14 19:15:07',NULL),(112,8,12,'booking_accepted','Booking accepted — downpayment required','Your booking #12 was accepted. Submit the ₱5,300.00 downpayment to confirm your reservation.',1,'2026-10-14 19:16:20','2026-10-14 19:16:32'),(113,9,12,'booking_accepted','Booking accepted','Booking #12 was accepted and its dates are reserved pending the guest\'s downpayment.',0,'2026-10-14 19:16:20',NULL),(114,9,12,'payment_submitted','Payment submitted','A payment of ₱5,300.00 was submitted for booking #12 and is awaiting review.',0,'2026-10-14 19:29:31',NULL),(115,8,12,'booking_confirmed','Booking confirmed','The payment for booking #12 was verified. The dates are confirmed and any remaining balance is due at checkout.',1,'2026-10-14 19:30:55','2026-10-14 19:31:08'),(116,9,12,'booking_confirmed','Booking confirmed','The downpayment for booking #12 was verified and the booking is confirmed.',0,'2026-10-14 19:30:55',NULL),(117,8,12,'discount_Verified','Discount ID verified','The PWD ID for PWD 1 was verified at checkout.',0,'2026-10-14 19:46:50',NULL),(118,8,12,'checkout_payment_recorded','Checkout payment recorded','A Cash payment of ₱5,125.45 was recorded for booking #12.',0,'2026-10-14 19:47:47',NULL),(119,8,12,'booking_completed','Booking completed','Your stay at Family Room is complete. Your receipt is ready.',1,'2026-10-14 19:48:14','2026-10-14 19:48:29'),(120,8,9,'checkout_payment_recorded','Checkout payment recorded','A Cash payment of ₱10,500.00 was recorded for booking #9.',0,'2026-10-23 18:16:23',NULL),(121,8,9,'booking_completed','Booking completed','Your stay at Delux is complete. Your receipt is ready.',0,'2026-10-23 18:16:32',NULL),(122,4,13,'booking_submitted','Booking request submitted','Your request for Delux is pending Owner/Staff review. You are the first request for these dates.',1,'2026-10-23 21:00:55','2026-10-23 23:45:45'),(123,9,13,'booking_requested','New booking request','Test Guest requested Delux. You are the first request for these dates.',0,'2026-10-23 21:00:55',NULL),(124,4,14,'booking_submitted','Booking request submitted','Your request for Delux is pending Owner/Staff review. You are the first request for these dates.',1,'2026-10-23 21:09:17','2026-10-23 21:10:42'),(125,6,14,'booking_requested','New booking request','Test Guest requested Delux. You are the first request for these dates.',1,'2026-10-23 21:09:17','2026-10-23 21:34:31'),(126,4,14,'booking_accepted','Booking accepted — downpayment required','Your booking #14 was accepted. Submit the ₱7,500.00 downpayment to confirm your reservation.',1,'2026-10-23 21:10:34','2026-10-23 23:45:45'),(127,6,14,'booking_accepted','Booking accepted','Booking #14 was accepted and its dates are reserved pending the guest\'s downpayment.',1,'2026-10-23 21:10:35','2026-10-23 21:34:31'),(128,6,14,'payment_submitted','Payment submitted','A payment of ₱7,500.00 was submitted for booking #14 and is awaiting review.',1,'2026-10-23 21:16:27','2026-10-23 21:34:31'),(129,4,14,'booking_confirmed','Booking confirmed','The payment for booking #14 was verified. The dates are confirmed and any remaining balance is due at checkout.',1,'2026-10-23 21:28:53','2026-10-23 23:45:45'),(130,6,14,'booking_confirmed','Booking confirmed','The downpayment for booking #14 was verified and the booking is confirmed.',1,'2026-10-23 21:28:53','2026-10-23 21:34:31'),(131,4,5,'booking_cancelled','Booking cancelled by Owner/Staff','Your booking was cancelled. Reason: no payment',1,'2026-10-23 21:45:06','2026-10-23 23:45:45'),(132,6,5,'booking_cancelled','Booking cancelled','Booking #5 was cancelled by Owner Ako.',0,'2026-10-23 21:45:06',NULL),(133,6,8,'booking_cancelled','Guest cancelled booking','Test Guest cancelled booking #8.',0,'2026-10-23 21:46:08',NULL),(134,4,15,'booking_submitted','Booking request submitted','Your request for Big Room is pending Owner/Staff review. You are the first request for these dates.',1,'2026-10-23 22:09:22','2026-10-23 23:45:45'),(135,7,15,'booking_requested','New booking request','Test Guest requested Big Room. You are the first request for these dates.',1,'2026-10-23 22:09:22','2026-10-23 23:03:25'),(136,4,15,'booking_accepted','Booking accepted — downpayment required','Your booking #15 was accepted. Submit the ₱5,000.00 downpayment to confirm your reservation.',1,'2026-10-23 22:09:32','2026-10-23 23:45:45'),(137,7,15,'booking_accepted','Booking accepted','Booking #15 was accepted and its dates are reserved pending the guest\'s downpayment.',1,'2026-10-23 22:09:32','2026-10-23 23:03:25'),(138,7,15,'payment_submitted','Payment submitted','A payment of ₱5,000.00 was submitted for booking #15 and is awaiting review.',1,'2026-10-23 22:09:54','2026-10-23 23:03:25'),(139,4,15,'booking_confirmed','Booking confirmed','The payment for booking #15 was verified. The dates are confirmed and any remaining balance is due at checkout.',1,'2026-10-23 22:10:14','2026-10-23 23:45:45'),(140,7,15,'booking_confirmed','Booking confirmed','The downpayment for booking #15 was verified and the booking is confirmed.',1,'2026-10-23 22:10:14','2026-10-23 23:03:25'),(141,4,15,'discount_Verified','Discount ID verified','The Senior Citizen ID for Senior Citizen 1 was verified at checkout.',1,'2026-10-23 22:23:28','2026-10-23 23:45:45'),(142,4,15,'checkout_payment_recorded','Checkout payment recorded','A Cash payment of ₱8,830.00 was recorded for booking #15.',1,'2026-10-23 22:55:34','2026-10-23 23:45:45'),(143,4,15,'booking_completed','Booking completed','Your stay at Big Room is complete. Your Booking Breakdown is ready.',1,'2026-10-23 22:57:04','2026-10-23 23:45:45'),(144,4,16,'booking_submitted','Booking request submitted','Your request for Big Room is pending Owner/Staff review. You are the first request for these dates.',0,'2026-10-24 01:26:22',NULL),(145,7,16,'booking_requested','New booking request','Test Guest requested Big Room. You are the first request for these dates.',1,'2026-10-24 01:26:22','2026-10-25 10:24:24'),(146,4,16,'booking_accepted','Booking accepted — downpayment required','Your booking #16 was accepted. Submit the ₱11,200.00 downpayment to confirm your reservation.',0,'2026-10-24 01:31:24',NULL),(147,7,16,'booking_accepted','Booking accepted','Booking #16 was accepted and its dates are reserved pending the guest\'s downpayment.',1,'2026-10-24 01:31:24','2026-10-25 10:24:24'),(148,7,16,'payment_submitted','Payment submitted','A payment of ₱22,400.00 was submitted for booking #16 and is awaiting review.',1,'2026-10-24 01:47:23','2026-10-25 10:24:24'),(149,4,16,'booking_confirmed','Booking confirmed','The payment for booking #16 was verified. The dates are confirmed and any remaining balance is due at checkout.',0,'2026-10-24 01:51:18',NULL),(150,7,16,'booking_confirmed','Booking confirmed','The downpayment for booking #16 was verified and the booking is confirmed.',1,'2026-10-24 01:51:18','2026-10-25 10:24:24'),(151,4,16,'discount_Verified','Discount ID verified','The Senior Citizen ID for Senior Citizen 1 was verified at checkout.',0,'2026-10-24 02:09:07',NULL),(152,4,16,'checkout_payment_recorded','Checkout payment recorded','A Cash payment of ₱24,156.67 was recorded for booking #16.',0,'2026-10-24 02:36:08',NULL),(153,4,16,'booking_completed','Booking completed','Your stay at Big Room is complete. Your Booking Breakdown is ready.',0,'2026-10-24 02:36:14',NULL),(154,4,17,'booking_submitted','Booking request submitted','Your request for Single Room is pending Owner/Staff review. You are the first request for these dates.',0,'2026-10-24 03:12:04',NULL),(155,7,17,'booking_requested','New booking request','Test Guest requested Single Room. You are the first request for these dates.',1,'2026-10-24 03:12:04','2026-10-24 03:13:59'),(156,4,17,'booking_accepted','Booking accepted — downpayment required','Your booking #17 was accepted. Submit the ₱2,300.00 downpayment to confirm your reservation.',0,'2026-10-24 03:14:02',NULL),(157,7,17,'booking_accepted','Booking accepted','Booking #17 was accepted and its dates are reserved pending the guest\'s downpayment.',1,'2026-10-24 03:14:02','2026-10-25 10:24:24'),(158,7,17,'payment_submitted','Payment submitted','A payment of ₱2,300.00 was submitted for booking #17 and is awaiting review.',1,'2026-10-24 03:14:21','2026-10-25 10:24:24'),(159,4,17,'booking_confirmed','Booking confirmed','The payment for booking #17 was verified. The dates are confirmed and any remaining balance is due at checkout.',0,'2026-10-24 03:15:25',NULL),(160,7,17,'booking_confirmed','Booking confirmed','The downpayment for booking #17 was verified and the booking is confirmed.',1,'2026-10-24 03:15:25','2026-10-25 10:24:24'),(161,4,17,'checkout_payment_recorded','Checkout payment recorded','A Cash payment of ₱3,500.00 was recorded for booking #17.',0,'2026-10-25 02:15:43',NULL),(162,4,17,'booking_completed','Booking completed','Your stay at Single Room is complete. Your Booking Breakdown is ready.',0,'2026-10-25 02:15:50',NULL),(163,4,18,'booking_submitted','Booking request submitted','Your request for Big Room is pending Owner/Staff review. You are the first request for these dates.',0,'2026-10-25 02:16:36',NULL),(164,7,18,'booking_requested','New booking request','Test Guest requested Big Room. You are the first request for these dates.',1,'2026-10-25 02:16:36','2026-10-25 10:24:24'),(165,4,18,'booking_accepted','Booking accepted — downpayment required','Your booking #18 was accepted. Submit the ₱11,800.00 downpayment to confirm your reservation.',0,'2026-10-25 02:16:48',NULL),(166,7,18,'booking_accepted','Booking accepted','Booking #18 was accepted and its dates are reserved pending the guest\'s downpayment.',1,'2026-10-25 02:16:48','2026-10-25 10:24:24'),(167,7,18,'payment_submitted','Payment submitted','A payment of ₱11,800.00 was submitted for booking #18 and is awaiting review.',1,'2026-10-25 02:17:09','2026-10-25 10:24:24'),(168,4,18,'booking_confirmed','Booking confirmed','The payment for booking #18 was verified. The dates are confirmed and any remaining balance is due at checkout.',0,'2026-10-25 02:17:41',NULL),(169,7,18,'booking_confirmed','Booking confirmed','The downpayment for booking #18 was verified and the booking is confirmed.',1,'2026-10-25 02:17:41','2026-10-25 10:24:24'),(170,4,18,'discount_Verified','Discount ID verified','The Senior Citizen ID for Senior Citizen 1 was verified at checkout.',0,'2026-10-25 02:50:29',NULL),(171,8,10,'discount_Verified','Discount ID verified','The PWD ID for PWD 1 was verified at checkout.',0,'2026-10-25 02:55:35',NULL),(172,4,18,'checkout_payment_recorded','Checkout payment recorded','A Cash payment of ₱70,236.92 was recorded for booking #18.',0,'2026-10-25 10:26:40',NULL),(173,4,18,'booking_completed','Booking completed','Your stay at Big Room is complete. Your Booking Breakdown is ready.',0,'2026-10-25 10:26:49',NULL),(174,4,20,'booking_submitted','Booking request submitted','Your request for Room 1 is pending Owner/Staff review. You are the first request for these dates.',0,'2026-11-03 08:36:58',NULL),(175,2,20,'booking_requested','New booking request','Test Guest requested Room 1. You are the first request for these dates.',0,'2026-11-03 08:36:58',NULL),(176,3,20,'booking_requested','New booking request','Test Guest requested Room 1. You are the first request for these dates.',0,'2026-11-03 08:36:58',NULL),(177,4,20,'booking_accepted','Booking accepted — downpayment required','Your booking #20 was accepted. Submit the ₱2,250.00 downpayment to confirm your reservation.',1,'2026-11-03 08:37:23','2026-11-03 09:11:26'),(178,2,20,'booking_accepted','Booking accepted','Booking #20 was accepted and its dates are reserved pending the guest\'s downpayment.',0,'2026-11-03 08:37:23',NULL),(179,3,20,'booking_accepted','Booking accepted','Booking #20 was accepted and its dates are reserved pending the guest\'s downpayment.',0,'2026-11-03 08:37:23',NULL),(180,2,20,'payment_submitted','Payment submitted','A payment of ₱2,250.00 was submitted for booking #20 and is awaiting review.',0,'2026-11-03 08:44:23',NULL),(181,3,20,'payment_submitted','Payment submitted','A payment of ₱2,250.00 was submitted for booking #20 and is awaiting review.',0,'2026-11-03 08:44:23',NULL),(182,4,20,'payment_incorrect','Payment incorrect','ulit',1,'2026-11-03 09:11:12','2026-11-03 09:11:34'),(183,2,20,'payment_reviewed','Payment review updated','Payment for booking #20 was marked Incorrect by Test Owner.',0,'2026-11-03 09:11:12',NULL),(184,3,20,'payment_reviewed','Payment review updated','Payment for booking #20 was marked Incorrect by Test Owner.',0,'2026-11-03 09:11:12',NULL),(185,2,20,'payment_submitted','Payment submitted','A payment of ₱2,250.00 was submitted for booking #20 and is awaiting review.',0,'2026-11-03 09:11:50',NULL),(186,3,20,'payment_submitted','Payment submitted','A payment of ₱2,250.00 was submitted for booking #20 and is awaiting review.',0,'2026-11-03 09:11:50',NULL),(187,4,20,'booking_confirmed','Booking confirmed','The payment for booking #20 was verified. The dates are confirmed and any remaining balance is due at checkout.',0,'2026-11-03 09:12:01',NULL),(188,2,20,'booking_confirmed','Booking confirmed','The downpayment for booking #20 was verified and the booking is confirmed.',0,'2026-11-03 09:12:01',NULL),(189,3,20,'booking_confirmed','Booking confirmed','The downpayment for booking #20 was verified and the booking is confirmed.',0,'2026-11-03 09:12:01',NULL),(190,4,20,'discount_Invalid','Discount ID not verified','The Senior Citizen ID for Senior Citizen 1 was not verified at checkout. Reason: not real',0,'2026-11-03 09:14:51',NULL),(191,4,20,'checkout_payment_recorded','Checkout payment recorded','A Cash payment of ₱2,250.00 was recorded for booking #20.',0,'2026-11-03 09:15:30',NULL),(192,4,20,'booking_completed','Booking completed','Your stay at Room 1 is complete. Your Booking Breakdown is ready.',0,'2026-11-03 09:15:36',NULL),(193,10,21,'booking_submitted','Booking request submitted','Your request for Single Bed is pending Owner/Staff review. You are the first request for these dates.',0,'2026-11-03 09:44:45',NULL),(194,11,21,'booking_requested','New booking request','Juan Dela Cruz requested Single Bed. You are the first request for these dates.',0,'2026-11-03 09:44:45',NULL),(195,10,21,'booking_accepted','Booking accepted — downpayment required','Your booking #21 was accepted. Submit the ₱2,700.00 downpayment to confirm your reservation.',1,'2026-11-03 09:45:08','2026-11-03 09:45:21'),(196,11,21,'booking_accepted','Booking accepted','Booking #21 was accepted and its dates are reserved pending the guest\'s downpayment.',0,'2026-11-03 09:45:08',NULL),(197,11,21,'payment_submitted','Payment submitted','A payment of ₱2,700.00 was submitted for booking #21 and is awaiting review.',0,'2026-11-03 09:45:43',NULL),(198,10,21,'booking_confirmed','Booking confirmed','The payment for booking #21 was verified. The dates are confirmed and any remaining balance is due at checkout.',1,'2026-11-03 09:46:18','2026-11-03 09:46:28'),(199,11,21,'booking_confirmed','Booking confirmed','The downpayment for booking #21 was verified and the booking is confirmed.',0,'2026-11-03 09:46:18',NULL),(200,10,21,'discount_Verified','Discount ID verified','The Senior Citizen ID for Senior Citizen 1 was verified at checkout.',0,'2026-11-03 09:47:37',NULL),(201,11,22,'booking_cancelled','Booking cancelled','Booking #22 was cancelled by Owner Ako. Reason: Scam People!!!!',0,'2026-11-03 09:52:20',NULL),(202,13,23,'booking_submitted','Booking request submitted','Your request for Deluxxx3 is pending Owner/Staff review. You are the first request for these dates.',1,'2026-11-03 10:13:14','2026-11-03 10:15:12'),(203,2,23,'booking_requested','New booking request','Samantha Marteja requested Deluxxx3. You are the first request for these dates.',0,'2026-11-03 10:13:14',NULL),(204,13,23,'booking_rejected','Booking request rejected','Your booking request was not accepted. Reason: TRY - BCOS YOU ARE SCAMMER WE JUST KNOW IT!!!',0,'2026-11-03 10:14:27',NULL),(205,2,23,'booking_rejected','Booking request rejected','Booking #23 was rejected. Reason: TRY - BCOS YOU ARE SCAMMER WE JUST KNOW IT!!!',0,'2026-11-03 10:14:27',NULL),(206,13,24,'booking_submitted','Booking request submitted','Your request for Deluxxx3 is pending Owner/Staff review. You are the first request for these dates.',0,'2026-11-03 10:15:46',NULL),(207,2,24,'booking_requested','New booking request','Samantha Marteja requested Deluxxx3. You are the first request for these dates.',0,'2026-11-03 10:15:46',NULL),(208,13,24,'booking_accepted','Booking accepted — downpayment required','Your booking #24 was accepted. Submit the ₱27,000.00 downpayment to confirm your reservation.',0,'2026-11-03 10:16:08',NULL),(209,2,24,'booking_accepted','Booking accepted','Booking #24 was accepted and its dates are reserved pending the guest\'s downpayment.',0,'2026-11-03 10:16:08',NULL),(210,13,24,'booking_cancelled','Booking cancelled by Owner/Staff','Your booking was cancelled. Reason: ops you are not allowed here!!',0,'2026-11-03 10:16:28',NULL),(211,2,24,'booking_cancelled','Booking cancelled','Booking #24 was cancelled by Test Owner. Reason: ops you are not allowed here!!',0,'2026-11-03 10:16:28',NULL),(212,13,25,'booking_submitted','Booking request submitted','Your request for Deluxxx3 is pending Owner/Staff review. You are the first request for these dates.',0,'2026-11-03 10:17:38',NULL),(213,2,25,'booking_requested','New booking request','Samantha Marteja requested Deluxxx3. You are the first request for these dates.',0,'2026-11-03 10:17:38',NULL),(214,13,25,'booking_accepted','Booking accepted — downpayment required','Your booking #25 was accepted. Submit the ₱27,000.00 downpayment to confirm your reservation.',0,'2026-11-03 10:17:48',NULL),(215,2,25,'booking_accepted','Booking accepted','Booking #25 was accepted and its dates are reserved pending the guest\'s downpayment.',0,'2026-11-03 10:17:48',NULL),(216,2,25,'payment_submitted','Payment submitted','A payment of ₱27,000.00 was submitted for booking #25 and is awaiting review.',0,'2026-11-03 10:18:30',NULL),(217,13,25,'booking_confirmed','Booking confirmed','The payment for booking #25 was verified. The dates are confirmed and any remaining balance is due at checkout.',0,'2026-11-03 10:18:53',NULL),(218,2,25,'booking_confirmed','Booking confirmed','The downpayment for booking #25 was verified and the booking is confirmed.',0,'2026-11-03 10:18:53',NULL),(219,2,25,'booking_cancelled','Guest cancelled booking','Samantha Marteja cancelled booking #25. Reason: i dont wanna go na pala...',1,'2026-11-03 10:19:07','2026-11-03 10:23:54'),(220,13,26,'booking_submitted','Booking request submitted','Your request for Deluxxx3 is pending Owner/Staff review. You are the first request for these dates.',0,'2026-11-03 10:24:27',NULL),(221,2,26,'booking_requested','New booking request','Samantha Marteja requested Deluxxx3. You are the first request for these dates.',1,'2026-11-03 10:24:27','2026-11-03 10:24:36'),(222,13,26,'booking_accepted','Booking accepted — downpayment required','Your booking #26 was accepted. Submit the ₱27,000.00 downpayment to confirm your reservation.',0,'2026-11-03 10:24:39',NULL),(223,2,26,'booking_accepted','Booking accepted','Booking #26 was accepted and its dates are reserved pending the guest\'s downpayment.',0,'2026-11-03 10:24:39',NULL),(224,2,26,'payment_submitted','Payment submitted','A payment of ₱27,000.00 was submitted for booking #26 and is awaiting review.',0,'2026-11-03 10:24:55',NULL),(225,13,26,'booking_confirmed','Booking confirmed','The payment for booking #26 was verified. The dates are confirmed and any remaining balance is due at checkout.',0,'2026-11-03 10:25:01',NULL),(226,2,26,'booking_confirmed','Booking confirmed','The downpayment for booking #26 was verified and the booking is confirmed.',0,'2026-11-03 10:25:01',NULL),(227,13,26,'discount_Invalid','Discount ID not verified','The PWD ID for PWD 1 was not verified at checkout. Reason: not real!',0,'2026-11-03 10:25:54',NULL),(228,13,26,'discount_Verified','Discount ID verified','The Senior Citizen ID for Senior Citizen 1 was verified at checkout.',0,'2026-11-03 10:25:56',NULL),(229,13,26,'refund_issued','Refund issued','A refund of ₱25,000.00 for booking #26 has been issued. Reason: Guest needs to go home early',0,'2026-11-03 10:28:01',NULL),(230,13,26,'checkout_payment_recorded','Checkout payment recorded','A GCash payment of ₱52,410.00 was recorded for booking #26.',0,'2026-11-03 10:28:51',NULL),(231,13,26,'booking_completed','Booking completed','Your stay at Deluxxx3 is complete. Your Booking Breakdown is ready.',0,'2026-11-03 10:28:57',NULL),(232,4,29,'booking_submitted','Booking request submitted','Your request for Deluxxx3 is pending Owner/Staff review. You are the first request for these dates.',0,'2026-11-11 13:41:42',NULL),(233,2,29,'booking_requested','New booking request','Test Guest requested Deluxxx3. You are the first request for these dates.',0,'2026-11-11 13:41:42',NULL),(234,14,29,'booking_requested','New booking request','Test Guest requested Deluxxx3. You are the first request for these dates.',0,'2026-11-11 13:41:42',NULL),(235,4,29,'booking_accepted','Booking accepted — downpayment required','Your booking #29 was accepted. Submit the ₱18,000.00 downpayment to confirm your reservation.',0,'2026-11-11 13:42:21',NULL),(236,2,29,'booking_accepted','Booking accepted','Booking #29 was accepted and its dates are reserved pending the guest\'s downpayment.',0,'2026-11-11 13:42:21',NULL),(237,14,29,'booking_accepted','Booking accepted','Booking #29 was accepted and its dates are reserved pending the guest\'s downpayment.',0,'2026-11-11 13:42:21',NULL),(238,2,29,'payment_submitted','Payment submitted','A payment of ₱18,000.00 was submitted for booking #29 and is awaiting review.',0,'2026-11-11 13:43:24',NULL),(239,14,29,'payment_submitted','Payment submitted','A payment of ₱18,000.00 was submitted for booking #29 and is awaiting review.',0,'2026-11-11 13:43:24',NULL),(240,4,29,'booking_confirmed','Booking confirmed','The payment for booking #29 was verified. The dates are confirmed and any remaining balance is due at checkout.',0,'2026-11-11 13:43:35',NULL),(241,2,29,'booking_confirmed','Booking confirmed','The downpayment for booking #29 was verified and the booking is confirmed.',0,'2026-11-11 13:43:35',NULL),(242,14,29,'booking_confirmed','Booking confirmed','The downpayment for booking #29 was verified and the booking is confirmed.',0,'2026-11-11 13:43:35',NULL),(243,4,29,'discount_Verified','Discount ID verified','The Senior Citizen ID for Senior Citizen 1 was verified at checkout.',0,'2026-11-11 13:48:02',NULL),(244,4,29,'discount_Verified','Discount ID verified','The Senior Citizen ID for Senior Citizen 2 was verified at checkout.',0,'2026-11-11 13:48:06',NULL);
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `reset_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`reset_id`),
  UNIQUE KEY `password_resets_token_unique` (`token`),
  KEY `password_resets_user_id_foreign` (`user_id`),
  CONSTRAINT `password_resets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `payment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` varchar(30) NOT NULL,
  `payment_date` datetime NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `paymongo_payment_id` varchar(255) DEFAULT NULL,
  `paymongo_payment_intent_id` varchar(255) DEFAULT NULL,
  `paymongo_checkout_session_id` varchar(255) DEFAULT NULL,
  `payment_receipt` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `reference_number_key` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `payments_reference_number_key_unique` (`reference_number_key`),
  KEY `payments_booking_id_foreign` (`booking_id`),
  CONSTRAINT `payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,3,'Bank Transfer',5000.00,'Verified','2026-10-12 12:49:48','0912345678',NULL,NULL,NULL,'payment-proofs/59yeeRuI2NYmNhd1YNdLWJ0UjenMG1ZwiDQXoJOI.png',NULL,'2026-10-12 04:49:48','2026-10-12 04:51:35','20faf05baccf8a477fa337b77c0acb0e8bff77f34664feec6a057bd3cf23235b'),(2,3,'GCash',5000.00,'Verified','2026-10-12 14:15:32','12345',NULL,NULL,NULL,'payment-proofs/nOOPGV8e60MHqcGFDrXqVsxwKBnI7XO5YyZsZEQE.png',NULL,'2026-10-12 06:15:32','2026-10-12 06:47:07','eb33f505bdd7e1b467492913707d2128757383d96f94e33ba47cdd60ff546358'),(3,3,'GCash',1199.80,'Verified','2026-10-12 14:49:29','12345',NULL,NULL,NULL,'payment-proofs/ahHrcqcm31fjXeCZUpHZ5QSms07ccQXDQBDjum8f.png',NULL,'2026-10-12 06:49:29','2026-10-12 06:49:40','ea25cb3484602ecd21e9ae08b9ee03978602c9ae596f5416815aaedd24fd37ce'),(4,4,'GCash',25000.00,'Verified','2026-10-14 03:22:26','8765432',NULL,NULL,NULL,'payment-proofs/WTZTgdnn1DBuo5srHR0CkIWFj0SK2EZgpDzzpCwv.jpg',NULL,'2026-10-13 19:22:26','2026-10-13 19:26:29','b5533b8b30907a802330634ea007da0aa92774869e885d4db12167c0143d0a0a'),(5,4,'GCash',18500.00,'Verified','2026-10-14 03:35:34','8765432',NULL,NULL,NULL,'payment-proofs/9UzAC7KLuQYohRrNULQ3J19GPP2mSP6VtS4Ute2m.jpg',NULL,'2026-10-13 19:35:34','2026-10-13 19:36:16','96dc1ad3714ac2f85376d64ebd7817ecc31bde135290e2adeccdca2b1e113490'),(6,4,'Cash',5000.00,'Verified','2026-10-14 03:39:22','1234567',NULL,NULL,NULL,'payment-proofs/fCzGidk0HdA50ACIwu3eEm2qWb65yxN4IqyFi8X1.png',NULL,'2026-10-13 19:39:22','2026-10-13 19:39:22','8bb0cf6eb9b17d0f7d22b456f121257dc1254e1f01665370476383ea776df414'),(7,6,'GCash',21500.00,'Verified','2026-10-14 07:41:14','12345678909',NULL,NULL,NULL,'payment-proofs/qcwpXkNn5MFve5HRKSmLj9gZtV0s0GIHUtGmytzi.png',NULL,'2026-10-13 23:41:14','2026-10-13 23:42:05','7ec94663084bd506d4f0c3e21042df233681fd7426e93f397c921b1d3e397bba'),(8,6,'Cash',18300.00,'Verified','2026-10-14 07:58:06','1234567567876543',NULL,NULL,NULL,'payment-proofs/wp2bBaWKlEzTtkfligNYtn3QupWm5r2ydfka4cO9.png',NULL,'2026-10-13 23:58:06','2026-10-13 23:58:06','72f2b35335c2281baf3e03511bea0ad8f9fccd99132e5873173845114be8161f'),(9,7,'GCash',6000.00,'Verified','2026-10-15 01:22:27','23',NULL,NULL,NULL,'payment-proofs/tHGvCcR9HxHf4Ka1w7NhK0TrRR6W02qxTP2wpQbZ.jpg',NULL,'2026-10-14 17:22:27','2026-10-14 17:22:46','535fa30d7e25dd8a49f1536779734ec8286108d115da5045d77f3b4185d8f790'),(10,10,'GCash',20000.00,'Verified','2026-10-15 01:24:23','123',NULL,NULL,NULL,NULL,NULL,'2026-10-14 17:24:23','2026-10-14 17:25:41','a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3'),(11,7,'Cash',5600.00,'Verified','2026-10-15 01:24:34','12',NULL,NULL,NULL,'payment-proofs/D8Oz8D0h9dePlWwjwJiMT43ItVDRatb9kMhBnYg5.png',NULL,'2026-10-14 17:24:34','2026-10-14 17:24:34','6b51d431df5d7f141cbececcf79edf3dd861c3b4069f0b11661a3eefacbba918'),(12,9,'GCash',10500.00,'Verified','2026-10-15 01:36:16',NULL,NULL,NULL,NULL,'payment-proofs/kmJCmFdOaBuwOzRJPIHm4fpL93hvHbgkrvwVO6SE.png',NULL,'2026-10-14 17:36:16','2026-10-14 17:36:51',NULL),(13,11,'GCash',10000.00,'Verified','2026-10-15 02:08:59','1',NULL,NULL,NULL,'payment-proofs/uUUOa7nVD8m3Dn4nRpxgaNZICUpQEleQG1wVmfw4.png',NULL,'2026-10-14 18:08:59','2026-10-14 18:09:40','6b86b273ff34fce19d6b804eff5a3f5747ada4eaa22f1d49c01e52ddb7875b4b'),(14,12,'GCash',5300.00,'Verified','2026-10-15 03:29:31','123456',NULL,NULL,NULL,NULL,NULL,'2026-10-14 19:29:31','2026-10-14 19:30:55','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),(15,12,'Cash',5125.45,'Verified','2026-10-15 03:47:47','123456789087654567',NULL,NULL,NULL,'payment-proofs/9VDmP9MWofkdk9vrn9nI60jo4cw4my1XH75xWq3I.jpg',NULL,'2026-10-14 19:47:47','2026-10-14 19:47:47','d3947f48b05362f4eb8566f016efad47f437920838b6382afc5c5340ed899bca'),(16,9,'Cash',10500.00,'Verified','2026-10-24 02:16:23','123456787654323456543',NULL,NULL,NULL,'payment-proofs/d6W21V7PHjPGmiq8oQsRPrgOwQEwHESXgKPWzzUW.jpg',NULL,'2026-10-23 18:16:23','2026-10-23 18:16:23','cfa6a47070d1358ea492eb3449a27c89bdf7d4ce2623d710fad3baea4395283f'),(17,14,'GCash',7500.00,'Verified','2026-10-24 05:16:27','1212121',NULL,NULL,NULL,'payment-proofs/usWsm4IcnwMZuXArVw8hV59E3SahanlQBUvalY9T.jpg',NULL,'2026-10-23 21:16:27','2026-10-23 21:28:53','3315c3490373d0c9c0eaf58f14331452d0f3b6e4db131d106d83376a61858b77'),(18,15,'GCash',5000.00,'Verified','2026-10-24 06:09:54','121221',NULL,NULL,NULL,NULL,NULL,'2026-10-23 22:09:54','2026-10-23 22:10:14','d33fc1b3218dd768a006fd04065822346d2063a93733d2372e0c40368e02a2c7'),(19,15,'Cash',8830.00,'Verified','2026-10-24 06:55:34','1211',NULL,NULL,NULL,'payment-proofs/lB1uixH3SxYbXGxQT5D0Td3h9MAA0Y173R7mM6ph.jpg',NULL,'2026-10-23 22:55:34','2026-10-23 22:55:34','2ecefe59ab6ae4e734bb1adec3bbb706be8c6fae0b39500d05f7a6665ffa7390'),(20,16,'Bank Transfer',22400.00,'Verified','2026-10-24 09:47:23','12111',NULL,NULL,NULL,'payment-proofs/KLHKs5fjEMKfV9DqN3iElVJDu28bwLKuP67xhBTl.png',NULL,'2026-10-24 01:47:23','2026-10-24 01:51:18','106ca1b823ceb804d7c014a5019632a780f7ce32c7a8737abe41de56f2037c7d'),(21,16,'Cash',24156.67,'Verified','2026-10-24 10:36:08','1221221',NULL,NULL,NULL,'payment-proofs/ptBZizyXYBCogV3XSDGYIg72l2OXaNnTGchvZaaO.jpg',NULL,'2026-10-24 02:36:08','2026-10-24 02:36:08','e9db2fab51def6a97f2c1475c1b84abed7b678230ec3ab86fcff3e38a158123b'),(22,17,'GCash',2300.00,'Verified','2026-10-24 11:14:21','11111',NULL,NULL,NULL,'payment-proofs/v52kj4czwzZCNuUaQVa6OVlpDM2PJbQ42dhlYg8t.png',NULL,'2026-10-24 03:14:21','2026-10-24 03:15:25','d17f25ecfbcc7857f7bebea469308be0b2580943e96d13a3ad98a13675c4bfc2'),(23,17,'Cash',3500.00,'Verified','2026-10-25 10:15:43','1111111',NULL,NULL,NULL,'payment-proofs/YnSu8GRh1tt7jE7WMZnYd45jxcDihboIQOu7ptbg.png',NULL,'2026-10-25 02:15:43','2026-10-25 02:15:43','2558a34d4d20964ca1d272ab26ccce9511d880579593cd4c9e01ab91ed00f325'),(24,18,'GCash',11800.00,'Verified','2026-10-25 10:17:09','1222',NULL,NULL,NULL,'payment-proofs/S8afPmxCs1uu4RiSWLVzoqbum2mVi8iFL6yv3D9u.png','yes right','2026-10-25 02:17:09','2026-10-25 02:17:41','16a3e9e922b41fde13be3ae377b18e08d44fb136594e7f9819ff2ac75bab5516'),(25,19,'Cash',1999.84,'Verified','2026-10-25 10:49:53',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-25 02:49:53','2026-10-25 02:49:53',NULL),(26,18,'Cash',70236.92,'Verified','2026-10-25 18:26:40','121122',NULL,NULL,NULL,'payment-proofs/2MlhXb7OULmSm0bCg3x1vZ6tpNfEHOmPcgYPeztQ.jpg',NULL,'2026-10-25 10:26:40','2026-10-25 10:26:40','7d75feac9e55f2fd9e6b9a49fbaf9f56162ddd0f8dac21f634936d2e15255d20'),(27,20,'GCash',2250.00,'Incorrect','2026-11-03 16:44:23','10000',NULL,NULL,NULL,'payment-proofs/Tixksc92IRtIPOrZDIfOJtmLPjgI4jmujRx6sjPv.jpg','ulit','2026-11-03 08:44:23','2026-11-03 09:11:12','39e5b4830d4d9c14db7368a95b65d5463ea3d09520373723430c03a5a453b5df'),(28,20,'GCash',2250.00,'Verified','2026-11-03 17:11:50','321',NULL,NULL,NULL,'payment-proofs/ZZxbC3jH55VfCRZ1pLcUVFQCVr8PuHkB3GNZtmQz.jpg',NULL,'2026-11-03 09:11:50','2026-11-03 09:12:01','8d23cf6c86e834a7aa6eded54c26ce2bb2e74903538c61bdd5d2197997ab2f72'),(29,20,'Cash',2250.00,'Verified','2026-11-03 17:15:30','1323',NULL,NULL,NULL,'payment-proofs/r577OOXIobNdOVuSLziTbE8lp9dm6S1JQIRydYn4.jpg',NULL,'2026-11-03 09:15:30','2026-11-03 09:15:30','b24e3e21870645af14da826d4cb300c0f4f877c308efa30ff084f976f191aafb'),(30,21,'GCash',2700.00,'Verified','2026-11-03 17:45:43','122212',NULL,NULL,NULL,'payment-proofs/IFO2Fog78CDiYrMEpWrEOcvNOpm7IGr2vr347wiu.jpg',NULL,'2026-11-03 09:45:43','2026-11-03 09:46:18','1f0fbb40c22436f0ccc8a975c99e5a843c953b4ca9517f9b8164fcec203fe617'),(31,22,'Cash',1200.00,'Verified','2026-11-03 17:52:08',NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-03 09:52:08','2026-11-03 09:52:08',NULL),(32,25,'GCash',27000.00,'Verified','2026-11-03 18:18:30','212111',NULL,NULL,NULL,'payment-proofs/QiSyHwQWVlZBepUbvI5YqkOjdDm3nsgFYnWvhwYp.jpg',NULL,'2026-11-03 10:18:30','2026-11-03 10:18:53','edebb6762ef8b7f4faa9c159dbded405d9e913d5e4c90ced63d556af77cc0570'),(33,26,'GCash',27000.00,'Verified','2026-11-03 18:24:55','1211111',NULL,NULL,NULL,'payment-proofs/Us66a8Iap24aTBmCiWQhtvX786dNpVNoqZulkcDg.jpg',NULL,'2026-11-03 10:24:55','2026-11-03 10:25:01','4d608a7382b298dd808c24a1feb4e0c98074e8c7f451f2e7dea3b58f49b114ea'),(34,26,'GCash',52410.00,'Verified','2026-11-03 18:28:51','12121111212121212121',NULL,NULL,NULL,'payment-proofs/dk3KE0RU2l30ffS3qmxmHM4T4a8RtT7SjmRxWjus.jpg',NULL,'2026-11-03 10:28:51','2026-11-03 10:28:51','091756213587a88a7735bc73559daf6adc6d1123a47ae010ecbd628f30f65e74'),(35,27,'Cash',16200.00,'Verified','2026-11-03 18:35:57','11',NULL,NULL,NULL,NULL,NULL,'2026-11-03 10:35:57','2026-11-03 10:35:57','4fc82b26aecb47d2868c4efbe3581732a3e7cbcc6c2efb32062c08170a05eeb8'),(36,27,'GCash',240.00,'Verified','2026-11-03 18:36:37','122111',NULL,NULL,NULL,'payment-proofs/vQO2wX5fBXuHSBhouwBpxtARFMHY5dj3WcEXS8o3.jpg',NULL,'2026-11-03 10:36:37','2026-11-03 10:36:37','3d5fd5223fce10c020b86bfe07f03407d98d47add73f0bf99ace0cfa2d48d5cd'),(37,29,'GCash',18000.00,'Verified','2026-11-11 21:43:24','12222',NULL,NULL,NULL,'payment-proofs/zqGNcTQ4xum9hVW4BUIi6VzX8DHWmPqZ5xmxLiqA.pdf',NULL,'2026-11-11 13:43:24','2026-11-11 13:43:35','fe9036dfac21956345742357dac23959170eecdc7e3d9630b14a369624de7ec7'),(38,30,'Cash',30000.00,'Verified','2026-11-11 22:35:13',NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-11 14:35:13','2026-11-11 14:35:13',NULL);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refunds`
--

DROP TABLE IF EXISTS `refunds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refunds` (
  `refund_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `payment_id` int(10) unsigned NOT NULL,
  `processed_by` int(10) unsigned DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `refund_status` varchar(30) NOT NULL,
  `refund_reference` varchar(100) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`refund_id`),
  KEY `refunds_booking_id_foreign` (`booking_id`),
  KEY `refunds_payment_id_foreign` (`payment_id`),
  KEY `refunds_processed_by_foreign` (`processed_by`),
  CONSTRAINT `refunds_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`),
  CONSTRAINT `refunds_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`),
  CONSTRAINT `refunds_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refunds`
--

LOCK TABLES `refunds` WRITE;
/*!40000 ALTER TABLE `refunds` DISABLE KEYS */;
INSERT INTO `refunds` VALUES (1,11,13,9,900.00,'PWD discount approved after payment.','Refunded',NULL,'2026-10-15 02:38:53','2026-10-14 18:38:09'),(2,26,33,2,25000.00,'Guest needs to go home early','Refunded',NULL,'2026-11-03 18:28:01','2026-11-03 10:28:01');
/*!40000 ALTER TABLE `refunds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `report_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `house_id` int(10) unsigned DEFAULT NULL,
  `generated_by` int(10) unsigned NOT NULL,
  `report_type` varchar(100) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`report_id`),
  KEY `reports_house_id_foreign` (`house_id`),
  KEY `reports_generated_by_foreign` (`generated_by`),
  CONSTRAINT `reports_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`),
  CONSTRAINT `reports_house_id_foreign` FOREIGN KEY (`house_id`) REFERENCES `transient_houses` (`house_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `review_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `unit_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `reviews_booking_id_unique` (`booking_id`),
  KEY `reviews_unit_id_foreign` (`unit_id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  CONSTRAINT `reviews_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`),
  CONSTRAINT `reviews_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`),
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'System Admin','Manages the MCTBS system and system-wide settings.'),(2,'Owner','Manages transient houses, units, bookings, and related operations.'),(3,'Staff','Assists with booking, guest, payment, and house operations.'),(4,'Guest','Uses the system to search, book, and manage guest-related transactions.');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_archives`
--

DROP TABLE IF EXISTS `transaction_archives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_archives` (
  `archive_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `archived_by` int(10) unsigned NOT NULL,
  `archive_note` text DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `scheduled_deletion_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`archive_id`),
  KEY `transaction_archives_booking_id_foreign` (`booking_id`),
  KEY `transaction_archives_archived_by_foreign` (`archived_by`),
  CONSTRAINT `transaction_archives_archived_by_foreign` FOREIGN KEY (`archived_by`) REFERENCES `users` (`user_id`),
  CONSTRAINT `transaction_archives_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_archives`
--

LOCK TABLES `transaction_archives` WRITE;
/*!40000 ALTER TABLE `transaction_archives` DISABLE KEYS */;
INSERT INTO `transaction_archives` VALUES (1,2,2,NULL,'2026-10-09 03:21:42',NULL),(2,22,11,NULL,'2026-11-03 09:52:26',NULL),(3,23,2,'note for this account since it is a scammer!!!','2026-11-03 10:14:45',NULL);
/*!40000 ALTER TABLE `transaction_archives` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transient_houses`
--

DROP TABLE IF EXISTS `transient_houses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transient_houses` (
  `house_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `contact_number` varchar(30) NOT NULL,
  `policies` text DEFAULT NULL,
  `cover_photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `social_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_links`)),
  PRIMARY KEY (`house_id`),
  KEY `transient_houses_owner_id_foreign` (`owner_id`),
  CONSTRAINT `transient_houses_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transient_houses`
--

LOCK TABLES `transient_houses` WRITE;
/*!40000 ALTER TABLE `transient_houses` DISABLE KEYS */;
INSERT INTO `transient_houses` VALUES (1,2,'Test House','123 Sample St',NULL,'Cebu City','Lahug','0321234567',NULL,'houses/yTYywWS8S5fyVAZFZlnOSrZyS1Ga8wlp8vxsVQEs.png',1,NULL),(2,6,'Tall Tree','12 Everlasting Street','This is a transient house own by a couple of dean. Hello and enjoy!','Baguio','Ferdinand','09123456789','No disturbing during 10pm. No pets allowed.','houses/qvMlA1vs4zSoyFHuXKiay7TKTcgadT83GqDreRXk.png',1,'[{\"name\":\"Facebook\",\"url\":\"https:\\/\\/www.facebook.com\\/mrtk.gelo\\/\"}]'),(3,7,'Absolute','01 Water District','This house is very amazing','Baguio','Imelda','0912345678','Bawal maligo','houses/m6BzVwwEdF7fLTdoctNDp1mV5MRz5WkyGdNvSjga.png',1,'[]'),(4,9,'Mother\'s Transient Home Baguio City','San Carlos Heights','If you are looking for a place to stay in Baguio City, we offer an cozy home with a stunning overlooking highland view. \r\nDrop us a message for your bookings & we\'ll be glad to assist you!😃','Baguio City','Rizal','09123456789','Baguio Silent Night Ordinance must be followed: No noise from 10:00 PM to 5:00 AM\r\nCheck-in is at 2:00 PM onwards; Check-out is at 12:00 Noon\r\nSmoking is not allowed inside rooms; only allowed at the balcony or terrace with proper cigarette butt disposal\r\nGambling and illegal drugs are strictly prohibited\r\nGuests must turn off faucets, lights, gas tanks, and electrical appliances when not in use\r\nDoors must be locked at all times; the business is not responsible for any loss of personal belongings','houses/S63YYQMkvKDxwkfcbJHDZBhmTbgufYKT1EbdE9uH.jpg',1,'[{\"name\":\"Facebook\",\"url\":\"https:\\/\\/www.facebook.com\\/mothershomebaguio\"}]'),(5,7,'Bahay ni Kuya','046 Everlasting Upper Q.M.','this is sooooo tiring huhu, so help me god.','Baguio','Ferdinand','12121212121','wala sige lng sirain nyo yung bahay!\nDont be quite','houses/fUk3NY57ar6bW1FzdmF2rjVEYEw9fwUs4CGFCKDT.png',1,'[{\"name\":\"Facebook\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/owner\\/houses\\/create\"}]'),(6,11,'Baguio Hotel','046 Everlasting Upper Q.M.','This is a very good transient house!','Baguio City','Ferdinand','09123456789','Quiet Hour Begins at 10 PM\nNo Pets Allowed\nNo Smoking\nEvery Item Missing/Broken has its corresponding Payment','houses/XsoWDPUrbBSMeYuhftmKpl89hvsenNXHZheaN7dx.jpg',1,'[{\"name\":\"Facebook\",\"url\":\"https:\\/\\/www.facebook.com\\/mrtk.gelo\\/\"}]'),(7,2,'Mancilaaaaa','046 Everlasting Upper Q.M.','This is a very very very cozy and good Transient House that youve ever see!','Baguio City','Imelda','09123456789','Quiet Hour Begins at 10 PM\nNo Pets Allowed\nNo Smoking\nNo Cellphone Allowed','houses/Zj8plPCt8vixl3FIViEzXnYUwm46I9yftSUdLnVt.jpg',1,'[{\"name\":\"Facebook\",\"url\":\"https:\\/\\/www.facebook.com\\/mrtk.gelo\\/\"}]');
/*!40000 ALTER TABLE `transient_houses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit_addons`
--

DROP TABLE IF EXISTS `unit_addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_addons` (
  `unit_addon_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`unit_addon_id`),
  KEY `unit_addons_unit_id_foreign` (`unit_id`),
  CONSTRAINT `unit_addons_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_addons`
--

LOCK TABLES `unit_addons` WRITE;
/*!40000 ALTER TABLE `unit_addons` DISABLE KEYS */;
INSERT INTO `unit_addons` VALUES (1,4,'Electric stove',3500.00),(2,4,'Electric kettle',1000.00),(3,4,'Kaserola',400.00),(4,4,'Pitcher',450.00),(5,7,'ewanch',1200.00),(6,9,'Towel',50.00),(7,9,'Electric Kettle',500.00),(8,9,'Toothpaste',19.90),(9,9,'Mineral Water',39.81),(13,10,'Towel',50.00),(14,10,'Toothpaste',20.00),(15,10,'Mineral Water',40.00);
/*!40000 ALTER TABLE `unit_addons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit_availability`
--

DROP TABLE IF EXISTS `unit_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_availability` (
  `availability_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `status` varchar(30) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`availability_id`),
  UNIQUE KEY `unit_availability_unit_id_date_unique` (`unit_id`,`date`),
  CONSTRAINT `unit_availability_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_availability`
--

LOCK TABLES `unit_availability` WRITE;
/*!40000 ALTER TABLE `unit_availability` DISABLE KEYS */;
INSERT INTO `unit_availability` VALUES (2,2,'2026-10-12','Unavailable','Renovation'),(3,2,'2026-10-17','Unavailable','Bday ni Jerlyn Mae'),(5,2,'2026-10-15','Unavailable','Bday ko'),(6,2,'2026-10-16','Unavailable','Bday ko'),(7,3,'2026-10-15','Booked','Confirmed booking #3'),(8,3,'2026-10-16','Booked','Confirmed booking #3'),(9,3,'2026-10-14','Unavailable','Renovation'),(10,2,'2026-10-21','Unavailable','Bday ko baket?'),(11,2,'2026-10-22','Unavailable','Bday ko baket?'),(17,3,'2026-10-23','Booked','Confirmed booking #4'),(18,3,'2026-10-24','Booked','Confirmed booking #4'),(19,3,'2026-10-25','Booked','Confirmed booking #4'),(20,3,'2026-10-26','Booked','Confirmed booking #4'),(21,3,'2026-10-27','Booked','Confirmed booking #4'),(22,3,'2026-10-28','Booked','Confirmed booking #4'),(23,3,'2026-10-29','Booked','Confirmed booking #4'),(24,3,'2026-10-30','Booked','Confirmed booking #4'),(25,3,'2026-10-21','Unavailable','bday ni sir rick, walang pasok'),(26,3,'2026-10-22','Unavailable','bday ni sir rick, walang pasok'),(27,4,'2026-10-14','Unavailable','BDAY'),(28,4,'2026-10-15','Unavailable','BDAY'),(29,4,'2026-10-16','Unavailable','BDAY'),(30,4,'2026-10-20','Booked','Confirmed booking #6'),(31,4,'2026-10-21','Booked','Confirmed booking #6'),(32,4,'2026-10-22','Booked','Confirmed booking #6'),(33,4,'2026-10-23','Booked','Confirmed booking #6'),(36,4,'2026-10-30','Booked','Confirmed booking #7'),(37,4,'2026-11-02','Booked','Confirmed booking #10'),(38,4,'2026-11-03','Booked','Confirmed booking #10'),(39,4,'2026-11-04','Booked','Confirmed booking #10'),(40,4,'2026-11-05','Booked','Confirmed booking #10'),(41,3,'2026-11-02','Booked','Confirmed booking #9'),(42,3,'2026-11-03','Booked','Confirmed booking #9'),(43,3,'2026-11-04','Booked','Confirmed booking #9'),(44,3,'2026-11-05','Booked','Confirmed booking #9'),(45,5,'2026-10-17','Booked','Confirmed booking #11'),(46,5,'2026-10-24','Booked','Confirmed booking #12'),(47,3,'2026-11-07','Booked','Confirmed booking #14'),(48,3,'2026-11-08','Booked','Confirmed booking #14'),(49,3,'2026-11-09','Booked','Confirmed booking #14'),(50,4,'2026-10-25','Booked','Confirmed booking #15'),(51,4,'2026-11-07','Booked','Confirmed booking #16'),(52,4,'2026-11-08','Booked','Confirmed booking #16'),(53,7,'2026-10-25','Booked','Confirmed booking #17'),(54,7,'2026-10-26','Booked','Confirmed booking #17'),(55,4,'2026-11-14','Booked','Confirmed booking #18'),(56,4,'2026-11-15','Booked','Confirmed booking #18'),(57,1,'2026-11-04','Booked','Confirmed booking #20'),(58,1,'2026-11-05','Booked','Confirmed booking #20'),(59,1,'2026-11-06','Booked','Confirmed booking #20'),(60,2,'2026-11-17','Unavailable','Maintenance'),(61,2,'2026-11-18','Unavailable','Maintenance'),(62,9,'2026-11-04','Booked','Confirmed booking #21'),(63,9,'2026-11-05','Booked','Confirmed booking #21'),(64,9,'2026-11-06','Booked','Confirmed booking #21'),(65,9,'2026-11-08','Unavailable','Maintenance'),(66,9,'2026-11-09','Unavailable','Maintenance'),(67,9,'2026-11-10','Unavailable','Maintenance'),(74,10,'2026-11-04','Booked','Confirmed booking #26'),(75,10,'2026-11-05','Booked','Confirmed booking #26'),(76,10,'2026-11-06','Booked','Confirmed booking #26'),(78,10,'2026-11-11','Unavailable','Bday ko no unit muna hehe'),(79,10,'2026-11-12','Unavailable','Bday ko no unit muna hehe'),(80,10,'2026-11-14','Booked','Confirmed booking #29'),(81,10,'2026-11-15','Booked','Confirmed booking #29');
/*!40000 ALTER TABLE `unit_availability` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit_damage_items`
--

DROP TABLE IF EXISTS `unit_damage_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_damage_items` (
  `unit_damage_item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`unit_damage_item_id`),
  KEY `unit_damage_items_unit_id_foreign` (`unit_id`),
  CONSTRAINT `unit_damage_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_damage_items`
--

LOCK TABLES `unit_damage_items` WRITE;
/*!40000 ALTER TABLE `unit_damage_items` DISABLE KEYS */;
INSERT INTO `unit_damage_items` VALUES (1,4,'Spoons',10.00),(2,4,'Spatula',40.00),(3,4,'Knife',70.00),(4,4,'Mugs',30.00),(5,7,'Sirang puso',4999.82),(6,9,'Spoon & Fork',10.00),(7,9,'Plate',30.00),(8,9,'Heater',500.00),(9,9,'Electric Kettle',1000.00),(13,10,'Spoon & Fork',10.00),(14,10,'Plate',40.00),(15,10,'Heater',1500.00);
/*!40000 ALTER TABLE `unit_damage_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit_photos`
--

DROP TABLE IF EXISTS `unit_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_photos` (
  `photo_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` int(10) unsigned NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `is_cover` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`photo_id`),
  KEY `unit_photos_unit_id_sort_order_index` (`unit_id`,`sort_order`),
  CONSTRAINT `unit_photos_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_photos`
--

LOCK TABLES `unit_photos` WRITE;
/*!40000 ALTER TABLE `unit_photos` DISABLE KEYS */;
INSERT INTO `unit_photos` VALUES (1,2,'units/2/A7I2TeLG2HOPkhbOBYIOqJ8iCHffXqaoT7YEeyYm.png',1,0),(2,1,'units/1/3ELJxetwhsGe7xAHjl4apkNGb7jm4Y6aiAgwL1KP.png',1,0),(3,3,'units/3/MxUArxYfpBvi5pjqaciIBU2f1ZsTeNynjxhA3LmY.png',1,0),(4,3,'units/3/53zYDS2Eb93zModt4cCCHxPvm8YUPASasK26AYhi.png',0,1),(5,4,'units/4/cESWby3igBVAt80Z5RV0bOb35rqg4aqCk9PtlYhr.png',1,0),(6,4,'units/4/xUSsfRP01ituroqrZJyjkWyiq6SQZTjXHM4kBQDf.png',0,1),(7,5,'units/5/D8ZpDxCCAkOVBEgx49RPjMZz4dP53Mcrbtne3WeM.jpg',1,0),(8,6,'units/6/WB1Ek9PQ0ZdHoRQGGYhiDbM4cWRWe6znwXzWWgHu.jpg',1,0),(9,7,'units/7/6P5sKDDZmILYbV9C87HupIRV2UxxsgYmGJPspiDs.jpg',1,0),(10,7,'units/7/gqOGDdX2s948K3Z8zigupnrhvbJHbCQwTplYDWKi.jpg',0,1),(11,7,'units/7/TyR8cG2f5nst7oo6tglKkro3ojvu3HNAmCixeHZv.png',0,2),(12,7,'units/7/TDmAKiBuwxHgWyQQTtehfkIw6Q5ao4ydqUXsJ9B5.png',0,3),(13,9,'units/9/fcwdEvwS9mqvPBXAypvC58tQ1vUvhWgaEHk3y82m.jpg',1,0),(14,9,'units/9/mcH1abU3ZZEeBSNvR6274cj1D4d6KHbQ2QmTtann.jpg',0,1),(15,9,'units/9/fi76CCWn56CysvmsWDIkmkNGTyuC8lSwv6Mz2atj.jpg',0,2),(16,9,'units/9/f6uMWUbW9AEApPF9vvfS6YyUdgGCkxNgAkt7pU3r.jpg',0,3),(17,10,'units/10/Si04BoeddgEFAJZLv91ppBndPlos91tu1iVWerFc.jpg',1,0),(19,10,'units/10/hNeM6lFpxn8dTC4yITaoruxkgKBGR4Fv2m9s7QSq.jpg',0,2),(20,10,'units/10/BYaKpb7tsZRJnYT2ASNTjZEpaMFHDr7qsLKymyx9.jpg',0,3);
/*!40000 ALTER TABLE `unit_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units` (
  `unit_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `house_id` int(10) unsigned NOT NULL,
  `unit_type` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `maximum_capacity` int(10) unsigned DEFAULT NULL,
  `extra_guest_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `child_free_through_age` tinyint(3) unsigned DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `check_in_time` time NOT NULL DEFAULT '14:00:00',
  `check_out_time` time NOT NULL DEFAULT '12:00:00',
  `pricing_mode` varchar(20) NOT NULL DEFAULT 'Per Unit',
  PRIMARY KEY (`unit_id`),
  KEY `units_house_id_foreign` (`house_id`),
  CONSTRAINT `units_house_id_foreign` FOREIGN KEY (`house_id`) REFERENCES `transient_houses` (`house_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (1,1,'Room 1',2,2,0.00,NULL,1500.00,'Available',NULL,'14:00:00','12:00:00','Per Unit'),(2,1,'Family Room',5,5,0.00,NULL,2800.00,'Available',NULL,'14:00:00','12:00:00','Per Unit'),(3,2,'Delux',10,15,600.00,3,5000.00,'Available','This is a very good unit max of 10','14:00:00','12:00:00','Per Unit'),(4,3,'Big Room',10,15,600.00,3,10000.00,'Available','this is a very good and wide spread unit.','14:00:00','12:00:00','Per Unit'),(5,4,'Family Room',10,15,600.00,0,9000.00,'Available','This is unit for a family good for 15 pax max.','14:00:00','12:00:00','Per Unit'),(6,4,'Delux',10,15,600.00,3,10000.00,'Available','This is a very good unit :)','14:00:00','12:00:00','Per Unit'),(7,5,'Single Room',2,3,300.00,2,2000.00,'Available','This is not a good unit, go out!','14:00:00','12:00:00','Per Unit'),(8,2,'sasa',1,1,0.00,0,100.00,'Unavailable','wqwq','13:00:00','10:00:00','Per Head'),(9,6,'Single Bed',2,3,0.00,3,600.00,'Available','This is a good spacious single bed. 1 extra is acceptable. blah blah blah....','14:00:00','12:00:00','Per Head'),(10,7,'Deluxxx3',10,15,600.00,3,15000.00,'Available','Come Here!','14:00:00','12:00:00','Per Unit');
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `registered_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'System','Admin','sysadmin@mctbs.com',NULL,'$2y$12$BnElukLT72uWTrt8zhjkAOhlTTI82izzg8a8Gd6pB07tdi.OaPFPS','2026-10-08 18:47:48',NULL,1,NULL),(2,2,'Test','Owner','owner@test.com','09170000001','$2y$12$rLaD4uzkK6TmWoVyTQ4C3OZcOhMRRCzjyQO6QCJ4EXYWOAys7dV6G','2026-10-08 19:02:31',NULL,1,NULL),(3,3,'Test','Staff','staff@test.com','09170000002','$2y$12$quHBzhAis9jVri1B9cZlp.9P8MDsyNGjfyJleT2mtH0E2pXJdPMOq','2026-10-08 19:02:32',NULL,1,NULL),(4,4,'Test','Guest','guest@test.com','09170000003','$2y$12$Id6CHcyTjCKdhYbEX8ho5e2yxOWR33XMIFfnzybiUC8HwRzC8nOkm','2026-10-08 19:02:32',NULL,1,NULL),(6,2,'Owner','Ako','owner2@gmail.com',NULL,'$2y$12$2c7INhOdq0S12IbwuE8iUO0SYHIt3jXByszVqeZrQZ55.UFmwU3VW','2026-10-11 23:19:29',NULL,1,'2026-10-11 23:19:29'),(7,2,'Karl','Marteja','karlangelomarteja2006@gmail.com','09761830493','$2y$12$6Mx9eaSd5nclWjb7cZpnZ.Q78CS/ibQ77dyE/ft3L6WOBfckDLEpe','2026-10-13 22:58:11',NULL,1,'2026-10-13 22:58:11'),(8,4,'Juan','Chu','juanchu@gmail.com',NULL,'$2y$12$WY4gzAM2QPf5YtiH8mTMDeYP6uguc7//J3rMvtXPpa4SmtsbWq06S',NULL,NULL,1,NULL),(9,2,'Loret','Motal-Ramirez','MTH@gmail.com',NULL,'$2y$12$PyfzIE5Fs4zYcuLTi4Qk8u4tfwWPysH8hkJIDX8iiEnEk6QiLHlGG','2026-10-14 17:37:38',NULL,1,'2026-10-14 17:37:38'),(10,4,'Juan','Dela Cruz','juan@gmail.com','0987654321','$2y$12$QFLC8AuIj0GVPhYhphQf..bY9Qa4ZpCp.Ty6PwPO1sqH7lhNqRIiW',NULL,NULL,1,'2026-11-03 09:36:45'),(11,2,'Owner','Ako','owner67@gmail.com','098765456','$2y$12$2W3Ebtp2TBKztFTGPuOCvutIG0/pxEWAtV0.7Oxd8Vfq0Z/jb65nq','2026-11-03 09:38:27',NULL,1,'2026-11-03 09:38:27'),(12,3,'Staffffffffff','Akochhh','staff1@gmail.com',NULL,'$2y$12$xDeW4iLjP2rqhr3wnfQSxeqJGgFunv0BJXoeZgt6.N8KejOeyGzgq','2026-11-03 09:55:18',NULL,1,'2026-11-03 09:55:18'),(13,4,'Samanthaaaaaa','Martejaaaaa','samantha@gmail.com',NULL,'$2y$12$DHqW7RN0lWjW3WoLYtIt0.1IcJGYwBQ33uFfdyRa3PVxNiN3DQQE6',NULL,NULL,1,'2026-11-03 10:04:03'),(14,3,'Staff','Ako','staffexample@gmail.com',NULL,'$2y$12$eIn96w4yNKA.niWtOertuu2V5ryqu9G72Bmn9hvaXCF7Qnt3zMEq2','2026-11-03 10:38:03',NULL,1,'2026-11-03 10:38:03');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-12  9:13:17
