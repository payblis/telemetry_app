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
    <title>Télémétrie Moto - Back Office</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary: #3f6ad8;
            --secondary: #6c757d;
            --success: #3ac47d;
            --info: #16aaff;
            --warning: #f7b924;
            --danger: #d92550;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f4f6;
            margin: 0;
            padding: 0;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #fff;
            box-shadow: 0 0 1rem rgba(0,0,0,.15);
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,.1);
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .sidebar-menu-item {
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
            color: #6c757d;
            text-decoration: none;
            transition: all .2s;
        }

        .sidebar-menu-item:hover {
            background: rgba(63,106,216,.1);
            color: var(--primary);
        }

        .sidebar-menu-item i {
            margin-right: 1rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 1.5rem;
        }

        /* Top Bar */
        .top-bar {
            background: #fff;
            padding: 1rem 1.5rem;
            border-radius: .5rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 1rem rgba(0,0,0,.15);
        }

        .page-title {
            margin: 0;
            font-size: 1.5rem;
            color: var(--dark);
        }

        .user-menu {
            display: flex;
            align-items: center;
        }

        .user-menu img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 1rem;
        }

        /* Cards */
        .card {
            background: #fff;
            border-radius: .5rem;
            box-shadow: 0 0 1rem rgba(0,0,0,.15);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-title {
            margin: 0;
            font-size: 1.25rem;
            color: var(--dark);
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Télémétrie Moto</h2>
            </div>
            <nav class="sidebar-menu">
                <a href="../index.php" class="sidebar-menu-item">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a href="../pilotes/" class="sidebar-menu-item">
                    <i class="fas fa-user"></i>
                    Pilotes
                </a>
                <a href="../motos/" class="sidebar-menu-item">
                    <i class="fas fa-motorcycle"></i>
                    Motos
                </a>
                <a href="../circuits/" class="sidebar-menu-item">
                    <i class="fas fa-route"></i>
                    Circuits
                </a>
                <a href="../sessions/" class="sidebar-menu-item">
                    <i class="fas fa-clock"></i>
                    Sessions
                </a>
                <?php if (isAdmin() || isExpert()): ?>
                <a href="../admin/" class="sidebar-menu-item">
                    <i class="fas fa-cog"></i>
                    Administration
                </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1 class="page-title"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                <div class="user-menu">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_email']); ?>" alt="Avatar">
                    <div>
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
                        <a href="../logout.php" class="logout-link">Déconnexion</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>