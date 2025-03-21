<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier l'authentification
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Récupérer la liste des motos
$stmt = $pdo->query("SELECT * FROM motos ORDER BY marque, modele");
$motos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motos - TéléMoto AI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo">TéléMoto AI</div>
                <ul>
                    <li><a href="index.php">Tableau de bord</a></li>
                    <li><a href="sessions.php">Sessions</a></li>
                    <li><a href="pilotes.php">Pilotes</a></li>
                    <li><a href="motos.php">Motos</a></li>
                    <li><a href="circuits.php">Circuits</a></li>
                    <li><a href="logout.php">Déconnexion</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <h1>Motos</h1>
            <a href="#" class="button">Ajouter une moto</a>
            
            <div class="grid">
                <?php foreach ($motos as $moto): ?>
                <div class="card">
                    <h2><?php echo htmlspecialchars($moto['marque'] . ' ' . $moto['modele']); ?></h2>
                    <ul class="info-list">
                        <li><strong>Année:</strong> <?php echo htmlspecialchars($moto['annee']); ?></li>
                        <li><strong>Cylindrée:</strong> <?php echo htmlspecialchars($moto['cylindree']); ?> cc</li>
                        <li><strong>Puissance:</strong> <?php echo htmlspecialchars($moto['puissance']); ?> ch</li>
                        <li><strong>Poids:</strong> <?php echo htmlspecialchars($moto['poids']); ?> kg</li>
                    </ul>
                    <div class="card-actions">
                        <a href="#" class="button button-small">Modifier</a>
                        <a href="#" class="button button-small button-danger">Supprimer</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> TéléMoto AI - Tous droits réservés</p>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html> 