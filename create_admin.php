<?php
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';

// Fonction pour lire une entrée utilisateur
function readline($prompt) {
    echo $prompt;
    return trim(fgets(STDIN));
}

// Fonction pour masquer le mot de passe pendant la saisie
function readPassword($prompt) {
    echo $prompt;
    if (PHP_OS == 'WINNT') {
        $password = stream_get_line(STDIN, 1024, PHP_EOL);
    } else {
        system('stty -echo');
        $password = stream_get_line(STDIN, 1024, PHP_EOL);
        system('stty echo');
    }
    echo PHP_EOL;
    return trim($password);
}

try {
    // Demander les informations de l'administrateur
    echo "Création d'un nouvel administrateur\n";
    echo "================================\n\n";
    
    $username = readline("Nom d'utilisateur : ");
    $email = readline("Email : ");
    $password = readPassword("Mot de passe : ");
    $confirmPassword = readPassword("Confirmer le mot de passe : ");
    $telemetricianName = readline("Nom du télémétriste (optionnel, appuyez sur Entrée pour utiliser le nom d'utilisateur) : ");
    
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
    
    echo "\nAdministrateur créé avec succès !\n";
    echo "ID : " . $userId . "\n";
    echo "Nom d'utilisateur : " . $username . "\n";
    echo "Email : " . $email . "\n";
    echo "Rôle : ADMIN\n";
    echo "Nom du télémétriste : " . $telemetricianName . "\n";
    
} catch (Exception $e) {
    echo "\nErreur : " . $e->getMessage() . "\n";
    exit(1);
} 