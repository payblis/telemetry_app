<?php
echo "<h1>Test de configuration de l'application Télémétrie IA</h1>";

// Test 1 : Version PHP
echo "<h2>1. Version PHP</h2>";
echo "Version PHP : " . phpversion();
echo "<br>Résultat : " . (version_compare(PHP_VERSION, '8.0.0') >= 0 ? '✅ OK' : '❌ PHP 8.0 ou supérieur requis');

// Test 2 : Extensions PHP requises
echo "<h2>2. Extensions PHP</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'openssl'];
foreach ($required_extensions as $ext) {
    echo "Extension $ext : " . (extension_loaded($ext) ? '✅ OK' : '❌ Manquante') . "<br>";
}

// Test 3 : Connexion à la base de données
echo "<h2>3. Connexion Base de données</h2>";
try {
    require_once 'app/config/database.php';
    $db = Database::getInstance()->getConnection();
    echo "Connexion à la base de données : ✅ OK<br>";
    
    // Test de la table users
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "Table 'users' accessible : ✅ OK ($count utilisateurs trouvés)<br>";
} catch (Exception $e) {
    echo "Erreur de connexion : ❌ " . $e->getMessage() . "<br>";
}

// Test 4 : Permissions des dossiers
echo "<h2>4. Permissions des dossiers</h2>";
$folders = [
    'app/views' => is_readable('app/views'),
    'public/css' => is_readable('public/css'),
    'public/js' => is_readable('public/js')
];

foreach ($folders as $folder => $readable) {
    echo "Dossier $folder : " . ($readable ? '✅ OK' : '❌ Non lisible') . "<br>";
}

// Test 5 : Configuration Apache
echo "<h2>5. Configuration Apache</h2>";
$mod_rewrite = false;
if (function_exists('apache_get_modules')) {
    $mod_rewrite = in_array('mod_rewrite', apache_get_modules());
} else {
    // Alternative check for mod_rewrite
    $mod_rewrite = isset($_SERVER['REDIRECT_URL']) || isset($_SERVER['HTTP_MOD_REWRITE']);
}
echo "mod_rewrite : " . ($mod_rewrite ? '✅ OK' : '⚠️ Statut inconnu') . "<br>";

// Informations supplémentaires
echo "<h2>6. Informations de connexion</h2>";
echo "URL de l'application : " . $_SERVER['HTTP_HOST'] . "<br>";
echo "URL de connexion : https://" . $_SERVER['HTTP_HOST'] . "/public/index.php?route=login<br>";
echo "Identifiants admin par défaut :<br>";
echo "- Username : admin<br>";
echo "- Email : admin@telemetrie-ia.fr<br>";
echo "- Mot de passe : Admin@2024!<br>";
echo "<br><strong>Important : Changez le mot de passe administrateur après la première connexion !</strong>"; 