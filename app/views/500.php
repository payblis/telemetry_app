<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur serveur - Télémétrie IA</title>
    <link href="/public/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/public/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="/public/css/custom.min.css" rel="stylesheet">
</head>
<body class="nav-md">
    <div class="container body">
        <div class="main_container">
            <div class="col-md-12">
                <div class="col-middle">
                    <div class="text-center">
                        <h1 class="error-number">500</h1>
                        <h2>Erreur interne du serveur</h2>
                        <p>Nous rencontrons actuellement des difficultés techniques. Veuillez réessayer plus tard.</p>
                        <?php if (isset($error_message) && $_SERVER['SERVER_NAME'] === 'localhost'): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                        <?php endif; ?>
                        <div class="mid_center">
                            <a href="/public/index.php" class="btn btn-primary">Retour à l'accueil</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 