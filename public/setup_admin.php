<?php
require_once '../app/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Vérifier si l'admin existe déjà
    $stmt = $db->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        // Créer l'admin s'il n'existe pas
        $password = 'Admin@2024!';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password, role) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'admin',
            'admin@telemetrie.local',
            $hashedPassword,
            'admin'
        ]);
        
        echo "<p style='color: green;'>Utilisateur admin créé avec succès!</p>";
        echo "<p>Username: admin</p>";
        echo "<p>Password: " . $password . "</p>";
    } else {
        echo "<p>L'utilisateur admin existe déjà.</p>";
        
        // Mettre à jour le mot de passe
        $password = 'Admin@2024!';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("
            UPDATE users 
            SET password = ? 
            WHERE username = 'admin'
        ");
        
        $stmt->execute([$hashedPassword]);
        
        echo "<p style='color: blue;'>Mot de passe admin mis à jour!</p>";
        echo "<p>Username: admin</p>";
        echo "<p>Password: " . $password . "</p>";
    }
    
    echo "<p><a href='index.php?route=login'>Aller à la page de connexion</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
} 