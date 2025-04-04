-- Base de données simplifiée pour l'application de télémétrie moto
-- Version sans routage complexe

-- Suppression des tables si elles existent déjà
DROP TABLE IF EXISTS recommandations;
DROP TABLE IF EXISTS telemetrie_points;
DROP TABLE IF EXISTS tours;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS circuits;
DROP TABLE IF EXISTS motos;
DROP TABLE IF EXISTS pilotes;
DROP TABLE IF EXISTS users;

-- Table des utilisateurs
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  remember_token VARCHAR(100) DEFAULT NULL,
  remember_expiry DATETIME DEFAULT NULL,
  date_creation DATETIME NOT NULL,
  date_modification DATETIME DEFAULT NULL
);

-- Table des pilotes
CREATE TABLE pilotes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  date_naissance DATE DEFAULT NULL,
  nationalite VARCHAR(50) DEFAULT NULL,
  taille INT DEFAULT NULL,
  poids INT DEFAULT NULL,
  experience INT DEFAULT NULL,
  categorie VARCHAR(50) DEFAULT NULL,
  niveau ENUM('debutant', 'intermediaire', 'avance', 'expert') NOT NULL DEFAULT 'intermediaire',
  notes TEXT DEFAULT NULL,
  date_creation DATETIME NOT NULL,
  date_modification DATETIME DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des motos
CREATE TABLE motos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  marque VARCHAR(100) NOT NULL,
  modele VARCHAR(100) NOT NULL,
  annee INT DEFAULT NULL,
  cylindree INT DEFAULT NULL,
  puissance INT DEFAULT NULL,
  poids INT DEFAULT NULL,
  type VARCHAR(50) DEFAULT 'sportive',
  configuration TEXT DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  date_creation DATETIME NOT NULL,
  date_modification DATETIME DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des circuits
CREATE TABLE circuits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  pays VARCHAR(50) NOT NULL,
  ville VARCHAR(100) NOT NULL,
  longueur FLOAT DEFAULT NULL,
  nombre_virages INT DEFAULT NULL,
  latitude FLOAT DEFAULT NULL,
  longitude FLOAT DEFAULT NULL,
  altitude FLOAT DEFAULT NULL,
  trace_gps TEXT DEFAULT NULL,
  description TEXT DEFAULT NULL,
  date_creation DATETIME NOT NULL,
  date_modification DATETIME DEFAULT NULL
);

-- Table des sessions
CREATE TABLE sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  pilote_id INT NOT NULL,
  moto_id INT NOT NULL,
  circuit_id INT NOT NULL,
  date_session DATE NOT NULL,
  heure_debut TIME NOT NULL,
  duree_totale INT DEFAULT NULL,
  nombre_tours INT DEFAULT NULL,
  meilleur_temps FLOAT DEFAULT NULL,
  temps_moyen FLOAT DEFAULT NULL,
  vitesse_max FLOAT DEFAULT NULL,
  vitesse_moyenne FLOAT DEFAULT NULL,
  conditions_meteo VARCHAR(50) DEFAULT NULL,
  temperature FLOAT DEFAULT NULL,
  humidite FLOAT DEFAULT NULL,
  pression_atm FLOAT DEFAULT NULL,
  vent_vitesse FLOAT DEFAULT NULL,
  vent_direction VARCHAR(10) DEFAULT NULL,
  reglages TEXT DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  problemes TEXT DEFAULT NULL,
  date_creation DATETIME NOT NULL,
  date_modification DATETIME DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (pilote_id) REFERENCES pilotes(id) ON DELETE CASCADE,
  FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE CASCADE,
  FOREIGN KEY (circuit_id) REFERENCES circuits(id) ON DELETE CASCADE
);

-- Table des tours
CREATE TABLE tours (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL,
  numero_tour INT NOT NULL,
  temps FLOAT NOT NULL,
  heure_debut DATETIME NOT NULL,
  heure_fin DATETIME NOT NULL,
  vitesse_max FLOAT DEFAULT NULL,
  vitesse_moyenne FLOAT DEFAULT NULL,
  valide BOOLEAN DEFAULT TRUE,
  meilleur_tour BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table des points de télémétrie
CREATE TABLE telemetrie_points (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL,
  tour_id INT DEFAULT NULL,
  timestamp DATETIME NOT NULL,
  latitude FLOAT DEFAULT NULL,
  longitude FLOAT DEFAULT NULL,
  altitude FLOAT DEFAULT NULL,
  vitesse FLOAT DEFAULT NULL,
  acceleration_x FLOAT DEFAULT NULL,
  acceleration_y FLOAT DEFAULT NULL,
  acceleration_z FLOAT DEFAULT NULL,
  gyro_x FLOAT DEFAULT NULL,
  gyro_y FLOAT DEFAULT NULL,
  gyro_z FLOAT DEFAULT NULL,
  angle_inclinaison FLOAT DEFAULT NULL,
  regime_moteur INT DEFAULT NULL,
  temperature_moteur FLOAT DEFAULT NULL,
  position_accelerateur FLOAT DEFAULT NULL,
  pression_frein_avant FLOAT DEFAULT NULL,
  pression_frein_arriere FLOAT DEFAULT NULL,
  FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
  FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE SET NULL
);

-- Table des recommandations
CREATE TABLE recommandations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL,
  titre VARCHAR(255) NOT NULL,
  texte TEXT NOT NULL,
  action_recommandee TEXT DEFAULT NULL,
  impact_attendu TEXT DEFAULT NULL,
  source ENUM('systeme', 'ia', 'communaute', 'expert') NOT NULL DEFAULT 'systeme',
  confiance INT DEFAULT NULL,
  statut ENUM('nouvelle', 'vue', 'appliquee', 'ignoree') NOT NULL DEFAULT 'nouvelle',
  feedback_utilisateur TEXT DEFAULT NULL,
  note_utilisateur INT DEFAULT NULL,
  reference_session_id INT DEFAULT NULL,
  date_creation DATETIME NOT NULL,
  date_feedback DATETIME DEFAULT NULL,
  FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
  FOREIGN KEY (reference_session_id) REFERENCES sessions(id) ON DELETE SET NULL
);

-- Insertion de données de test
-- Utilisateur administrateur
INSERT INTO users (email, password, nom, prenom, role, date_creation)
VALUES ('admin@telemetrie-moto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Système', 'admin', NOW());

-- Utilisateur standard
INSERT INTO users (email, password, nom, prenom, role, date_creation)
VALUES ('user@telemetrie-moto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dupont', 'Jean', 'user', NOW());

-- Pilotes
INSERT INTO pilotes (user_id, nom, prenom, date_naissance, nationalite, taille, poids, experience, categorie, niveau, date_creation)
VALUES (2, 'Dupont', 'Jean', '1990-05-15', 'Française', 178, 75, 5, 'amateur', 'intermediaire', NOW());

-- Motos
INSERT INTO motos (user_id, marque, modele, annee, cylindree, puissance, poids, type, date_creation)
VALUES (2, 'Yamaha', 'R6', 2019, 600, 120, 190, 'sportive', NOW());

-- Circuits
INSERT INTO circuits (nom, pays, ville, longueur, nombre_virages, date_creation)
VALUES ('Circuit Paul Ricard', 'France', 'Le Castellet', 5.842, 15, NOW()),
       ('Circuit de Spa-Francorchamps', 'Belgique', 'Stavelot', 7.004, 19, NOW()),
       ('Circuit de Barcelone-Catalogne', 'Espagne', 'Montmeló', 4.655, 16, NOW());
