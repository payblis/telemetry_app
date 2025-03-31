<?php
// Inclure le fichier de configuration
require_once __DIR__ . '/../config/config.php';

// Inclure les fonctions d'authentification
require_once __DIR__ . '/../auth/auth_functions.php';

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeleMoto - Application d'Assistance Technique Moto Racing</title>
    <link rel="stylesheet" href="<?php echo $css_path; ?>style.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>racing-elements.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="<?php echo url(); ?>">
                    <span class="logo-text">TeleMoto</span>
                </a>
            </div>
            <nav>
                <ul class="main-nav">
                    <li><a href="<?php echo url(); ?>">Accueil</a></li>
                    <li><a href="<?php echo url('pilotes/'); ?>">Pilotes</a></li>
                    <li><a href="<?php echo url('motos/'); ?>">Motos</a></li>
                    <li><a href="<?php echo url('circuits/'); ?>">Circuits</a></li>
                    <li><a href="<?php echo url('sessions/'); ?>">Sessions</a></li>
                    <li><a href="<?php echo url('chatgpt/'); ?>">ChatGPT</a></li>
                    <li><a href="<?php echo url('experts/'); ?>">Experts</a></li>
                    <?php if (isAdmin()): ?>
                    <li><a href="<?php echo url('admin/'); ?>" class="admin-link"><i class="fas fa-cog"></i> Admin</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo url('mecanique/'); ?>"><i class="fas fa-wrench"></i> Assistance</a></li>
                </ul>
            </nav>
            <?php echo getUserMenu(); ?>
        </div>
    </header>
    
    <main>
        <div class="container">
