<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once 'classes/User.php';
        $user = new User();
        
        // Récupérer les données du formulaire
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Debug : Afficher les données reçues
        echo "<div class='debug-info'>";
        echo "<h3>Données reçues :</h3>";
        echo "<pre>";
        echo "Email : " . htmlspecialchars($email) . "\n";
        echo "Mot de passe : " . str_repeat('*', strlen($password)) . "\n";
        echo "</pre>";
        echo "</div>";
        
        // Tenter l'authentification
        $userData = $user->authenticate($email, $password);
        
        if ($userData) {
            // Debug : Afficher les données de l'utilisateur
            echo "<div class='debug-info'>";
            echo "<h3>Données utilisateur :</h3>";
            echo "<pre>";
            print_r($userData);
            echo "</pre>";
            echo "</div>";
            
            // Connecter l'utilisateur
            loginUser(
                $userData['id'], 
                $userData['username'], 
                $userData['role'],
                $userData['telemetrician_name'] ?? $userData['username']
            );
            
            // Rediriger vers le tableau de bord
            header('Location: ' . BASE_URL . '/index.php?page=dashboard');
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } catch (Exception $e) {
        // Debug : Afficher l'erreur complète
        echo "<div class='debug-error'>";
        echo "<h3>Erreur :</h3>";
        echo "<pre>";
        echo $e->getMessage() . "\n";
        echo $e->getTraceAsString();
        echo "</pre>";
        echo "</div>";
        
        $error = "Une erreur est survenue lors de la connexion.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - TeleMoto</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .debug-info, .debug-error {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin: 15px 0;
        }
        .debug-error {
            background-color: #fff3f3;
            border-color: #ffcdd2;
        }
        .debug-info h3, .debug-error h3 {
            margin-top: 0;
            color: #666;
        }
        .debug-info pre, .debug-error pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-logo">
            <h1>TeleMoto</h1>
            <p>Application de télémétrie moto</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                Connexion
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form action="index.php?page=login" method="post">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="login" class="btn btn-primary">Se connecter</button>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <p>Vous n'avez pas de compte ? <a href="index.php?page=register">Inscrivez-vous</a></p>
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
