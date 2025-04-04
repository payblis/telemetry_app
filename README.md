# Application de Réglage de Moto

Une application simple en PHP pour gérer les réglages de moto et les pilotes.

## Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache, Nginx, etc.)

## Installation

1. Clonez le dépôt :
```bash
git clone [URL_DU_REPO]
```

2. Créez la base de données :
```bash
mysql -u root -p < database.sql
```

3. Configurez la connexion à la base de données :
- Ouvrez le fichier `config/database.php`
- Modifiez les constantes selon votre configuration :
  - DB_HOST
  - DB_USER
  - DB_PASS
  - DB_NAME

4. Placez les fichiers dans le répertoire de votre serveur web.

## Utilisation

1. Accédez à l'application via votre navigateur :
```
http://localhost/chemin/vers/l'application
```

2. Compte administrateur par défaut :
- Email : admin@example.com
- Mot de passe : password

3. Fonctionnalités :
- Connexion/Inscription
- Gestion des pilotes
- Ajout de pilotes
- Liste des pilotes

## Structure des fichiers

```
├── assets/
│   └── css/
│       └── style.css
├── config/
│   └── database.php
├── login.php
├── register.php
├── riders.php
├── database.sql
└── README.md
```

## Sécurité

- Les mots de passe sont hachés avec l'algorithme bcrypt
- Protection contre les injections SQL avec PDO
- Échappement des données avec htmlspecialchars
- Gestion des sessions

## Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :
1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Ouvrir une Pull Request 