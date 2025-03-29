<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - TeleMoto</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-logo">
            <h1>TeleMoto</h1>
            <p>Application de télémétrie moto</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                Inscription
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form action="index.php?page=register" method="post">
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telemetrician_name">Nom de votre télémétriste virtuel</label>
                        <input type="text" id="telemetrician_name" name="telemetrician_name" value="Télémétriste">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="register" class="btn btn-primary">S'inscrire</button>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <p>Vous avez déjà un compte ? <a href="index.php?page=login">Connectez-vous</a></p>
            </div>
        </div>
    </div>
    
    <footer class="login-footer">
        <div class="footer-container">
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> TeleMoto - Application de télémétrie moto
            </div>
        </div>
    </footer>
</body>
</html>
