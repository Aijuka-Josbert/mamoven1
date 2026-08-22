/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.8-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: mamaove
-- ------------------------------------------------------
-- Server version	11.8.8-MariaDB-1 from Debian

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `activity_description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `page_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_activity_type` (`activity_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES
(1,3,'samie','order_cancellation','Order #MO202604184262 cancelled','127.0.0.1',NULL,NULL,'2026-04-20 12:46:25');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` varchar(50) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_action` (`action`),
  KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES
(1,1,'admin','admin','admin_login','user','1','Admin logged in.','127.0.0.1','2026-08-20 06:59:52'),
(2,1,'admin','admin','admin_logout','user','1','','127.0.0.1','2026-08-20 07:11:21'),
(3,1,'admin','admin','admin_login','user','1','Admin logged in.','127.0.0.1','2026-08-20 07:11:45'),
(4,1,'admin','admin','admin_logout','user','1','','127.0.0.1','2026-08-20 07:21:21'),
(5,1,'admin','admin','admin_login','user','1','Admin logged in.','127.0.0.1','2026-08-20 21:58:10'),
(6,1,'admin','admin','admin_2fa_enabled','user','1','Two-factor authentication enabled (email code).','127.0.0.1','2026-08-20 22:04:53'),
(7,1,'admin','admin','admin_logout','user','1','','127.0.0.1','2026-08-20 22:05:07'),
(8,1,'admin','admin','admin_login','user','1','Admin logged in (2FA verified).','127.0.0.1','2026-08-20 22:05:47'),
(9,1,'admin','admin','order_deleted','order','21','Order #MO2026081966CC5B permanently deleted.','127.0.0.1','2026-08-20 22:05:54'),
(10,1,'admin','admin','order_deleted','order','20','Order #MO2026081958416C permanently deleted.','127.0.0.1','2026-08-20 22:06:00'),
(11,1,'admin','admin','admin_2fa_disabled','user','1','Two-factor authentication disabled.','127.0.0.1','2026-08-20 22:06:51'),
(12,1,'admin','admin','admin_logout','user','1','','127.0.0.1','2026-08-20 22:07:47'),
(13,10,'jonah','customer','customer_login','user','10','','127.0.0.1','2026-08-20 22:07:51'),
(14,10,'jonah','customer','customer_logout','user','10','','127.0.0.1','2026-08-20 22:08:16'),
(15,1,'admin','admin','admin_login','user','1','Admin logged in.','127.0.0.1','2026-08-20 22:08:21'),
(16,1,'admin','admin','customer_deleted','user','10','','127.0.0.1','2026-08-20 22:08:58'),
(17,1,'admin','admin','admin_logout','user','1','','127.0.0.1','2026-08-20 22:09:04'),
(18,NULL,NULL,NULL,'customer_registered','user','11','Username: jonah','127.0.0.1','2026-08-20 22:09:42'),
(19,11,'jonah','customer','customer_verified','user','11','','127.0.0.1','2026-08-20 22:11:56'),
(20,11,'jonah','customer','order_placed','order','22','Order #MO20260820E1C49B, UGX 15,800','127.0.0.1','2026-08-20 22:12:59'),
(21,11,'jonah','customer','customer_logout','user','11','','127.0.0.1','2026-08-20 22:13:59'),
(22,1,'admin','admin','admin_login','user','1','Admin logged in.','127.0.0.1','2026-08-20 22:14:04'),
(23,1,'admin','admin','order_status_updated','order','22','Status changed to: confirmed','127.0.0.1','2026-08-20 22:14:13'),
(24,1,'admin','admin','order_status_updated','order','22','Status changed to: ready','127.0.0.1','2026-08-20 22:15:18'),
(25,1,'admin','admin','order_status_updated','order','22','Status changed to: delivered','127.0.0.1','2026-08-20 22:15:53'),
(26,1,'admin','admin','admin_logout','user','1','','127.0.0.1','2026-08-20 22:42:25'),
(27,1,'admin','admin','admin_login','user','1','Admin logged in.','127.0.0.1','2026-08-21 07:56:13');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
INSERT INTO `cart` VALUES
(7,4,5,1,'2025-11-14 15:50:09'),
(12,4,3,1,'2025-12-06 21:12:29');
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES
(1,'Cakes','Delicious celebration cakes','active'),
(2,'Pastries','Freshly baked pastries','active'),
(3,'Cookies','Assorted cookies','active'),
(4,'biscuits','Bold sweet in all flavours','active'),
(5,'maize','maize','active'),
(6,'crunchies','for free time snack','active');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES
(1,'Heyy','josbertaijuka15@gmail.com','Hehe','2025-11-14 16:06:52'),
(2,'hey','josbert.aijuka@stud.umu.ac.ug','hhh','2026-01-01 17:26:11'),
(3,'hey','josbert.aijuka@stud.umu.ac.ug','hhh','2026-01-01 17:26:52'),
(4,'jjj','josbert.aijuka@stud.umu.ac.ug','jhjhj','2026-01-01 17:28:25'),
(5,'hey','josbertaijuka15@gmail.com','heyy','2026-04-20 08:06:19'),
(6,'hehy','josbertaijuka15@gmail.com','need some help','2026-04-20 09:00:10'),
(8,'Auto Contact','auto@test.local','AUTOTEST_CONTACT_DELETE','2026-04-20 10:26:56'),
(9,'josbert','josbertaijuka15@gmail.com','good morning','2026-08-19 03:38:51');
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `delivery_locations`
--

DROP TABLE IF EXISTS `delivery_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `delivery_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `fee` decimal(10,2) NOT NULL DEFAULT 5000.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_delivery_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_locations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `delivery_locations` WRITE;
/*!40000 ALTER TABLE `delivery_locations` DISABLE KEYS */;
INSERT INTO `delivery_locations` VALUES
(1,'Kampala Central',3000.00,1),
(2,'Ntinda / Kiwatule',5000.00,1),
(3,'Entebbe',15000.00,1),
(4,'Mukono',10000.00,1),
(5,'Bweyogerere',8000.00,1),
(6,'namugongo',7000.00,1),
(7,'Nakasero',0.00,0),
(8,'Old Kampala',0.00,0),
(9,'Kamwokya',0.00,0),
(10,'Kololo',0.00,0),
(11,'Bukoto',0.00,0),
(12,'Naguru',0.00,0),
(13,'Ntinda',0.00,0),
(14,'Kisaasi',0.00,0),
(15,'Kyanja',0.00,0),
(16,'Najjera',0.00,0),
(17,'Kiwatule',0.00,0),
(18,'Naalya',0.00,0),
(19,'Kireka',0.00,0),
(20,'Kyaliwajjala',0.00,0),
(21,'Kira Town',0.00,0),
(22,'Nakawa',0.00,0),
(23,'Luzira',0.00,0),
(24,'Butabika',0.00,0),
(25,'Bugolobi',0.00,0),
(26,'Mbuya',0.00,0),
(27,'Banda',0.00,0),
(28,'Kinawataka',0.00,0),
(29,'Makindye',0.00,0),
(30,'Nsambya',0.00,0),
(31,'Kabalagala',0.00,0),
(32,'Muyenga',0.00,0),
(33,'Ggaba',0.00,0),
(34,'Bunga',0.00,0),
(35,'Kansanga',0.00,0),
(36,'Najjanankumbi',0.00,0),
(37,'Salaama',0.00,0),
(38,'Kawempe',0.00,0),
(39,'Bwaise',0.00,0),
(40,'Kalerwe',0.00,0),
(41,'Mpererwe',0.00,0),
(42,'Kyebando',0.00,0),
(43,'Kazo',0.00,0),
(44,'Komamboga',0.00,0),
(45,'Rubaga',0.00,0),
(46,'Mengo',0.00,0),
(47,'Namirembe',0.00,0),
(48,'Nateete',0.00,0),
(49,'Lubaga',0.00,0),
(50,'Busega',0.00,0),
(51,'Ndeeba',0.00,0),
(52,'Kasubi',0.00,0),
(53,'Wandegeya',0.00,0),
(54,'Makerere',0.00,0),
(55,'Mulago',0.00,0),
(56,'Entebbe Road / Kajjansi',0.00,0),
(57,'Entebbe Town',0.00,0),
(58,'Najjera - Wakiso',0.00,0),
(59,'Nansana',0.00,0),
(60,'Namasuba',0.00,0),
(61,'Ssabagabo',0.00,0),
(62,'Gayaza',0.00,0),
(63,'Matugga',0.00,0),
(64,'Wakiso Town',0.00,0),
(65,'Mukono Town',0.00,0),
(66,'Seeta',0.00,0),
(67,'Namanve',0.00,0),
(68,'Jinja Town',0.00,0);
/*!40000 ALTER TABLE `delivery_locations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `email_logs`
--

DROP TABLE IF EXISTS `email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email_to` varchar(255) NOT NULL,
  `email_subject` varchar(255) NOT NULL,
  `email_type` varchar(50) NOT NULL,
  `related_order_id` int(11) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed') DEFAULT 'sent',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `email_type` (`email_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `email_logs` WRITE;
/*!40000 ALTER TABLE `email_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `low_stock_alerts`
--

DROP TABLE IF EXISTS `low_stock_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `low_stock_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `current_quantity` int(11) NOT NULL,
  `alert_triggered_at` timestamp NULL DEFAULT current_timestamp(),
  `acknowledged_by` int(11) DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_alert_product` (`product_id`),
  KEY `fk_alert_user` (`acknowledged_by`),
  CONSTRAINT `low_stock_alerts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `low_stock_alerts_ibfk_2` FOREIGN KEY (`acknowledged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `low_stock_alerts`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `low_stock_alerts` WRITE;
/*!40000 ALTER TABLE `low_stock_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `low_stock_alerts` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES
(1,1,3,1,70000.00,70000.00),
(2,2,3,1,70000.00,70000.00),
(3,3,3,2,70000.00,140000.00),
(4,4,3,1,70000.00,70000.00),
(5,4,5,1,2300.00,2300.00),
(6,5,5,1,2300.00,2300.00),
(7,6,3,1,70000.00,70000.00),
(8,6,4,6,5000.00,30000.00),
(9,7,5,2,2300.00,4600.00),
(10,8,5,1,2300.00,2300.00),
(11,9,3,1,70000.00,70000.00),
(12,10,5,1,2300.00,2300.00),
(13,11,4,1,5000.00,5000.00),
(16,14,3,1,70000.00,70000.00),
(17,15,3,1,70000.00,70000.00),
(19,17,5,1,2300.00,2300.00),
(20,17,4,1,5000.00,5000.00),
(21,18,4,1,5000.00,5000.00),
(25,22,5,1,2300.00,2300.00),
(26,22,4,1,5000.00,5000.00),
(27,22,6,1,1500.00,1500.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `order_status_history`
--

DROP TABLE IF EXISTS `order_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_history_order` (`order_id`),
  KEY `fk_history_user` (`changed_by`),
  CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_history`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `order_status_history` WRITE;
/*!40000 ALTER TABLE `order_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_status_history` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(32) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(20) NOT NULL DEFAULT 'cash_on_delivery',
  `payment_status` varchar(20) NOT NULL DEFAULT 'not_applicable',
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_provider` varchar(20) DEFAULT NULL,
  `delivery_address` varchar(255) NOT NULL,
  `delivery_phone` varchar(30) NOT NULL,
  `special_instructions` varchar(255) DEFAULT NULL,
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `cancelled_by` varchar(50) DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL,
  `promo_code_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `review_reminder_sent` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_user_status` (`user_id`,`status`),
  KEY `orders_ibfk_promo_code` (`promo_code_id`),
  KEY `idx_orders_payment_reference` (`payment_reference`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_promo_code` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES
(1,1,'MO202510107336',75000.00,'pending','cash_on_delivery','not_applicable',NULL,NULL,'naalya','+256 7995044','ks',NULL,NULL,NULL,'2025-10-10 10:18:10','2026-04-18 13:49:46',NULL,NULL,0.00,0),
(2,1,'MO202510101651',75000.00,'pending','cash_on_delivery','not_applicable',NULL,NULL,'haj','+256 759420168','s',NULL,NULL,NULL,'2025-10-10 10:41:16','2026-04-18 13:49:46',NULL,NULL,0.00,0),
(3,3,'MO202510183842',145000.00,'cancelled','cash_on_delivery','not_applicable',NULL,NULL,'joosojo','+256 7995044','jakksjjjs',NULL,NULL,NULL,'2025-10-18 18:50:44','2026-04-18 13:49:46',NULL,NULL,0.00,0),
(4,4,'MO202511148615',77300.00,'delivered','cash_on_delivery','not_applicable',NULL,NULL,'seta','+256 759420168','s',NULL,NULL,NULL,'2025-11-14 11:59:16','2026-04-18 15:08:53',NULL,NULL,0.00,0),
(5,5,'MO202511146776',7300.00,'confirmed','cash_on_delivery','not_applicable',NULL,NULL,'Jttg','+256759420168','',NULL,NULL,NULL,'2025-11-14 12:14:47','2026-04-18 13:49:46',NULL,NULL,0.00,0),
(6,6,'MO202511141695',105000.00,'ready','cash_on_delivery','not_applicable',NULL,NULL,'Lubaga','0754539083','Happy birthday 🎉',NULL,NULL,NULL,'2025-11-14 15:56:53','2026-04-18 13:49:46',NULL,NULL,0.00,0),
(7,1,'MO202512064505',9600.00,'delivered','cash_on_delivery','not_applicable',NULL,NULL,'fggfg','+256 7995044','fs',NULL,NULL,NULL,'2025-12-06 19:19:18','2026-04-18 13:49:46',NULL,NULL,0.00,0),
(8,3,'MO202604184899',7300.00,'delivered','cash_on_delivery','not_applicable',NULL,NULL,'jinja','+256759420168','this',NULL,NULL,NULL,'2026-04-18 14:23:01','2026-04-18 14:33:39',NULL,NULL,0.00,0),
(9,3,'MO202604184262',75000.00,'cancelled','cash_on_delivery','not_applicable',NULL,NULL,'namugongo, near shrine','0708173219','dds',NULL,NULL,NULL,'2026-04-18 15:23:42','2026-04-20 12:46:25',NULL,NULL,0.00,0),
(10,1,'MO202604188418',7300.00,'pending','cash_on_delivery','not_applicable',NULL,NULL,'this','099283-3-233','eew',NULL,NULL,NULL,'2026-04-18 15:54:01','2026-04-18 15:54:01',NULL,NULL,0.00,0),
(11,1,'MO202604200FB28B',13000.00,'delivered','cash_on_delivery','not_applicable',NULL,NULL,'Bweyogerere - watoto','0708173219','',NULL,NULL,NULL,'2026-04-20 09:07:07','2026-04-20 09:10:49',NULL,NULL,0.00,0),
(14,1,'MO20260420DCED99',73000.00,'pending','cash_on_delivery','not_applicable',NULL,NULL,'Kampala Central - near here','0708173219','leave at gate',NULL,NULL,NULL,'2026-04-20 12:57:16','2026-04-20 12:57:16',NULL,NULL,0.00,0),
(15,1,'MO202604200578BE',73000.00,'pending','cash_on_delivery','not_applicable',NULL,NULL,'Kampala Central - aah','0708173219','leave at gate',NULL,NULL,NULL,'2026-04-20 13:00:49','2026-04-20 13:00:49',NULL,NULL,0.00,0),
(17,1,'MO20260819A95090',17300.00,'pending','cash_on_delivery','not_applicable',NULL,NULL,'Mukono - thid snf thidq','0759420168','merry xmas',NULL,NULL,NULL,'2026-08-19 03:38:06','2026-08-19 03:38:06',NULL,NULL,0.00,0),
(18,1,'MO2026081935D59F',7000.00,'cancelled','cash_on_delivery','not_applicable',NULL,NULL,'namugongo - kyaliwajjala','0759420168','ds',NULL,NULL,NULL,'2026-08-19 03:43:53','2026-08-20 22:07:20',NULL,7,5000.00,0),
(22,11,'MO20260820E1C49B',15800.00,'delivered','cash_on_delivery','not_applicable',NULL,NULL,'namugongo - namugongo','+256786129181','snacks',NULL,NULL,NULL,'2026-08-20 22:12:59','2026-08-20 22:15:53',NULL,NULL,0.00,0);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `reset_code` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`email`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES
(10,5,'josbertaijuka15@gmail.com','45123','2026-08-18 17:55:28','2026-08-18 12:55:28');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image` longtext NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `is_primary` (`is_primary`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `product_reviews`
--

DROP TABLE IF EXISTS `product_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_review` (`product_id`,`user_id`),
  KEY `fk_review_product` (`product_id`),
  KEY `fk_review_user` (`user_id`),
  KEY `idx_reviews_product` (`product_id`),
  KEY `idx_reviews_user` (`user_id`),
  CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_reviews`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `product_reviews` WRITE;
/*!40000 ALTER TABLE `product_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_reviews` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `flavours` varchar(255) DEFAULT NULL,
  `ingredients` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 10,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `image` mediumtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `idx_products_featured` (`featured`,`status`),
  KEY `idx_products_stock` (`stock_quantity`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES
(3,'chocolate cake','cn','chocolate, flour','ch',70000.00,1,6,10,'active',1,'b83a93fc09466be714304ac55e393bdb.jpg','2025-10-10 10:09:36'),
(4,'sugar cane juice','made of sugarcane','sugarcane','sugarcane, water',5000.00,3,21,10,'active',0,'e807668c49f7c42dc4c5e3e825d38623.jpg','2025-10-18 19:02:26'),
(5,'chocolate biscuit','sweet','chocolate','dour, chocolate',2300.00,4,22,10,'active',0,'19c17e3fd4ddb69728031f21a70463b9.jpg','2025-11-13 18:35:39'),
(6,'crisps','tasty and crunchy','irish','irish',1500.00,6,99,10,'active',0,'d591b48ffc2cae4200104704bcfde0dc.avif','2026-08-19 04:00:09');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `promo_codes`
--

DROP TABLE IF EXISTS `promo_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `max_uses` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `valid_from` datetime DEFAULT NULL,
  `valid_until` datetime DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `status` (`status`),
  KEY `idx_promo_code_status` (`code`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promo_codes`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `promo_codes` WRITE;
/*!40000 ALTER TABLE `promo_codes` DISABLE KEYS */;
INSERT INTO `promo_codes` VALUES
(7,'CHRISTMAS','20% off birthday cakes','fixed',5000.00,1.00,NULL,1,'2026-08-19 00:00:00','2026-08-20 00:00:00','active','2026-08-19 03:42:24');
/*!40000 ALTER TABLE `promo_codes` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `promo_usage`
--

DROP TABLE IF EXISTS `promo_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `promo_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promo_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `promo_id` (`promo_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `promo_usage_ibfk_1` FOREIGN KEY (`promo_id`) REFERENCES `promo_codes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promo_usage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promo_usage_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promo_usage`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `promo_usage` WRITE;
/*!40000 ALTER TABLE `promo_usage` DISABLE KEYS */;
INSERT INTO `promo_usage` VALUES
(3,7,1,18,'2026-08-19 03:43:53');
/*!40000 ALTER TABLE `promo_usage` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `request_rate_limits`
--

DROP TABLE IF EXISTS `request_rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `request_rate_limits` (
  `bucket_hash` char(40) NOT NULL,
  `window_start` int(11) NOT NULL,
  `request_count` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`bucket_hash`),
  KEY `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request_rate_limits`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `request_rate_limits` WRITE;
/*!40000 ALTER TABLE `request_rate_limits` DISABLE KEYS */;
INSERT INTO `request_rate_limits` VALUES
('4fc420eb92ab04d9bf2e183be6c6c3c8578b4880',1787059134,1,'2026-08-18 13:18:54'),
('52b862b4ae790813e8c58d21fc8513daa8c5e7e2',1776689185,1,'2026-04-20 12:46:25'),
('577161c95e664c10a44ade8307663b097f13447d',1776690014,1,'2026-04-20 13:00:14'),
('6c8a20e1cfafb21bcb68baf949391ac99b016084',1776689150,1,'2026-04-20 12:45:50'),
('7047cdc0c62c85adfa944529ec3128623d718cb4',1787058780,5,'2026-08-18 13:14:35'),
('84ff686028ffdc0be067237ee8a69359d8277851',1776690016,1,'2026-04-20 13:00:16'),
('88921a8c2917770e356a8fb79d15fe6c472742a8',1787057430,5,'2026-08-18 12:54:29'),
('bf958d8d1b4eae62407f09dc1749be9c2b8d603d',1787057178,3,'2026-08-18 12:55:28'),
('cbbe9963c2340bd68891a80752ac54fba0383910',1787059134,1,'2026-08-18 13:18:54'),
('d2da194dd41eb8d56096431f7a411ced0c1f292b',1787055443,1,'2026-08-18 12:17:23'),
('f1bd3638f399d3242dd4c66da0aa7ee9d3393e46',1776690048,1,'2026-04-20 13:00:48');
/*!40000 ALTER TABLE `request_rate_limits` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `is_verified_purchase` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_review` (`product_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_reviews_product_rating` (`product_id`,`rating`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES
(1,5,3,4,'really good staff',1,'2026-04-18 14:55:42','2026-04-20 12:45:50'),
(2,4,9,4,'nice',0,'2026-04-18 21:11:47','2026-04-18 21:11:47');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_settings`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES
(1,'delivery_fee','5000'),
(2,'business_address','Kampala, Uganda'),
(3,'business_phone','+256 XXX XXX XXX'),
(4,'business_email','info@mamasoven.com'),
(5,'whatsapp_number','+256 XXX XXX XXX'),
(6,'low_stock_threshold','10');
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES
(1,3,'Auto Test','auto@example.com','AUTOTEST_FEEDBACK_1776680760',5,'approved','2026-04-20 10:26:00'),
(2,NULL,'Auto Test','auto@example.com','AUTOTEST_FEEDBACK_1776680816',5,'approved','2026-04-20 10:26:56'),
(3,NULL,'josbert',NULL,'best i have tested',5,'rejected','2026-08-19 03:45:03'),
(4,NULL,'josbert',NULL,'best i have tested',5,'pending','2026-08-19 03:45:09');
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id_hash` varchar(64) NOT NULL,
  `role` varchar(20) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `revoked_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_session_hash` (`session_id_hash`),
  KEY `idx_sessions_user` (`user_id`),
  KEY `idx_sessions_activity` (`last_activity_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
INSERT INTO `user_sessions` VALUES
(1,1,'49cdf6c3085c61ab5063b60b84e864eb7afb5990751e697fc8c83c2b1a89a0df','admin','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0','2026-08-20 21:58:10','2026-08-20 22:04:53',NULL),
(2,1,'6874ecc0ec1f6edb15f6ee39c3b7d4010e55b803b53b03c9bab569cc02beabc0','admin','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0','2026-08-20 22:05:47','2026-08-20 22:07:01',NULL),
(3,10,'53f8eaa2ce5e7bc808842853ea2baa753bab42abb88e5572ccc2bc3412c033f0','customer','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0','2026-08-20 22:07:51','2026-08-20 22:07:51',NULL),
(4,1,'dba7100d8aa086c10a2a44671ff10300c63c9570037ae4afba532e2a82f14f24','admin','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0','2026-08-20 22:08:21','2026-08-20 22:08:58',NULL),
(5,11,'09dc987efa1ec9c71e9d6fa3848b59004cf34c899eb6ea7cd0dc7ab91c852ef3','customer','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0','2026-08-20 22:11:56','2026-08-20 22:11:56',NULL),
(6,1,'4246ebbe45d98da200a914849dda5be33e2cb0197a8a148723f0baac89800f29','admin','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0','2026-08-20 22:14:03','2026-08-20 22:15:58',NULL),
(7,1,'e4175249bfc95799a33e015232da33396d5d9d6dea99a1bebaec379f5f7fb25a','admin','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0','2026-08-21 07:56:13','2026-08-21 07:56:49',NULL);
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL DEFAULT '',
  `address` varchar(255) DEFAULT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `verification_code` varchar(10) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(32) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_method` enum('totp','email') DEFAULT NULL,
  `email_otp_code` varchar(10) DEFAULT NULL,
  `email_otp_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin','joszialvin@gmail.com','$2y$12$nozThHFNP4es9TrWGX60iOGpihMS477lo.kpwWmnNWuXenKzfU1zy','Administrator','0759420168','','admin','2025-10-10 06:39:07',NULL,1,NULL,0,NULL,NULL,NULL),
(3,'samie','samanthak@gmail.com','$2y$12$F6h3EwU7YyuzQG.Kc.Tan.mZcOQqoepgC50NEkPFVVBUYpXy1D4BK','kawambe Samantha wokrach','0708173219','namugongo, near shrine','customer','2025-10-10 06:46:58',NULL,1,NULL,0,NULL,NULL,NULL),
(4,'Sheba','sheba@gmail.com','$2y$12$Hi1CibexaU3b1qzXEhe0Ce4yRh.HkuQ5PNF.hNARuFM.2WsNcNw8q','Sheba1','0000000000',NULL,'customer','2025-11-13 16:20:05',NULL,1,NULL,0,NULL,NULL,NULL),
(5,'0759420168','josbertaijuka15@gmail.com','$2y$12$TRvXQ3uDKvQowAoHwu7t4.qed5AO01IisfqRwPKj8l5yBqOoKJlMi','Aijuka Josbert','','','customer','2025-11-14 12:10:21',NULL,1,NULL,0,NULL,NULL,NULL),
(6,'Joanitah ','nabayegojoanitah70@gmail.com','$2y$12$D7L5L9oMAuJeqqnvZB8RJObAXiAtGbmuh4h.4XsyFehqn3ifrid5a','Nabayego ','0000000000',NULL,'customer','2025-11-14 15:43:42',NULL,1,NULL,0,NULL,NULL,NULL),
(9,'joszi','josbert.aijuka@stud.umu.ac.ug','$2y$12$8vvNIT8QOXbldIgJxem.suNgKrMU3mkVYFlPQeHmFKF1TlGDGeDxS','aijuka joszi','0759420168','','customer','2026-04-18 17:35:34',NULL,1,NULL,0,NULL,NULL,NULL),
(11,'jonah','josbertaijuk@gmail.com','$2y$12$fD9s8frXhNxpZxcu5vGNNOJ4bOvlwCxwRb6GHnyJeRNYCJf449aUO','jonah','+256786129181','namugongo','customer','2026-08-20 22:09:42',NULL,1,NULL,0,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-08-22 17:16:17
