# README - Application SaaS de Télémétrie Moto

## Présentation

Cette application SaaS de télémétrie moto est conçue pour démocratiser l'accès à la télémétrie pour les pilotes amateurs et semi-professionnels. Elle permet d'acquérir, d'analyser et de visualiser des données télémétriques à partir de smartphones (via l'application Sensor Logger) et de vidéos, puis de générer des recommandations de réglages personnalisées grâce à l'intelligence artificielle.

## Fonctionnalités principales

- Acquisition de données via l'application Sensor Logger (iOS/Android)
- Importation et analyse des données télémétriques
- Détection automatique des tours et segments
- Visualisation graphique des performances
- Analyse vidéo intelligente
- Recommandations de réglages par IA (OpenAI)
- Système de recommandations communautaires
- Interface utilisateur professionnelle avec thème racing
- Gestion des pilotes, motos et circuits
- Comparaison de performances entre sessions

## Prérequis techniques

- Serveur web (Apache/Nginx)
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Extensions PHP requises : mysqli, json, fileinfo, gd

## Installation

1. Décompressez l'archive ZIP sur votre serveur web
2. Créez une base de données MySQL
3. Importez le fichier `database/telemetrie_moto.sql` dans votre base de données
4. Configurez les paramètres de connexion à la base de données dans `config/config.php`
5. Assurez-vous que les répertoires `storage/uploads` et `storage/temp` sont accessibles en écriture
6. Configurez votre clé API OpenAI dans `config/config.php` (section API_KEYS)

## Structure du projet

- `app/` - Code source de l'application
  - `controllers/` - Contrôleurs de l'application
  - `models/` - Modèles de données
  - `utils/` - Classes utilitaires
  - `views/` - Vues et templates
- `config/` - Fichiers de configuration
- `database/` - Scripts SQL
- `public/` - Point d'entrée public et assets
  - `assets/` - CSS, JavaScript, images
  - `index.php` - Point d'entrée principal
- `storage/` - Stockage des fichiers uploadés
- `tests/` - Scripts de test

## Comptes de test

L'application est préinstallée avec trois comptes de test :

- Admin: admin@telemetrie-moto.com / password
- Utilisateur: user@telemetrie-moto.com / password
- Coach: coach@telemetrie-moto.com / password

## Intégration avec Sensor Logger

Pour utiliser l'application avec Sensor Logger :
1. Installez l'application Sensor Logger sur votre smartphone (iOS/Android)
2. Configurez l'application pour enregistrer les données de gyroscope, accéléromètre, GPS et attitude
3. Fixez votre smartphone sur la moto de manière sécurisée
4. Enregistrez vos sessions avec Sensor Logger
5. Exportez les données au format JSON
6. Importez le fichier JSON dans l'application via la section "Importer des données"

## Intégration avec l'IA

L'application utilise l'API OpenAI pour générer des recommandations de réglages personnalisées. Pour activer cette fonctionnalité, vous devez :
1. Obtenir une clé API OpenAI
2. Configurer cette clé dans le fichier `config/config.php`
3. Activer les recommandations IA dans les paramètres de l'application

## Tests

Des scripts de test sont disponibles dans le répertoire `tests/` :
- `application_test.php` - Test général de l'application
- `sensor_logger_test.php` - Test de l'intégration avec Sensor Logger
- `ai_integration_test.php` - Test de l'intégration avec l'IA
- `run_all_tests.sh` - Script pour exécuter tous les tests

## Licence

Cette application est fournie à titre d'exemple et peut être utilisée comme base pour développer votre propre solution de télémétrie moto.

## Support

Pour toute question ou assistance, veuillez contacter le support technique.
