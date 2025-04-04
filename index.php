<?php
/**
 * Page d'index principale de l'application simplifiée de télémétrie moto
 */

// Démarrer la session
session_start();

// Inclure les fichiers nécessaires
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Définir la page par défaut
$page = $_GET['page'] ?? 'dashboard';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn() && $page != 'login' && $page != 'register' && $page != 'forgot_password') {
    // Rediriger vers la page de connexion
    $page = 'login';
}

// En-tête HTML
include 'includes/header.php';

// Charger la page demandée
$file = 'pages/' . $page . '.php';
if (file_exists($file)) {
    include $file;
} else {
    include 'pages/404.php';
}

// Pied de page HTML
include 'includes/footer.php';
