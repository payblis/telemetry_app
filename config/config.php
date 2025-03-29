<?php
/**
 * Configuration générale de l'application
 */

// Informations sur l'application
define('APP_NAME', 'TéléMoto');
define('APP_VERSION', '1.0.0');

// Chemins de l'application
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('PAGES_PATH', ROOT_PATH . '/pages');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('API_PATH', ROOT_PATH . '/api');

// URL de base (à modifier selon l'environnement)
define('BASE_URL', 'http://localhost/telemoto_simple');

// Configuration de l'API ChatGPT
define('OPENAI_API_KEY', 'votre_clé_api_ici'); // À remplacer par la clé API réelle

// Configuration des sessions
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Affichage des erreurs (à désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fonction d'autoloading des classes
spl_autoload_register(function ($class_name) {
    $file = CLASSES_PATH . '/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
