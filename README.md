# Moto SaaS - Application de réglage de moto

Une application web simple pour gérer les réglages de moto, les pilotes, les circuits et les sessions de roulage.

## Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache, Nginx, etc.)

## Installation

1. Clonez le dépôt dans votre répertoire web :
```bash
git clone [URL_DU_REPO] moto-saas
cd moto-saas
```

2. Créez la base de données et importez le schéma :
```bash
mysql -u root -p < init_db.sql
```

3. Configurez la connexion à la base de données :
   - Ouvrez le fichier `includes/config.php`
   - Modifiez les constantes DB_HOST, DB_USER, DB_PASS et DB_NAME selon votre configuration

4. Assurez-vous que le serveur web a les droits d'écriture sur le dossier `data/uploads/`

## Utilisation

1. Accédez à l'application via votre navigateur :
```
http://localhost/moto-saas
```

2. Connectez-vous avec les identifiants par défaut :
   - Email : admin@example.com
   - Mot de passe : admin123

3. Fonctionnalités disponibles :
   - Gestion des pilotes (ajout, modification, liste)
   - Gestion des circuits
   - Gestion des sessions de roulage
   - Suivi des réglages
   - Analyse des performances

## Structure des dossiers

```
moto-saas/
├── index.php                # Accueil / redirection connexion
├── dashboard.php            # Accueil connecté
├── login.php               # Page de connexion
├── register.php            # Page d'inscription
├── logout.php              # Déconnexion
├── pilotes/                # Gestion des pilotes
├── circuits/               # Gestion des circuits
├── sessions/               # Gestion des sessions
├── settings/               # Paramètres utilisateur
├── includes/               # Fichiers PHP inclus
├── assets/                 # CSS, JS, images
└── data/                   # Données uploadées
```

## Sécurité

- Les mots de passe sont hachés avec bcrypt
- Protection contre les injections SQL avec PDO
- Protection XSS avec htmlspecialchars
- Vérification des droits d'accès
- Sessions sécurisées

## Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :
1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Ouvrir une Pull Request

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails. 