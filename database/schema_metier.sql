
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(100),
    address VARCHAR(100),
    postal_code VARCHAR(20),
    city VARCHAR(100),
    is_active BOOLEAN NOT NULL,
    created_at DATETIME NOT NULL
);

CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT,
    theme VARCHAR(100),
    diet VARCHAR(100),
    minimum_persons INT NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    condition_text LONGTEXT,
    stock_available INT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME
);

CREATE TABLE dish (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description LONGTEXT,
    type VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL
);

CREATE TABLE allergen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE customer_order (
    id INT AUTO_INCREMENT PRIMARY KEY,
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
    menu_price DECIMAL(10,2),
    delivery_price DECIMAL(10,2),
    discount_amount DECIMAL(10,2),
    total_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL,
    cancel_reason LONGTEXT,
    contact_mode VARCHAR(50),
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    user_id INT NOT NULL,
    menu_id INT NOT NULL,

    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (menu_id) REFERENCES menu(id)
);

CREATE TABLE menu_dish (
    menu_id INT NOT NULL,
    dish_id INT NOT NULL,

    PRIMARY KEY (menu_id, dish_id),
    FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dish(id) ON DELETE CASCADE
);

CREATE TABLE dish_allergen (
    dish_id INT NOT NULL,
    allergen_id INT NOT NULL,

    PRIMARY KEY (dish_id, allergen_id),
    FOREIGN KEY (dish_id) REFERENCES dish(id) ON DELETE CASCADE,
    FOREIGN KEY (allergen_id) REFERENCES allergen(id) ON DELETE CASCADE
);

CREATE TABLE order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status VARCHAR(50) NOT NULL,
    changed_at DATETIME NOT NULL,
    customer_order_id INT NOT NULL,

    FOREIGN KEY (customer_order_id)
        REFERENCES customer_order(id)
);

CREATE TABLE review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rating INT NOT NULL,
    comment LONGTEXT,
    is_validated BOOLEAN NOT NULL,
    created_at DATETIME NOT NULL,
    user_id INT NOT NULL,

    FOREIGN KEY (user_id) REFERENCES user(id)
);

CREATE TABLE reset_password_request (
    id INT AUTO_INCREMENT PRIMARY KEY,
    selector VARCHAR(20) NOT NULL,
    hashed_token VARCHAR(100) NOT NULL,
    requested_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    user_id INT NOT NULL,

    FOREIGN KEY (user_id) REFERENCES user(id)
);