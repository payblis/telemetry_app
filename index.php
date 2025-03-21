<?php
session_start();
require_once 'config/database.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupération des informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Télémétrie Moto - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo">TéléMoto AI</div>
                <ul>
                    <li><a href="sessions.php">Sessions</a></li>
                    <li><a href="pilotes.php">Pilotes</a></li>
                    <li><a href="motos.php">Motos</a></li>
                    <li><a href="circuits.php">Circuits</a></li>
                    <?php if ($user['role'] === 'admin'): ?>
                    <li><a href="admin/dashboard.php">Administration</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Déconnexion</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <h1>Bienvenue, <?php echo htmlspecialchars($user['username']); ?></h1>
            
            <div class="dashboard-grid">
                <div class="card">
                    <h2>Nouvelle Session</h2>
                    <a href="sessions/nouvelle.php" class="button">Créer une session</a>
                </div>

                <div class="card">
                    <h2>Sessions Récentes</h2>
                    <?php
                    $stmt = $pdo->query("SELECT s.*, c.nom as circuit_nom, p.nom as pilote_nom 
                                       FROM sessions s 
                                       JOIN circuits c ON s.circuit_id = c.id 
                                       JOIN pilotes p ON s.pilote_id = p.id 
                                       ORDER BY s.created_at DESC LIMIT 5");
                    $sessions = $stmt->fetchAll();
                    ?>
                    <ul class="sessions-list">
                        <?php foreach ($sessions as $session): ?>
                        <li>
                            <a href="sessions/details.php?id=<?php echo $session['id']; ?>">
                                <?php echo htmlspecialchars($session['circuit_nom']); ?> - 
                                <?php echo htmlspecialchars($session['pilote_nom']); ?> - 
                                <?php echo date('d/m/Y H:i', strtotime($session['date_session'])); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="card">
                    <h2>Statistiques</h2>
                    <?php
                    $stats = $pdo->query("SELECT 
                        (SELECT COUNT(*) FROM sessions) as total_sessions,
                        (SELECT COUNT(*) FROM pilotes) as total_pilotes,
                        (SELECT COUNT(*) FROM motos) as total_motos")->fetch();
                    ?>
                    <ul class="stats-list">
                        <li>Sessions totales : <?php echo $stats['total_sessions']; ?></li>
                        <li>Pilotes : <?php echo $stats['total_pilotes']; ?></li>
                        <li>Motos : <?php echo $stats['total_motos']; ?></li>
                    </ul>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> TéléMoto AI - Tous droits réservés</p>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html> 