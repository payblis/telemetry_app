<?php
/**
 * Fichier de configuration de la base de données
 */

// Paramètres de connexion à la base de données
$db_host = 'localhost';
$db_name = 'test2';
$db_user = 'test2';
$db_pass = 'Ei58~99wt';

/**
 * Fonction pour établir la connexion à la base de données
 * @return mysqli La connexion à la base de données
 */
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