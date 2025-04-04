<?php
/**
 * Fichier index.php à la racine du projet
 * 
 * Ce fichier redirige toutes les requêtes vers le dossier public
 * pour une meilleure sécurité de l'application
 */

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log de la requête
error_log("ROOT INDEX.PHP - Requête reçue: " . $_SERVER['REQUEST_URI']);

// Vérifier si c'est une requête pour les assets
if (strpos($_SERVER['REQUEST_URI'], '/assets/') === 0) {
    error_log("ROOT INDEX.PHP - Requête d'asset détectée");
    // Laisser Apache gérer la requête
    return false;
}

// Vérifier si nous ne sommes pas déjà dans le dossier public
if (strpos($_SERVER['REQUEST_URI'], '/public/') === false) {
    error_log("ROOT INDEX.PHP - Redirection vers public/");
    header('Location: /public/');
    exit;
} else {
    error_log("ROOT INDEX.PHP - Déjà dans public/, redirection vers la racine");
    header('Location: /');
    exit;
}
