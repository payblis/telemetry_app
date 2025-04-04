<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

requireLogin();

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Moto SaaS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>Tableau de bord</h1>
        <div class="user-info">
            <p>Bienvenue, <?php echo htmlspecialchars($user['email']); ?>!</p>
            <p>Rôle: <?php echo htmlspecialchars($user['role']); ?></p>
        </div>
        
        <div class="dashboard-menu">
            <?php if (isAdmin()): ?>
                <h2>Administration</h2>
                <ul>
                    <li><a href="pilotes/list.php">Gérer les pilotes</a></li>
                    <li><a href="circuits/list.php">Gérer les circuits</a></li>
                </ul>
            <?php endif; ?>
            
            <h2>Mes options</h2>
            <ul>
                <li><a href="pilotes/add.php">Ajouter un pilote</a></li>
                <li><a href="sessions/list.php">Mes sessions</a></li>
                <li><a href="settings/user_profile.php">Mon profil</a></li>
            </ul>
        </div>
        
        <div class="logout">
            <a href="logout.php">Déconnexion</a>
        </div>
    </div>
</body>
</html> 