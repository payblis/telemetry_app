-- -----------------------------------------------------
-- 6. IA et Recommandations
-- -----------------------------------------------------

USE telemetrie_moto;

-- Table des recommandations IA
CREATE TABLE recommandations_ia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id INT,
    pilote_id INT,
    moto_id INT,
    circuit_id INT,
    type_probleme VARCHAR(100) NOT NULL,
    description_probleme TEXT NOT NULL,
    source ENUM('chatgpt', 'communautaire') NOT NULL,
    recommandation TEXT NOT NULL,
    contexte_json JSON,
    prompt_utilise TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (pilote_id) REFERENCES pilotes(id) ON DELETE SET NULL,
    FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE SET NULL,
    FOREIGN KEY (circuit_id) REFERENCES circuits(id) ON DELETE SET NULL
);

-- Table des validations de recommandations
CREATE TABLE validations_recommandations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recommandation_id INT NOT NULL,
    user_id INT NOT NULL,
    evaluation ENUM('positif', 'neutre', 'negatif') NOT NULL,
    commentaire TEXT,
    appliquee BOOLEAN DEFAULT FALSE,
    resultat_application TEXT,
    date_validation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recommandation_id) REFERENCES recommandations_ia(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des contributions d'experts
CREATE TABLE contributions_experts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type_probleme VARCHAR(100) NOT NULL,
    conditions_application TEXT NOT NULL,
    recommandation TEXT NOT NULL,
    justification TEXT,
    moto_applicable VARCHAR(255),
    circuit_applicable VARCHAR(255),
    conditions_meteo VARCHAR(100),
    statut ENUM('en_attente', 'validee', 'rejetee') DEFAULT 'en_attente',
    votes_positifs INT DEFAULT 0,
    votes_negatifs INT DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des votes sur les contributions
CREATE TABLE votes_contributions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contribution_id INT NOT NULL,
    user_id INT NOT NULL,
    vote ENUM('positif', 'negatif') NOT NULL,
    commentaire TEXT,
    date_vote TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contribution_id) REFERENCES contributions_experts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (contribution_id, user_id)
);

-- Table des métriques de performance IA
CREATE TABLE metriques_ia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recommandation_id INT NOT NULL,
    source ENUM('chatgpt', 'communautaire') NOT NULL,
    resultat ENUM('positif', 'neutre', 'negatif') NOT NULL,
    type_probleme VARCHAR(100),
    temps_generation DECIMAL(10,3), -- en secondes
    tokens_utilises INT,
    cout_api DECIMAL(10,5),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recommandation_id) REFERENCES recommandations_ia(id) ON DELETE CASCADE
);

-- Table des modèles de prompts
CREATE TABLE modeles_prompts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    template TEXT NOT NULL,
    variables JSON,
    type_probleme VARCHAR(100),
    version VARCHAR(20) NOT NULL,
    actif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des jumeaux numériques de motos
CREATE TABLE jumeaux_numeriques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    moto_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    parametres_json JSON NOT NULL,
    modele_mathematique TEXT,
    precision_estimee DECIMAL(5,2),
    version VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE CASCADE
);

-- Table des simulations
CREATE TABLE simulations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jumeau_numerique_id INT NOT NULL,
    user_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    parametres_entree JSON NOT NULL,
    resultats_json JSON,
    statut ENUM('en_attente', 'en_cours', 'terminee', 'erreur') DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (jumeau_numerique_id) REFERENCES jumeaux_numeriques(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insertion de données d'exemple pour les recommandations IA
INSERT INTO recommandations_ia (user_id, session_id, pilote_id, moto_id, circuit_id, type_probleme, 
                              description_probleme, source, recommandation, contexte_json) VALUES
(1, 1, 1, 1, 1, 'Sous-virage en entrée', 
 'La moto a tendance à élargir sa trajectoire en entrée de virage, particulièrement dans les virages serrés.',
 'chatgpt',
 'Pour corriger le sous-virage en entrée de virage, je recommande d\'augmenter la précharge du ressort de fourche de 5 à 6 tours et de réduire la compression de la fourche de 8 à 7 clics. Cela permettra d\'améliorer la stabilité en entrée de virage tout en maintenant un bon feeling. Assurez-vous également que votre position sur la moto est correcte, avec un bon appui sur le guidon lors de la phase de freinage.',
 '{"conditions": "sec", "temperature": 22.5, "circuit": "Paul Ricard", "virage_problematique": "Virage du Pont"}'),
(1, 2, 2, 2, 2, 'Manque de motricité en sortie', 
 'La moto a du mal à transmettre la puissance au sol en sortie de virage, particulièrement dans les virages à basse vitesse.',
 'chatgpt',
 'Pour améliorer la motricité en sortie de virage, je recommande d\'augmenter la précharge du ressort d\'amortisseur de 8 à 9 mm et de réduire légèrement la pression du pneu arrière de 2.2 à 2.1 bar. Ces ajustements permettront une meilleure adhérence du pneu arrière. Essayez également d\'adoucir votre commande de gaz en sortie de virage pour éviter les à-coups qui peuvent provoquer des pertes d\'adhérence.',
 '{"conditions": "nuageux", "temperature": 18.2, "circuit": "Magny-Cours", "virage_problematique": "Adelaide"}');

-- Insertion de données d'exemple pour les validations de recommandations
INSERT INTO validations_recommandations (recommandation_id, user_id, evaluation, commentaire, appliquee, resultat_application) VALUES
(1, 1, 'positif', 'Recommandation très efficace, la moto est beaucoup plus stable en entrée de virage.', 1, 'Gain de 0.5s sur le tour complet, trajectoires plus précises.'),
(2, 1, 'neutre', 'Légère amélioration mais pas aussi significative qu\'espéré.', 1, 'Petite amélioration de la motricité, mais il reste encore du travail à faire.');

-- Insertion de données d'exemple pour les contributions d'experts
INSERT INTO contributions_experts (user_id, type_probleme, conditions_application, recommandation, justification, 
                                moto_applicable, circuit_applicable, conditions_meteo, statut) VALUES
(1, 'Chattering avant en freinage', 
 'Freinage fort en ligne droite ou en légère courbe, particulièrement sur surface irrégulière.',
 'Augmenter la détente de la fourche de 2-3 clics et réduire légèrement la pression du pneu avant de 0.1 bar.',
 'Le chattering est souvent causé par des rebonds trop rapides de la suspension avant. Augmenter la détente permet de mieux contrôler ces rebonds, tandis que la légère réduction de pression augmente la surface de contact du pneu.',
 'Toutes sportives avec fourche inversée', 'Tous circuits', 'Toutes conditions sèches', 'validee'),
(1, 'Pompage arrière en accélération', 
 'Accélération forte en sortie de virage, particulièrement sur les motos puissantes.',
 'Augmenter la compression de l\'amortisseur de 2 clics et vérifier la hauteur arrière de la moto.',
 'Le pompage est souvent dû à un transfert de masse trop important et mal contrôlé. Augmenter la compression permet de mieux gérer ce transfert de masse lors des phases d\'accélération.',
 'Motos 600cc et plus', 'Circuits avec fortes accélérations', 'Toutes conditions', 'en_attente');

-- Insertion de données d'exemple pour les votes sur les contributions
INSERT INTO votes_contributions (contribution_id, user_id, vote, commentaire) VALUES
(1, 1, 'positif', 'Très efficace sur ma CBR1000RR, le chattering a presque complètement disparu.'),
(2, 1, 'positif', 'Bonne suggestion, à tester sur différentes motos.');

-- Insertion de données d'exemple pour les métriques de performance IA
INSERT INTO metriques_ia (recommandation_id, source, resultat, type_probleme, temps_generation, tokens_utilises, cout_api) VALUES
(1, 'chatgpt', 'positif', 'Sous-virage en entrée', 2.345, 520, 0.01040),
(2, 'chatgpt', 'neutre', 'Manque de motricité en sortie', 1.987, 480, 0.00960);

-- Insertion de données d'exemple pour les modèles de prompts
INSERT INTO modeles_prompts (nom, description, template, variables, type_probleme, version, actif) VALUES
('Recommandation réglages généraux', 
 'Modèle de prompt pour les recommandations de réglages généraux',
 'Tu es un expert en réglages de motos de course. Analyse le problème suivant et propose une solution détaillée:\n\nMoto: {{moto}}\nCircuit: {{circuit}}\nConditions: {{conditions}}\nProblème: {{probleme}}\n\nDonne une recommandation précise avec les valeurs exactes à modifier.',
 '{"moto": "string", "circuit": "string", "conditions": "string", "probleme": "string"}',
 'general',
 '1.0',
 1),
('Recommandation sous-virage', 
 'Modèle de prompt spécifique pour les problèmes de sous-virage',
 'Tu es un expert en réglages de motos de course. Le pilote rencontre un problème de sous-virage dans les conditions suivantes:\n\nMoto: {{moto}}\nCircuit: {{circuit}}\nVirage: {{virage}}\nConditions: {{conditions}}\nRéglages actuels: {{reglages}}\n\nPropose une solution détaillée pour corriger ce sous-virage avec des valeurs précises.',
 '{"moto": "string", "circuit": "string", "virage": "string", "conditions": "string", "reglages": "string"}',
 'sous-virage',
 '1.0',
 1);

-- Insertion de données d'exemple pour les jumeaux numériques
INSERT INTO jumeaux_numeriques (moto_id, nom, description, parametres_json, modele_mathematique, precision_estimee, version) VALUES
(1, 'Jumeau R6 2019', 
 'Modèle numérique de la Yamaha R6 2019 pour simulation de comportement',
 '{"masse": 190, "empattement": 1375, "angle_chasse": 24, "chasse": 97, "hauteur_cg": 450, "repartition_poids": 52.5, "inertie_roulis": 25.3, "inertie_tangage": 45.7, "inertie_lacet": 28.9, "rigidite_cadre": 85.2}',
 'Modèle multi-corps avec 6 degrés de liberté et simulation des suspensions',
 85.5,
 '1.0'),
(2, 'Jumeau CBR1000RR 2020', 
 'Modèle numérique de la Honda CBR1000RR 2020 pour simulation de comportement',
 '{"masse": 201, "empattement": 1405, "angle_chasse": 23.3, "chasse": 96, "hauteur_cg": 440, "repartition_poids": 53.5, "inertie_roulis": 27.8, "inertie_tangage": 48.2, "inertie_lacet": 30.5, "rigidite_cadre": 90.1}',
 'Modèle multi-corps avec 6 degrés de liberté et simulation des suspensions',
 87.2,
 '1.0');

-- Insertion de données d'exemple pour les simulations
INSERT INTO simulations (jumeau_numerique_id, user_id, nom, description, parametres_entree, resultats_json, statut) VALUES
(1, 1, 'Simulation virage 1 Paul Ricard', 
 'Simulation du comportement dans le virage 1 du circuit Paul Ricard',
 '{"vitesse_entree": 120, "vitesse_apex": 80, "vitesse_sortie": 140, "angle_inclinaison_max": 50, "trajectoire": "interieure", "freinage": "fort", "acceleration": "progressive"}',
 '{"temps_secteur": 12.45, "vitesse_min": 78.5, "vitesse_max": 142.3, "angle_max": 51.2, "forces_laterales_max": 1.2, "sous_virage_indice": 0.3, "survirage_indice": 0.1, "stabilite_score": 8.5}',
 'terminee'),
(2, 1, 'Simulation virage Adelaide Magny-Cours', 
 'Simulation du comportement dans le virage Adelaide du circuit Magny-Cours',
 '{"vitesse_entree": 110, "vitesse_apex": 60, "vitesse_sortie": 130, "angle_inclinaison_max": 48, "trajectoire": "exterieure", "freinage": "progressif", "acceleration": "forte"}',
 '{"temps_secteur": 14.23, "vitesse_min": 58.7, "vitesse_max": 132.5, "angle_max": 49.3, "forces_laterales_max": 1.1, "sous_virage_indice": 0.2, "survirage_indice": 0.4, "stabilite_score": 7.8}',
 'terminee');
