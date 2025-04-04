-- -----------------------------------------------------
-- 3. Gestion des Circuits
-- -----------------------------------------------------

USE telemetrie_moto;

-- Table des circuits
CREATE TABLE circuits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    pays VARCHAR(50),
    ville VARCHAR(50),
    longueur DECIMAL(6,3),
    largeur DECIMAL(5,2),
    nombre_virages INT,
    altitude DECIMAL(6,2),
    coordonnees_gps VARCHAR(100),
    description TEXT,
    image_path VARCHAR(255),
    created_by INT,
    source ENUM('manuel', 'ia', 'communaute') DEFAULT 'manuel',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Table des virages de circuit
CREATE TABLE virages_circuit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    circuit_id INT NOT NULL,
    numero_virage INT NOT NULL,
    nom VARCHAR(50),
    direction ENUM('gauche', 'droite') NOT NULL,
    angle DECIMAL(5,2),
    vitesse_estimee INT,
    rapport_conseille INT,
    coordonnees_entree VARCHAR(100),
    coordonnees_apex VARCHAR(100),
    coordonnees_sortie VARCHAR(100),
    difficultes TEXT,
    notes TEXT,
    FOREIGN KEY (circuit_id) REFERENCES circuits(id) ON DELETE CASCADE
);

-- Table des secteurs de circuit
CREATE TABLE secteurs_circuit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    circuit_id INT NOT NULL,
    nom VARCHAR(50) NOT NULL,
    numero_debut INT NOT NULL,
    numero_fin INT NOT NULL,
    longueur DECIMAL(6,3),
    description TEXT,
    FOREIGN KEY (circuit_id) REFERENCES circuits(id) ON DELETE CASCADE
);

-- Insertion de données d'exemple pour les circuits
INSERT INTO circuits (nom, pays, ville, longueur, largeur, nombre_virages, altitude, coordonnees_gps, description, source) VALUES
('Circuit Paul Ricard', 'France', 'Le Castellet', 5.842, 12.00, 15, 432.00, '43.2506° N, 5.7910° E', 'Circuit moderne avec zones de dégagement colorées, utilisé pour le Grand Prix de France de F1.', 'manuel'),
('Circuit de Magny-Cours', 'France', 'Magny-Cours', 4.411, 15.00, 17, 228.00, '46.8642° N, 3.1630° E', 'Ancien circuit de F1, technique avec une variété de virages.', 'manuel'),
('Circuit de Spa-Francorchamps', 'Belgique', 'Stavelot', 7.004, 14.00, 19, 400.00, '50.4372° N, 5.9714° E', 'Circuit historique dans les Ardennes belges, connu pour son virage Eau Rouge.', 'manuel');

-- Insertion de données d'exemple pour les virages
INSERT INTO virages_circuit (circuit_id, numero_virage, nom, direction, angle, vitesse_estimee, rapport_conseille, notes) VALUES
(1, 1, 'Virage du Pont', 'droite', 90.00, 120, 3, 'Freinage important, point de corde tardif'),
(1, 2, 'S de la Verrerie', 'gauche', 45.00, 160, 4, 'Enchaînement rapide, garder une trajectoire fluide'),
(1, 3, 'Virage du Camp', 'droite', 120.00, 80, 2, 'Virage serré, attention à la sortie'),
(2, 1, 'Estoril', 'droite', 180.00, 70, 2, 'Épingle serrée, freinage tardif possible'),
(2, 2, 'Grande Courbe', 'gauche', 60.00, 150, 4, 'Virage rapide en appui, garder la vitesse'),
(2, 3, 'Adelaide', 'droite', 180.00, 60, 1, 'Épingle technique, freinage progressif'),
(3, 1, 'La Source', 'droite', 180.00, 60, 1, 'Épingle serrée après la ligne droite'),
(3, 2, 'Eau Rouge', 'gauche', 40.00, 280, 6, 'Montée rapide, prendre à fond'),
(3, 3, 'Raidillon', 'droite', 35.00, 270, 6, 'Suite de l\'Eau Rouge, virage en aveugle');

-- Insertion de données d'exemple pour les secteurs
INSERT INTO secteurs_circuit (circuit_id, nom, numero_debut, numero_fin, longueur, description) VALUES
(1, 'Secteur 1', 1, 5, 1.968, 'Section technique avec virages serrés'),
(1, 'Secteur 2', 6, 10, 2.041, 'Section rapide avec longue ligne droite'),
(1, 'Secteur 3', 11, 15, 1.833, 'Section mixte avec chicane finale'),
(2, 'Secteur 1', 1, 6, 1.502, 'Début technique avec épingle'),
(2, 'Secteur 2', 7, 12, 1.604, 'Section centrale rapide'),
(2, 'Secteur 3', 13, 17, 1.305, 'Fin de circuit avec chicane'),
(3, 'Secteur 1', 1, 6, 2.347, 'Début avec La Source et Eau Rouge'),
(3, 'Secteur 2', 7, 13, 2.451, 'Section centrale avec Les Combes'),
(3, 'Secteur 3', 14, 19, 2.206, 'Fin avec Bus Stop et ligne droite');
