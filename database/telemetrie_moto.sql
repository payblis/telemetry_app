-- Script SQL complet pour la base de données de l'application de télémétrie moto
-- Ce script combine tous les fichiers SQL individuels en un seul fichier pour faciliter l'installation

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS telemetrie_moto;
USE telemetrie_moto;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user', 'coach') NOT NULL DEFAULT 'user',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion DATETIME NULL,
    token_reset_password VARCHAR(255) NULL,
    date_expiration_token DATETIME NULL,
    statut ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    preferences JSON NULL
);

-- Table des pilotes
CREATE TABLE IF NOT EXISTS pilotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE NULL,
    nationalite VARCHAR(50) NULL,
    taille INT NULL COMMENT 'Taille en cm',
    poids INT NULL COMMENT 'Poids en kg',
    experience INT NULL COMMENT 'Années d\'expérience',
    categorie VARCHAR(50) NULL COMMENT 'Catégorie de compétition',
    niveau ENUM('debutant', 'intermediaire', 'avance', 'expert', 'pro') NOT NULL DEFAULT 'intermediaire',
    notes TEXT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des motos
CREATE TABLE IF NOT EXISTS motos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    marque VARCHAR(100) NOT NULL,
    modele VARCHAR(100) NOT NULL,
    annee INT NULL,
    cylindree INT NULL,
    puissance INT NULL COMMENT 'Puissance en CV',
    poids INT NULL COMMENT 'Poids en kg',
    type ENUM('sportive', 'roadster', 'trail', 'custom', 'autre') NOT NULL DEFAULT 'sportive',
    configuration JSON NULL COMMENT 'Configuration technique en JSON',
    notes TEXT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des circuits
CREATE TABLE IF NOT EXISTS circuits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    pays VARCHAR(50) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    longueur INT NULL COMMENT 'Longueur en mètres',
    nombre_virages INT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    altitude INT NULL COMMENT 'Altitude en mètres',
    trace_gps JSON NULL COMMENT 'Tracé GPS du circuit en GeoJSON',
    description TEXT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

-- Table des sessions de télémétrie
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pilote_id INT NOT NULL,
    moto_id INT NOT NULL,
    circuit_id INT NOT NULL,
    date_session DATE NOT NULL,
    heure_debut TIME NOT NULL,
    duree_totale INT NULL COMMENT 'Durée en secondes',
    conditions_meteo JSON NULL COMMENT 'Conditions météo en JSON',
    temperature INT NULL COMMENT 'Température en °C',
    humidite INT NULL COMMENT 'Humidité en %',
    pression_atm INT NULL COMMENT 'Pression atmosphérique en hPa',
    vent_vitesse INT NULL COMMENT 'Vitesse du vent en km/h',
    vent_direction VARCHAR(10) NULL COMMENT 'Direction du vent',
    nombre_tours INT NULL DEFAULT 0,
    meilleur_temps FLOAT NULL COMMENT 'Meilleur temps en secondes',
    temps_moyen FLOAT NULL COMMENT 'Temps moyen en secondes',
    vitesse_max FLOAT NULL COMMENT 'Vitesse max en km/h',
    vitesse_moyenne FLOAT NULL COMMENT 'Vitesse moyenne en km/h',
    distance_totale FLOAT NULL COMMENT 'Distance totale en mètres',
    source_donnees ENUM('sensor_logger', 'race2025', 'manuel', 'autre') NOT NULL DEFAULT 'sensor_logger',
    reglages JSON NULL COMMENT 'Réglages de la moto en JSON',
    notes TEXT NULL,
    problemes TEXT NULL COMMENT 'Problèmes rencontrés',
    statut ENUM('active', 'archived', 'deleted') NOT NULL DEFAULT 'active',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pilote_id) REFERENCES pilotes(id) ON DELETE CASCADE,
    FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE CASCADE,
    FOREIGN KEY (circuit_id) REFERENCES circuits(id) ON DELETE CASCADE
);

-- Table des tours
CREATE TABLE IF NOT EXISTS tours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    numero_tour INT NOT NULL,
    temps FLOAT NOT NULL COMMENT 'Temps en secondes',
    heure_debut DATETIME NOT NULL,
    heure_fin DATETIME NOT NULL,
    distance FLOAT NULL COMMENT 'Distance en mètres',
    vitesse_max FLOAT NULL COMMENT 'Vitesse max en km/h',
    vitesse_moyenne FLOAT NULL COMMENT 'Vitesse moyenne en km/h',
    acceleration_max FLOAT NULL COMMENT 'Accélération max en g',
    inclinaison_max FLOAT NULL COMMENT 'Inclinaison max en degrés',
    regime_moteur_max INT NULL COMMENT 'Régime moteur max en tr/min',
    valide BOOLEAN NOT NULL DEFAULT TRUE,
    meilleur_tour BOOLEAN NOT NULL DEFAULT FALSE,
    notes TEXT NULL,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table des données télémétriques
CREATE TABLE IF NOT EXISTS telemetrie_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    tour_id INT NULL,
    timestamp DATETIME NOT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    altitude FLOAT NULL,
    vitesse FLOAT NULL COMMENT 'Vitesse en km/h',
    acceleration_x FLOAT NULL COMMENT 'Accélération latérale en g',
    acceleration_y FLOAT NULL COMMENT 'Accélération longitudinale en g',
    acceleration_z FLOAT NULL COMMENT 'Accélération verticale en g',
    gyroscope_x FLOAT NULL COMMENT 'Vitesse angulaire en rad/s',
    gyroscope_y FLOAT NULL COMMENT 'Vitesse angulaire en rad/s',
    gyroscope_z FLOAT NULL COMMENT 'Vitesse angulaire en rad/s',
    inclinaison FLOAT NULL COMMENT 'Inclinaison en degrés',
    angle_virage FLOAT NULL COMMENT 'Angle de virage en degrés',
    regime_moteur INT NULL COMMENT 'Régime moteur en tr/min',
    position_accelerateur FLOAT NULL COMMENT 'Position accélérateur en %',
    position_frein FLOAT NULL COMMENT 'Position frein en %',
    rapport_vitesse INT NULL COMMENT 'Rapport de vitesse',
    temperature_moteur FLOAT NULL COMMENT 'Température moteur en °C',
    temperature_pneu_avant FLOAT NULL COMMENT 'Température pneu avant en °C',
    temperature_pneu_arriere FLOAT NULL COMMENT 'Température pneu arrière en °C',
    pression_pneu_avant FLOAT NULL COMMENT 'Pression pneu avant en bar',
    pression_pneu_arriere FLOAT NULL COMMENT 'Pression pneu arrière en bar',
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE SET NULL
);

-- Table des segments de circuit
CREATE TABLE IF NOT EXISTS segments_circuit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    circuit_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    type ENUM('ligne_droite', 'virage_gauche', 'virage_droite', 'chicane', 'epingle', 'autre') NOT NULL,
    position_debut JSON NOT NULL COMMENT 'Position de début en GeoJSON',
    position_fin JSON NOT NULL COMMENT 'Position de fin en GeoJSON',
    longueur FLOAT NULL COMMENT 'Longueur en mètres',
    difficulte INT NULL COMMENT 'Difficulté de 1 à 10',
    notes TEXT NULL,
    FOREIGN KEY (circuit_id) REFERENCES circuits(id) ON DELETE CASCADE
);

-- Table des performances par segment
CREATE TABLE IF NOT EXISTS performances_segment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tour_id INT NOT NULL,
    segment_id INT NOT NULL,
    temps FLOAT NOT NULL COMMENT 'Temps en secondes',
    vitesse_entree FLOAT NULL COMMENT 'Vitesse d\'entrée en km/h',
    vitesse_sortie FLOAT NULL COMMENT 'Vitesse de sortie en km/h',
    vitesse_min FLOAT NULL COMMENT 'Vitesse minimale en km/h',
    vitesse_max FLOAT NULL COMMENT 'Vitesse maximale en km/h',
    inclinaison_max FLOAT NULL COMMENT 'Inclinaison maximale en degrés',
    acceleration_max FLOAT NULL COMMENT 'Accélération maximale en g',
    freinage_max FLOAT NULL COMMENT 'Freinage maximal en g',
    trajectoire_ideale BOOLEAN NULL COMMENT 'Si la trajectoire est idéale',
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE,
    FOREIGN KEY (segment_id) REFERENCES segments_circuit(id) ON DELETE CASCADE
);

-- Table des vidéos
CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT NULL,
    url_fichier VARCHAR(255) NULL,
    url_externe VARCHAR(255) NULL,
    duree INT NULL COMMENT 'Durée en secondes',
    format VARCHAR(20) NULL,
    resolution VARCHAR(20) NULL,
    taille_fichier INT NULL COMMENT 'Taille en Ko',
    synchronise BOOLEAN NOT NULL DEFAULT FALSE,
    offset_temps INT NULL COMMENT 'Décalage temporel en ms',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table des analyses vidéo
CREATE TABLE IF NOT EXISTS analyses_video (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    timestamp_debut INT NOT NULL COMMENT 'Position de début en secondes',
    timestamp_fin INT NOT NULL COMMENT 'Position de fin en secondes',
    type_analyse ENUM('trajectoire', 'position_pilote', 'freinage', 'acceleration', 'autre') NOT NULL,
    resultat JSON NULL COMMENT 'Résultat de l\'analyse en JSON',
    notes TEXT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

-- Table des recommandations
CREATE TABLE IF NOT EXISTS recommandations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    texte TEXT NOT NULL,
    action_recommandee TEXT NULL,
    impact_attendu TEXT NULL,
    source ENUM('openai', 'communaute', 'coach', 'systeme') NOT NULL DEFAULT 'systeme',
    confiance INT NULL COMMENT 'Niveau de confiance de 0 à 100',
    reference_session_id INT NULL COMMENT 'Session de référence pour les recommandations communautaires',
    feedback_utilisateur TEXT NULL,
    note_utilisateur INT NULL COMMENT 'Note de 1 à 5',
    date_feedback DATETIME NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('active', 'applied', 'rejected', 'deleted') NOT NULL DEFAULT 'active',
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (reference_session_id) REFERENCES sessions(id) ON DELETE SET NULL
);

-- Insertion de données de test pour les utilisateurs
INSERT INTO users (email, password, nom, prenom, role) VALUES
('admin@telemetrie-moto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'System', 'admin'),
('user@telemetrie-moto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Utilisateur', 'Test', 'user'),
('coach@telemetrie-moto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Coach', 'Expert', 'coach');

-- Insertion de données de test pour les pilotes
INSERT INTO pilotes (user_id, nom, prenom, date_naissance, nationalite, taille, poids, experience, categorie, niveau) VALUES
(2, 'Utilisateur', 'Test', '1990-01-01', 'Française', 175, 70, 5, 'Amateur', 'intermediaire'),
(3, 'Coach', 'Expert', '1985-05-15', 'Française', 180, 75, 15, 'Professionnel', 'expert');

-- Insertion de données de test pour les motos
INSERT INTO motos (user_id, marque, modele, annee, cylindree, puissance, poids, type) VALUES
(2, 'Yamaha', 'R6', 2020, 600, 120, 190, 'sportive'),
(2, 'Honda', 'CBR1000RR', 2021, 1000, 200, 200, 'sportive'),
(3, 'Kawasaki', 'ZX-10R', 2022, 1000, 210, 195, 'sportive');

-- Insertion de données de test pour les circuits
INSERT INTO circuits (nom, pays, ville, longueur, nombre_virages, latitude, longitude) VALUES
('Circuit Paul Ricard', 'France', 'Le Castellet', 5842, 15, 43.2506, 5.7910),
('Circuit de Nevers Magny-Cours', 'France', 'Magny-Cours', 4411, 17, 46.8642, 3.1630),
('Circuit de Barcelona-Catalunya', 'Espagne', 'Montmeló', 4655, 16, 41.5690, 2.2610);

-- Création des index pour optimiser les performances
CREATE INDEX idx_sessions_user_id ON sessions(user_id);
CREATE INDEX idx_sessions_pilote_id ON sessions(pilote_id);
CREATE INDEX idx_sessions_moto_id ON sessions(moto_id);
CREATE INDEX idx_sessions_circuit_id ON sessions(circuit_id);
CREATE INDEX idx_tours_session_id ON tours(session_id);
CREATE INDEX idx_telemetrie_points_session_id ON telemetrie_points(session_id);
CREATE INDEX idx_telemetrie_points_tour_id ON telemetrie_points(tour_id);
CREATE INDEX idx_telemetrie_points_timestamp ON telemetrie_points(timestamp);
CREATE INDEX idx_recommandations_session_id ON recommandations(session_id);
