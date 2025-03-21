<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telemetry App</title>
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <nav class="main-nav">
                <a href="index.php" class="logo">Telemetry App</a>
                <ul class="nav-links">
                    <li><a href="index.php?route=motos" class="<?php echo $route === 'motos' ? 'active' : ''; ?>">Motos</a></li>
                    <li><a href="index.php?route=sessions" class="<?php echo $route === 'sessions' ? 'active' : ''; ?>">Sessions</a></li>
                    <li><a href="index.php?route=pilotes" class="<?php echo $route === 'pilotes' ? 'active' : ''; ?>">Pilotes</a></li>
                    <li><a href="index.php?route=circuits" class="<?php echo $route === 'circuits' ? 'active' : ''; ?>">Circuits</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="user-menu">
                            <a href="#" class="user-toggle">
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                                <span class="arrow">▼</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="index.php?route=profil">Profil</a></li>
                                <li><a href="index.php?route=logout">Déconnexion</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <?php if(isset($_SESSION['flash_message'])): ?>
            <div class="container">
                <div class="alert <?php echo $_SESSION['flash_type']; ?>">
                    <?php 
                        echo $_SESSION['flash_message'];
                        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html> 