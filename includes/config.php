<?php
// config.php

define('DB_HOST', 'localhost');
define('DB_USER', 'test2');
define('DB_PASS', 'm4u*l2L00');
define('DB_NAME', 'test2');

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

session_start();
?>