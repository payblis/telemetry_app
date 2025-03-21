-- Création de la base de données
CREATE DATABASE IF NOT EXISTS db_tm_7845;
USE db_tm_7845;

-- Table des sessions
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_session DATETIME DEFAULT CURRENT_TIMESTAMP,
    nom_circuit VARCHAR(100) NOT NULL,
    donnees_circuit TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des messages de chat
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT,
    date_message DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_message TEXT,
    ia_message TEXT,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table de configuration API
CREATE TABLE IF NOT EXISTS api_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle_api_openai VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
); 