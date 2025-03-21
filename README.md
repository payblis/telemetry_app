# Telemetry App - Assistant Moto

Application web interactive permettant au staff technique/pilote de dialoguer en direct avec l'IA ChatGPT pour obtenir des conseils techniques durant une session sur piste moto.

## Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache recommandé)
- Clé API OpenAI valide

## Installation

1. Cloner le repository :
```bash
git clone https://github.com/votre-username/telemetry_app.git
cd telemetry_app
```

2. Créer la base de données :
```bash
mysql -u root -p < database.sql
```

3. Configurer l'application :
- Copier le fichier `config.php` et ajuster les paramètres selon votre environnement
- Assurez-vous que la clé API OpenAI est correctement configurée

4. Installer les dépendances PHP :
```bash
composer require guzzlehttp/guzzle
```

5. Configurer les permissions :
```bash
chmod 755 -R .
chmod 777 -R storage/logs
```

## Utilisation

1. Créer une nouvelle session :
- Cliquer sur "Nouvelle Session"
- Sélectionner un circuit dans la liste
- L'application récupère automatiquement les informations techniques du circuit

2. Dialoguer avec l'assistant :
- Poser des questions techniques dans la zone de chat
- L'assistant répond en tenant compte des spécificités du circuit

## Structure des fichiers

```
telemetry_app/
├── api/
│   ├── chat.php
│   └── create_session.php
├── config.php
├── database.php
├── database.sql
├── index.php
└── README.md
```

## Sécurité

- La clé API OpenAI est stockée de manière sécurisée côté serveur
- Toutes les requêtes sont validées et nettoyées
- Les connexions à la base de données utilisent des requêtes préparées

## Support

Pour toute question ou problème, veuillez ouvrir une issue sur le repository GitHub.

## Licence

MIT License 