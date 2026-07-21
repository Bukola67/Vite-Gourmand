CREATE DATABASE IF NOT EXISTS vite_gourmand CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE vite_gourmand;

DROP TABLE IF EXISTS dish_allergen;
DROP TABLE IF EXISTS menu_dish;
DROP TABLE IF EXISTS order_status_history;
DROP TABLE IF EXISTS reset_password_request;
DROP TABLE IF EXISTS review;
DROP TABLE IF EXISTS customer_order;
DROP TABLE IF EXISTS messenger_messages;
DROP TABLE IF EXISTS allergen;
DROP TABLE IF EXISTS dish;
DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS user;
DROP TABLE IF EXISTS doctrine_migration_versions;

CREATE TABLE allergen (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE menu (
  id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description LONGTEXT,
  theme VARCHAR(100) DEFAULT NULL,
  diet VARCHAR(100) DEFAULT NULL,
  minimum_persons INT NOT NULL,
  base_price DECIMAL(10,2) NOT NULL,
  condition_text LONGTEXT,
  stock_available INT DEFAULT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE dish (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  description LONGTEXT,
  type VARCHAR(50) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE user (
  id INT NOT NULL AUTO_INCREMENT,
  email VARCHAR(180) NOT NULL,
  roles JSON NOT NULL,
  password VARCHAR(255) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  phone VARCHAR(100) DEFAULT NULL,
  address VARCHAR(100) DEFAULT NULL,
  postal_code VARCHAR(20) DEFAULT NULL,
  city VARCHAR(100) DEFAULT NULL,
  is_active TINYINT NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY UNIQ_IDENTIFIER_EMAIL (email)
) ENGINE=InnoDB;

CREATE TABLE menu_dish (
  menu_id INT NOT NULL,
  dish_id INT NOT NULL,
  PRIMARY KEY (menu_id, dish_id),
  CONSTRAINT FK_MENU_DISH_MENU FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE,
  CONSTRAINT FK_MENU_DISH_DISH FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE dish_allergen (
  dish_id INT NOT NULL,
  allergen_id INT NOT NULL,
  PRIMARY KEY (dish_id, allergen_id),
  CONSTRAINT FK_DISH_ALLERGEN_DISH FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE,
  CONSTRAINT FK_DISH_ALLERGEN_ALLERGEN FOREIGN KEY (allergen_id) REFERENCES allergen (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE customer_order (
  id INT NOT NULL AUTO_INCREMENT,
  customer_first_name VARCHAR(100) NOT NULL,
  customer_last_name VARCHAR(100) NOT NULL,
  customer_email VARCHAR(180) NOT NULL,
  customer_phone VARCHAR(20) NOT NULL,
  delivery_address VARCHAR(255) NOT NULL,
  delivery_postal_code VARCHAR(20) NOT NULL,
  delivery_city VARCHAR(100) NOT NULL,
  delivery_time TIME NOT NULL,
  delivery_date DATE NOT NULL,
  delivery_place VARCHAR(255) NOT NULL,
  person_count INT NOT NULL,
  menu_price DECIMAL(10,2) DEFAULT NULL,
  delivery_price DECIMAL(10,2) DEFAULT NULL,
  discount_amount DECIMAL(10,2) DEFAULT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  status VARCHAR(50) NOT NULL,
  cancel_reason LONGTEXT,
  contact_mode VARCHAR(50) DEFAULT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME DEFAULT NULL,
  user_id INT DEFAULT NULL,
  menu_id INT NOT NULL,
  PRIMARY KEY (id),
  KEY idx_customer_order_user (user_id),
  KEY idx_customer_order_menu (menu_id),
  CONSTRAINT FK_CUSTOMER_ORDER_USER FOREIGN KEY (user_id) REFERENCES user (id),
  CONSTRAINT FK_CUSTOMER_ORDER_MENU FOREIGN KEY (menu_id) REFERENCES menu (id)
) ENGINE=InnoDB;

CREATE TABLE order_status_history (
  id INT NOT NULL AUTO_INCREMENT,
  status VARCHAR(50) NOT NULL,
  changed_at DATETIME NOT NULL,
  customer_order_id INT NOT NULL,
  PRIMARY KEY (id),
  KEY idx_order_status_history_order (customer_order_id),
  CONSTRAINT FK_ORDER_STATUS_HISTORY_ORDER FOREIGN KEY (customer_order_id) REFERENCES customer_order (id)
) ENGINE=InnoDB;

CREATE TABLE review (
  id INT NOT NULL AUTO_INCREMENT,
  rating INT NOT NULL,
  comment LONGTEXT,
  is_validated TINYINT NOT NULL,
  created_at DATETIME NOT NULL,
  user_id INT NOT NULL,
  PRIMARY KEY (id),
  KEY idx_review_user (user_id),
  CONSTRAINT FK_REVIEW_USER FOREIGN KEY (user_id) REFERENCES user (id)
) ENGINE=InnoDB;

CREATE TABLE reset_password_request (
  id INT NOT NULL AUTO_INCREMENT,
  selector VARCHAR(20) NOT NULL,
  hashed_token VARCHAR(100) NOT NULL,
  requested_at DATETIME NOT NULL,
  expires_at DATETIME NOT NULL,
  user_id INT NOT NULL,
  PRIMARY KEY (id),
  KEY idx_reset_password_user (user_id),
  CONSTRAINT FK_RESET_PASSWORD_USER FOREIGN KEY (user_id) REFERENCES user (id)
) ENGINE=InnoDB;

CREATE TABLE messenger_messages (
  id BIGINT NOT NULL AUTO_INCREMENT,
  body LONGTEXT NOT NULL,
  headers LONGTEXT NOT NULL,
  queue_name VARCHAR(190) NOT NULL,
  created_at DATETIME NOT NULL,
  available_at DATETIME NOT NULL,
  delivered_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_messenger_queue (queue_name, available_at, delivered_at, id)
) ENGINE=InnoDB;

CREATE TABLE doctrine_migration_versions (
  version VARCHAR(191) NOT NULL,
  executed_at DATETIME DEFAULT NULL,
  execution_time INT DEFAULT NULL,
  PRIMARY KEY (version)
) ENGINE=InnoDB;
