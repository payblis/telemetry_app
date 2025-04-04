-- Base de données pour SaaS Télémétrie Moto

CREATE DATABASE IF NOT EXISTS moto_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE moto_saas;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user', 'expert') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pilotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    pseudo VARCHAR(100),
    taille_cm INT,
    poids_kg INT,
    niveau VARCHAR(50),
    experience_annees INT,
    style_pilotage VARCHAR(100),
    sensibilite_grip VARCHAR(50),
    licence BOOLEAN,
    numero_licence VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE motos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pilote_id INT NOT NULL,
    marque VARCHAR(100),
    modele VARCHAR(100),
    annee INT,
    configuration TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pilote_id) REFERENCES pilotes(id)
);

CREATE TABLE circuits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    pays VARCHAR(100),
    longueur_km FLOAT,
    nb_virages INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pilote_id INT NOT NULL,
    moto_id INT NOT NULL,
    circuit_id INT NOT NULL,
    date_session DATE,
    type_session ENUM('free practice', 'qualification', 'course', 'trackday'),
    meteo VARCHAR(100),
    temperature_air FLOAT,
    temperature_piste FLOAT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pilote_id) REFERENCES pilotes(id),
    FOREIGN KEY (moto_id) REFERENCES motos(id),
    FOREIGN KEY (circuit_id) REFERENCES circuits(id)
);

CREATE TABLE laps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    numero_tour INT,
    temps_tour FLOAT,
    vitesse_max FLOAT,
    vitesse_moyenne FLOAT,
    angle_max FLOAT,
    acceleration_moyenne FLOAT,
    freinage_moyen FLOAT,
    video_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

CREATE TABLE reglages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    precharge_avant FLOAT,
    precharge_arriere FLOAT,
    detente_avant FLOAT,
    detente_arriere FLOAT,
    compression_avant FLOAT,
    compression_arriere FLOAT,
    hauteur_avant FLOAT,
    hauteur_arriere FLOAT,
    rapport_final VARCHAR(50),
    pression_pneu_avant FLOAT,
    pression_pneu_arriere FLOAT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

CREATE TABLE expert_knowledge (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    situation TEXT,
    symptome TEXT,
    conseil TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id)
);

CREATE TABLE ia_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    prompt_envoye TEXT,
    reponse_ia TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);
