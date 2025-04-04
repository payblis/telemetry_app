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

// Log de la requête
error_log("PUBLIC INDEX.PHP - Requête reçue: " . $_SERVER['REQUEST_URI']);
error_log("PUBLIC INDEX.PHP - Méthode: " . $_SERVER['REQUEST_METHOD']);
error_log("PUBLIC INDEX.PHP - Script: " . $_SERVER['SCRIPT_NAME']);

// Définir le chemin racine de l'application
define('ROOT_PATH', dirname(__DIR__));
error_log("PUBLIC INDEX.PHP - ROOT_PATH: " . ROOT_PATH);

try {
    // Charger la configuration
    error_log("PUBLIC INDEX.PHP - Chargement de la configuration");
    require_once ROOT_PATH . '/config/config.php';
    error_log("PUBLIC INDEX.PHP - Configuration chargée");

    // Charger l'autoloader
    error_log("PUBLIC INDEX.PHP - Chargement de l'autoloader");
    require_once ROOT_PATH . '/config/autoload.php';
    error_log("PUBLIC INDEX.PHP - Autoloader chargé");

    // Initialiser la session
    error_log("PUBLIC INDEX.PHP - Initialisation de la session");
    session_start();
    error_log("PUBLIC INDEX.PHP - Session démarrée");

    // Charger le routeur
    error_log("PUBLIC INDEX.PHP - Chargement du routeur");
    require_once ROOT_PATH . '/config/router.php';
    error_log("PUBLIC INDEX.PHP - Routeur chargé");

    // Traiter la requête
    error_log("PUBLIC INDEX.PHP - Création de l'instance du routeur");
    $router = new Router();
    error_log("PUBLIC INDEX.PHP - Démarrage du dispatch");
    $router->dispatch();
    error_log("PUBLIC INDEX.PHP - Dispatch terminé");

} catch (Exception $e) {
    // Log de l'erreur
    error_log("PUBLIC INDEX.PHP - ERREUR: " . $e->getMessage());
    error_log("PUBLIC INDEX.PHP - TRACE: " . $e->getTraceAsString());
    
    // Gérer les erreurs
    if (DEBUG_MODE) {
        echo '<h1>Erreur</h1>';
        echo '<p>' . $e->getMessage() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    } else {
        // En production, afficher un message générique
        include ROOT_PATH . '/resources/views/errors/500.php';
    }
}
