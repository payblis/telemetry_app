<?php
/**
 * Point d'entrée principal de l'application SaaS de Télémétrie Moto
 * 
 * Ce fichier sert de contrôleur frontal pour toutes les requêtes
 * et initialise l'environnement de l'application
 */

// Définir le chemin racine de l'application
define('ROOT_PATH', dirname(__DIR__));

// Charger la configuration
require_once ROOT_PATH . '/config/config.php';

// Charger l'autoloader
require_once ROOT_PATH . '/config/autoload.php';

// Initialiser la session
session_start();

// Charger le routeur
require_once ROOT_PATH . '/config/router.php';

// Traiter la requête
try {
    $router = new Router();
    $router->dispatch();
} catch (Exception $e) {
    // Gérer les erreurs
    error_log($e->getMessage());
    
    // Rediriger vers une page d'erreur ou afficher un message
    if (DEBUG_MODE) {
        echo '<h1>Erreur</h1>';
        echo '<p>' . $e->getMessage() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    } else {
        // En production, afficher un message générique
        include ROOT_PATH . '/resources/views/errors/500.php';
    }
}
