<?php
/**
 * Vue pour le layout principal avec le thème racing
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - ' : '' ?>Télémétrie Moto SaaS</title>
    
    <!-- Polices -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&family=Roboto+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles CSS -->
    <link rel="stylesheet" href="<?= \App\Utils\View::asset('/assets/css/racing.css') ?>">
    <link rel="stylesheet" href="<?= \App\Utils\View::asset('/assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= \App\Utils\View::asset('/assets/css/dashboard.css') ?>">
    
    <!-- Scripts JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <a href="<?= \App\Utils\View::url('/') ?>" class="sidebar-brand">
                    <img src="<?= \App\Utils\View::asset('/assets/img/logo.png') ?>" alt="Logo">
                    <span>TéléMoto</span>
                </a>
            </div>
            
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="<?= \App\Utils\View::url('/dashboard') ?>" class="sidebar-link <?= \App\Utils\View::isActive('/dashboard') ?>">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?= \App\Utils\View::url('/telemetrie') ?>" class="sidebar-link <?= \App\Utils\View::isActive('/telemetrie') ?>">
                        <i class="fas fa-chart-line"></i> Télémétrie
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?= \App\Utils\View::url('/pilotes') ?>" class="sidebar-link <?= \App\Utils\View::isActive('/pilotes') ?>">
                        <i class="fas fa-user-astronaut"></i> Pilotes
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?= \App\Utils\View::url('/motos') ?>" class="sidebar-link <?= \App\Utils\View::isActive('/motos') ?>">
                        <i class="fas fa-motorcycle"></i> Motos
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?= \App\Utils\View::url('/circuits') ?>" class="sidebar-link <?= \App\Utils\View::isActive('/circuits') ?>">
                        <i class="fas fa-road"></i> Circuits
                    </a>
                </li>
                
                <div class="sidebar-divider"></div>
                
                <li class="sidebar-item">
                    <a href="<?= \App\Utils\View::url('/analyses') ?>" class="sidebar-link <?= \App\Utils\View::isActive('/analyses') ?>">
                        <i class="fas fa-brain"></i> Analyses IA
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?= \App\Utils\View::url('/communaute') ?>" class="sidebar-link <?= \App\Utils\View::isActive('/communaute') ?>">
                        <i class="fas fa-users"></i> Communauté
                    </a>
                </li>
                
                <div class="sidebar-divider"></div>
                
                <li class="sidebar-item">
                    <a href="<?= \App\Utils\View::url('/profil') ?>" class="sidebar-link <?= \App\Utils\View::isActive('/profil') ?>">
                        <i class="fas fa-user-circle"></i> Mon Profil
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?= \App\Utils\View::url('/parametres') ?>" class="sidebar-link <?= \App\Utils\View::isActive('/parametres') ?>">
                        <i class="fas fa-cog"></i> Paramètres
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?= \App\Utils\View::url('/auth/logout') ?>" class="sidebar-link">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Contenu principal -->
            <?php require_once $viewPath; ?>
        </div>
    </div>
    
    <!-- Scripts JS -->
    <script src="<?= \App\Utils\View::asset('/assets/js/main.js') ?>"></script>
</body>
</html>
