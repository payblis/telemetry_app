<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telemetry App</title>
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <nav class="main-nav">
                <a href="index.php" class="logo">Telemetry App</a>
                <ul class="nav-links">
                    <li><a href="index.php?route=motos">Motos</a></li>
                    <li><a href="index.php?route=sessions">Sessions</a></li>
                    <li><a href="index.php?route=pilotes">Pilotes</a></li>
                    <li><a href="index.php?route=circuits">Circuits</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <div class="container body">
            <div class="main_container">
                <!-- Sidebar -->
                <div class="col-md-3 left_col">
                    <div class="left_col scroll-view">
                        <div class="navbar nav_title" style="border: 0;">
                            <a href="index.php" class="site_title">
                                <i class="fa fa-motorcycle"></i> 
                                <span>Télémétrie IA</span>
                            </a>
                        </div>

                        <div class="clearfix"></div>

                        <!-- Menu profile quick info -->
                        <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="profile clearfix">
                            <div class="profile_pic">
                                <img src="images/user.png" alt="..." class="img-circle profile_img">
                            </div>
                            <div class="profile_info">
                                <span>Bienvenue,</span>
                                <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- /Menu profile quick info -->

                        <br />

                        <!-- Sidebar menu -->
                        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                            <div class="menu_section">
                                <h3>Général</h3>
                                <ul class="nav side-menu">
                                    <li>
                                        <a href="index.php?route=dashboard">
                                            <i class="fa fa-tachometer"></i> Dashboard
                                        </a>
                                    </li>
                                    <li>
                                        <a><i class="fa fa-users"></i> Pilotes <span class="fa fa-chevron-down"></span></a>
                                        <ul class="nav child_menu">
                                            <li><a href="index.php?route=pilotes">Liste des pilotes</a></li>
                                            <li><a href="index.php?route=pilote/new">Ajouter un pilote</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a><i class="fa fa-motorcycle"></i> Motos <span class="fa fa-chevron-down"></span></a>
                                        <ul class="nav child_menu">
                                            <li><a href="index.php?route=motos">Liste des motos</a></li>
                                            <li><a href="index.php?route=moto/new">Ajouter une moto</a></li>
                                            <li><a href="index.php?route=equipements">Équipements</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a><i class="fa fa-line-chart"></i> Télémétrie <span class="fa fa-chevron-down"></span></a>
                                        <ul class="nav child_menu">
                                            <li><a href="index.php?route=telemetrie/import">Importer des données</a></li>
                                            <li><a href="index.php?route=telemetrie/analyse">Analyse</a></li>
                                            <li><a href="index.php?route=telemetrie/historique">Historique</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a><i class="fa fa-wrench"></i> Réglages <span class="fa fa-chevron-down"></span></a>
                                        <ul class="nav child_menu">
                                            <li><a href="index.php?route=reglages/diagnostic">Diagnostic</a></li>
                                            <li><a href="index.php?route=reglages/interactif">Réglages interactifs</a></li>
                                        </ul>
                                    </li>
                                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'expert'): ?>
                                    <li>
                                        <a><i class="fa fa-brain"></i> IA & Expertise <span class="fa fa-chevron-down"></span></a>
                                        <ul class="nav child_menu">
                                            <li><a href="index.php?route=expertise/questions">Questions en attente</a></li>
                                            <li><a href="index.php?route=expertise/contributions">Mes contributions</a></li>
                                        </ul>
                                    </li>
                                    <?php endif; ?>
                                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <li>
                                        <a><i class="fa fa-cogs"></i> Administration <span class="fa fa-chevron-down"></span></a>
                                        <ul class="nav child_menu">
                                            <li><a href="index.php?route=admin/users">Utilisateurs</a></li>
                                            <li><a href="index.php?route=admin/api">Configuration API</a></li>
                                            <li><a href="index.php?route=admin/logs">Logs système</a></li>
                                        </ul>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <!-- /Sidebar menu -->
                    </div>
                </div>

                <!-- Top navigation -->
                <div class="top_nav">
                    <div class="nav_menu">
                        <div class="nav toggle">
                            <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                        </div>
                        <nav class="nav navbar-nav">
                            <ul class="navbar-right">
                                <?php if(isset($_SESSION['user_id'])): ?>
                                <li class="nav-item dropdown open">
                                    <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                        <img src="images/user.png" alt=""><?php echo htmlspecialchars($_SESSION['username']); ?>
                                        <span class="fa fa-angle-down"></span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-usermenu pull-right">
                                        <li><a href="index.php?route=profil"> Profile</a></li>
                                        <li><a href="index.php?route=telemetriste">Mon Télémétriste IA</a></li>
                                        <li><a href="index.php?route=logout"><i class="fa fa-sign-out pull-right"></i> Déconnexion</a></li>
                                    </ul>
                                </li>

                                <li role="presentation" class="nav-item dropdown open">
                                    <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-envelope-o"></i>
                                        <span class="badge bg-green">6</span>
                                    </a>
                                    <ul class="dropdown-menu list-unstyled msg_list" role="menu">
                                        <li class="nav-item">
                                            <a class="dropdown-item">
                                                <span class="image"><img src="images/user.png" alt="Profile Image" /></span>
                                                <span>
                                                    <span>Alerte Système</span>
                                                    <span class="time">3 mins</span>
                                                </span>
                                                <span class="message">
                                                    Température des pneus anormalement élevée détectée
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
                <!-- /Top navigation -->

                <!-- Page content -->
                <div class="right_col" role="main">
                    <?php if(isset($_SESSION['flash_message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['flash_message']; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html> 