-- Insertion de l'utilisateur administrateur par défaut
-- Mot de passe par défaut : Admin@2024! (à changer lors de la première connexion)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@telemetrie-ia.fr', '$2y$10$92Jq5rX7nrZ0UqUj6Ky0q.YtAZwZy3h1khG4NxZ8Kj5j5X5X5j5j5', 'admin'); 