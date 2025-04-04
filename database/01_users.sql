-- Base de données pour l'application SaaS de Télémétrie Moto
-- Version 1.0.0

-- Suppression de la base de données si elle existe déjà
DROP DATABASE IF EXISTS telemetrie_moto;

-- Création de la base de données
CREATE DATABASE telemetrie_moto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utilisation de la base de données
USE telemetrie_moto;

-- -----------------------------------------------------
-- 1. Gestion des Utilisateurs
-- -----------------------------------------------------

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'expert', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    active BOOLEAN DEFAULT TRUE,
    consent_community BOOLEAN DEFAULT FALSE,
    consent_data_collection BOOLEAN DEFAULT FALSE,
    api_usage_count INT DEFAULT 0,
    api_usage_limit INT DEFAULT 100
);

-- Table des appareils utilisateurs
CREATE TABLE user_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_uuid VARCHAR(100) NOT NULL,
    device_name VARCHAR(100),
    device_type VARCHAR(50),
    os_version VARCHAR(50),
    app_version VARCHAR(20),
    last_sync TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des préférences utilisateurs
CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    theme VARCHAR(20) DEFAULT 'default',
    language VARCHAR(10) DEFAULT 'fr',
    notifications_enabled BOOLEAN DEFAULT TRUE,
    telemetry_frequency INT DEFAULT 10, -- Hz
    video_quality VARCHAR(20) DEFAULT 'high',
    data_sync_wifi_only BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des jetons d'API
CREATE TABLE api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    expires_at TIMESTAMP NULL,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des réinitialisations de mot de passe
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE
);

-- Insertion d'un utilisateur administrateur par défaut
-- Mot de passe: admin123 (à changer en production)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@telemetrie-moto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
