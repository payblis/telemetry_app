<?php
// Activation de l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Débogage des chemins
error_log("Document Root: " . $_SERVER['DOCUMENT_ROOT']);
error_log("Script Filename: " . $_SERVER['SCRIPT_FILENAME']);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);

session_start();
require_once '../app/config/database.php';

// Définition des constantes de base
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT_PATH . 'app' . DIRECTORY_SEPARATOR);

// Débogage des chemins définis
error_log("ROOT_PATH: " . ROOT_PATH);
error_log("APP_PATH: " . APP_PATH);

// Vérification de l'existence du fichier database.php
$databaseFile = '../app/config/database.php';
if (!file_exists($databaseFile)) {
    error_log("Database file not found: " . $databaseFile);
    die("Configuration file not found");
} else {
    error_log("Database file found: " . $databaseFile);
    require_once $databaseFile;
}

// Autoloader des classes
spl_autoload_register(function($className) {
    $file = APP_PATH . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
    error_log("Attempting to load class: " . $file);
    if (file_exists($file)) {
        require_once $file;
        error_log("Class file loaded successfully: " . $file);
    } else {
        error_log("Class file not found: " . $file);
    }
});

// Router simple
$route = isset($_GET['route']) ? $_GET['route'] : 'home';
error_log("Current route: " . $route);

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
    
    case 'logout':
        require_once APP_PATH . 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;
    
    case 'dashboard':
        require_once APP_PATH . 'controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;
    
    case 'motos':
        require_once APP_PATH . 'controllers/MotoController.php';
        $controller = new MotoController();
        $controller->index();
        break;

    case 'moto/new':
        require_once APP_PATH . 'controllers/MotoController.php';
        $controller = new MotoController();
        $controller->create();
        break;
    
    case 'moto/edit':
        require_once APP_PATH . 'controllers/MotoController.php';
        $controller = new MotoController();
        if (isset($_GET['id'])) {
            $controller->edit($_GET['id']);
        } else {
            header('Location: index.php?route=motos');
        }
        break;

    case 'moto/view':
        require_once APP_PATH . 'controllers/MotoController.php';
        $controller = new MotoController();
        if (isset($_GET['id'])) {
            $controller->view($_GET['id']);
        } else {
            header('Location: index.php?route=motos');
        }
        break;

    case 'moto/delete':
        require_once APP_PATH . 'controllers/MotoController.php';
        $controller = new MotoController();
        if (isset($_GET['id'])) {
            $controller->delete($_GET['id']);
        }
        header('Location: index.php?route=motos');
        break;

    case 'moto/specs':
        require_once APP_PATH . 'controllers/MotoController.php';
        $controller = new MotoController();
        $controller->specs();
        break;
    
    case 'pilote/new':
        require_once APP_PATH . 'controllers/PiloteController.php';
        $controller = new PiloteController();
        $controller->create();
        break;
    
    case 'pilote/edit':
        require_once APP_PATH . 'controllers/PiloteController.php';
        $controller = new PiloteController();
        $controller->edit();
        break;
    
    case 'session/new':
        require_once APP_PATH . 'controllers/SessionController.php';
        $controller = new SessionController();
        $controller->create();
        break;
    
    case 'session':
        require_once APP_PATH . 'controllers/SessionController.php';
        $controller = new SessionController();
        $controller->view();
        break;
    
    case 'telemetrie':
        require_once APP_PATH . 'controllers/TelemetrieController.php';
        $controller = new TelemetrieController();
        $controller->index();
        break;
    
    case 'reglages':
        require_once APP_PATH . 'controllers/ReglagesController.php';
        $controller = new ReglagesController();
        $controller->index();
        break;
    
    default:
        // Page 404
        header("HTTP/1.0 404 Not Found");
        require_once APP_PATH . 'views/404.php';
        break;
} 