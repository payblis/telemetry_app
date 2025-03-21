-- Base de données pour l'application de télémétrie
CREATE DATABASE IF NOT EXISTS db_tm_7845 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_tm_7845;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user', 'expert') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des télémétristes virtuels
CREATE TABLE telemetriste_virtuel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des pilotes
CREATE TABLE pilotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    poids DECIMAL(5,2),
    taille INT,
    age INT,
    experience TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des motos
CREATE TABLE motos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marque VARCHAR(50) NOT NULL,
    modele VARCHAR(100) NOT NULL,
    cylindree INT,
    puissance INT,
    poids DECIMAL(6,2),
    annee INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des circuits
CREATE TABLE circuits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    localisation VARCHAR(255),
    longueur DECIMAL(6,2),
    type_virages TEXT,
    adherence TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des équipements
CREATE TABLE equipements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    moto_id INT NOT NULL,
    type ENUM('suspension', 'frein', 'electronique', 'pneu', 'echappement', 'transmission', 'accessoire') NOT NULL,
    marque VARCHAR(100),
    modele VARCHAR(100),
    specifications TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE CASCADE
);

-- Table des sessions
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pilote_id INT NOT NULL,
    moto_id INT NOT NULL,
    circuit_id INT NOT NULL,
    date_session DATETIME NOT NULL,
    conditions_meteo TEXT,
    temperature_piste DECIMAL(4,1),
    temperature_air DECIMAL(4,1),
    humidite INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pilote_id) REFERENCES pilotes(id),
    FOREIGN KEY (moto_id) REFERENCES motos(id),
    FOREIGN KEY (circuit_id) REFERENCES circuits(id)
);

-- Table des tours
CREATE TABLE tours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    numero_tour INT NOT NULL,
    temps_tour DECIMAL(8,3),
    meilleur_temps BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table des données télémétriques
CREATE TABLE telemetry_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tour_id INT NOT NULL,
    timestamp DATETIME(3) NOT NULL,
    vitesse DECIMAL(6,2),
    regime_moteur INT,
    rapport_engage INT,
    angle_inclinaison DECIMAL(5,2),
    acceleration_longitudinale DECIMAL(5,2),
    acceleration_laterale DECIMAL(5,2),
    position_gps_lat DECIMAL(10,8),
    position_gps_long DECIMAL(11,8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE
);

-- Table des réponses experts
CREATE TABLE expert_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    question TEXT NOT NULL,
    reponse TEXT NOT NULL,
    validation_score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id)
);

-- Table des connaissances IA interne
CREATE TABLE ia_internal_knowledge (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categorie VARCHAR(50) NOT NULL,
    question TEXT NOT NULL,
    reponse TEXT NOT NULL,
    source_type ENUM('expert', 'system', 'validated') NOT NULL,
    source_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table de configuration API
CREATE TABLE api_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key VARCHAR(255) NOT NULL,
    service_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
); 