-- MySQL dump 10.13  Distrib 8.0.46, for Linux (aarch64)
--
-- Host: localhost    Database: vite_gourmand
-- ------------------------------------------------------
-- Server version	8.0.46

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


LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` (`id`, `title`, `description`, `theme`, `diet`, `minimum_persons`, `base_price`, `condition_text`, `stock_available`, `created_at`, `updated_at`) VALUES (1,'Menu Noël','Un menu festif pour les repas de fin d’année.','Noël','Classique',4,25.00,'Commande à effectuer au moins 7 jours avant la prestation.',5,'2026-06-19 11:00:00',NULL);
INSERT INTO `menu` (`id`, `title`, `description`, `theme`, `diet`, `minimum_persons`, `base_price`, `condition_text`, `stock_available`, `created_at`, `updated_at`) VALUES (2,'Menu Pâques','Un menu gourmand pour vos repas de Pâques.','Pâques','Végétarien',4,22.00,'Commande à effectuer au moins 5 jours avant la prestation.',5,'2026-06-19 11:00:00',NULL);
INSERT INTO `menu` (`id`, `title`, `description`, `theme`, `diet`, `minimum_persons`, `base_price`, `condition_text`, `stock_available`, `created_at`, `updated_at`) VALUES (3,'Menu Classique','Une formule simple et savoureuse pour tous vos événements.','Classique','Classique',2,18.00,'Commande à effectuer 48h avant la prestation.',10,'2026-06-19 11:00:00',NULL);
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`id`, `email`, `roles`, `password`, `first_name`, `last_name`, `phone`, `address`, `postal_code`, `city`, `is_active`, `created_at`) VALUES (1,'user@test.com','[\"ROLE_USER\"]','$2y$13$yEZC5WCO0tOOyUd/ceVDY.PQlP8nS/imfzR5PASnmdvlvyQtQawrO','Test','User','0600000000','1 rue du Test','33000','Bordeaux',1,'2026-06-10 14:14:16');
INSERT INTO `user` (`id`, `email`, `roles`, `password`, `first_name`, `last_name`, `phone`, `address`, `postal_code`, `city`, `is_active`, `created_at`) VALUES (2,'stephie.saurel@gmail.com','[\"ROLE_USER\"]','$2y$13$gr1JIq87.EjsocCYm6.mROFtLdorsSIlioEESFLbPvKilP8.jpiCW','Stéphie','Saurel','0683399551','4 Impasse Plobmann','67600','Sélestat',1,'2026-06-17 09:08:15');
INSERT INTO `user` (`id`, `email`, `roles`, `password`, `first_name`, `last_name`, `phone`, `address`, `postal_code`, `city`, `is_active`, `created_at`) VALUES (3,'employe@test.com','[\"ROLE_EMPLOYEE\"]','$2y$13$SnyIWLAru2xQTR1vGL37..YS5.boGWojyAuYahIK.Jb/YWXtvZt3m','Cristiano','Ronaldo','0600000000','10 rue de Bordeaux','33000','Bordeaux',1,'2026-06-25 10:53:07');
INSERT INTO `user` (`id`, `email`, `roles`, `password`, `first_name`, `last_name`, `phone`, `address`, `postal_code`, `city`, `is_active`, `created_at`) VALUES (4,'admin@test.com','[\"ROLE_ADMIN\"]','$2y$13$ObLNhAq81dxJgTWfACjeaujhcIvyP0TCnA/m8IlrC5OsX1i2uAGiO','Padre','Pizzaïolo','0600000000','10 rue de Bordeaux','33000','Bordeaux',1,'2026-06-26 09:24:23');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-03  8:49:20
