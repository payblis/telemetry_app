<?php
session_start();
require_once '../app/config/database.php';

// Définition des constantes de base
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT_PATH . 'app' . DIRECTORY_SEPARATOR);

// Autoloader des classes
spl_autoload_register(function($className) {
    $file = APP_PATH . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Router simple
$route = isset($_GET['route']) ? $_GET['route'] : 'home';

// Gestion des routes
switch ($route) {
    case 'home':
        require_once APP_PATH . 'controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
    
    case 'login':
        require_once APP_PATH . 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;
    
    default:
        // Page 404
        header("HTTP/1.0 404 Not Found");
        require_once APP_PATH . 'views/404.php';
        break;
} 