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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Telemetry App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #ff3d00;
            --primary-dark: #c30000;
            --secondary: #1a1a1a;
            --dark: #121212;
            --darker: #0a0a0a;
            --light: #ffffff;
            --gray: #2a2a2a;
            --success: #00c853;
            --warning: #ffd600;
            --danger: #d50000;
        }

        body {
            background-color: var(--dark);
            color: var(--light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar Styles */
        .sidebar {
            background-color: var(--darker);
            border-right: 1px solid var(--gray);
            height: 100vh;
            position: fixed;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--gray);
        }

        .sidebar-header h3 {
            color: var(--primary);
            font-weight: bold;
            margin: 0;
        }

        .nav-link {
            color: var(--light);
            padding: 12px 20px;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .nav-link:hover, .nav-link.active {
            background-color: var(--gray);
            color: var(--primary);
            border-left: 3px solid var(--primary);
        }

        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }

        /* Card Styles */
        .card {
            background-color: var(--secondary);
            border: 1px solid var(--gray);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: var(--darker);
            border-bottom: 1px solid var(--gray);
            padding: 15px 20px;
        }

        .card-title {
            color: var(--primary);
            margin: 0;
            font-weight: bold;
        }

        .card-body {
            padding: 20px;
        }

        /* Form Styles */
        .form-control, .form-select {
            background-color: var(--darker);
            border: 1px solid var(--gray);
            color: var(--light);
            padding: 10px 15px;
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--darker);
            border-color: var(--primary);
            color: var(--light);
            box-shadow: 0 0 0 0.25rem rgba(255, 61, 0, 0.25);
        }

        .form-label {
            color: var(--light);
            font-weight: 500;
        }

        /* Button Styles */
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: var(--gray);
            border-color: var(--gray);
        }

        .btn-secondary:hover {
            background-color: var(--darker);
            border-color: var(--darker);
        }

        .btn-danger {
            background-color: var(--danger);
            border-color: var(--danger);
        }

        .btn-danger:hover {
            background-color: #b71c1c;
            border-color: #b71c1c;
        }

        /* Table Styles */
        .table {
            color: var(--light);
        }

        .table thead th {
            background-color: var(--darker);
            border-bottom: 2px solid var(--gray);
            color: var(--primary);
        }

        .table tbody td {
            border-bottom: 1px solid var(--gray);
            vertical-align: middle;
        }

        /* Avatar Styles */
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            background-color: var(--primary);
            color: var(--light);
        }

        /* Badge Styles */
        .badge {
            padding: 6px 10px;
            border-radius: 4px;
            font-weight: 500;
        }

        .badge-beginner { background-color: #4caf50; }
        .badge-intermediate { background-color: #2196f3; }
        .badge-advanced { background-color: #9c27b0; }
        .badge-expert { background-color: #ff9800; }
        .badge-professional { background-color: #f44336; }

        /* Alert Styles */
        .alert {
            border: none;
            border-radius: 4px;
            padding: 15px;
        }

        .alert-warning {
            background-color: rgba(255, 214, 0, 0.1);
            color: var(--warning);
            border-left: 4px solid var(--warning);
        }

        .alert-danger {
            background-color: rgba(213, 0, 0, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        /* Stats Card Styles */
        .stats-card {
            background-color: var(--darker);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--gray);
        }

        .stats-card h3 {
            color: var(--primary);
            font-size: 24px;
            margin-bottom: 10px;
        }

        .stats-card p {
            color: var(--light);
            margin: 0;
            font-size: 14px;
        }

        /* Responsive Design */
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
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>Telemetry App</h3>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="../dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../circuits/list.php">
                        <i class="fas fa-route"></i>
                        Circuits
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="../pilotes/list.php">
                        <i class="fas fa-user"></i>
                        Pilots
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../motos/list.php">
                        <i class="fas fa-motorcycle"></i>
                        Bikes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../sessions/list.php">
                        <i class="fas fa-clock"></i>
                        Sessions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../statistiques.php">
                        <i class="fas fa-chart-bar"></i>
                        Statistics
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0"><?php echo $page_title; ?></h1>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../profile.php">Profile</a></li>
                        <li><a class="dropdown-item" href="../settings.php">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
            <div class="content-wrapper">
                <!-- Le contenu de la page sera inséré ici -->
            </div>
        </div>
    </div>
</body>
</html>