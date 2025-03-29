<?php
/**
 * Fichier d'index principal
 * Point d'entrée de l'application
 */

// Inclure la configuration
require_once 'config/config.php';

// Inclure les fonctions utilitaires
require_once 'includes/functions.php';

// Vérifier l'authentification
require_once 'includes/auth.php';

// Déterminer la page à afficher
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn() && $page != 'login' && $page != 'register') {
    // Rediriger vers la page de connexion
    header('Location: ' . BASE_URL . '/index.php?page=login');
    exit;
}

// Inclure l'en-tête
include_once 'includes/header.php';

// Charger la page demandée
switch ($page) {
    case 'login':
        include_once 'pages/login.php';
        break;
    case 'register':
        include_once 'pages/register.php';
        break;
    case 'dashboard':
        include_once 'pages/dashboard.php';
        break;
    case 'pilots':
        include_once 'pages/pilots/index.php';
        break;
    case 'pilot_add':
        include_once 'pages/pilots/add.php';
        break;
    case 'pilot_edit':
        include_once 'pages/pilots/edit.php';
        break;
    case 'pilot_view':
        include_once 'pages/pilots/view.php';
        break;
    case 'motos':
        include_once 'pages/motos/index.php';
        break;
    case 'moto_add':
        include_once 'pages/motos/add.php';
        break;
    case 'moto_edit':
        include_once 'pages/motos/edit.php';
        break;
    case 'moto_view':
        include_once 'pages/motos/view.php';
        break;
    case 'circuits':
        include_once 'pages/circuits/index.php';
        break;
    case 'circuit_add':
        include_once 'pages/circuits/add.php';
        break;
    case 'circuit_edit':
        include_once 'pages/circuits/edit.php';
        break;
    case 'circuit_view':
        include_once 'pages/circuits/view.php';
        break;
    case 'sessions':
        include_once 'pages/sessions/index.php';
        break;
    case 'session_add':
        include_once 'pages/sessions/add.php';
        break;
    case 'session_edit':
        include_once 'pages/sessions/edit.php';
        break;
    case 'session_view':
        include_once 'pages/sessions/view.php';
        break;
    case 'ai_chat':
        include_once 'pages/ai/chat.php';
        break;
    case 'ai_feedbacks':
        include_once 'pages/ai/feedbacks.php';
        break;
    case 'profile':
        include_once 'pages/profile.php';
        break;
    case 'logout':
        include_once 'pages/logout.php';
        break;
    default:
        include_once 'pages/404.php';
        break;
}

// Inclure le pied de page
include_once 'includes/footer.php';
