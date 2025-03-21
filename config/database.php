<?php
require_once __DIR__ . '/environment.php';

// Configuration de la base de données selon l'environnement
if (ENVIRONMENT === 'development') {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'db_tm_7845');
    define('DB_USER', 'db_tm_7845_us');
    define('DB_PASS', '6*N76k2ef');
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'db_tm_7845');
    define('DB_USER', 'db_tm_7845_us');
    define('DB_PASS', '6*N76k2ef');
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        )
    );
} catch(PDOException $e) {
    if (ENVIRONMENT === 'development') {
        die("Erreur de connexion : " . $e->getMessage());
    } else {
        die("Une erreur est survenue lors de la connexion à la base de données.");
    }
}
?> 