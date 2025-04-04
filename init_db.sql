-- Création de la base de données
CREATE DATABASE IF NOT EXISTS moto_saas;
USE moto_saas;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user', 'expert') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des pilotes
CREATE TABLE IF NOT EXISTS pilotes (
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

-- Table des motos
CREATE TABLE IF NOT EXISTS motos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pilote_id INT NOT NULL,
    marque VARCHAR(100),
    modele VARCHAR(100),
    annee INT,
    configuration TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pilote_id) REFERENCES pilotes(id)
);

-- Table des circuits
CREATE TABLE IF NOT EXISTS circuits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    pays VARCHAR(100),
    longueur_km FLOAT,
    nb_virages INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des sessions
CREATE TABLE IF NOT EXISTS sessions (
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

-- Table des tours
CREATE TABLE IF NOT EXISTS laps (
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

-- Table des réglages
CREATE TABLE IF NOT EXISTS reglages (
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

-- Table des connaissances expertes
CREATE TABLE IF NOT EXISTS expert_knowledge (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    situation TEXT,
    symptome TEXT,
    conseil TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id)
);

-- Table des logs IA
CREATE TABLE IF NOT EXISTS ia_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    prompt_envoye TEXT,
    reponse_ia TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

-- Création de l'utilisateur administrateur par défaut
-- Mot de passe : admin123
INSERT INTO users (email, password, role) VALUES 
('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); 