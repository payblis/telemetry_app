<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Télémétrie Moto</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/racing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/chart.min.js"></script>
</head>
<body>
    <div class="racing-stripe"></div>
    
    <?php if (isLoggedIn()): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-motorcycle"></i> <?= APP_NAME ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'sessions' ? 'active' : '' ?>" href="index.php?page=sessions">
                            <i class="fas fa-stopwatch"></i> Sessions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'pilotes' ? 'active' : '' ?>" href="index.php?page=pilotes">
                            <i class="fas fa-user-astronaut"></i> Pilotes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'motos' ? 'active' : '' ?>" href="index.php?page=motos">
                            <i class="fas fa-motorcycle"></i> Motos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'circuits' ? 'active' : '' ?>" href="index.php?page=circuits">
                            <i class="fas fa-road"></i> Circuits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'analyses' ? 'active' : '' ?>" href="index.php?page=analyses">
                            <i class="fas fa-chart-line"></i> Analyses
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= getCurrentUserName() ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="index.php?page=profile">
                                    <i class="fas fa-user-cog"></i> Mon profil
                                </a>
                            </li>
                            <?php if (isAdmin()): ?>
                            <li>
                                <a class="dropdown-item" href="index.php?page=admin">
                                    <i class="fas fa-tools"></i> Administration
                                </a>
                            </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="index.php?page=logout">
                                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <div class="container mt-4">
        <?php
        // Afficher les messages flash
        $flash = getFlashMessage();
        if ($flash) {
            $alertClass = 'alert-info';
            if ($flash['type'] === 'success') {
                $alertClass = 'alert-success';
            } elseif ($flash['type'] === 'error') {
                $alertClass = 'alert-danger';
            } elseif ($flash['type'] === 'warning') {
                $alertClass = 'alert-warning';
            }
            
            echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
            echo $flash['message'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>';
            echo '</div>';
        }
        ?>
