<?php
/**
 * Autoloader pour l'application SaaS de Télémétrie Moto
 * 
 * Permet le chargement automatique des classes selon la convention PSR-4
 */

spl_autoload_register(function ($class) {
    // Préfixes de namespace et leurs répertoires correspondants
    $prefixes = [
        'App\\Controllers\\' => ROOT_PATH . '/app/controllers/',
        'App\\Models\\' => ROOT_PATH . '/app/models/',
        'App\\Services\\' => ROOT_PATH . '/app/services/',
        'App\\Utils\\' => ROOT_PATH . '/app/utils/',
        'App\\Jobs\\' => ROOT_PATH . '/app/jobs/',
        'Api\\' => ROOT_PATH . '/api/'
    ];
    
    // Parcourir chaque préfixe de namespace
    foreach ($prefixes as $prefix => $base_dir) {
        // Vérifier si la classe utilise le préfixe
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            // Passer au préfixe suivant si ce n'est pas le bon
            continue;
        }
        
        // Obtenir le chemin relatif de la classe
        $relative_class = substr($class, $len);
        
        // Remplacer les séparateurs de namespace par des séparateurs de répertoire
        // Ajouter .php à la fin du nom de fichier
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        // Si le fichier existe, le charger
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    
    return false;
});

// Fonction pour charger les fichiers de configuration
function load_config($file) {
    $file_path = ROOT_PATH . '/config/' . $file . '.php';
    if (file_exists($file_path)) {
        return require $file_path;
    }
    return false;
}

// Fonction pour charger les vues
function load_view($view, $data = []) {
    // Extraire les données pour les rendre disponibles dans la vue
    if (is_array($data)) {
        extract($data);
    }
    
    $view_path = VIEWS_PATH . '/' . $view . '.php';
    if (file_exists($view_path)) {
        ob_start();
        include $view_path;
        return ob_get_clean();
    }
    
    throw new Exception("Vue non trouvée: " . $view);
}

// Fonction pour inclure les parties communes (header, footer, etc.)
function include_part($part, $data = []) {
    // Extraire les données pour les rendre disponibles dans la partie
    if (is_array($data)) {
        extract($data);
    }
    
    $part_path = VIEWS_PATH . '/parts/' . $part . '.php';
    if (file_exists($part_path)) {
        include $part_path;
        return true;
    }
    
    return false;
}
