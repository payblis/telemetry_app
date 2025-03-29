# Spécifications pour l'application de télémétrie moto simplifiée

## Objectif
Développer une application web simple en PHP orienté objet, MySQL, HTML et CSS personnalisé pour assister ou remplacer un télémétriste moto lors de courses, qualifications, entraînements et essais libres.

## Technologies
- PHP orienté objet simple (sans framework)
- MySQL pour la base de données
- HTML/CSS personnalisé (sans framework frontend)
- JavaScript vanilla pour les interactions côté client
- API ChatGPT pour les recommandations d'IA

## Fonctionnalités requises

### 1. Gestion des utilisateurs
- Système d'authentification (connexion/inscription)
- Trois rôles : Admin, User (Pilote/staff), Expert
- Personnalisation du nom du télémétriste virtuel

### 2. Gestion des pilotes
- Création/édition de fiches pilote (nom, taille, poids, expérience)
- Affichage des pilotes avec filtres et recherche

### 3. Gestion des motos
- Création/édition de fiches moto (marque, modèle, cylindrée)
- Gestion des réglages (suspensions, transmission)
- Historique des modifications

### 4. Gestion des circuits
- Importation de données circuit via ChatGPT
- Détails des virages (angle, vitesse estimée, rapport conseillé)
- Visualisation des circuits

### 5. Gestion des sessions
- Types : Course, qualification, entraînement
- Sélection pilote, moto, circuit
- Saisie des chronos tour par tour
- Remarques et ressentis pilote

### 6. Système d'IA dual
- IA Principale (ChatGPT) pour recommandations instantanées
- IA Communautaire enrichie par les validations utilisateurs
- Interface de chat avec l'assistant
- Validation des recommandations (positive, neutre, négative)

### 7. Bibliothèque communautaire
- Consultation des réglages validés par la communauté
- Filtres par circuit, moto, problème
- Affichage du taux d'efficacité

### 8. Statistiques et analyses
- Graphiques comparatifs des sessions/chronos
- Analyse des performances avant/après recommandations

## Structure simplifiée

### Organisation des fichiers
```
telemoto_simple/
├── config/             # Configuration de l'application
│   ├── config.php      # Configuration générale
│   └── database.php    # Configuration de la base de données
├── classes/            # Classes PHP
│   ├── Database.php    # Classe de connexion à la base de données
│   ├── User.php        # Gestion des utilisateurs
│   ├── Pilot.php       # Gestion des pilotes
│   ├── Moto.php        # Gestion des motos
│   ├── Circuit.php     # Gestion des circuits
│   ├── Session.php     # Gestion des sessions
│   ├── ChatGPT.php     # Intégration avec l'API ChatGPT
│   └── AIFeedback.php  # Gestion des recommandations IA
├── includes/           # Fichiers inclus dans plusieurs pages
│   ├── header.php      # En-tête des pages
│   ├── footer.php      # Pied de page
│   ├── functions.php   # Fonctions utilitaires
│   └── auth.php        # Fonctions d'authentification
├── assets/             # Ressources statiques
│   ├── css/            # Feuilles de style personnalisées
│   ├── js/             # Scripts JavaScript
│   └── img/            # Images
├── pages/              # Pages de l'application
│   ├── login.php       # Page de connexion
│   ├── register.php    # Page d'inscription
│   ├── dashboard.php   # Tableau de bord
│   ├── pilots/         # Pages de gestion des pilotes
│   ├── motos/          # Pages de gestion des motos
│   ├── circuits/       # Pages de gestion des circuits
│   ├── sessions/       # Pages de gestion des sessions
│   └── ai/             # Pages d'interaction avec l'IA
├── api/                # Points d'entrée API pour AJAX
│   ├── chatgpt.php     # API pour ChatGPT
│   └── data.php        # API pour les données
├── database/           # Scripts de base de données
│   └── schema.sql      # Structure de la base de données
└── index.php           # Point d'entrée de l'application
```

## Base de données simplifiée

### Tables principales
1. `users` - Informations utilisateurs et authentification
2. `pilots` - Données des pilotes
3. `motos` - Informations sur les motos
4. `moto_settings` - Réglages des motos
5. `circuits` - Informations sur les circuits
6. `circuit_corners` - Détails des virages
7. `sessions` - Sessions d'entraînement/course
8. `lap_times` - Temps au tour
9. `ai_feedbacks` - Recommandations de l'IA
10. `ai_validations` - Validations des recommandations
11. `expert_feedbacks` - Contributions des experts

## Approche de développement
- Code PHP orienté objet simple et bien commenté
- Séparation claire entre logique métier et présentation
- Requêtes SQL préparées pour la sécurité
- CSS personnalisé avec design responsive
- JavaScript minimal pour les interactions nécessaires
- Intégration directe avec l'API ChatGPT

## Sécurité
- Hachage des mots de passe
- Protection contre les injections SQL
- Validation des données utilisateur
- Contrôle des accès basé sur les rôles

## Déploiement
- Application facilement déployable sur un hébergement Plesk
- Installation simple via import SQL et copie de fichiers
- Configuration minimale requise
