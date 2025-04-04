-- -----------------------------------------------------
-- 2. Gestion des Pilotes et Motos
-- -----------------------------------------------------

USE telemetrie_moto;

-- Table des pilotes
CREATE TABLE pilotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    taille DECIMAL(5,2),
    poids DECIMAL(5,2),
    championnat VARCHAR(100),
    niveau_experience ENUM('debutant', 'intermediaire', 'avance', 'expert') DEFAULT 'intermediaire',
    style_pilotage TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table des motos
CREATE TABLE motos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    marque VARCHAR(50) NOT NULL,
    modele VARCHAR(50) NOT NULL,
    annee YEAR,
    cylindree INT,
    poids_sec DECIMAL(6,2),
    type_moteur VARCHAR(50),
    type_cadre VARCHAR(50),
    image_path VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table des réglages de moto
CREATE TABLE reglages_moto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    moto_id INT NOT NULL,
    nom_reglage VARCHAR(100) NOT NULL,
    type_reglage ENUM('fourche', 'amortisseur', 'transmission', 'pneus', 'moteur', 'autre') NOT NULL,
    valeur VARCHAR(50) NOT NULL,
    unite VARCHAR(20),
    ajustable BOOLEAN DEFAULT TRUE,
    plage_min VARCHAR(20),
    plage_max VARCHAR(20),
    increment VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE CASCADE
);

-- Table des configurations de moto
CREATE TABLE configurations_moto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    moto_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    est_configuration_defaut BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE CASCADE
);

-- Table des réglages par configuration
CREATE TABLE configuration_reglages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    configuration_id INT NOT NULL,
    reglage_id INT NOT NULL,
    valeur VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (configuration_id) REFERENCES configurations_moto(id) ON DELETE CASCADE,
    FOREIGN KEY (reglage_id) REFERENCES reglages_moto(id) ON DELETE CASCADE
);

-- Insertion de données d'exemple pour les pilotes
INSERT INTO pilotes (user_id, nom, prenom, taille, poids, niveau_experience, style_pilotage) VALUES
(1, 'Dupont', 'Jean', 178.5, 72.5, 'intermediaire', 'Pilote polyvalent avec une préférence pour les virages rapides'),
(1, 'Martin', 'Sophie', 165.0, 58.0, 'avance', 'Style agressif, excellente en freinage tardif');

-- Insertion de données d'exemple pour les motos
INSERT INTO motos (user_id, marque, modele, annee, cylindree, poids_sec, type_moteur, type_cadre) VALUES
(1, 'Yamaha', 'R6', 2019, 600, 190.0, '4 cylindres en ligne', 'Deltabox aluminium'),
(1, 'Honda', 'CBR1000RR', 2020, 1000, 201.0, '4 cylindres en ligne', 'Aluminium double poutre');

-- Insertion de données d'exemple pour les réglages
INSERT INTO reglages_moto (moto_id, nom_reglage, type_reglage, valeur, unite, ajustable, plage_min, plage_max, increment) VALUES
(1, 'Précharge ressort fourche', 'fourche', '5', 'tours', 1, '0', '10', '0.5'),
(1, 'Compression fourche', 'fourche', '8', 'clics', 1, '0', '20', '1'),
(1, 'Détente fourche', 'fourche', '10', 'clics', 1, '0', '20', '1'),
(1, 'Précharge ressort amortisseur', 'amortisseur', '7', 'mm', 1, '0', '15', '1'),
(1, 'Compression amortisseur', 'amortisseur', '12', 'clics', 1, '0', '24', '1'),
(1, 'Détente amortisseur', 'amortisseur', '8', 'clics', 1, '0', '24', '1'),
(1, 'Pression pneu avant', 'pneus', '2.3', 'bar', 1, '1.8', '2.6', '0.1'),
(1, 'Pression pneu arrière', 'pneus', '2.1', 'bar', 1, '1.8', '2.4', '0.1'),
(2, 'Précharge ressort fourche', 'fourche', '4', 'tours', 1, '0', '10', '0.5'),
(2, 'Compression fourche', 'fourche', '10', 'clics', 1, '0', '20', '1'),
(2, 'Détente fourche', 'fourche', '12', 'clics', 1, '0', '20', '1'),
(2, 'Précharge ressort amortisseur', 'amortisseur', '8', 'mm', 1, '0', '15', '1'),
(2, 'Compression amortisseur', 'amortisseur', '14', 'clics', 1, '0', '24', '1'),
(2, 'Détente amortisseur', 'amortisseur', '10', 'clics', 1, '0', '24', '1'),
(2, 'Pression pneu avant', 'pneus', '2.4', 'bar', 1, '1.8', '2.6', '0.1'),
(2, 'Pression pneu arrière', 'pneus', '2.2', 'bar', 1, '1.8', '2.4', '0.1');

-- Insertion de données d'exemple pour les configurations
INSERT INTO configurations_moto (moto_id, nom, description, est_configuration_defaut) VALUES
(1, 'Configuration standard', 'Réglages d\'usine recommandés', 1),
(1, 'Configuration piste sèche', 'Optimisé pour conditions de piste sèche', 0),
(2, 'Configuration standard', 'Réglages d\'usine recommandés', 1),
(2, 'Configuration piste mouillée', 'Optimisé pour conditions de piste mouillée', 0);

-- Insertion de données d'exemple pour les réglages par configuration
INSERT INTO configuration_reglages (configuration_id, reglage_id, valeur) VALUES
(1, 1, '5'),
(1, 2, '8'),
(1, 3, '10'),
(1, 4, '7'),
(1, 5, '12'),
(1, 6, '8'),
(1, 7, '2.3'),
(1, 8, '2.1'),
(2, 1, '6'),
(2, 2, '6'),
(2, 3, '8'),
(2, 4, '8'),
(2, 5, '10'),
(2, 6, '6'),
(2, 7, '2.2'),
(2, 8, '2.0'),
(3, 9, '4'),
(3, 10, '10'),
(3, 11, '12'),
(3, 12, '8'),
(3, 13, '14'),
(3, 14, '10'),
(3, 15, '2.4'),
(3, 16, '2.2'),
(4, 9, '3'),
(4, 10, '12'),
(4, 11, '14'),
(4, 12, '6'),
(4, 13, '16'),
(4, 14, '12'),
(4, 15, '2.2'),
(4, 16, '2.0');
