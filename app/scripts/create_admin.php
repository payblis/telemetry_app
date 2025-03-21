<?php
require_once '../config/database.php';

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
        
        echo "Utilisateur admin créé avec succès!\n";
        echo "Username: admin\n";
        echo "Password: " . $password . "\n";
    } else {
        echo "L'utilisateur admin existe déjà.\n";
        
        // Mettre à jour le mot de passe
        $password = 'Admin@2024!';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("
            UPDATE users 
            SET password = ? 
            WHERE username = 'admin'
        ");
        
        $stmt->execute([$hashedPassword]);
        
        echo "Mot de passe admin mis à jour!\n";
        echo "Username: admin\n";
        echo "Password: " . $password . "\n";
    }
    
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
} 