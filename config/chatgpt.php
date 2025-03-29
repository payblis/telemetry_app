<?php
/**
 * Fichier de configuration pour l'intégration avec l'API ChatGPT
 */

// Clé API OpenAI (à remplacer par votre propre clé)
define('OPENAI_API_KEY', 'votre_clé_api_ici');

// URL de l'API
define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');

// Modèle à utiliser
define('OPENAI_MODEL', 'gpt-3.5-turbo');

// Paramètres par défaut
define('OPENAI_TEMPERATURE', 0.7); // Créativité (0 = déterministe, 1 = créatif)
define('OPENAI_MAX_TOKENS', 1000); // Longueur maximale de la réponse

// Messages système pour différents contextes
$OPENAI_SYSTEM_MESSAGES = [
    'SETTINGS_RECOMMENDATION' => 'Vous êtes un expert en télémétrie moto qui aide à optimiser les réglages pour améliorer les performances sur circuit. Donnez des recommandations précises et techniques basées sur les informations fournies.',
    'CIRCUIT_IMPORT' => 'Vous êtes un expert en circuits moto qui fournit des informations détaillées sur les circuits. Donnez des informations précises et techniques sur le circuit demandé, en particulier sur ses virages.',
    'COMMUNITY_ENRICHMENT' => 'Vous êtes un expert en télémétrie moto qui aide à optimiser les réglages en combinant l\'IA et les données communautaires. Votre tâche est d\'enrichir une recommandation initiale avec des données provenant d\'experts.'
];
