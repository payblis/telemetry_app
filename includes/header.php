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
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            padding: 20px;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 10px;
            margin-bottom: 5px;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <h4 class="text-white mb-4">Telemetry App</h4>
                <a href="../dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="../circuits/list.php">
                    <i class="fas fa-route"></i> Circuits
                </a>
                <a href="../pilotes/list.php">
                    <i class="fas fa-user"></i> Pilots
                </a>
                <a href="../motos/list.php">
                    <i class="fas fa-motorcycle"></i> Bikes
                </a>
                <a href="../sessions/list.php">
                    <i class="fas fa-clock"></i> Sessions
                </a>
                <a href="../statistiques.php">
                    <i class="fas fa-chart-bar"></i> Statistics
                </a>
                <a href="../logout.php" class="mt-4">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            <div class="col-md-10 main-content">
                <div class="content-wrapper">
                    <!-- Le contenu de la page sera inséré ici -->
                </div>
            </div>
        </div>
    </div>
</body>
</html>