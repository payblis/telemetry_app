<?php
// header.php
require_once 'config.php';
require_once 'auth.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Telemetry App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #ff3d00;
            --primary-light: rgba(255, 61, 0, 0.1);
            --secondary: #00bcd4;
            --success: #4caf50;
            --danger: #f44336;
            --warning: #ff9800;
            --info: #2196f3;
            --dark: #121212;
            --darker: #0a0a0a;
            --light: #f8f9fa;
            --gray: #2d2d2d;
            --border: #404040;
        }

        body {
            background-color: var(--dark);
            color: var(--light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background-color: var(--darker);
            border-right: 1px solid var(--border);
            width: 250px;
            position: fixed;
            height: 100vh;
            padding: 1rem;
            z-index: 1000;
        }

        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-bar {
            background-color: var(--darker);
            border-bottom: 1px solid var(--border);
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .content-wrapper {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .card {
            background-color: var(--gray);
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: var(--darker);
            border-bottom: 1px solid var(--border);
            padding: 1rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .nav-link {
            color: var(--light);
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: #ff5722;
            border-color: #ff5722;
        }

        .btn-secondary {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }

        .btn-secondary:hover {
            background-color: #00acc1;
            border-color: #00acc1;
        }

        .form-control {
            background-color: var(--darker);
            border: 1px solid var(--border);
            color: var(--light);
        }

        .form-control:focus {
            background-color: var(--darker);
            border-color: var(--primary);
            color: var(--light);
            box-shadow: 0 0 0 0.2rem rgba(255, 61, 0, 0.25);
        }

        .table {
            color: var(--light);
        }

        .table thead th {
            background-color: var(--darker);
            border-bottom: 2px solid var(--border);
        }

        .table td, .table th {
            border-color: var(--border);
        }

        .badge {
            padding: 0.5em 0.75em;
            font-weight: 600;
        }

        .avatar-circle {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .user-menu {
            background-color: var(--darker);
            border: 1px solid var(--border);
        }

        .user-menu .dropdown-item {
            color: var(--light);
        }

        .user-menu .dropdown-item:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .alert {
            border: none;
            border-radius: 6px;
        }

        .alert-danger {
            background-color: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }

        /* Animation pour les éléments interactifs */
        .btn, .nav-link, .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn:hover, .nav-link:hover, .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        /* Style pour les badges de niveau */
        .badge-danger { background-color: #f44336; }
        .badge-warning { background-color: #ff9800; }
        .badge-info { background-color: #2196f3; }
        .badge-success { background-color: #4caf50; }
        .badge-secondary { background-color: #757575; }

        /* Style pour les avatars */
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .avatar-initials {
            color: var(--primary);
        }

        /* Responsive design */
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
        <div class="d-flex flex-column h-100">
            <div class="mb-4">
                <h4 class="text-primary mb-0">Telemetry App</h4>
                <small class="text-muted">Racing Analytics</small>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a class="nav-link" href="../pilotes/list.php">
                    <i class="fas fa-user"></i>
                    Pilotes
                </a>
                <a class="nav-link" href="../motos/list.php">
                    <i class="fas fa-motorcycle"></i>
                    Motos
                </a>
                <a class="nav-link" href="../circuits/list.php">
                    <i class="fas fa-route"></i>
                    Circuits
                </a>
                <a class="nav-link" href="../sessions/list.php">
                    <i class="fas fa-chart-line"></i>
                    Sessions
                </a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a class="nav-link" href="../admin/index.php">
                    <i class="fas fa-cog"></i>
                    Administration
                </a>
                <?php endif; ?>
            </nav>
            <div class="mt-auto">
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" id="userMenu" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['email']); ?>
                    </a>
                    <ul class="dropdown-menu user-menu" aria-labelledby="userMenu">
                        <li><a class="dropdown-item" href="../profile.php">Profil</a></li>
                        <li><a class="dropdown-item" href="../logout.php">Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><?php echo $page_title; ?></h4>
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary me-3">
                        <i class="fas fa-bolt"></i>
                        Racing Mode
                    </span>
                    <div class="dropdown">
                        <button class="btn btn-link text-light" type="button" id="notifications" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger">3</span>
                        </button>
                        <ul class="dropdown-menu user-menu" aria-labelledby="notifications">
                            <li><a class="dropdown-item" href="#">Nouvelle session enregistrée</a></li>
                            <li><a class="dropdown-item" href="#">Analyse de performance disponible</a></li>
                            <li><a class="dropdown-item" href="#">Mise à jour du circuit</a></li>
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