-- Données de démonstration

-- Utilisateurs
INSERT INTO users (email, password, role) VALUES
('admin@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewKy7PqVKZ5xQY9C', 'admin'), -- mot de passe: admin123
('user@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewKy7PqVKZ5xQY9C', 'user'); -- mot de passe: admin123

-- Pilotes
INSERT INTO pilotes (user_id, nom, prenom, pseudo, date_naissance, poids, taille) VALUES
(1, 'Rossi', 'Valentino', 'The Doctor', '1979-02-16', 67, 1.82),
(1, 'Marquez', 'Marc', 'The Baby', '1993-02-17', 59, 1.68),
(2, 'Quartararo', 'Fabio', 'El Diablo', '1999-04-20', 64, 1.77);

-- Motos
INSERT INTO motos (pilote_id, marque, modele, annee, cylindree, puissance, poids) VALUES
(1, 'Yamaha', 'YZF-R1', 2023, 1000, 200, 201),
(2, 'Honda', 'RC213V', 2023, 1000, 250, 157),
(3, 'Yamaha', 'YZF-R1', 2023, 1000, 200, 201);

-- Circuits
INSERT INTO circuits (nom, pays, longueur_km, nb_virages, record_tour) VALUES
('Circuit de Barcelona-Catalunya', 'Espagne', 4.627, 16, 89.589),
('Circuit de Jerez', 'Espagne', 4.428, 13, 91.189),
('Circuit du Mans', 'France', 4.185, 14, 92.123),
('Mugello Circuit', 'Italie', 5.245, 15, 90.456);

-- Sessions
INSERT INTO sessions (pilote_id, moto_id, circuit_id, date_session, type_session, meteo, temperature_air, temperature_piste, notes) VALUES
(1, 1, 1, '2023-06-10', 'free practice', 'Ensoleillé', 25.5, 32.8, 'Session de test des nouveaux réglages'),
(1, 1, 1, '2023-06-11', 'qualification', 'Nuageux', 23.2, 28.5, 'Qualification pour la course principale'),
(2, 2, 2, '2023-07-15', 'course', 'Pluvieux', 18.5, 21.2, 'Course sous la pluie'),
(3, 3, 3, '2023-08-20', 'trackday', 'Ensoleillé', 28.0, 35.5, 'Journée de test');

-- Tours
INSERT INTO laps (session_id, numero_tour, temps_tour, vitesse_max, vitesse_moyenne, angle_max, acceleration_moyenne, freinage_moyen, video_url) VALUES
(1, 1, 92.345, 312.5, 180.2, 58.5, 1.2, 1.5, 'https://example.com/video1'),
(1, 2, 91.789, 315.2, 182.5, 59.2, 1.3, 1.6, 'https://example.com/video2'),
(1, 3, 91.234, 318.5, 185.2, 60.1, 1.4, 1.7, 'https://example.com/video3'),
(2, 1, 90.456, 320.5, 188.2, 61.2, 1.5, 1.8, 'https://example.com/video4'),
(2, 2, 89.789, 322.5, 190.2, 62.1, 1.6, 1.9, 'https://example.com/video5'),
(3, 1, 95.123, 305.5, 175.2, 55.2, 1.1, 1.4, 'https://example.com/video6'),
(4, 1, 93.456, 310.5, 178.2, 57.2, 1.2, 1.5, 'https://example.com/video7');

-- Réglages
INSERT INTO reglages (session_id, precharge_avant, precharge_arriere, detente_avant, detente_arriere, compression_avant, compression_arriere, hauteur_avant, hauteur_arriere, rapport_final, pression_pneu_avant, pression_pneu_arriere, notes) VALUES
(1, 5.5, 5.0, 12, 10, 8, 6, 5.0, 5.5, '16/42', 2.1, 1.9, 'Réglages pour piste sèche'),
(2, 5.0, 4.5, 10, 8, 6, 4, 4.5, 5.0, '16/42', 2.0, 1.8, 'Réglages pour qualification'),
(3, 6.0, 5.5, 14, 12, 10, 8, 5.5, 6.0, '16/43', 2.2, 2.0, 'Réglages pour piste humide'),
(4, 5.5, 5.0, 12, 10, 8, 6, 5.0, 5.5, '16/42', 2.1, 1.9, 'Réglages pour trackday'); 