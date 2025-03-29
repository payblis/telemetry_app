-- Script SQL pour la création de la base de données de l'application de télémétrie moto
-- Version simplifiée sans framework

-- Création de la base de données (à décommenter si nécessaire)
-- CREATE DATABASE IF NOT EXISTS telemoto;
-- USE telemoto;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'USER', 'EXPERT') NOT NULL DEFAULT 'USER',
    telemetrician_name VARCHAR(100) DEFAULT 'Télémétriste',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des pilotes
CREATE TABLE IF NOT EXISTS pilots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    height INT NOT NULL COMMENT 'Taille en cm',
    weight INT NOT NULL COMMENT 'Poids en kg',
    experience TEXT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des motos
CREATE TABLE IF NOT EXISTS motos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    engine_capacity INT NOT NULL COMMENT 'Cylindrée en cc',
    year INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des réglages de moto
CREATE TABLE IF NOT EXISTS moto_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    moto_id INT NOT NULL,
    setting_name VARCHAR(50) NOT NULL,
    setting_value VARCHAR(50) NOT NULL,
    setting_unit VARCHAR(20),
    setting_type ENUM('SUSPENSION', 'TRANSMISSION', 'ENGINE', 'TIRES', 'OTHER') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE CASCADE
);

-- Table des circuits
CREATE TABLE IF NOT EXISTS circuits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(50) NOT NULL,
    length INT NOT NULL COMMENT 'Longueur en mètres',
    width INT COMMENT 'Largeur en mètres',
    corners_count INT COMMENT 'Nombre de virages',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des virages de circuit
CREATE TABLE IF NOT EXISTS circuit_corners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    circuit_id INT NOT NULL,
    corner_number INT NOT NULL,
    corner_type ENUM('LEFT', 'RIGHT', 'CHICANE') NOT NULL,
    angle INT COMMENT 'Angle en degrés',
    estimated_speed INT COMMENT 'Vitesse estimée en km/h',
    recommended_gear INT COMMENT 'Rapport conseillé',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (circuit_id) REFERENCES circuits(id) ON DELETE CASCADE
);

-- Table des sessions
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    session_type ENUM('RACE', 'QUALIFYING', 'PRACTICE', 'TRAINING', 'TRACK_DAY') NOT NULL,
    pilot_id INT NOT NULL,
    moto_id INT NOT NULL,
    circuit_id INT NOT NULL,
    weather VARCHAR(50),
    track_temperature FLOAT,
    air_temperature FLOAT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pilot_id) REFERENCES pilots(id) ON DELETE CASCADE,
    FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE CASCADE,
    FOREIGN KEY (circuit_id) REFERENCES circuits(id) ON DELETE CASCADE
);

-- Table des temps au tour
CREATE TABLE IF NOT EXISTS lap_times (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    lap_number INT NOT NULL,
    time_seconds FLOAT NOT NULL COMMENT 'Temps en secondes',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table des feedbacks IA
CREATE TABLE IF NOT EXISTS ai_feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT,
    problem_description TEXT NOT NULL,
    problem_type VARCHAR(50) NOT NULL,
    solution_description TEXT NOT NULL,
    settings_changes TEXT NOT NULL,
    source ENUM('AI', 'COMMUNITY', 'EXPERT') NOT NULL DEFAULT 'AI',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL
);

-- Table des validations des recommandations IA
CREATE TABLE IF NOT EXISTS ai_validations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ai_feedback_id INT NOT NULL,
    user_id INT NOT NULL,
    validation_type ENUM('POSITIVE', 'NEUTRAL', 'NEGATIVE') NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ai_feedback_id) REFERENCES ai_feedbacks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des feedbacks experts
CREATE TABLE IF NOT EXISTS expert_feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    problem_type VARCHAR(50) NOT NULL,
    moto_id INT,
    circuit_id INT,
    feedback_text TEXT NOT NULL,
    settings_recommendations TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE SET NULL,
    FOREIGN KEY (circuit_id) REFERENCES circuits(id) ON DELETE SET NULL
);

-- Insertion d'un utilisateur admin par défaut (mot de passe: admin123)
INSERT INTO users (username, email, password, role, telemetrician_name) 
VALUES ('admin', 'admin@telemoto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', 'Admin Télémétriste')
ON DUPLICATE KEY UPDATE id=id;

-- Insertion de quelques circuits d'exemple
INSERT INTO circuits (name, country, length, width, corners_count) VALUES
('Circuit Bugatti', 'France', 4185, 13, 14),
('Circuit Paul Ricard', 'France', 5842, 15, 15),
('Circuit de Barcelona-Catalunya', 'Espagne', 4655, 12, 16),
('Misano World Circuit', 'Italie', 4226, 14, 16)
ON DUPLICATE KEY UPDATE id=id;
