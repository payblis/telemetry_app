<?php
/**
 * Page de déconnexion
 */

// Détruire la session
session_destroy();

// Supprimer le cookie de connexion automatique s'il existe
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Rediriger vers la page de connexion
setFlashMessage('success', 'Vous avez été déconnecté avec succès.');
redirect('index.php?page=login');
