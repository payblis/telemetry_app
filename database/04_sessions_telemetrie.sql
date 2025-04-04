-- -----------------------------------------------------
-- 4. Gestion des Sessions et Télémétrie
-- -----------------------------------------------------

USE telemetrie_moto;

-- Table des sessions
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pilote_id INT NOT NULL,
    moto_id INT NOT NULL,
    circuit_id INT NOT NULL,
    configuration_id INT,
    type_session ENUM('course', 'qualification', 'free_practice', 'entrainement', 'track_day') NOT NULL,
    date_session DATE NOT NULL,
    heure_debut TIME,
    heure_fin TIME,
    conditions_meteo VARCHAR(50),
    temperature_air DECIMAL(4,1),
    temperature_piste DECIMAL(4,1),
    humidite DECIMAL(5,2),
    pression_atmospherique DECIMAL(6,2),
    vitesse_vent DECIMAL(5,2),
    direction_vent VARCHAR(20),
    notes_generales TEXT,
    statut ENUM('en_cours', 'terminee', 'annulee') DEFAULT 'en_cours',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pilote_id) REFERENCES pilotes(id) ON DELETE CASCADE,
    FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE CASCADE,
    FOREIGN KEY (circuit_id) REFERENCES circuits(id) ON DELETE CASCADE,
    FOREIGN KEY (configuration_id) REFERENCES configurations_moto(id) ON DELETE SET NULL
);

-- Table des réglages de session
CREATE TABLE reglages_session (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    reglage_id INT NOT NULL,
    valeur_initiale VARCHAR(50) NOT NULL,
    valeur_finale VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (reglage_id) REFERENCES reglages_moto(id) ON DELETE CASCADE
);

-- Table des tours
CREATE TABLE tours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    numero_tour INT NOT NULL,
    temps TIME(3) NOT NULL,
    source ENUM('manuel', 'telemetrie', 'video') DEFAULT 'manuel',
    valide BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table des temps par secteur
CREATE TABLE secteurs_temps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tour_id INT NOT NULL,
    secteur_id INT NOT NULL,
    temps TIME(3) NOT NULL,
    vitesse_max DECIMAL(5,2),
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE,
    FOREIGN KEY (secteur_id) REFERENCES secteurs_circuit(id) ON DELETE CASCADE
);

-- Table des sessions de télémétrie
CREATE TABLE telemetrie_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    device_id INT NOT NULL,
    frequence_echantillonnage INT DEFAULT 10,
    precision_gps DECIMAL(5,2),
    calibration_gyro BOOLEAN DEFAULT FALSE,
    calibration_accel BOOLEAN DEFAULT FALSE,
    debut_enregistrement TIMESTAMP NULL,
    fin_enregistrement TIMESTAMP NULL,
    taille_donnees BIGINT,
    statut ENUM('en_cours', 'complete', 'erreur') DEFAULT 'en_cours',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (device_id) REFERENCES user_devices(id) ON DELETE CASCADE
);

-- Table des points de télémétrie
CREATE TABLE telemetrie_points (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    telemetrie_session_id INT NOT NULL,
    timestamp BIGINT NOT NULL,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    altitude DECIMAL(7,2),
    vitesse DECIMAL(6,2),
    acceleration_x DECIMAL(6,3),
    acceleration_y DECIMAL(6,3),
    acceleration_z DECIMAL(6,3),
    gyro_x DECIMAL(6,3),
    gyro_y DECIMAL(6,3),
    gyro_z DECIMAL(6,3),
    angle_inclinaison DECIMAL(5,2),
    tour_id INT,
    INDEX (telemetrie_session_id, timestamp),
    FOREIGN KEY (telemetrie_session_id) REFERENCES telemetrie_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE SET NULL
);

-- Table des données agrégées de télémétrie
CREATE TABLE telemetrie_agregee (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tour_id INT NOT NULL,
    vitesse_max DECIMAL(6,2),
    vitesse_min DECIMAL(6,2),
    vitesse_moyenne DECIMAL(6,2),
    acceleration_max DECIMAL(6,3),
    deceleration_max DECIMAL(6,3),
    angle_inclinaison_max DECIMAL(5,2),
    distance_parcourue DECIMAL(8,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE
);

-- Insertion de données d'exemple pour les sessions
INSERT INTO sessions (user_id, pilote_id, moto_id, circuit_id, configuration_id, type_session, date_session, 
                     conditions_meteo, temperature_air, temperature_piste, humidite, notes_generales, statut) VALUES
(1, 1, 1, 1, 1, 'track_day', '2025-03-15', 'Ensoleillé', 22.5, 28.3, 45.0, 'Bonne adhérence, vent faible', 'terminee'),
(1, 2, 2, 2, 3, 'entrainement', '2025-03-22', 'Nuageux', 18.2, 21.0, 65.0, 'Piste légèrement humide par endroits', 'terminee');

-- Insertion de données d'exemple pour les réglages de session
INSERT INTO reglages_session (session_id, reglage_id, valeur_initiale, valeur_finale, notes) VALUES
(1, 1, '5', '6', 'Augmenté pour améliorer la stabilité en entrée de virage'),
(1, 2, '8', '7', 'Réduit pour moins d\'affaissement en freinage'),
(1, 3, '10', '10', 'Conservé, bon comportement'),
(1, 4, '7', '8', 'Augmenté pour améliorer la traction en sortie'),
(2, 9, '4', '3', 'Réduit pour plus de feeling en entrée'),
(2, 10, '10', '12', 'Augmenté pour plus de stabilité'),
(2, 11, '12', '12', 'Conservé, bon comportement'),
(2, 12, '8', '9', 'Augmenté pour améliorer la motricité');

-- Insertion de données d'exemple pour les tours
INSERT INTO tours (session_id, numero_tour, temps, source, valide) VALUES
(1, 1, '00:02:12.345', 'telemetrie', 1),
(1, 2, '00:02:08.721', 'telemetrie', 1),
(1, 3, '00:02:05.432', 'telemetrie', 1),
(1, 4, '00:02:03.876', 'telemetrie', 1),
(1, 5, '00:02:02.543', 'telemetrie', 1),
(2, 1, '00:01:58.234', 'telemetrie', 1),
(2, 2, '00:01:56.123', 'telemetrie', 1),
(2, 3, '00:01:55.876', 'telemetrie', 1),
(2, 4, '00:01:54.321', 'telemetrie', 1);

-- Insertion de données d'exemple pour les temps par secteur
INSERT INTO secteurs_temps (tour_id, secteur_id, temps, vitesse_max) VALUES
(1, 1, '00:00:45.123', 165.2),
(1, 2, '00:00:52.456', 210.5),
(1, 3, '00:00:34.766', 185.3),
(2, 1, '00:00:44.321', 168.7),
(2, 2, '00:00:50.234', 215.2),
(2, 3, '00:00:34.166', 187.1),
(3, 1, '00:00:43.654', 170.3),
(3, 2, '00:00:48.543', 218.6),
(3, 3, '00:00:33.235', 189.4);

-- Insertion de données d'exemple pour les sessions de télémétrie
INSERT INTO telemetrie_sessions (session_id, device_id, frequence_echantillonnage, precision_gps, 
                               calibration_gyro, calibration_accel, statut) VALUES
(1, 1, 10, 2.5, 1, 1, 'complete'),
(2, 1, 10, 2.2, 1, 1, 'complete');

-- Insertion de données d'exemple pour les données agrégées de télémétrie
INSERT INTO telemetrie_agregee (tour_id, vitesse_max, vitesse_min, vitesse_moyenne, 
                              acceleration_max, deceleration_max, angle_inclinaison_max, distance_parcourue) VALUES
(1, 210.5, 65.2, 159.8, 0.85, 1.25, 48.5, 5842.0),
(2, 215.2, 67.8, 162.3, 0.88, 1.28, 49.2, 5842.0),
(3, 218.6, 68.5, 164.7, 0.90, 1.30, 50.1, 5842.0),
(4, 220.1, 69.2, 165.8, 0.92, 1.32, 50.8, 5842.0),
(5, 221.5, 69.8, 166.2, 0.93, 1.33, 51.2, 5842.0),
(6, 198.3, 62.1, 152.4, 0.82, 1.20, 47.5, 4411.0),
(7, 201.2, 63.5, 154.2, 0.84, 1.22, 48.2, 4411.0),
(8, 202.5, 64.2, 155.1, 0.85, 1.23, 48.8, 4411.0),
(9, 204.1, 65.0, 156.3, 0.86, 1.24, 49.3, 4411.0);
