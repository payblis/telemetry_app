<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';

// Démarrer la session
session_start();

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil
header('Location: ' . url());
exit;
?>
