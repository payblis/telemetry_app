<?php
/**
 * Fichier index.php à la racine du projet
 * 
 * Ce fichier redirige toutes les requêtes vers le dossier public
 * pour une meilleure sécurité de l'application
 */

// Redirection vers le dossier public
header('Location: public/');
exit;