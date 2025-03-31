-- Mise à jour de la base de données TeleMoto
-- Version: 2.0.0
-- Date: 31/03/2025

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Base de données : `telemoto`

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('utilisateur','expert','admin') NOT NULL DEFAULT 'utilisateur',
  `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `derniere_connexion` datetime DEFAULT NULL,
  `token_verification` varchar(255) DEFAULT NULL,
  `email_verifie` tinyint(1) NOT NULL DEFAULT '0',
  `token_reset` varchar(255) DEFAULT NULL,
  `token_reset_expiration` datetime DEFAULT NULL,
  `niveau` int(11) NOT NULL DEFAULT '1',
  `experience` int(11) NOT NULL DEFAULT '0',
  `experience_totale` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `motos`
--

CREATE TABLE IF NOT EXISTS `motos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `marque` varchar(100) NOT NULL,
  `modele` varchar(100) NOT NULL,
  `annee` int(4) NOT NULL,
  `type` enum('origine','race') NOT NULL DEFAULT 'origine',
  `cylindree` int(11) DEFAULT NULL,
  `poids` int(11) DEFAULT NULL,
  `puissance` int(11) DEFAULT NULL,
  `couple` int(11) DEFAULT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `equipements_moto`
--

CREATE TABLE IF NOT EXISTS `equipements_moto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moto_id` int(11) NOT NULL,
  `type` enum('fourche','amortisseur','bras_oscillant','tes_fourche','cadre','direction','suspension_arriere','roues','pneus','freins','commandes','embrayage','guidon','repose_pieds','selecteur','boite_vitesses','electronique','telemetrie','controle_traction','echappement') NOT NULL,
  `marque` varchar(100) DEFAULT NULL,
  `modele` varchar(100) DEFAULT NULL,
  `specifications` text,
  `date_installation` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `moto_id` (`moto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `circuits`
--

CREATE TABLE IF NOT EXISTS `circuits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `longueur` float DEFAULT NULL,
  `nombre_virages` int(11) DEFAULT NULL,
  `description` text,
  `caracteristiques` text,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `createur_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `createur_id` (`createur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pilote_id` int(11) NOT NULL,
  `moto_id` int(11) NOT NULL,
  `circuit_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `type` enum('course','qualification','free_practice','entrainement','track_day') NOT NULL,
  `conditions_meteo` enum('sec','humide','mouille','mixte') NOT NULL,
  `temperature_air` float DEFAULT NULL,
  `temperature_piste` float DEFAULT NULL,
  `grip_estime` enum('faible','moyen','bon','excellent') DEFAULT NULL,
  `meilleur_chrono` time DEFAULT NULL,
  `chrono_moyen` time DEFAULT NULL,
  `tours_effectues` int(11) DEFAULT NULL,
  `notes_pilote` text,
  `reglages_suspension` text,
  `reglages_geometrie` text,
  `reglages_moteur` text,
  `reglages_pneus` text,
  `reglages_electronique` text,
  `suggestions_chatgpt` text,
  `suggestions_communautaires` text,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pilote_id` (`pilote_id`),
  KEY `moto_id` (`moto_id`),
  KEY `circuit_id` (`circuit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `questions_experts`
--

CREATE TABLE IF NOT EXISTS `questions_experts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `categorie` enum('suspension','geometrie','moteur','pneus','electronique','general') NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `reponses_experts`
--

CREATE TABLE IF NOT EXISTS `reponses_experts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `expert_id` int(11) NOT NULL,
  `reponse` text NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `votes_positifs` int(11) NOT NULL DEFAULT '0',
  `votes_negatifs` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  KEY `expert_id` (`expert_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `statistiques_utilisation`
--

CREATE TABLE IF NOT EXISTS `statistiques_utilisation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `action` enum('connexion','creation_moto','creation_session','consultation_expert','utilisation_chatgpt','modification_reglages') NOT NULL,
  `details` text,
  `date_action` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `guides_mecanique`
--

CREATE TABLE IF NOT EXISTS `guides_mecanique` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `categorie` enum('suspension','geometrie','moteur','pneus','electronique','general') NOT NULL,
  `contenu` text NOT NULL,
  `images` text,
  `createur_id` int(11) DEFAULT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `createur_id` (`createur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `comparaisons_sessions`
--

CREATE TABLE IF NOT EXISTS `comparaisons_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `session1_id` int(11) NOT NULL,
  `session2_id` int(11) NOT NULL,
  `notes_comparaison` text,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `session1_id` (`session1_id`),
  KEY `session2_id` (`session2_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `donnees_meteo`
--

CREATE TABLE IF NOT EXISTS `donnees_meteo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `temperature` float NOT NULL,
  `humidite` float NOT NULL,
  `pression_atmospherique` float NOT NULL,
  `vitesse_vent` float NOT NULL,
  `direction_vent` varchar(50) NOT NULL,
  `conditions` varchar(100) NOT NULL,
  `date_releve` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `entretiens_moto`
--

CREATE TABLE IF NOT EXISTS `entretiens_moto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moto_id` int(11) NOT NULL,
  `type` enum('vidange','pneus','plaquettes','chaine','revision_complete','autre') NOT NULL,
  `description` text NOT NULL,
  `date_entretien` date NOT NULL,
  `kilometrage` int(11) DEFAULT NULL,
  `cout` decimal(10,2) DEFAULT NULL,
  `prestataire` varchar(100) DEFAULT NULL,
  `notes` text,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `moto_id` (`moto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `videos_sessions`
--

CREATE TABLE IF NOT EXISTS `videos_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text,
  `url` varchar(255) DEFAULT NULL,
  `fichier` varchar(255) DEFAULT NULL,
  `duree` time DEFAULT NULL,
  `date_upload` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `donnees_telemetrie`
--

CREATE TABLE IF NOT EXISTS `donnees_telemetrie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `tour` int(11) NOT NULL,
  `temps_tour` time NOT NULL,
  `vitesse_max` float DEFAULT NULL,
  `vitesse_moyenne` float DEFAULT NULL,
  `acceleration_max` float DEFAULT NULL,
  `deceleration_max` float DEFAULT NULL,
  `angle_inclinaison_max` float DEFAULT NULL,
  `regime_moteur_max` int(11) DEFAULT NULL,
  `temperature_moteur_max` float DEFAULT NULL,
  `donnees_brutes` text,
  `date_import` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `evenements_circuit`
--

CREATE TABLE IF NOT EXISTS `evenements_circuit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `circuit_id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `type` enum('course','entrainement','track_day','stage') NOT NULL,
  `statut` enum('planifie','confirme','annule','termine') NOT NULL DEFAULT 'planifie',
  `notes_preparation` text,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `circuit_id` (`circuit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `categories_produits`
--

CREATE TABLE IF NOT EXISTS `categories_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE IF NOT EXISTS `produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `etat` enum('neuf','comme_neuf','tres_bon','bon','acceptable') NOT NULL,
  `marque` varchar(100) DEFAULT NULL,
  `modele` varchar(100) DEFAULT NULL,
  `annee` int(4) DEFAULT NULL,
  `vendeur_id` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `categorie_id` (`categorie_id`),
  KEY `vendeur_id` (`vendeur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `avis_produits`
--

CREATE TABLE IF NOT EXISTS `avis_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produit_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `note` int(1) NOT NULL,
  `commentaire` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `produit_utilisateur` (`produit_id`,`utilisateur_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `badges`
--

CREATE TABLE IF NOT EXISTS `badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `categorie` enum('sessions','performance','exploration','contribution','progression') NOT NULL,
  `niveau` int(1) NOT NULL DEFAULT '1',
  `condition_obtention` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs_badges`
--

CREATE TABLE IF NOT EXISTS `utilisateurs_badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `date_obtention` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `utilisateur_badge` (`utilisateur_id`,`badge_id`),
  KEY `badge_id` (`badge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `push_subscriptions`
--

CREATE TABLE IF NOT EXISTS `push_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `endpoint` varchar(500) NOT NULL,
  `auth` varchar(100) NOT NULL,
  `p256dh` varchar(200) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Contraintes pour les tables déchargées
--

ALTER TABLE `motos`
  ADD CONSTRAINT `motos_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

ALTER TABLE `equipements_moto`
  ADD CONSTRAINT `equipements_moto_ibfk_1` FOREIGN KEY (`moto_id`) REFERENCES `motos` (`id`) ON DELETE CASCADE;

ALTER TABLE `circuits`
  ADD CONSTRAINT `circuits_ibfk_1` FOREIGN KEY (`createur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`pilote_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sessions_ibfk_2` FOREIGN KEY (`moto_id`) REFERENCES `motos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sessions_ibfk_3` FOREIGN KEY (`circuit_id`) REFERENCES `circuits` (`id`) ON DELETE CASCADE;

ALTER TABLE `reponses_experts`
  ADD CONSTRAINT `reponses_experts_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions_experts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reponses_experts_ibfk_2` FOREIGN KEY (`expert_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

ALTER TABLE `statistiques_utilisation`
  ADD CONSTRAINT `statistiques_utilisation_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

ALTER TABLE `guides_mecanique`
  ADD CONSTRAINT `guides_mecanique_ibfk_1` FOREIGN KEY (`createur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

ALTER TABLE `comparaisons_sessions`
  ADD CONSTRAINT `comparaisons_sessions_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comparaisons_sessions_ibfk_2` FOREIGN KEY (`session1_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comparaisons_sessions_ibfk_3` FOREIGN KEY (`session2_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE;

ALTER TABLE `donnees_meteo`
  ADD CONSTRAINT `donnees_meteo_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE;

ALTER TABLE `entretiens_moto`
  ADD CONSTRAINT `entretiens_moto_ibfk_1` FOREIGN KEY (`moto_id`) REFERENCES `motos` (`id`) ON DELETE CASCADE;

ALTER TABLE `videos_sessions`
  ADD CONSTRAINT `videos_sessions_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE;

ALTER TABLE `donnees_telemetrie`
  ADD CONSTRAINT `donnees_telemetrie_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE;

ALTER TABLE `evenements_circuit`
  ADD CONSTRAINT `evenements_circuit_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evenements_circuit_ibfk_2` FOREIGN KEY (`circuit_id`) REFERENCES `circuits` (`id`) ON DELETE CASCADE;

ALTER TABLE `produits`
  ADD CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories_produits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `produits_ibfk_2` FOREIGN KEY (`vendeur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

ALTER TABLE `avis_produits`
  ADD CONSTRAINT `avis_produits_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_produits_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

ALTER TABLE `utilisateurs_badges`
  ADD CONSTRAINT `utilisateurs_badges_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `utilisateurs_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE;

ALTER TABLE `push_subscriptions`
  ADD CONSTRAINT `push_subscriptions_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Données initiales pour la table `categories_produits`
--

INSERT INTO `categories_produits` (`nom`, `description`) VALUES
('Casques', 'Casques de moto de toutes marques et tailles'),
('Vêtements', 'Combinaisons, blousons, pantalons et autres vêtements de moto'),
('Gants', 'Gants de moto pour toutes saisons'),
('Bottes', 'Bottes et chaussures de moto'),
('Pneus', 'Pneus avant et arrière pour motos de route et de course'),
('Pièces moteur', 'Pièces et composants pour moteurs de moto'),
('Échappements', 'Systèmes d\'échappement complets et silencieux'),
('Suspensions', 'Fourches, amortisseurs et pièces de suspension'),
('Freins', 'Disques, plaquettes et systèmes de freinage'),
('Électronique', 'Boîtiers électroniques, capteurs et accessoires électroniques'),
('Accessoires', 'Accessoires divers pour motos'),
('Bagagerie', 'Sacoches, top-cases et solutions de rangement'),
('Entretien', 'Produits d\'entretien et outils'),
('Autres', 'Autres équipements et pièces');

-- --------------------------------------------------------

--
-- Données initiales pour la table `badges`
--

INSERT INTO `badges` (`nom`, `description`, `categorie`, `niveau`, `condition_obtention`) VALUES
('Première Session', 'Vous avez enregistré votre première session sur circuit', 'sessions', 1, 'Enregistrer une première session'),
('Pilote Régulier', 'Vous avez enregistré 10 sessions sur circuit', 'sessions', 2, 'Enregistrer 10 sessions'),
('Pilote Assidu', 'Vous avez enregistré 50 sessions sur circuit', 'sessions', 3, 'Enregistrer 50 sessions'),
('Pilote Dévoué', 'Vous avez enregistré 100 sessions sur circuit', 'sessions', 4, 'Enregistrer 100 sessions'),
('Pilote Élite', 'Vous avez enregistré 500 sessions sur circuit', 'sessions', 5, 'Enregistrer 500 sessions'),
('Premier Chrono', 'Vous avez enregistré votre premier chrono', 'performance', 1, 'Enregistrer un premier chrono'),
('Amélioration', 'Vous avez amélioré votre chrono de 1 seconde', 'performance', 2, 'Améliorer son chrono de 1 seconde'),
('Progression Significative', 'Vous avez amélioré votre chrono de 3 secondes', 'performance', 3, 'Améliorer son chrono de 3 secondes'),
('Progression Majeure', 'Vous avez amélioré votre chrono de 5 secondes', 'performance', 4, 'Améliorer son chrono de 5 secondes'),
('Maître du Chrono', 'Vous avez amélioré votre chrono de 10 secondes', 'performance', 5, 'Améliorer son chrono de 10 secondes'),
('Découvreur', 'Vous avez roulé sur votre premier circuit', 'exploration', 1, 'Rouler sur un premier circuit'),
('Explorateur', 'Vous avez roulé sur 5 circuits différents', 'exploration', 2, 'Rouler sur 5 circuits différents'),
('Globe-Trotter', 'Vous avez roulé sur 10 circuits différents', 'exploration', 3, 'Rouler sur 10 circuits différents'),
('Aventurier', 'Vous avez roulé sur 20 circuits différents', 'exploration', 4, 'Rouler sur 20 circuits différents'),
('Légende des Circuits', 'Vous avez roulé sur 50 circuits différents', 'exploration', 5, 'Rouler sur 50 circuits différents'),
('Premier Conseil', 'Vous avez partagé votre première réponse d\'expert', 'contribution', 1, 'Partager une première réponse d\'expert'),
('Conseiller', 'Vous avez partagé 10 réponses d\'expert', 'contribution', 2, 'Partager 10 réponses d\'expert'),
('Mentor', 'Vous avez partagé 50 réponses d\'expert', 'contribution', 3, 'Partager 50 réponses d\'expert'),
('Gourou', 'Vous avez partagé 100 réponses d\'expert', 'contribution', 4, 'Partager 100 réponses d\'expert'),
('Sage', 'Vous avez partagé 500 réponses d\'expert', 'contribution', 5, 'Partager 500 réponses d\'expert'),
('Première Connexion', 'Vous vous êtes connecté pour la première fois', 'progression', 1, 'Se connecter une première fois'),
('Fidèle', 'Vous vous êtes connecté 10 jours', 'progression', 2, 'Se connecter 10 jours'),
('Habitué', 'Vous vous êtes connecté 30 jours', 'progression', 3, 'Se connecter 30 jours'),
('Passionné', 'Vous vous êtes connecté 100 jours', 'progression', 4, 'Se connecter 100 jours'),
('Légende', 'Vous vous êtes connecté 365 jours', 'progression', 5, 'Se connecter 365 jours');

COMMIT;
