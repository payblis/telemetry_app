<?php
/**
 * Configuration de la base de données
 */

// Paramètres de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'telemoto');
define('DB_USER', 'telemoto_user');
define('DB_PASS', 'telemoto_password');
define('DB_CHARSET', 'utf8mb4');

// Options PDO
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
