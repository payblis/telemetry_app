<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_tm_7845');
define('DB_USER', 'db_tm_7845_us');
define('DB_PASS', '6*N76k2ef');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?> 