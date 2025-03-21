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

// Récupérer la liste des sessions
$stmt = $pdo->prepare("
    SELECT s.*, 
           c.nom as circuit_nom,
           p.nom as pilote_nom,
           m.marque as moto_marque,
           m.modele as moto_modele
    FROM sessions s
    JOIN circuits c ON s.circuit_id = c.id
    JOIN pilotes p ON s.pilote_id = p.id
    JOIN motos m ON s.moto_id = m.id
    ORDER BY s.date_session DESC
");
$stmt->execute();
$sessions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessions - TéléMoto AI</title>
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
            <h1>Sessions</h1>
            <a href="sessions/nouvelle.php" class="button">Nouvelle session</a>
            
            <div class="sessions-grid">
                <?php foreach ($sessions as $session): ?>
                <div class="card">
                    <div class="card-header">
                        <h2><?php echo htmlspecialchars($session['circuit_nom']); ?></h2>
                        <span class="date"><?php echo formatDate($session['date_session']); ?></span>
                    </div>
                    <div class="card-content">
                        <ul class="info-list">
                            <li>
                                <strong>Pilote:</strong> 
                                <?php echo htmlspecialchars($session['pilote_nom']); ?>
                            </li>
                            <li>
                                <strong>Moto:</strong> 
                                <?php echo htmlspecialchars($session['moto_marque'] . ' ' . $session['moto_modele']); ?>
                            </li>
                            <li>
                                <strong>Conditions:</strong> 
                                <?php echo htmlspecialchars($session['conditions_meteo']); ?>
                            </li>
                            <li>
                                <strong>Température:</strong> 
                                <?php echo htmlspecialchars($session['temperature']); ?>°C
                            </li>
                        </ul>
                    </div>
                    <div class="card-actions">
                        <a href="sessions/details.php?id=<?php echo $session['id']; ?>" class="button button-small">Détails</a>
                        <a href="sessions/telemetry.php?id=<?php echo $session['id']; ?>" class="button button-small">Télémétrie</a>
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