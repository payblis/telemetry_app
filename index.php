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

// Redirection vers le dossier public
error_log("ROOT INDEX.PHP - Redirection vers public/");
header('Location: public/');
exit;
