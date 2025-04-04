<?php
/**
 * Point d'entrée principal de l'application SaaS de Télémétrie Moto
 * 
 * Ce fichier sert de point d'entrée pour toutes les requêtes
 * et redirige vers le dossier public
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
logError("ROOT INDEX.PHP - Requête reçue: " . $_SERVER['REQUEST_URI']);

// Vérifier si la requête est déjà dans le dossier public
if (strpos($_SERVER['REQUEST_URI'], '/public/') === 0) {
    // Si c'est déjà dans public, ne pas rediriger
    logError("ROOT INDEX.PHP - Requête déjà dans public, pas de redirection");
    return false;
}

// Rediriger vers le dossier public
logError("ROOT INDEX.PHP - Redirection vers public/");
header('Location: /public/');
exit;
