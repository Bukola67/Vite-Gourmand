
-- ============================================================
-- VITE & GOURMAND
-- Script de création de la base de données MySQL
-- ============================================================
-- SGBD : MySQL 8
-- Moteur : InnoDB
-- Encodage : utf8mb4
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================
-- 1. TABLE USER
-- ============================================================

CREATE TABLE `user` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(180) NOT NULL,
    `roles` JSON NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(100) DEFAULT NULL,
    `address` VARCHAR(100) DEFAULT NULL,
    `postal_code` VARCHAR(20) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `is_active` TINYINT NOT NULL,
    `created_at` DATETIME NOT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


-- ============================================================
-- 2. TABLE MENU
-- ============================================================

CREATE TABLE `menu` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` LONGTEXT,
    `theme` VARCHAR(100) DEFAULT NULL,
    `diet` VARCHAR(100) DEFAULT NULL,
    `minimum_persons` INT NOT NULL,
    `base_price` DECIMAL(10,2) NOT NULL,
    `condition_text` LONGTEXT,
    `stock_available` INT DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME DEFAULT NULL,

    PRIMARY KEY (`id`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


-- ============================================================
-- 3. TABLE DISH
-- ============================================================

CREATE TABLE `dish` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` LONGTEXT,
    `type` VARCHAR(50) NOT NULL,
    `created_at` DATETIME NOT NULL,

    PRIMARY KEY (`id`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


-- ============================================================
-- 4. TABLE ALLERGEN
-- ============================================================

CREATE TABLE `allergen` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,

    PRIMARY KEY (`id`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


-- ============================================================
-- 5. TABLE CUSTOMER_ORDER
-- ============================================================
-- Relations :
-- user (1,N) customer_order
-- menu (1,N) customer_order
-- ============================================================

CREATE TABLE `customer_order` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `customer_first_name` VARCHAR(100) NOT NULL,
    `customer_last_name` VARCHAR(100) NOT NULL,
    `customer_email` VARCHAR(180) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `delivery_address` VARCHAR(255) NOT NULL,
    `delivery_postal_code` VARCHAR(20) NOT NULL,
    `delivery_city` VARCHAR(100) NOT NULL,
    `delivery_time` TIME NOT NULL,
    `delivery_date` DATE NOT NULL,
    `delivery_place` VARCHAR(255) NOT NULL,
    `person_count` INT NOT NULL,
    `menu_price` DECIMAL(10,2) DEFAULT NULL,
    `delivery_price` DECIMAL(10,2) DEFAULT NULL,
    `discount_amount` DECIMAL(10,2) DEFAULT NULL,
    `total_price` DECIMAL(10,2) NOT NULL,
    `status` VARCHAR(50) NOT NULL,
    `cancel_reason` LONGTEXT,
    `contact_mode` VARCHAR(50) DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    `user_id` INT NOT NULL,
    `menu_id` INT NOT NULL,

    PRIMARY KEY (`id`),

    KEY `IDX_3B1CE6A3A76ED395` (`user_id`),
    KEY `IDX_3B1CE6A3CCD7E912` (`menu_id`),

    CONSTRAINT `FK_3B1CE6A3A76ED395`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`),

    CONSTRAINT `FK_3B1CE6A3CCD7E912`
        FOREIGN KEY (`menu_id`)
        REFERENCES `menu` (`id`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


-- ============================================================
-- 6. TABLE MENU_DISH
-- ============================================================
-- Table associative : relation ManyToMany
-- ============================================================

CREATE TABLE `menu_dish` (
    `menu_id` INT NOT NULL,
    `dish_id` INT NOT NULL,

    PRIMARY KEY (`menu_id`, `dish_id`),

    KEY `IDX_5D327CF6148EB0CB` (`dish_id`),

    CONSTRAINT `FK_5D327CF6CCD7E912`
        FOREIGN KEY (`menu_id`)
        REFERENCES `menu` (`id`)
        ON DELETE CASCADE,

    CONSTRAINT `FK_5D327CF6148EB0CB`
        FOREIGN KEY (`dish_id`)
        REFERENCES `dish` (`id`)
        ON DELETE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


-- ============================================================
-- 7. TABLE DISH_ALLERGEN
-- ============================================================
-- Table associative : relation ManyToMany
-- ============================================================

CREATE TABLE `dish_allergen` (
    `dish_id` INT NOT NULL,
    `allergen_id` INT NOT NULL,

    PRIMARY KEY (`dish_id`, `allergen_id`),

    KEY `IDX_3C4389A56E775A4A` (`allergen_id`),

    CONSTRAINT `FK_3C4389A5148EB0CB`
        FOREIGN KEY (`dish_id`)
        REFERENCES `dish` (`id`)
        ON DELETE CASCADE,

    CONSTRAINT `FK_3C4389A56E775A4A`
        FOREIGN KEY (`allergen_id`)
        REFERENCES `allergen` (`id`)
        ON DELETE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


-- ============================================================
-- 8. TABLE ORDER_STATUS_HISTORY
-- ============================================================
-- Historique des changements de statut des commandes
-- ============================================================

CREATE TABLE `order_status_history` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `status` VARCHAR(50) NOT NULL,
    `changed_at` DATETIME NOT NULL,
    `customer_order_id` INT NOT NULL,

    PRIMARY KEY (`id`),

    KEY `IDX_471AD77EA15A2E17` (`customer_order_id`),

    CONSTRAINT `FK_471AD77EA15A2E17`
        FOREIGN KEY (`customer_order_id`)
        REFERENCES `customer_order` (`id`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


-- ============================================================
-- 9. TABLE REVIEW
-- ============================================================
-- Avis déposés par les utilisateurs
-- ============================================================

CREATE TABLE `review` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `rating` INT NOT NULL,
    `comment` LONGTEXT,
    `is_validated` TINYINT NOT NULL,
    `created_at` DATETIME NOT NULL,
    `user_id` INT NOT NULL,

    PRIMARY KEY (`id`),

    KEY `IDX_794381C6A76ED395` (`user_id`),

    CONSTRAINT `FK_794381C6A76ED395`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


-- ============================================================
-- 10. TABLE RESET_PASSWORD_REQUEST
-- ============================================================
-- Gestion des demandes de réinitialisation de mot de passe
-- ============================================================

CREATE TABLE `reset_password_request` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `selector` VARCHAR(20) NOT NULL,
    `hashed_token` VARCHAR(100) NOT NULL,
    `requested_at` DATETIME NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `user_id` INT NOT NULL,

    PRIMARY KEY (`id`),

    KEY `IDX_7CE748AA76ED395` (`user_id`),

    CONSTRAINT `FK_7CE748AA76ED395`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


-- ============================================================
-- 11. TABLE DOCTRINE_MIGRATION_VERSIONS
-- ============================================================
-- Suivi des migrations Doctrine
-- ============================================================

CREATE TABLE `doctrine_migration_versions` (
    `version` VARCHAR(191) NOT NULL,
    `executed_at` DATETIME DEFAULT NULL,
    `execution_time` INT DEFAULT NULL,

    PRIMARY KEY (`version`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


-- ============================================================
-- 12. TABLE MESSENGER_MESSAGES
-- ============================================================
-- Gestion des messages asynchrones Symfony Messenger
-- ============================================================

CREATE TABLE `messenger_messages` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `body` LONGTEXT NOT NULL,
    `headers` LONGTEXT NOT NULL,
    `queue_name` VARCHAR(190) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `available_at` DATETIME NOT NULL,
    `delivered_at` DATETIME DEFAULT NULL,

    PRIMARY KEY (`id`),

    KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750`
        (`queue_name`, `available_at`, `delivered_at`, `id`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


