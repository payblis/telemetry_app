<?php
// Fichier de configuration central pour l'application TeleMoto
// Définir le chemin de base de l'application

// Obtenir le chemin physique du dossier racine de l'application
$root_path = dirname(__DIR__);
$root_path = str_replace('\\', '/', $root_path);

// Obtenir le chemin du script actuel
$script_path = $_SERVER['SCRIPT_NAME'];
$script_path = str_replace('\\', '/', $script_path);

// Calculer le chemin de base relatif à la racine du serveur web
$base_path = dirname($script_path);
$base_path = str_replace('\\', '/', $base_path);

// Si le chemin de base est vide ou est '.', utiliser '/'
if (empty($base_path) || $base_path === '.') {
    $base_path = '/';
} else {
    // S'assurer que le chemin commence et se termine par un slash
    $base_path = '/' . trim($base_path, '/') . '/';
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
