<?php
/**
 * Configuration de la base de données et des paramètres globaux
 */

// Informations de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'telemetrie_moto');
define('DB_USER', 'root');
define('DB_PASS', '');

// Chemin de base de l'application
define('BASE_PATH', dirname(__FILE__));
define('UPLOADS_PATH', BASE_PATH . '/uploads');

// URL de base de l'application (à modifier selon votre configuration)
define('BASE_URL', 'http://localhost/telemetrie_moto_simple');

// Configuration de l'application
define('APP_NAME', 'Télémétrie Moto');
define('APP_VERSION', '1.0.0');
define('DEBUG_MODE', true);

// Clé API OpenAI (à remplacer par votre propre clé)
define('OPENAI_API_KEY', 'votre_cle_api_openai');

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Fonction pour se connecter à la base de données
function connectDB() {
    try {
        $db = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $db;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die('Erreur de connexion à la base de données: ' . $e->getMessage());
        } else {
            die('Erreur de connexion à la base de données. Veuillez contacter l\'administrateur.');
        }
    }
}

// Fonction pour échapper les sorties HTML
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Fonction pour rediriger
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Fonction pour afficher un message flash
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Fonction pour récupérer et effacer un message flash
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier si l'utilisateur est admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Fonction pour obtenir l'ID de l'utilisateur connecté
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Fonction pour obtenir le rôle de l'utilisateur connecté
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

// Fonction pour obtenir le nom complet de l'utilisateur connecté
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'Invité';
}

// Fonction pour charger une page
function loadPage($page) {
    $file = BASE_PATH . '/pages/' . $page . '.php';
    if (file_exists($file)) {
        require_once $file;
    } else {
        require_once BASE_PATH . '/pages/404.php';
    }
}
