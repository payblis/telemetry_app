#!/bin/bash

# Script d'installation pour l'application de télémétrie moto sur Plesk
# Ce script doit être exécuté après avoir décompressé les fichiers sur le serveur

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Installation de l'application de télémétrie moto ===${NC}"
echo ""

# Vérifier si le script est exécuté dans le bon répertoire
if [ ! -f "index.php" ] || [ ! -d "config" ]; then
    echo -e "${RED}Erreur: Ce script doit être exécuté depuis le répertoire racine de l'application${NC}"
    exit 1
fi

# Demander les informations de la base de données
echo -e "${YELLOW}Configuration de la base de données${NC}"
echo "Veuillez entrer les informations de connexion à votre base de données MySQL:"
read -p "Nom de la base de données: " DB_NAME
read -p "Utilisateur MySQL: " DB_USER
read -p "Mot de passe MySQL: " DB_PASS
read -p "Hôte MySQL (généralement localhost): " DB_HOST
echo ""

# Demander la clé API OpenAI
echo -e "${YELLOW}Configuration de l'API ChatGPT${NC}"
read -p "Clé API OpenAI (laissez vide pour configurer plus tard): " OPENAI_KEY
echo ""

# Mettre à jour le fichier de configuration de la base de données
echo -e "${GREEN}Mise à jour des fichiers de configuration...${NC}"
sed -i "s/define('DB_HOST', 'localhost');/define('DB_HOST', '$DB_HOST');/" config/database.php
sed -i "s/define('DB_NAME', 'telemoto');/define('DB_NAME', '$DB_NAME');/" config/database.php
sed -i "s/define('DB_USER', 'telemoto_user');/define('DB_USER', '$DB_USER');/" config/database.php
sed -i "s/define('DB_PASS', 'password');/define('DB_PASS', '$DB_PASS');/" config/database.php

# Mettre à jour la clé API OpenAI si fournie
if [ ! -z "$OPENAI_KEY" ]; then
    sed -i "s/define('OPENAI_API_KEY', 'votre_clé_api_ici');/define('OPENAI_API_KEY', '$OPENAI_KEY');/" config/chatgpt.php
    echo -e "${GREEN}Clé API OpenAI configurée${NC}"
else
    echo -e "${YELLOW}Aucune clé API OpenAI fournie. Vous devrez la configurer manuellement dans config/chatgpt.php${NC}"
fi

# Importer la base de données
echo -e "${GREEN}Importation de la base de données...${NC}"
if command -v mysql &> /dev/null; then
    # Si mysql est disponible en ligne de commande
    mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < database/schema.sql
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}Base de données importée avec succès${NC}"
    else
        echo -e "${RED}Erreur lors de l'importation de la base de données${NC}"
        echo -e "${YELLOW}Vous devrez importer manuellement le fichier database/schema.sql via phpMyAdmin${NC}"
    fi
else
    echo -e "${YELLOW}Commande mysql non disponible. Vous devrez importer manuellement le fichier database/schema.sql via phpMyAdmin${NC}"
fi

# Définir les permissions des fichiers et dossiers
echo -e "${GREEN}Configuration des permissions...${NC}"
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# Créer un utilisateur administrateur par défaut
echo -e "${YELLOW}Création d'un utilisateur administrateur${NC}"
read -p "Nom d'utilisateur admin: " ADMIN_USERNAME
read -p "Email admin: " ADMIN_EMAIL
read -p "Mot de passe admin: " ADMIN_PASSWORD
read -p "Nom du télémétriste virtuel: " TELEMETRICIAN_NAME

# Hasher le mot de passe
HASHED_PASSWORD=$(php -r "echo password_hash('$ADMIN_PASSWORD', PASSWORD_DEFAULT);")

# Insérer l'utilisateur admin dans la base de données
if command -v mysql &> /dev/null; then
    mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "INSERT INTO users (username, email, password, role, telemetrician_name) VALUES ('$ADMIN_USERNAME', '$ADMIN_EMAIL', '$HASHED_PASSWORD', 'ADMIN', '$TELEMETRICIAN_NAME');"
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}Utilisateur administrateur créé avec succès${NC}"
    else
        echo -e "${RED}Erreur lors de la création de l'utilisateur administrateur${NC}"
        echo -e "${YELLOW}Vous devrez créer manuellement un utilisateur administrateur via l'interface d'inscription${NC}"
    fi
else
    echo -e "${YELLOW}Commande mysql non disponible. Vous devrez créer manuellement un utilisateur administrateur via l'interface d'inscription${NC}"
fi

echo ""
echo -e "${GREEN}=== Installation terminée ===${NC}"
echo -e "${GREEN}Vous pouvez maintenant accéder à votre application via votre navigateur${NC}"
echo -e "${YELLOW}N'oubliez pas de configurer votre serveur web pour qu'il pointe vers ce répertoire${NC}"
echo ""
echo -e "${YELLOW}Si vous rencontrez des problèmes, consultez le guide d'installation dans docs/installation.md${NC}"
