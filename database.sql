-- =====================================================
-- Smart Farm Management & Market Linkage Decision Support System
-- Database Schema
-- =====================================================

CREATE DATABASE IF NOT EXISTS farm_system;
USE farm_system;

-- -----------------------------------------------------
-- Table: users
-- Stores farmers, extension officers, and buyers
-- -----------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('farmer', 'extension_officer', 'buyer', 'admin') NOT NULL DEFAULT 'farmer',
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table: farm_records
-- Crop production data: inputs, costs, yields
-- -----------------------------------------------------
CREATE TABLE farm_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    crop_name VARCHAR(100) NOT NULL,
    planting_date DATE,
    expected_harvest_date DATE,
    input_cost DECIMAL(10,2) DEFAULT 0,
    labour_cost DECIMAL(10,2) DEFAULT 0,
    other_cost DECIMAL(10,2) DEFAULT 0,
    quantity_harvested DECIMAL(10,2) DEFAULT 0,
    unit VARCHAR(20) DEFAULT 'kg',
    status ENUM('planted', 'growing', 'harvested') DEFAULT 'planted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Table: sales
-- Records of produce sold, linked to a farm record
-- -----------------------------------------------------
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_record_id INT NOT NULL,
    user_id INT NOT NULL,
    buyer_name VARCHAR(100),
    quantity_sold DECIMAL(10,2) NOT NULL,
    price_per_unit DECIMAL(10,2) NOT NULL,
    sale_date DATE NOT NULL,
    market_type ENUM('local', 'international') DEFAULT 'local',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_record_id) REFERENCES farm_records(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Table: market_listings
-- Produce a farmer lists for buyers to see (Market Linkage module)
-- -----------------------------------------------------
CREATE TABLE market_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    crop_name VARCHAR(100) NOT NULL,
    quantity_available DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) DEFAULT 'kg',
    asking_price DECIMAL(10,2) NOT NULL,
    location VARCHAR(100),
    status ENUM('available', 'sold') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Table: market_prices
-- Reference prices per crop, used by the decision support module
-- -----------------------------------------------------
CREATE TABLE market_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_name VARCHAR(100) NOT NULL,
    month INT NOT NULL,
    average_price DECIMAL(10,2) NOT NULL,
    market_type ENUM('local', 'international') DEFAULT 'local'
);

-- -----------------------------------------------------
-- Sample data to test the system
-- -----------------------------------------------------
INSERT INTO users (full_name, email, phone, password, role, location) VALUES
('Joseph Kamindo', 'joseph@example.com', '0712345678', '$2y$10$examplehashvalueplaceholder', 'farmer', 'Kiambu'),
('Peter Halwenge', 'peter@example.com', '0798765432', '$2y$10$examplehashvalueplaceholder', 'extension_officer', 'Kiambu');

INSERT INTO market_prices (crop_name, month, average_price, market_type) VALUES
('Tomatoes', 1, 45.00, 'local'), ('Tomatoes', 6, 70.00, 'local'), ('Tomatoes', 12, 90.00, 'local'),
('Maize', 1, 40.00, 'local'), ('Maize', 6, 55.00, 'local'), ('Maize', 12, 65.00, 'local'),
('Cabbages', 1, 20.00, 'local'), ('Cabbages', 6, 35.00, 'local'), ('Cabbages', 12, 30.00, 'local');
