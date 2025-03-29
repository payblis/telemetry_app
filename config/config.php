<?php
// Fichier de configuration central pour l'application TeleMoto
// Définir le chemin de base de l'application

// URL de base pour les liens (racine du domaine)
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";

// Chemins pour les ressources
$css_path = $base_url . 'css/';
$js_path = $base_url . 'js/';
$images_path = $base_url . 'images/';

// Inclure la configuration de la base de données
require_once __DIR__ . '/database.php';

// Fonction pour générer des URLs
function url($path = '') {
    global $base_url;
    return $base_url . ltrim($path, '/');
}
