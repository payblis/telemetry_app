# Telemetry App - Application Web IA pour la Télémétrie Moto

Une application web moderne pour l'analyse de télémétrie moto assistée par IA, combinant l'expertise de ChatGPT et une base de connaissances interne enrichie par des télémétristes experts.

## 🚀 Fonctionnalités

- Analyse en temps réel des données de télémétrie
- Recommandations d'amélioration basées sur l'IA
- Interface intuitive avec Tailwind CSS
- Base de connaissances enrichie par des experts
- Gestion complète des sessions, pilotes, motos et circuits
- Visualisation graphique des données
- Système de rôles (Admin, Utilisateur, Télémétriste)

## 📋 Prérequis

- PHP 8.1 ou supérieur
- MySQL 5.7 ou supérieur
- Composer
- Node.js et NPM (pour Tailwind CSS)
- Clé API OpenAI

## 🛠 Installation

1. Cloner le dépôt :
```bash
git clone https://github.com/votre-username/telemetry_app.git
cd telemetry_app
```

2. Installer les dépendances PHP :
```bash
composer install
```

3. Copier le fichier d'environnement :
```bash
cp .env.example .env
```

4. Configurer les variables d'environnement dans `.env` :
```env
APP_NAME=TelemetryApp
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=telemetry_db
DB_USERNAME=root
DB_PASSWORD=

OPENAI_API_KEY=your-api-key-here
```

5. Créer la base de données :
```bash
mysql -u root -p
CREATE DATABASE telemetry_db;
```

6. Exécuter les migrations :
```bash
php artisan migrate
```

## 🔧 Configuration

### Base de données

La structure de la base de données est automatiquement créée lors de l'exécution des migrations. Les tables principales sont :

- users
- telemetriste_virtuel
- pilotes
- motos
- equipements_moto
- circuits
- sessions
- tours
- telemetry_data
- ia_internal_knowledge
- expert_responses

### API OpenAI

1. Obtenir une clé API sur [OpenAI](https://platform.openai.com/)
2. Ajouter la clé dans le fichier `.env` :
```env
OPENAI_API_KEY=your-api-key-here
```

## 👥 Rôles utilisateurs

- **Administrateur** : Gestion complète du système
- **Utilisateur** : Accès aux fonctionnalités de base
- **Télémétriste** : Accès aux fonctionnalités avancées et enrichissement de l'IA

## 📊 Utilisation

1. Créer un compte utilisateur
2. Se connecter à l'application
3. Configurer les données de base (circuits, pilotes, motos)
4. Créer une nouvelle session
5. Importer les données de télémétrie
6. Analyser les données avec l'assistance IA
7. Recevoir des recommandations en temps réel

## 🔒 Sécurité

- Authentification sécurisée avec JWT
- Protection CSRF
- Validation des entrées
- Échappement des sorties
- HTTPS obligatoire en production

## 📱 Interface utilisateur

L'interface utilise Tailwind CSS pour un design moderne et responsive. Les principales sections sont :

- Dashboard
- Gestion des sessions
- Analyse télémétrique
- Recommandations IA
- Administration

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Forker le projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Ouvrir une Pull Request

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🆘 Support

Pour toute question ou problème :

1. Consulter la documentation
2. Ouvrir une issue sur GitHub
3. Contacter l'équipe de support

## 🔄 Mises à jour

Pour mettre à jour l'application :

```bash
git pull
composer install
php artisan migrate
```

## 📈 Roadmap

- [ ] Import de données en temps réel
- [ ] Application mobile
- [ ] Intégration avec d'autres systèmes de télémétrie
- [ ] Analyses prédictives avancées
- [ ] Export de rapports personnalisés 