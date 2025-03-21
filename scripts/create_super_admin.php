<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $user = new User();
    
    // Créer le super admin
    $superAdmin = $user->create([
        'username' => 'superadmin',
        'email' => 'admin@telemetry-app.com',
        'password' => password_hash('Admin123!', PASSWORD_BCRYPT),
        'role' => 'admin'
    ]);

    if ($superAdmin) {
        echo "Super admin créé avec succès !\n";
        echo "Email: admin@telemetry-app.com\n";
        echo "Mot de passe: Admin123!\n";
    }
} catch (\Exception $e) {
    echo "Erreur lors de la création du super admin : " . $e->getMessage() . "\n";
} 