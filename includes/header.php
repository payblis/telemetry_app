<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Application de télémétrie moto</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <script src="<?php echo BASE_URL; ?>/assets/js/script.js" defer></script>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <h1><?php echo APP_NAME; ?></h1>
            </div>
            
            <?php if (isLoggedIn()): ?>
            <nav>
                <ul class="main-menu">
                    <li><a href="<?php echo BASE_URL; ?>/index.php?page=dashboard">Tableau de bord</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/index.php?page=pilots">Pilotes</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/index.php?page=motos">Motos</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/index.php?page=circuits">Circuits</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/index.php?page=sessions">Sessions</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/index.php?page=ai_chat">Assistant IA</a></li>
                    <?php if (isAdmin()): ?>
                    <li><a href="<?php echo BASE_URL; ?>/index.php?page=users">Utilisateurs</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="user-menu">
                <span class="username"><?php echo $_SESSION['username']; ?></span>
                <div class="dropdown">
                    <button class="dropdown-toggle">Menu</button>
                    <div class="dropdown-menu">
                        <a href="<?php echo BASE_URL; ?>/index.php?page=profile">Mon profil</a>
                        <a href="<?php echo BASE_URL; ?>/index.php?page=logout">Déconnexion</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </header>
    
    <main>
        <div class="container">
            <?php echo displayAlert(); ?>
