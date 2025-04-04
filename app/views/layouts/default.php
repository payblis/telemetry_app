<?php
/**
 * Layout principal de l'application
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - ' : '' ?>Télémétrie Moto SaaS</title>
    <link rel="stylesheet" href="<?= \App\Utils\View::asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= \App\Utils\View::asset('css/auth.css') ?>">
    <link rel="stylesheet" href="<?= \App\Utils\View::asset('css/dashboard.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Navigation principale pour utilisateurs connectés -->
            <nav class="main-nav">
                <div class="nav-brand">
                    <a href="<?= \App\Utils\View::url('/dashboard') ?>">
                        <span class="brand-icon"><i class="fas fa-motorcycle"></i></span>
                        <span class="brand-name">Télémétrie Moto</span>
                    </a>
                </div>
                
                <ul class="nav-menu">
                    <li><a href="<?= \App\Utils\View::url('/dashboard') ?>"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                    <li><a href="<?= \App\Utils\View::url('/pilotes') ?>"><i class="fas fa-user-astronaut"></i> Pilotes</a></li>
                    <li><a href="<?= \App\Utils\View::url('/motos') ?>"><i class="fas fa-motorcycle"></i> Motos</a></li>
                    <li><a href="<?= \App\Utils\View::url('/circuits') ?>"><i class="fas fa-road"></i> Circuits</a></li>
                    <li><a href="<?= \App\Utils\View::url('/sessions') ?>"><i class="fas fa-stopwatch"></i> Sessions</a></li>
                    <li><a href="<?= \App\Utils\View::url('/telemetrie') ?>"><i class="fas fa-chart-line"></i> Télémétrie</a></li>
                    <li><a href="<?= \App\Utils\View::url('/analyses') ?>"><i class="fas fa-chart-bar"></i> Analyses</a></li>
                    <li><a href="<?= \App\Utils\View::url('/recommandations') ?>"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
                </ul>
                
                <div class="nav-user">
                    <div class="user-dropdown">
                        <button class="user-dropdown-btn">
                            <i class="fas fa-user-circle"></i>
                            <span><?= \App\Utils\View::escape($_SESSION['username']) ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown-content">
                            <a href="<?= \App\Utils\View::url('/profile') ?>"><i class="fas fa-user"></i> Profil</a>
                            <a href="<?= \App\Utils\View::url('/preferences') ?>"><i class="fas fa-cog"></i> Préférences</a>
                            <a href="<?= \App\Utils\View::url('/logout') ?>"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                        </div>
                    </div>
                </div>
            </nav>
        <?php else: ?>
            <!-- Navigation simplifiée pour visiteurs -->
            <nav class="main-nav visitor-nav">
                <div class="nav-brand">
                    <a href="<?= \App\Utils\View::url('/') ?>">
                        <span class="brand-icon"><i class="fas fa-motorcycle"></i></span>
                        <span class="brand-name">Télémétrie Moto</span>
                    </a>
                </div>
                
                <div class="nav-auth">
                    <a href="<?= \App\Utils\View::url('/login') ?>" class="btn btn-outline">Connexion</a>
                    <a href="<?= \App\Utils\View::url('/register') ?>" class="btn btn-primary">Inscription</a>
                </div>
            </nav>
        <?php endif; ?>
        
        <main class="main-content">
            <?= $content ?>
        </main>
        
        <footer class="main-footer">
            <div class="footer-content">
                <div class="footer-copyright">
                    &copy; <?= date('Y') ?> Télémétrie Moto SaaS. Tous droits réservés.
                </div>
                <div class="footer-links">
                    <a href="<?= \App\Utils\View::url('/about') ?>">À propos</a>
                    <a href="<?= \App\Utils\View::url('/contact') ?>">Contact</a>
                    <a href="<?= \App\Utils\View::url('/privacy') ?>">Confidentialité</a>
                    <a href="<?= \App\Utils\View::url('/terms') ?>">Conditions d'utilisation</a>
                </div>
            </div>
        </footer>
    </div>
    
    <script src="<?= \App\Utils\View::asset('js/main.js') ?>"></script>
</body>
</html>
