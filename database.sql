CREATE DATABASE IF NOT EXISTS moto_telemetry;
USE moto_telemetry;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS riders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- Informations personnelles
    last_name VARCHAR(255) NOT NULL,
    first_name VARCHAR(255),
    track_name VARCHAR(255),
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    profile_photo VARCHAR(255),
    
    -- Informations morphologiques
    height INT COMMENT 'Taille en cm',
    weight DECIMAL(5,2) COMMENT 'Poids en kg',
    bmi DECIMAL(4,2) COMMENT 'Indice de masse corporelle',
    preferred_position ENUM('avant', 'neutre', 'arriere') COMMENT 'Position préférée sur la moto',
    arm_length INT COMMENT 'Longueur des bras en cm',
    leg_length INT COMMENT 'Longueur des jambes en cm',
    
    -- Niveau / expérience
    level ENUM('debutant', 'intermediaire', 'confirme', 'competition') NOT NULL,
    years_experience INT,
    riding_types SET('trackday', 'fsbk', 'endurance', 'loisir') NOT NULL,
    has_license BOOLEAN DEFAULT FALSE,
    license_number VARCHAR(50),
    has_coach BOOLEAN DEFAULT FALSE,
    
    -- Comportement / ressentis
    front_grip_sensitivity ENUM('forte', 'moyenne', 'faible'),
    rear_grip_sensitivity ENUM('forte', 'moyenne', 'faible'),
    riding_style ENUM('freinage_tardif', 'sortie_rapide', 'neutre'),
    progression_areas TEXT COMMENT 'Zones de progression identifiées',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertion de l'administrateur par défaut
INSERT INTO users (email, password, name, role) 
VALUES ('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', 'admin'); 