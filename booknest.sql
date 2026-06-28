-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: booknest-booknest.b.aivencloud.com    Database: defaultdb
-- ------------------------------------------------------
-- Server version	8.4.8

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- GTID state at the beginning of the backup 
--


--
-- Table structure for table `books`
--

DROP TABLE IF EXISTS `books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `books` (
  `book_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `author` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`book_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `books`
--

LOCK TABLES `books` WRITE;
/*!40000 ALTER TABLE `books` DISABLE KEYS */;
INSERT INTO `books` VALUES (1,'The Quiet Library','Mira Tan','Children',42.94,22,'A calm fiction story about friendship, choices, and memories.','book_6a3102815c25d4.59420222.png','2026-05-13 17:04:33'),(2,'Learning Web Apps','D. Kumar','Academic',58.00,8,'A beginner-friendly academic book for learning web application development.','book_6a3106850e6e87.31080354.png','2026-05-13 17:04:33'),(3,'Tiny Adventures','Lily Chen','Children',26.50,19,'A children book about imagination and adventure.','book_6a30fee3c15121.78743828.png','2026-05-13 17:04:33'),(4,'Mindful Habits','Aaron','Self-Improvement',39.90,11,'A self-improvement book about building better daily habits.','book_6a311182d4cce6.37909872.png','2026-05-13 17:04:33'),(7,'he Moonlight Treehouse','Aina Lee','Children',24.90,20,'A heartwarming children’s story about two best friends who discover a magical treehouse hidden in the forest. Inside, they find glowing lanterns, talking birds, and secret storybooks that take them on gentle nighttime adventures. Perfect for young readers who enjoy imagination, friendship, and magical places.','book_6a310516474a25.18471461.png','2026-06-16 08:11:02'),(8,'Database Systems Made Simple','R. Lim','Academic',52.00,10,'A clear and beginner-friendly academic guide to understanding database systems, including data models, SQL basics, relationships, normalization, and database design concepts. Suitable for students who are learning database fundamentals for the first time.','book_6a310a3b05a718.03644065.png','2026-06-16 08:32:59'),(9,'Introduction to Cybersecurity','S. Rahman','Academic',60.00,9,'An introductory academic book that explains the basic concepts of cybersecurity, including online threats, password safety, network security, malware, encryption, and safe digital practices. Suitable for students and beginners who want to understand cybersecurity fundamentals.','book_6a310abd2f73d0.39951034.png','2026-06-16 08:35:09'),(10,'The Last Letter at Dawn','Clara Venn','Fiction',39.90,24,'A touching fiction novel about a young woman who discovers an old letter hidden inside her grandmother’s house. As she follows the clues from the past, she uncovers family secrets, lost memories, and the meaning of forgiveness.','book_6a310b8baacf45.71952548.png','2026-06-16 08:38:35'),(11,'Beneath the Silver Rain','Eliza Hart','Fiction',42.00,10,'A captivating fiction novel about two strangers whose lives unexpectedly cross during a rainy evening in the city. As their connection deepens, they must face old regrets, hidden truths, and the possibility of a second chance at love.','book_6a310c1d538405.22426678.png','2026-06-16 08:41:01'),(12,'The House of Hidden Stars','Nora Vale','Fiction',45.90,15,'A mysterious fiction novel about a young writer who moves into an old countryside house and discovers strange star-shaped marks hidden throughout the rooms. As she investigates their meaning, she uncovers secrets about the house, its former owner, and a story that was never meant to be forgotten.','book_6a310c7fbe90c6.17276431.png','2026-06-16 08:42:39'),(13,'Small Steps, Big Change','Daniel Koh','Self-Improvement',36.90,3,'A practical self-improvement book that teaches readers how small daily habits can create meaningful long-term change. It focuses on goal setting, motivation, discipline, time management, and building a better routine without feeling overwhelmed.','book_6a310f2d4d0522.70220542.png','2026-06-16 08:54:05'),(14,'Focus Your Day','Megan Lee','Self-Improvement',37.90,21,'A practical self-improvement book that helps readers improve focus, manage daily tasks, reduce distractions, and build a more productive routine. Suitable for students, workers, and anyone who wants to make better use of their time.','book_6a3111f319df51.45675527.png','2026-06-16 09:05:55'),(15,'Pixel Heroes: Level One','Max Chen','Comics',25.00,10,'A fun comic book about three young heroes who are pulled into a mysterious video game world. To return home, they must complete challenges, defeat digital monsters, and learn the power of teamwork.','book_6a311291336fb6.43047597.png','2026-06-16 09:08:33'),(16,'Captain Cat and the Sky Pirates','Leo Tan','Comics',31.90,9,'A humorous adventure comic about Captain Cat, a brave and clever hero who protects the floating islands from mischievous sky pirates. With funny battles, flying ships, and loyal friends, this comic is full of action and comedy.','book_6a311313e08a89.43801363.png','2026-06-16 09:10:43'),(17,'Robot School Adventures','Nina Wong','Comics',28.90,9,'A funny comic book about a group of robot students learning how to become future heroes. From classroom chaos to science fair disasters, every chapter is filled with friendship, teamwork, and hilarious robot mistakes.','book_6a3113822a3744.50812359.png','2026-06-16 09:12:34');
/*!40000 ALTER TABLE `books` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_messages`
--

DROP TABLE IF EXISTS `customer_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `sender_role` enum('customer','admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `customer_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_messages`
--

LOCK TABLES `customer_messages` WRITE;
/*!40000 ALTER TABLE `customer_messages` DISABLE KEYS */;
INSERT INTO `customer_messages` VALUES (1,2,'customer','hi',0,'2026-06-21 16:28:33');
/*!40000 ALTER TABLE `customer_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `book_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,4,4,1,39.90),(2,5,15,1,25.00),(3,5,17,2,28.90),(4,6,2,1,58.00),(5,6,11,1,42.00),(6,7,9,1,60.00),(7,7,16,1,31.90),(8,8,15,1,25.00),(9,8,16,1,31.90),(10,9,16,1,31.90),(11,10,17,1,28.90),(12,11,3,1,26.50),(13,11,17,1,28.90),(14,12,12,1,45.90),(15,13,14,1,37.90),(16,14,17,1,28.90);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status_history`
--

DROP TABLE IF EXISTS `order_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_status_history` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `status` enum('Pending','Processing','Completed','Cancelled','Refunded','Refund Requested','Refund Approved','Refund Rejected') NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changed_by_user_id` int DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`history_id`),
  KEY `fk_history_order` (`order_id`),
  KEY `fk_history_user` (`changed_by_user_id`),
  CONSTRAINT `fk_history_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_history_user` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_history`
--

LOCK TABLES `order_status_history` WRITE;
/*!40000 ALTER TABLE `order_status_history` DISABLE KEYS */;
INSERT INTO `order_status_history` VALUES (2,2,'Pending','2026-05-13 17:04:34',NULL,'Initial imported order status'),(4,4,'Pending','2026-06-15 08:33:21',NULL,'Initial imported order status'),(8,5,'Pending','2026-06-16 19:34:40',2,'Order placed by customer'),(9,5,'Processing','2026-06-16 19:43:32',1,'Status changed from Pending to Processing'),(10,5,'Completed','2026-06-16 19:43:45',1,'Status changed from Processing to Completed'),(11,5,'Cancelled','2026-06-16 19:44:14',1,'Status changed from Completed to Cancelled'),(12,5,'Completed','2026-06-16 19:44:37',1,'Status changed from Cancelled to Completed'),(13,5,'Pending','2026-06-17 16:44:24',1,'Status changed from Completed to Pending'),(14,5,'Processing','2026-06-17 16:44:25',1,'Status changed from Pending to Processing'),(15,5,'Completed','2026-06-17 16:44:26',1,'Status changed from Processing to Completed'),(16,5,'Processing','2026-06-17 17:01:44',1,'Status changed from Completed to Processing'),(17,5,'Completed','2026-06-17 17:03:17',1,'Status changed from Processing to Completed'),(18,6,'Pending','2026-06-20 09:44:32',2,'Order placed by customer'),(19,6,'Cancelled','2026-06-20 09:44:50',2,'Cancelled by customer'),(20,7,'Pending','2026-06-21 06:26:15',2,'Order placed by customer'),(21,7,'Processing','2026-06-21 06:28:02',1,'Status changed from Pending to Processing'),(22,7,'Completed','2026-06-21 06:28:15',1,'Status changed from Processing to Completed'),(23,2,'Cancelled','2026-06-21 07:03:54',3,'Cancelled by customer'),(24,8,'Pending','2026-06-21 07:04:35',3,'Order placed by customer'),(25,8,'Processing','2026-06-21 07:05:27',1,'Status changed from Pending to Processing'),(26,8,'Completed','2026-06-21 07:05:32',1,'Status changed from Processing to Completed'),(27,9,'Pending','2026-06-21 07:26:05',3,'Order placed by customer'),(28,10,'Pending','2026-06-21 07:28:05',2,'Order placed by customer'),(29,10,'Completed','2026-06-21 07:29:32',1,'Status changed from Pending to Completed'),(30,10,'Refund Requested','2026-06-21 07:30:22',2,'Customer requested refund: water damaged'),(31,10,'Refund Rejected','2026-06-21 07:31:28',1,'Refund rejected by admin'),(32,10,'Refund Requested','2026-06-21 07:57:31',2,'Customer requested refund: water damaged'),(33,10,'Refund Approved','2026-06-21 07:58:39',1,'Refund approved by admin'),(34,11,'Processing','2026-06-21 16:07:39',1,'Status changed from Pending to Processing'),(35,13,'Completed','2026-06-21 16:13:31',1,'Status changed from Pending to Completed'),(36,12,'Cancelled','2026-06-21 17:35:53',2,'Cancelled by customer'),(37,14,'Pending','2026-06-21 17:36:37',2,'Order placed by customer');
/*!40000 ALTER TABLE `order_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Processing','Completed','Cancelled','Refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delivery_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `delivery_contact` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `delivery_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `delivery_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Cash on Delivery',
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `payment_reference` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `card_last_four` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (2,3,42.90,'Cancelled','2026-05-13 17:04:34',NULL,NULL,NULL,NULL,'Cash on Delivery','Pending',NULL,NULL,NULL),(4,4,44.90,'Pending','2026-06-15 08:33:21',NULL,NULL,NULL,NULL,'Cash on Delivery','Pending',NULL,NULL,NULL),(5,2,82.80,'Completed','2026-06-16 19:34:40',NULL,NULL,NULL,NULL,'Cash on Delivery','Pending',NULL,NULL,NULL),(6,2,100.00,'Cancelled','2026-06-20 09:44:32',NULL,NULL,NULL,NULL,'Cash on Delivery','Pending',NULL,NULL,NULL),(7,2,91.90,'Refunded','2026-06-21 06:26:14',NULL,NULL,NULL,NULL,'Cash on Delivery','Pending',NULL,NULL,NULL),(8,3,61.90,'Completed','2026-06-21 07:04:35',NULL,NULL,NULL,NULL,'Cash on Delivery','Pending',NULL,NULL,NULL),(9,3,36.90,'Pending','2026-06-21 07:26:05',NULL,NULL,NULL,NULL,'Cash on Delivery','Pending',NULL,NULL,NULL),(10,2,33.90,'Refunded','2026-06-21 07:28:05',NULL,NULL,NULL,NULL,'Cash on Delivery','Pending',NULL,NULL,NULL),(11,3,60.40,'Processing','2026-06-21 16:04:53','Tan Kai','0987654321','tankai@example.com','jln abc','Online Transfer','Pending Verification','MBB09876123456',NULL,'2026-06-21 18:04:53'),(12,2,50.90,'Cancelled','2026-06-21 16:09:38','Amanda Lee','0987654321','amanda@example.com','1234567','Credit/Debit Card','Paid',NULL,'3456','2026-06-21 18:09:38'),(13,2,42.90,'Completed','2026-06-21 16:11:12','Amanda Lee','123456789','amanda@example.com','1267','Online Transfer','Pending Verification','CMB0987654321',NULL,'2026-06-21 18:11:12'),(14,2,33.90,'Pending','2026-06-21 17:36:37','Amanda Lee','0123456789','amanda@example.com','Kuala Lumpur','Cash on Delivery','Pending',NULL,NULL,NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refunds`
--

DROP TABLE IF EXISTS `refunds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refunds` (
  `refund_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `reason` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`refund_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refunds`
--

LOCK TABLES `refunds` WRITE;
/*!40000 ALTER TABLE `refunds` DISABLE KEYS */;
INSERT INTO `refunds` VALUES (3,7,'Books are damaged',91.90,'Approved','2026-06-21 06:36:01','2026-06-21 06:36:58'),(4,8,'damage books',61.90,'Rejected','2026-06-21 07:06:12','2026-06-21 07:06:49'),(5,10,'water damaged',33.90,'Rejected','2026-06-21 07:30:22','2026-06-21 07:31:28'),(6,10,'water damaged',33.90,'Approved','2026-06-21 07:57:31','2026-06-21 07:58:39');
/*!40000 ALTER TABLE `refunds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `book_id` int NOT NULL,
  `order_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `one_review_per_purchase` (`order_id`,`book_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`),
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `reviews_chk_1` CHECK ((`rating` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,17,5,3,'good for my kids','2026-06-20 03:38:24'),(2,15,5,5,'Best book ever!','2026-06-21 08:35:19');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('customer','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'customer',
  `contact` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin User','admin@booknest.com','$2y$10$RGaXtTtoaR2pnDUsgt7PmeXIySBGwdYDyrh/kBOBHfyS4nN2u7rfi','admin','0123456789','2026-05-13 17:04:33'),(2,'Amanda Lee','amanda@example.com','$2y$10$ur78ic6/Lfe3ycu1g7CQoed3ZY1nxtYOutHCeprGJlG1L.OECYYaO','customer','0112222333','2026-05-13 17:04:33'),(3,'Tan Kai','tankai@example.com','$2y$10$d4XwEzm4VMcw1vDxiZuNO.QlX3ocZ47kEvl2Tomwqr5r1YnJzjMru','customer','0113333444','2026-05-13 17:04:33'),(4,'Test','test@example.com','$2y$10$fRG..0mSIZVBRRR9j1t8POR2xJ6aeU/2eSUrbxKcUUIo2S6PpUY42','customer','0123456889','2026-06-09 04:55:52');
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

-- Dump completed on 2026-06-22  1:44:18