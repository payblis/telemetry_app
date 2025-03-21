# Application de Télémétrie IA pour Compétition Moto 🏍️

Une application web complète utilisant une double intelligence artificielle (ChatGPT + IA interne spécialisée) pour assister ou remplacer efficacement un télémétriste humain lors des compétitions moto.

## 🚀 Fonctionnalités

- Double système d'IA (ChatGPT + IA spécialisée)
- Analyse en temps réel des données télémétriques
- Gestion complète des pilotes, motos et circuits
- Interface responsive et intuitive
- Système d'enrichissement continu par experts
- Visualisation graphique des données
- Gestion multi-utilisateurs avec différents rôles

## 📋 Prérequis

- PHP 8.0 ou supérieur
- MySQL 8.0 ou supérieur
- Serveur Web (Apache/Nginx)
- Composer (Gestionnaire de dépendances PHP)
- Clé API OpenAI (pour ChatGPT)

## 🔧 Installation

1. Cloner le repository :
```bash
git clone https://github.com/votre-username/telemetry_app.git
cd telemetry_app
```

2. Installer les dépendances PHP :
```bash
composer install
```

3. Configurer la base de données :
- Créer une base de données MySQL
- Copier le fichier `.env.example` vers `.env`
- Modifier les paramètres de connexion dans `.env`
- Importer le schéma de base de données :
```bash
mysql -u votre_utilisateur -p votre_base < app/database/schema.sql
```

4. Configurer l'API ChatGPT :
- Ajouter votre clé API OpenAI dans le fichier `.env`

5. Configurer les permissions :
```bash
chmod -R 755 .
chmod -R 777 storage/
```

6. Configurer le serveur web :
- Pointer le DocumentRoot vers le dossier `public/`
- Activer le module de réécriture d'URL (mod_rewrite pour Apache)

## 🔒 Configuration de la Sécurité

1. Activer HTTPS sur votre serveur
2. Configurer les en-têtes de sécurité dans le fichier `.htaccess`
3. Vérifier les permissions des fichiers
4. Protéger le fichier `.env`

## 👥 Rôles Utilisateurs

- **Admin** : Gestion complète du système
- **User** : Accès aux analyses et réglages
- **Expert** : Contribution à l'enrichissement de l'IA

## 📊 Gestion des Données

L'application gère les données suivantes :
- Profils pilotes
- Caractéristiques motos
- Données circuits
- Équipements techniques
- Sessions d'entraînement/course
- Données télémétriques

## 🔄 Mise à Jour de l'IA

L'IA interne s'enrichit continuellement grâce aux :
- Contributions des experts
- Analyses des sessions
- Validation des réponses

## 📱 Interface Utilisateur

- Design moderne et responsive
- Navigation intuitive
- Visualisation graphique des données
- Tableaux de bord personnalisés

## 🛠️ Support Technique

Pour toute question ou assistance :
- Créer une issue sur GitHub
- Contacter le support technique
- Consulter la documentation

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 🤝 Contribution

Les contributions sont les bienvenues ! Voir `CONTRIBUTING.md` pour les détails. 