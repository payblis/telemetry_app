<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'test2');
define('DB_PASS', 'y6q?9tJ30');
define('DB_NAME', 'test2');

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    die();
}
?> 