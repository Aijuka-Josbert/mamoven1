/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: mamaove
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-5 from Debian

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
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
(5,'maize','maize','active');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
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
(4,'jjj','josbert.aijuka@stud.umu.ac.ug','jhjhj','2026-01-01 17:28:25');
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
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
(5,'Bweyogerere',8000.00,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
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
(12,10,5,1,2300.00,2300.00);
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
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_promo_code` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES
(1,1,'MO202510107336',75000.00,'pending','naalya','+256 7995044','ks',NULL,NULL,NULL,'2025-10-10 10:18:10','2026-04-18 13:49:46',NULL,NULL,0.00,0),
(2,1,'MO202510101651',75000.00,'pending','haj','+256 759420168','s',NULL,NULL,NULL,'2025-10-10 10:41:16','2026-04-18 13:49:46',NULL,NULL,0.00,0),
(3,3,'MO202510183842',145000.00,'cancelled','joosojo','+256 7995044','jakksjjjs',NULL,NULL,NULL,'2025-10-18 18:50:44','2026-04-18 13:49:46',NULL,NULL,0.00,0),
(4,4,'MO202511148615',77300.00,'delivered','seta','+256 759420168','s',NULL,NULL,NULL,'2025-11-14 11:59:16','2026-04-18 15:08:53',NULL,NULL,0.00,0),
(5,5,'MO202511146776',7300.00,'confirmed','Jttg','+256759420168','',NULL,NULL,NULL,'2025-11-14 12:14:47','2026-04-18 13:49:46',NULL,NULL,0.00,0),
(6,6,'MO202511141695',105000.00,'ready','Lubaga','0754539083','Happy birthday 🎉',NULL,NULL,NULL,'2025-11-14 15:56:53','2026-04-18 13:49:46',NULL,NULL,0.00,0),
(7,1,'MO202512064505',9600.00,'delivered','fggfg','+256 7995044','fs',NULL,NULL,NULL,'2025-12-06 19:19:18','2026-04-18 13:49:46',NULL,NULL,0.00,0),
(8,3,'MO202604184899',7300.00,'delivered','jinja','+256759420168','this',NULL,NULL,NULL,'2026-04-18 14:23:01','2026-04-18 14:33:39',NULL,NULL,0.00,0),
(9,3,'MO202604184262',75000.00,'pending','namugongo, near shrine','0708173219','dds',NULL,NULL,NULL,'2026-04-18 15:23:42','2026-04-18 15:23:42',NULL,NULL,0.00,0),
(10,1,'MO202604188418',7300.00,'pending','this','099283-3-233','eew',NULL,NULL,NULL,'2026-04-18 15:54:01','2026-04-18 15:54:01',NULL,NULL,0.00,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES
(1,1,'joszialvin@gmail.com','47588','2025-11-14 12:34:27','2025-10-10 10:22:29');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES
(3,'chocolate cake','cn','chocolate, flour','ch',70000.00,1,8,10,'active',1,'data:image/jpeg;base64,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','2025-10-10 10:09:36'),
(4,'sugar cane juice','made of sugarcane','sugarcane','sugarcane, water',5000.00,3,24,10,'active',0,'data:image/jpeg;base64,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','2025-10-18 19:02:26'),
(5,'chocolate biscuit','sweet','chocolate','dour, chocolate',2300.00,4,24,10,'active',0,'data:image/jpeg;base64,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','2025-11-13 18:35:39');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promo_codes`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `promo_codes` WRITE;
/*!40000 ALTER TABLE `promo_codes` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promo_usage`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `promo_usage` WRITE;
/*!40000 ALTER TABLE `promo_usage` DISABLE KEYS */;
/*!40000 ALTER TABLE `promo_usage` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES
(1,5,3,2,'delicious',1,'2026-04-18 14:55:42','2026-04-18 14:55:42');
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin','joszialvin@gmail.com','$2y$12$1zBGwYleUbSNalJDoEKrOuLqnbWkrUS8Jc7hSPBP7nRpkFujIdB..','Administrator','','','admin','2025-10-10 06:39:07',NULL,1),
(3,'samie','samanthak@gmail.com','$2y$12$F6h3EwU7YyuzQG.Kc.Tan.mZcOQqoepgC50NEkPFVVBUYpXy1D4BK','kawambe Samantha','0708173219','namugongo, near shrine','customer','2025-10-10 06:46:58',NULL,1),
(4,'Sheba','sheba@gmail.com','$2y$12$Hi1CibexaU3b1qzXEhe0Ce4yRh.HkuQ5PNF.hNARuFM.2WsNcNw8q','Sheba1','0000000000',NULL,'customer','2025-11-13 16:20:05',NULL,1),
(5,'0759420168','josbertaijuka15@gmail.com','$2y$12$TRvXQ3uDKvQowAoHwu7t4.qed5AO01IisfqRwPKj8l5yBqOoKJlMi','Aijuka Josbert','','','customer','2025-11-14 12:10:21',NULL,1),
(6,'Joanitah ','nabayegojoanitah70@gmail.com','$2y$12$D7L5L9oMAuJeqqnvZB8RJObAXiAtGbmuh4h.4XsyFehqn3ifrid5a','Nabayego ','0000000000',NULL,'customer','2025-11-14 15:43:42',NULL,1),
(9,'Aijuka','josbert.aijuka@stud.umu.ac.ug','$2y$12$8vvNIT8QOXbldIgJxem.suNgKrMU3mkVYFlPQeHmFKF1TlGDGeDxS','aijuka','0759420168',NULL,'customer','2026-04-18 17:35:34','503422',0);
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

-- Dump completed on 2026-04-18 21:38:42
