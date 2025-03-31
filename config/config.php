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
    // Sinon, on s'assure qu'il y a un slash à la fin
    $base_path = $base_path . '/';
}

// URL de base pour les liens
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$base_path";

// Chemins pour les ressources
$css_path = $base_url . 'css/';
$js_path = $base_url . 'js/';
$images_path = $base_url . 'images/';

// Paramètres de connexion à la base de données
$db_host = 'localhost';
$db_name = 'test2';
$db_user = 'test2';
$db_pass = 'J31us30x%';

// Fonction pour obtenir la connexion à la base de données
function getDBConnection() {
    global $db_host, $db_name, $db_user, $db_pass;
    
    // Créer la connexion
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Vérifier la connexion
    if ($conn->connect_error) {
        die("Erreur de connexion à la base de données: " . $conn->connect_error);
    }
    
    // Définir l'encodage des caractères
    $conn->set_charset("utf8");
    
    return $conn;
}

// Fonction pour générer des URLs
function url($path = '') {
    global $base_url;
    return $base_url . ltrim($path, '/');
}
