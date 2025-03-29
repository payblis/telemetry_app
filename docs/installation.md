# Guide d'installation de l'application de télémétrie moto

Ce document explique comment installer l'application de télémétrie moto sur un serveur Plesk.

## Prérequis

- Un serveur avec Plesk installé
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Une clé API OpenAI (pour l'intégration ChatGPT)

## Étapes d'installation

### 1. Préparation des fichiers

1. Téléchargez l'archive ZIP contenant l'application
2. Décompressez l'archive sur votre ordinateur

### 2. Configuration du domaine dans Plesk

1. Connectez-vous à votre panneau Plesk
2. Créez un nouveau domaine ou utilisez un domaine existant
3. Assurez-vous que PHP 7.4 (ou supérieur) est activé pour ce domaine

### 3. Transfert des fichiers

1. Dans Plesk, allez dans "Fichiers" pour votre domaine
2. Supprimez les fichiers par défaut dans le répertoire racine (généralement httpdocs)
3. Utilisez l'outil de téléchargement de Plesk pour uploader tous les fichiers de l'application

### 4. Configuration de la base de données

1. Dans Plesk, allez dans "Bases de données"
2. Créez une nouvelle base de données MySQL et un utilisateur avec tous les privilèges
3. Notez le nom de la base de données, le nom d'utilisateur et le mot de passe

### 5. Import des tables dans phpMyAdmin

1. Dans Plesk, cliquez sur l'icône phpMyAdmin à côté de votre base de données
2. Sélectionnez votre base de données dans le panneau de gauche
3. Allez dans l'onglet "Importer"
4. Cliquez sur "Parcourir" et sélectionnez le fichier `database/schema.sql`
5. Cliquez sur "Exécuter" pour importer les tables

### 6. Configuration de l'application

1. Ouvrez le fichier `config/database.php` avec l'éditeur de fichiers de Plesk
2. Modifiez les paramètres de connexion à la base de données :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'votre_base_de_donnees');
   define('DB_USER', 'votre_utilisateur');
   define('DB_PASS', 'votre_mot_de_passe');
   ```

3. Ouvrez le fichier `config/chatgpt.php` avec l'éditeur de fichiers de Plesk
4. Modifiez la clé API OpenAI :
   ```php
   define('OPENAI_API_KEY', 'votre_clé_api_openai');
   ```

### 7. Configuration des permissions

1. Dans Plesk, allez dans "Outils et paramètres" > "Paramètres PHP"
2. Assurez-vous que les extensions suivantes sont activées : mysqli, mbstring, xml, curl
3. Dans le gestionnaire de fichiers, définissez les permissions 755 pour les dossiers et 644 pour les fichiers

### 8. Finalisation

1. Accédez à votre domaine dans un navigateur
2. Vous devriez voir la page d'accueil de l'application
3. Créez un compte administrateur en accédant à la page d'inscription
4. Connectez-vous avec vos identifiants

## Résolution des problèmes courants

### Erreur de connexion à la base de données

- Vérifiez que les paramètres de connexion dans `config/database.php` sont corrects
- Assurez-vous que l'utilisateur de la base de données a les privilèges nécessaires

### Erreur d'API ChatGPT

- Vérifiez que votre clé API OpenAI est valide et correctement configurée
- Assurez-vous que l'extension curl de PHP est activée

### Problèmes de permissions

- Si vous rencontrez des erreurs d'écriture, vérifiez les permissions des dossiers et fichiers
- Les dossiers doivent avoir la permission 755 et les fichiers 644

## Support

Si vous rencontrez des problèmes lors de l'installation, veuillez contacter notre équipe de support à l'adresse support@telemoto.com.
