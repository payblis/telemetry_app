<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/auth.php';

checkAuth();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Télémétrie Moto</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Bienvenue sur l'application de Télémétrie Moto</h1>
        
        <div class="menu">
            <h2>Menu principal</h2>
            <ul>
                <li><a href="pilotes/">Gestion des pilotes</a></li>
                <li><a href="motos/">Gestion des motos</a></li>
                <li><a href="circuits/">Gestion des circuits</a></li>
                <li><a href="sessions/">Gestion des sessions</a></li>
                <?php if (isAdmin() || isExpert()): ?>
                <li><a href="admin/">Administration</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <p>
            <a href="logout.php">Se déconnecter</a>
        </p>
    </div>

    <style>
        .menu {
            margin: 2rem 0;
        }
        
        .menu h2 {
            color: #555;
            margin-bottom: 1rem;
        }
        
        .menu ul {
            list-style: none;
            padding: 0;
        }
        
        .menu li {
            margin-bottom: 1rem;
        }
        
        .menu a {
            display: block;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .menu a:hover {
            background-color: #e9ecef;
            text-decoration: none;
        }
    </style>
</body>
</html>