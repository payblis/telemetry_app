-- -----------------------------------------------------
-- 5. Analyse Vidéo
-- -----------------------------------------------------

USE telemetrie_moto;

-- Table des vidéos
CREATE TABLE videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_stockage VARCHAR(255) NOT NULL,
    duree INT, -- en secondes
    resolution VARCHAR(20),
    fps INT,
    taille_fichier BIGINT,
    format VARCHAR(10),
    statut_analyse ENUM('en_attente', 'en_cours', 'terminee', 'erreur') DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table des analyses vidéo
CREATE TABLE video_analyses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    timestamp_debut BIGINT,
    timestamp_fin BIGINT,
    type_analyse ENUM('trajectoire', 'inclinaison', 'freinage', 'acceleration', 'complete') DEFAULT 'complete',
    statut ENUM('en_attente', 'en_cours', 'terminee', 'erreur') DEFAULT 'en_attente',
    progression INT DEFAULT 0,
    resultats_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

-- Table des points d'analyse vidéo
CREATE TABLE video_points (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    video_analyse_id INT NOT NULL,
    timestamp BIGINT NOT NULL,
    frame_number INT NOT NULL,
    position_x DECIMAL(6,2),
    position_y DECIMAL(6,2),
    angle_inclinaison DECIMAL(5,2),
    trajectoire_x DECIMAL(6,2),
    trajectoire_y DECIMAL(6,2),
    vitesse_estimee DECIMAL(6,2),
    tour_id INT,
    INDEX (video_analyse_id, timestamp),
    FOREIGN KEY (video_analyse_id) REFERENCES video_analyses(id) ON DELETE CASCADE,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE SET NULL
);

-- Table des segments vidéo
CREATE TABLE video_segments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    type_segment ENUM('tour', 'virage', 'ligne_droite', 'incident') NOT NULL,
    timestamp_debut BIGINT NOT NULL,
    timestamp_fin BIGINT NOT NULL,
    reference_id INT, -- ID du tour ou virage correspondant
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

-- Table des données météo des sessions
CREATE TABLE meteo_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    source VARCHAR(50) DEFAULT 'api',
    temperature_min DECIMAL(4,1),
    temperature_max DECIMAL(4,1),
    temperature_moyenne DECIMAL(4,1),
    humidite_min DECIMAL(5,2),
    humidite_max DECIMAL(5,2),
    humidite_moyenne DECIMAL(5,2),
    pression_min DECIMAL(6,2),
    pression_max DECIMAL(6,2),
    pression_moyenne DECIMAL(6,2),
    vitesse_vent_min DECIMAL(5,2),
    vitesse_vent_max DECIMAL(5,2),
    vitesse_vent_moyenne DECIMAL(5,2),
    direction_vent_dominante VARCHAR(20),
    precipitation_totale DECIMAL(5,2),
    conditions_generales VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table des points météo
CREATE TABLE meteo_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meteo_session_id INT NOT NULL,
    timestamp BIGINT NOT NULL,
    temperature DECIMAL(4,1),
    humidite DECIMAL(5,2),
    pression DECIMAL(6,2),
    vitesse_vent DECIMAL(5,2),
    direction_vent VARCHAR(20),
    precipitation DECIMAL(5,2),
    conditions VARCHAR(50),
    INDEX (meteo_session_id, timestamp),
    FOREIGN KEY (meteo_session_id) REFERENCES meteo_sessions(id) ON DELETE CASCADE
);

-- Table des données de pneus par session
CREATE TABLE pneus_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    type_pneu_avant VARCHAR(100),
    type_pneu_arriere VARCHAR(100),
    pression_avant_debut DECIMAL(4,2),
    pression_arriere_debut DECIMAL(4,2),
    pression_avant_fin DECIMAL(4,2),
    pression_arriere_fin DECIMAL(4,2),
    temperature_avant_debut DECIMAL(4,1),
    temperature_arriere_debut DECIMAL(4,1),
    temperature_avant_fin DECIMAL(4,1),
    temperature_arriere_fin DECIMAL(4,1),
    usure_avant_debut INT, -- pourcentage
    usure_arriere_debut INT, -- pourcentage
    usure_avant_fin INT, -- pourcentage
    usure_arriere_fin INT, -- pourcentage
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Insertion de données d'exemple pour les vidéos
INSERT INTO videos (session_id, nom_fichier, chemin_stockage, duree, resolution, fps, taille_fichier, format, statut_analyse) VALUES
(1, 'session_paul_ricard_20250315.mp4', '/uploads/videos/session_1_20250315.mp4', 3600, '1920x1080', 60, 4500000000, 'mp4', 'terminee'),
(2, 'session_magny_cours_20250322.mp4', '/uploads/videos/session_2_20250322.mp4', 2700, '1920x1080', 60, 3200000000, 'mp4', 'terminee');

-- Insertion de données d'exemple pour les analyses vidéo
INSERT INTO video_analyses (video_id, timestamp_debut, timestamp_fin, type_analyse, statut, progression, resultats_json) VALUES
(1, 0, 3600000, 'complete', 'terminee', 100, '{"tours_detectes": 5, "virages_analyses": 15, "inclinaison_max": 52.3, "vitesse_max_estimee": 225.7}'),
(2, 0, 2700000, 'complete', 'terminee', 100, '{"tours_detectes": 4, "virages_analyses": 17, "inclinaison_max": 49.8, "vitesse_max_estimee": 205.2}');

-- Insertion de données d'exemple pour les segments vidéo
INSERT INTO video_segments (video_id, type_segment, timestamp_debut, timestamp_fin, reference_id, notes) VALUES
(1, 'tour', 120000, 252345, 1, 'Premier tour chronométré'),
(1, 'tour', 252345, 381066, 2, 'Deuxième tour chronométré'),
(1, 'tour', 381066, 505498, 3, 'Troisième tour chronométré'),
(1, 'virage', 150000, 158000, 1, 'Virage 1 - Premier tour'),
(1, 'virage', 280000, 288000, 1, 'Virage 1 - Deuxième tour'),
(2, 'tour', 90000, 208234, 6, 'Premier tour chronométré'),
(2, 'tour', 208234, 324357, 7, 'Deuxième tour chronométré'),
(2, 'virage', 120000, 127000, 4, 'Virage 1 - Premier tour'),
(2, 'virage', 238000, 245000, 4, 'Virage 1 - Deuxième tour');

-- Insertion de données d'exemple pour les données météo
INSERT INTO meteo_sessions (session_id, source, temperature_min, temperature_max, temperature_moyenne, 
                          humidite_min, humidite_max, humidite_moyenne, conditions_generales) VALUES
(1, 'api', 21.2, 23.8, 22.5, 40.0, 50.0, 45.0, 'Ensoleillé'),
(2, 'api', 17.5, 19.0, 18.2, 60.0, 70.0, 65.0, 'Nuageux');

-- Insertion de données d'exemple pour les données de pneus
INSERT INTO pneus_sessions (session_id, type_pneu_avant, type_pneu_arriere, 
                          pression_avant_debut, pression_arriere_debut, 
                          pression_avant_fin, pression_arriere_fin,
                          temperature_avant_debut, temperature_arriere_debut,
                          temperature_avant_fin, temperature_arriere_fin) VALUES
(1, 'Michelin Power Cup Evo', 'Michelin Power Cup Evo', 2.3, 2.1, 2.5, 2.3, 20.0, 20.0, 80.5, 85.2),
(2, 'Pirelli Diablo Supercorsa SP', 'Pirelli Diablo Supercorsa SP', 2.4, 2.2, 2.6, 2.4, 18.5, 18.5, 75.8, 82.3);
