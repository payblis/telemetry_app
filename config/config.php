<?php
// Fichier de configuration central pour l'application TeleMoto
// Définir le chemin de base de l'application

// Détection automatique du chemin de base
$script_name = $_SERVER['SCRIPT_NAME'];
$script_path = dirname($script_name);
$base_path = rtrim(str_replace('\\', '/', $script_path), '/');

// Si l'application est à la racine, $base_path sera vide, donc on met '/'
if (empty($base_path)) {
    $base_path = '/';
} else {
    // Sinon, on remonte à la racine de l'application
    $base_path = dirname($base_path);
    $base_path = rtrim($base_path, '/');
    // Si le chemin contient /telemoto/, on le retire
    if (strpos($base_path, '/telemoto') !== false) {
        $base_path = str_replace('/telemoto', '', $base_path);
    }
    $base_path = $base_path ? $base_path . '/' : '/';
}

// URL de base pour les liens
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$base_path";

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
