-- ============================================================
-- FILE: database.sql
-- PURPOSE: Create ThreadHaven database and tables
-- HOW TO USE: Open phpMyAdmin → SQL tab → paste and run
-- ============================================================

CREATE DATABASE IF NOT EXISTS threadhaven_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE threadhaven_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(120)  NOT NULL,
    email       VARCHAR(180)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    phone       VARCHAR(20)   DEFAULT NULL,
    bio         TEXT          DEFAULT NULL,
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    seller_id   INT           NOT NULL,
    title       VARCHAR(200)  NOT NULL,
    description TEXT,
    price       DECIMAL(10,2) NOT NULL,
    category    ENUM('sweaters','hoodies','pants','jorts','other') DEFAULT 'other',
    size        VARCHAR(10)   DEFAULT NULL,
    condition_q ENUM('New','Like New','Good','Fair') DEFAULT 'Good',
    emoji       VARCHAR(8)    DEFAULT '👕',
    status      ENUM('available','reserved','sold') DEFAULT 'available',
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT           NOT NULL,
    product_id  INT           NOT NULL,
    quantity    INT           DEFAULT 1,
    added_at    DATETIME      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id    INT           NOT NULL,
    total       DECIMAL(10,2) NOT NULL,
    status      ENUM('pending','paid','shipped','delivered','cancelled') DEFAULT 'pending',
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items
CREATE TABLE IF NOT EXISTS order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT           NOT NULL,
    product_id  INT           NOT NULL,
    quantity    INT           NOT NULL DEFAULT 1,
    price_each  DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Reservations table
-- FIX: expires_at uses a DATETIME with a trigger instead of expression default
-- for compatibility with MySQL 5.7 / MariaDB < 10.2
CREATE TABLE IF NOT EXISTS reservations (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT           NOT NULL,
    product_id  INT           NOT NULL,
    reserved_at DATETIME      DEFAULT CURRENT_TIMESTAMP,
    expires_at  DATETIME      NOT NULL,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Trigger to set expires_at = reserved_at + 24 hours on insert
DROP TRIGGER IF EXISTS set_reservation_expiry;
DELIMITER $$
CREATE TRIGGER set_reservation_expiry
BEFORE INSERT ON reservations
FOR EACH ROW
BEGIN
    SET NEW.expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR);
END$$
DELIMITER ;

-- Sample data (password is: password)
INSERT INTO users (full_name, email, password, phone) VALUES
('Demo User', 'demo@threadhaven.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0700000000');

INSERT INTO products (seller_id, title, description, price, category, size, emoji) VALUES
(1,'Vintage Cream Hoodie',  'Super soft fleece hoodie in vintage cream. Barely worn.',28.00,'hoodies','M','🧥'),
(1,'Dark Wash Slim Jeans',  'Classic dark wash, slim fit. Great condition.',          22.00,'pants',  '32','👖'),
(1,'Forest Green Sweater',  'Cable-knit crewneck sweater. Warm and stylish.',         35.00,'sweaters','L','🧶'),
(1,'Denim Jorts',           'Distressed denim shorts, cut just above the knee.',      18.00,'jorts',  '30','🩳'),
(1,'Oversized Grey Hoodie', 'Extra comfy oversized hoodie with kangaroo pocket.',     32.00,'hoodies','XL','🧥'),
(1,'Burgundy Knit Sweater', 'Slim-fit V-neck sweater in rich burgundy.',              40.00,'sweaters','S','🧶');
