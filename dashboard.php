<?php
session_start();
require_once 'config/database.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupération des informations de l'utilisateur
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Moto Telemetry</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Tableau de bord</h1>
            <div class="user-info">
                <p>Connecté en tant que : <?php echo htmlspecialchars($user['name']); ?></p>
                <a href="logout.php" class="logout-btn">Déconnexion</a>
            </div>
        </header>

        <nav class="main-nav">
            <ul>
                <li><a href="riders.php">Gestion des pilotes</a></li>
                <?php if ($user['role'] === 'admin'): ?>
                    <li><a href="admin.php">Administration</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <main>
            <div class="dashboard-welcome">
                <h2>Bienvenue dans l'application Moto Telemetry</h2>
                <p>Cette application vous permet de gérer les réglages de moto et les pilotes.</p>
                
                <div class="quick-stats">
                    <div class="stat-card">
                        <h3>Pilotes</h3>
                        <?php
                        $stmt = $db->query("SELECT COUNT(*) FROM riders");
                        $riderCount = $stmt->fetchColumn();
                        ?>
                        <p><?php echo $riderCount; ?> pilotes enregistrés</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 