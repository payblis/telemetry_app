<?php
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer les données du formulaire
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $telemetricianName = $_POST['telemetrician_name'] ?? '';
        
        // Vérifications
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception("Tous les champs obligatoires doivent être remplis.");
        }
        
        if ($password !== $confirmPassword) {
            throw new Exception("Les mots de passe ne correspondent pas.");
        }
        
        if (empty($telemetricianName)) {
            $telemetricianName = $username;
        }
        
        // Créer l'utilisateur
        $user = new User();
        $userId = $user->create($username, $email, $password, 'ADMIN', $telemetricianName);
        
        $success = "Administrateur créé avec succès !";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création d'un administrateur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .success {
            color: #4CAF50;
            margin-bottom: 15px;
        }
        .error {
            color: #f44336;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Création d'un nouvel administrateur</h1>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <label for="telemetrician_name">Nom du télémétriste (optionnel) :</label>
                <input type="text" id="telemetrician_name" name="telemetrician_name">
            </div>
            
            <button type="submit">Créer l'administrateur</button>
        </form>
    </div>
</body>
</html> 