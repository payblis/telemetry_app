-- Base de données pour l'application de télémétrie moto

-- Table des utilisateurs
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'user', 'expert') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des télémétristes virtuels
CREATE TABLE telemetriste_virtuel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table des pilotes
CREATE TABLE pilotes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    age INT,
    poids DECIMAL(5,2),
    taille INT,
    experience TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des motos
CREATE TABLE motos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    marque VARCHAR(50) NOT NULL,
    modele VARCHAR(100) NOT NULL,
    annee INT,
    cylindree INT,
    puissance INT,
    poids INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des équipements moto
CREATE TABLE equipements_moto (
    id INT PRIMARY KEY AUTO_INCREMENT,
    moto_id INT,
    type ENUM('suspension', 'frein', 'pneu', 'ecu', 'capteur') NOT NULL,
    marque VARCHAR(50),
    modele VARCHAR(100),
    specifications TEXT,
    FOREIGN KEY (moto_id) REFERENCES motos(id)
);

-- Table des circuits
CREATE TABLE circuits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    longueur INT,
    type_virages TEXT,
    meteo_habituelle TEXT,
    adherence TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des sessions
CREATE TABLE sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    circuit_id INT,
    pilote_id INT,
    moto_id INT,
    date_session DATETIME,
    conditions_meteo TEXT,
    temperature INT,
    humidite INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (circuit_id) REFERENCES circuits(id),
    FOREIGN KEY (pilote_id) REFERENCES pilotes(id),
    FOREIGN KEY (moto_id) REFERENCES motos(id)
);

-- Table des tours
CREATE TABLE tours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT,
    numero_tour INT,
    temps_tour TIME(3),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

-- Table des données télémétriques
CREATE TABLE telemetry_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tour_id INT,
    timestamp DATETIME(3),
    vitesse DECIMAL(6,2),
    regime_moteur INT,
    angle_inclinaison DECIMAL(4,1),
    temperature_pneu_avant DECIMAL(4,1),
    temperature_pneu_arriere DECIMAL(4,1),
    suspension_avant_position DECIMAL(5,2),
    suspension_arriere_position DECIMAL(5,2),
    FOREIGN KEY (tour_id) REFERENCES tours(id)
);

-- Table de la base de connaissances IA
CREATE TABLE ia_internal_knowledge (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categorie VARCHAR(50),
    probleme TEXT,
    solution TEXT,
    confiance DECIMAL(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des réponses experts
CREATE TABLE expert_responses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expert_id INT,
    question_id INT,
    reponse TEXT,
    validee BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id)
);

-- Table de configuration API
CREATE TABLE api_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    api_key VARCHAR(255),
    api_usage INT DEFAULT 0,
    last_reset TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 