<?php
// header.php
require_once 'config.php';
require_once 'auth.php';

checkAuth();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Télémétrie Moto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3f6ad8;
            --primary-light: #e8f0fe;
            --secondary: #6c757d;
            --success: #2e7d32;
            --danger: #d32f2f;
            --warning: #ed6c02;
            --info: #0288d1;
        }

        body {
            min-height: 100vh;
            display: flex;
            background-color: #f5f7fb;
        }

        .sidebar {
            width: 260px;
            background-color: #fff;
            box-shadow: 0 0 35px 0 rgba(154, 161, 171, 0.15);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1000;
            padding-top: 20px;
        }

        .main-content {
            flex: 1;
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-bar {
            background-color: #fff;
            padding: 1rem;
            box-shadow: 0 0 35px 0 rgba(154, 161, 171, 0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .content-wrapper {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }

        .card {
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0 35px 0 rgba(154, 161, 171, 0.15);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body {
            padding: 1.5rem;
        }

        .nav-link {
            color: #6c757d;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary);
            background-color: var(--primary-light);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-menu .dropdown-toggle::after {
            display: none;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            color: #6c757d;
            border-top: none;
        }

        .alert {
            border: none;
            border-radius: 0.5rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="px-3 mb-4">
            <h4 class="text-primary">Télémétrie Moto</h4>
        </div>
        <nav>
            <a href="../index.php" class="nav-link">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
            <a href="../pilotes/index.php" class="nav-link">
                <i class="fas fa-user"></i>
                Pilotes
            </a>
            <a href="../motos/index.php" class="nav-link">
                <i class="fas fa-motorcycle"></i>
                Motos
            </a>
            <a href="../circuits/index.php" class="nav-link">
                <i class="fas fa-route"></i>
                Circuits
            </a>
            <a href="../sessions/index.php" class="nav-link">
                <i class="fas fa-clock"></i>
                Sessions
            </a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="../admin/index.php" class="nav-link">
                <i class="fas fa-cog"></i>
                Administration
            </a>
            <?php endif; ?>
        </nav>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo $page_title ?? 'Dashboard'; ?></h5>
                <div class="user-menu">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatar">
                                <?php echo strtoupper(substr($_SESSION['email'], 0, 1)); ?>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="../profile.php">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Déconnexion</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-wrapper">
            <!-- Le contenu de la page sera inséré ici -->
        </div>
    </div>
</body>
</html>