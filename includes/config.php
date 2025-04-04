<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'test2');
define('DB_PASS', '6aR^70ug7');
define('DB_NAME', 'test2');

// Connexion à la base de données
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

// Définition du fuseau horaire
date_default_timezone_set('Europe/Paris');

// Démarrage de la session
session_start();
?> 