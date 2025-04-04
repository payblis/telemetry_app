-- Base de données TeleMoto
-- Script de création et d'initialisation

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS telemoto;
USE telemoto;

-- Table des pilotes
CREATE TABLE IF NOT EXISTS pilotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    taille FLOAT,
    poids FLOAT,
    championnat VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des motos
CREATE TABLE IF NOT EXISTS motos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marque VARCHAR(100) NOT NULL,
    modele VARCHAR(100) NOT NULL,
    cylindree INT,
    annee INT,
    type ENUM('origine', 'race') DEFAULT 'origine',
    reglages_standards TEXT,
    equipements_specifiques TEXT,
    pignon_avant INT,
    pignon_arriere INT,
    longueur_chaine FLOAT,
    pneu_avant VARCHAR(100),
    pneu_arriere VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des équipements de moto
CREATE TABLE IF NOT EXISTS equipements_moto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    moto_id INT NOT NULL,
    categorie VARCHAR(50) NOT NULL,
    type_equipement ENUM('Fourche avant', 'Amortisseur arrière', 'Bras oscillant', 'Tés de fourche', 'Cadre', 'Direction', 'Suspension arrière', 'Roues', 'Pneus', 'Freins', 'Commande de frein', 'Embrayage', 'Guidon', 'Repose-pieds', 'Sélecteur de vitesse', 'Boîte de vitesses', 'Électronique', 'Télémétrie', 'Contrôle de traction', 'Échappement', 'Autre'),
    marque VARCHAR(100) NOT NULL,
    modele VARCHAR(100),
    specifications TEXT,
    valeurs_defaut TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE CASCADE
);

-- Table des circuits
CREATE TABLE IF NOT EXISTS circuits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    pays VARCHAR(100),
    longueur FLOAT,
    largeur FLOAT,
    details_virages TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des sessions
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    type ENUM('course', 'qualification', 'free_practice', 'entrainement', 'track_day') NOT NULL,
    pilote_id INT NOT NULL,
    moto_id INT NOT NULL,
    circuit_id INT NOT NULL,
    conditions VARCHAR(255),
    reglages_initiaux TEXT,
    pignon_avant INT,
    pignon_arriere INT,
    longueur_chaine FLOAT,
    pneu_avant VARCHAR(100),
    pneu_arriere VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pilote_id) REFERENCES pilotes(id),
    FOREIGN KEY (moto_id) REFERENCES motos(id),
    FOREIGN KEY (circuit_id) REFERENCES circuits(id)
);

-- Table des données techniques de session
CREATE TABLE IF NOT EXISTS donnees_techniques_session (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    
    -- Suspension
    precharge_avant FLOAT,
    precharge_arriere FLOAT,
    compression_basse_avant FLOAT,
    compression_haute_avant FLOAT,
    compression_basse_arriere FLOAT,
    compression_haute_arriere FLOAT,
    detente_avant FLOAT,
    detente_arriere FLOAT,
    course_morte_statique FLOAT,
    course_morte_dynamique FLOAT,
    hauteur_fourche FLOAT,
    hauteur_arriere FLOAT,
    durete_ressort_avant VARCHAR(20),
    durete_ressort_arriere VARCHAR(20),
    
    -- Châssis / géométrie
    empattement FLOAT,
    angle_chasse FLOAT,
    chasse FLOAT,
    offset_tes_fourche FLOAT,
    position_bras_oscillant VARCHAR(50),
    position_axe_roue_arriere VARCHAR(50),
    
    -- Moteur / Transmission
    mapping_moteur VARCHAR(50),
    frein_moteur INT,
    reglage_quickshifter VARCHAR(50),
    rapports_boite TEXT,
    rapport_transmission VARCHAR(20),
    
    -- Pneumatiques
    type_pneu_avant ENUM('slick', 'pluie', 'mixte', 'standard'),
    type_pneu_arriere ENUM('slick', 'pluie', 'mixte', 'standard'),
    pression_avant_froid FLOAT,
    pression_avant_chaud FLOAT,
    pression_arriere_froid FLOAT,
    pression_arriere_chaud FLOAT,
    temperature_pneu_avant FLOAT,
    temperature_pneu_arriere FLOAT,
    usure_pneus TEXT,
    
    -- Électronique / Aides
    traction_control INT,
    anti_wheeling INT,
    launch_control BOOLEAN,
    mode_moteur VARCHAR(20),
    abs_active BOOLEAN,
    
    -- Métadonnées
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table des chronos
CREATE TABLE IF NOT EXISTS chronos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    tour_numero INT NOT NULL,
    temps VARCHAR(20) NOT NULL,
    temps_secondes FLOAT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table des remarques pilote
CREATE TABLE IF NOT EXISTS remarques_pilote (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    remarque TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table des recommandations
CREATE TABLE IF NOT EXISTS recommandations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    source ENUM('chatgpt', 'expert') NOT NULL,
    probleme TEXT NOT NULL,
    solution TEXT NOT NULL,
    validation ENUM('positif', 'neutre', 'negatif'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user', 'expert') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des questions aux experts
CREATE TABLE IF NOT EXISTS questions_experts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    session_id INT,
    question TEXT NOT NULL,
    statut ENUM('en_attente', 'repondue', 'fermee') NOT NULL DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL
);

-- Table des réponses des experts
CREATE TABLE IF NOT EXISTS reponses_experts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    expert_id INT NOT NULL,
    reponse TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES questions_experts(id) ON DELETE CASCADE,
    FOREIGN KEY (expert_id) REFERENCES utilisateurs(id)
);

-- Insertion de données de test
-- Pilotes
INSERT INTO pilotes (nom, prenom, taille, poids, championnat) VALUES
('Dupont', 'Jean', 1.75, 68, 'Championnat Supersport France'),
('Martin', 'Sophie', 1.65, 58, 'Championnat Féminin'),
('Garcia', 'Carlos', 1.80, 72, 'Championnat Superbike');

-- Motos
INSERT INTO motos (marque, modele, cylindree, annee, type, reglages_standards) VALUES
('Yamaha', 'R6', 600, 2022, 'origine', 'Fourche standard, amortisseur Öhlins, pignon avant 15 dents, arrière 46 dents'),
('Honda', 'CBR1000RR-R', 1000, 2023, 'race', 'Fourche Öhlins, amortisseur Öhlins, pignon avant 16 dents, arrière 42 dents'),
('Kawasaki', 'ZX-10R', 1000, 2021, 'race', 'Fourche Showa, amortisseur Showa, pignon avant 16 dents, arrière 43 dents');

-- Équipements moto
INSERT INTO equipements_moto (moto_id, categorie, type_equipement, marque, modele, specifications) VALUES
(2, 'Suspension', 'Fourche avant', 'Öhlins', 'FGR 300', 'Cartouche pressurisée, réglable en précharge, compression et détente'),
(2, 'Suspension', 'Amortisseur arrière', 'Öhlins', 'TTX GP', 'Réglable en précharge, compression haute/basse vitesse et détente'),
(2, 'Freinage', 'Freins', 'Brembo', 'M50', 'Étriers monobloc 4 pistons, disques 330mm'),
(3, 'Suspension', 'Fourche avant', 'Showa', 'BFF', 'Balance Free Fork, réglable en précharge, compression et détente'),
(3, 'Suspension', 'Amortisseur arrière', 'Showa', 'BFRC', 'Balance Free Rear Cushion, réglable en précharge, compression et détente');

-- Circuits
INSERT INTO circuits (nom, pays, longueur, details_virages) VALUES
('Circuit Bugatti', 'France', 4.185, 'Virage Dunlop (droite, angle 45°, 4ème rapport, vitesse apex ~130 km/h)\nVirage de la Chapelle (gauche, angle 90°, 3ème rapport, vitesse apex ~100 km/h)\nVirage du Musée (droite, angle 180°, 2ème rapport, vitesse apex ~80 km/h)'),
('Circuit de Barcelona-Catalunya', 'Espagne', 4.655, 'Virage 1 (droite, angle 90°, 2ème rapport, vitesse apex ~80 km/h)\nVirage 2 (gauche, angle 90°, 3ème rapport, vitesse apex ~100 km/h)\nVirage 3 (droite, angle 180°, 2ème rapport, vitesse apex ~70 km/h)'),
('Misano World Circuit', 'Italie', 4.226, 'Virage 1 (droite, angle 90°, 2ème rapport, vitesse apex ~80 km/h)\nVirage 2 (gauche, angle 90°, 3ème rapport, vitesse apex ~110 km/h)\nVirage 3 (droite, angle 45°, 4ème rapport, vitesse apex ~140 km/h)');

-- Utilisateurs
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES
('Admin', 'System', 'admin@telemoto.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6QKO5C5vVvZn2VB3Ck3IAZpPOq', 'admin'),
('User', 'Standard', 'user@telemoto.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6QKO5C5vVvZn2VB3Ck3IAZpPOq', 'user'),
('Expert', 'Technique', 'expert@telemoto.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6QKO5C5vVvZn2VB3Ck3IAZpPOq', 'expert');
