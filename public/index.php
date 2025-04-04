<?php
/**
 * Point d'entrée principal de l'application SaaS de Télémétrie Moto
 * 
 * Ce fichier sert de contrôleur frontal pour toutes les requêtes
 * et initialise l'environnement de l'application
 */

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fonction de log détaillée
function logError($message, $data = null) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $message;
    if ($data !== null) {
        $logMessage .= " - " . print_r($data, true);
    }
    error_log($logMessage);
}

// Log de la requête
logError("PUBLIC INDEX.PHP - Requête reçue", $_SERVER);
logError("PUBLIC INDEX.PHP - Document Root", $_SERVER['DOCUMENT_ROOT']);
logError("PUBLIC INDEX.PHP - Script Filename", $_SERVER['SCRIPT_FILENAME']);
logError("PUBLIC INDEX.PHP - Request URI", $_SERVER['REQUEST_URI']);

// Définir le chemin racine de l'application
define('ROOT_PATH', dirname(__DIR__));
logError("PUBLIC INDEX.PHP - ROOT_PATH", ROOT_PATH);

try {
    // Charger la configuration
    logError("PUBLIC INDEX.PHP - Chargement de la configuration");
    if (!file_exists(ROOT_PATH . '/config/config.php')) {
        throw new Exception("Fichier de configuration non trouvé: " . ROOT_PATH . '/config/config.php');
    }
    require_once ROOT_PATH . '/config/config.php';
    logError("PUBLIC INDEX.PHP - Configuration chargée");

    // Charger l'autoloader
    logError("PUBLIC INDEX.PHP - Chargement de l'autoloader");
    if (!file_exists(ROOT_PATH . '/config/autoload.php')) {
        throw new Exception("Fichier autoloader non trouvé: " . ROOT_PATH . '/config/autoload.php');
    }
    require_once ROOT_PATH . '/config/autoload.php';
    logError("PUBLIC INDEX.PHP - Autoloader chargé");

    // Initialiser la session
    logError("PUBLIC INDEX.PHP - Initialisation de la session");
    session_start();
    logError("PUBLIC INDEX.PHP - Session démarrée");

    // Charger le routeur
    logError("PUBLIC INDEX.PHP - Chargement du routeur");
    if (!file_exists(ROOT_PATH . '/config/router.php')) {
        throw new Exception("Fichier routeur non trouvé: " . ROOT_PATH . '/config/router.php');
    }
    require_once ROOT_PATH . '/config/router.php';
    logError("PUBLIC INDEX.PHP - Routeur chargé");

    // Traiter la requête
    logError("PUBLIC INDEX.PHP - Création de l'instance du routeur");
    $router = new Router();
    logError("PUBLIC INDEX.PHP - Démarrage du dispatch");
    $router->dispatch();
    logError("PUBLIC INDEX.PHP - Dispatch terminé");

} catch (Exception $e) {
    // Log de l'erreur
    logError("PUBLIC INDEX.PHP - ERREUR FATALE", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Gérer les erreurs
    if (DEBUG_MODE) {
        echo '<h1>Erreur</h1>';
        echo '<p>' . $e->getMessage() . '</p>';
        echo '<p>Fichier: ' . $e->getFile() . '</p>';
        echo '<p>Ligne: ' . $e->getLine() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    } else {
        // En production, afficher un message générique
        include ROOT_PATH . '/resources/views/errors/500.php';
    }
}
