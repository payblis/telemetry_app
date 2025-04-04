<?php
/**
 * Routeur pour l'application SaaS de Télémétrie Moto
 * 
 * Gère le routage des requêtes vers les contrôleurs appropriés
 */

class Router {
    private $routes = [];
    private $params = [];
    private $notFoundCallback;
    
    /**
     * Constructeur
     */
    public function __construct() {
        // Charger les routes définies
        $this->loadRoutes();
    }
    
    /**
     * Charger les routes depuis le fichier de configuration
     */
    private function loadRoutes() {
        // Routes par défaut
        $this->addRoute('GET', '/', 'HomeController@index');
        $this->addRoute('GET', '/login', 'AuthController@loginForm');
        $this->addRoute('POST', '/login', 'AuthController@login');
        $this->addRoute('GET', '/register', 'AuthController@registerForm');
        $this->addRoute('POST', '/register', 'AuthController@register');
        $this->addRoute('GET', '/logout', 'AuthController@logout');
        
        // Routes du dashboard
        $this->addRoute('GET', '/dashboard', 'DashboardController@index');
        
        // Routes des pilotes
        $this->addRoute('GET', '/pilotes', 'PiloteController@index');
        $this->addRoute('GET', '/pilotes/create', 'PiloteController@create');
        $this->addRoute('POST', '/pilotes/store', 'PiloteController@store');
        $this->addRoute('GET', '/pilotes/edit/([0-9]+)', 'PiloteController@edit');
        $this->addRoute('POST', '/pilotes/update/([0-9]+)', 'PiloteController@update');
        $this->addRoute('POST', '/pilotes/delete/([0-9]+)', 'PiloteController@delete');
        
        // Routes des motos
        $this->addRoute('GET', '/motos', 'MotoController@index');
        $this->addRoute('GET', '/motos/create', 'MotoController@create');
        $this->addRoute('POST', '/motos/store', 'MotoController@store');
        $this->addRoute('GET', '/motos/edit/([0-9]+)', 'MotoController@edit');
        $this->addRoute('POST', '/motos/update/([0-9]+)', 'MotoController@update');
        $this->addRoute('POST', '/motos/delete/([0-9]+)', 'MotoController@delete');
        
        // Routes des circuits
        $this->addRoute('GET', '/circuits', 'CircuitController@index');
        $this->addRoute('GET', '/circuits/create', 'CircuitController@create');
        $this->addRoute('POST', '/circuits/store', 'CircuitController@store');
        $this->addRoute('GET', '/circuits/edit/([0-9]+)', 'CircuitController@edit');
        $this->addRoute('POST', '/circuits/update/([0-9]+)', 'CircuitController@update');
        $this->addRoute('POST', '/circuits/delete/([0-9]+)', 'CircuitController@delete');
        $this->addRoute('POST', '/circuits/import-ia', 'CircuitController@importIA');
        
        // Routes des sessions
        $this->addRoute('GET', '/sessions', 'SessionController@index');
        $this->addRoute('GET', '/sessions/create', 'SessionController@create');
        $this->addRoute('POST', '/sessions/store', 'SessionController@store');
        $this->addRoute('GET', '/sessions/view/([0-9]+)', 'SessionController@view');
        $this->addRoute('GET', '/sessions/edit/([0-9]+)', 'SessionController@edit');
        $this->addRoute('POST', '/sessions/update/([0-9]+)', 'SessionController@update');
        $this->addRoute('POST', '/sessions/delete/([0-9]+)', 'SessionController@delete');
        
        // Routes de télémétrie
        $this->addRoute('GET', '/telemetrie/import', 'TelemetrieController@importForm');
        $this->addRoute('POST', '/telemetrie/import', 'TelemetrieController@import');
        $this->addRoute('GET', '/telemetrie/view/([0-9]+)', 'TelemetrieController@view');
        $this->addRoute('GET', '/telemetrie/export/([0-9]+)', 'TelemetrieController@export');
        
        // Routes d'analyse vidéo
        $this->addRoute('GET', '/videos', 'VideoController@index');
        $this->addRoute('GET', '/videos/upload', 'VideoController@uploadForm');
        $this->addRoute('POST', '/videos/upload', 'VideoController@upload');
        $this->addRoute('GET', '/videos/view/([0-9]+)', 'VideoController@view');
        $this->addRoute('POST', '/videos/analyze/([0-9]+)', 'VideoController@analyze');
        
        // Routes des recommandations IA
        $this->addRoute('POST', '/ia/recommandation', 'IAController@getRecommendation');
        $this->addRoute('POST', '/ia/feedback/([0-9]+)', 'IAController@saveFeedback');
        
        // Routes de la bibliothèque communautaire
        $this->addRoute('GET', '/bibliotheque', 'BibliothequeController@index');
        $this->addRoute('GET', '/bibliotheque/reglages/([0-9]+)', 'BibliothequeController@viewReglage');
        
        // Routes des statistiques
        $this->addRoute('GET', '/statistiques', 'StatistiqueController@index');
        $this->addRoute('GET', '/statistiques/pilote/([0-9]+)', 'StatistiqueController@pilote');
        $this->addRoute('GET', '/statistiques/moto/([0-9]+)', 'StatistiqueController@moto');
        $this->addRoute('GET', '/statistiques/circuit/([0-9]+)', 'StatistiqueController@circuit');
        
        // Routes d'administration
        $this->addRoute('GET', '/admin', 'AdminController@index');
        $this->addRoute('GET', '/admin/users', 'AdminController@users');
        $this->addRoute('GET', '/admin/experts', 'AdminController@experts');
        $this->addRoute('GET', '/admin/stats', 'AdminController@stats');
        $this->addRoute('GET', '/admin/logs', 'AdminController@logs');
        
        // Routes API
        $this->addRoute('GET', '/api/v1/pilotes', 'Api\\PiloteController@index');
        $this->addRoute('GET', '/api/v1/motos', 'Api\\MotoController@index');
        $this->addRoute('GET', '/api/v1/circuits', 'Api\\CircuitController@index');
        
        // Route par défaut pour les pages non trouvées
        $this->setNotFound(function() {
            header("HTTP/1.0 404 Not Found");
            include VIEWS_PATH . '/errors/404.php';
        });
    }
    
    /**
     * Ajouter une route
     * 
     * @param string $method Méthode HTTP (GET, POST, etc.)
     * @param string $pattern Modèle d'URL
     * @param string $callback Contrôleur@méthode à appeler
     */
    public function addRoute($method, $pattern, $callback) {
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        $this->routes[$method][$pattern] = $callback;
    }
    
    /**
     * Définir le callback pour les routes non trouvées
     * 
     * @param callable $callback Fonction à appeler
     */
    public function setNotFound($callback) {
        $this->notFoundCallback = $callback;
    }
    
    /**
     * Dispatcher la requête vers le contrôleur approprié
     */
    public function dispatch() {
        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Parcourir les routes pour la méthode HTTP actuelle
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $pattern => $callback) {
                if (preg_match($pattern, $uri, $matches)) {
                    // Supprimer le premier élément (correspondance complète)
                    array_shift($matches);
                    $this->params = $matches;
                    
                    // Appeler le contrôleur
                    return $this->callController($callback);
                }
            }
        }
        
        // Route non trouvée
        if ($this->notFoundCallback) {
            call_user_func($this->notFoundCallback);
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "Page non trouvée";
        }
    }
    
    /**
     * Obtenir l'URI de la requête
     * 
     * @return string URI nettoyée
     */
    private function getUri() {
        $uri = $_SERVER['REQUEST_URI'];
        
        // Supprimer la query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Supprimer le chemin de base
        $base_path = parse_url(BASE_URL, PHP_URL_PATH);
        if ($base_path && $base_path !== '/') {
            $uri = substr($uri, strlen($base_path));
        }
        
        // Nettoyer l'URI
        $uri = '/' . trim($uri, '/');
        
        return $uri;
    }
    
    /**
     * Appeler le contrôleur
     * 
     * @param string $callback Format: "ControllerName@methodName"
     * @return mixed Résultat de l'appel au contrôleur
     */
    private function callController($callback) {
        list($controller, $method) = explode('@', $callback);
        
        // Vérifier si c'est un contrôleur API
        if (strpos($controller, 'Api\\') === 0) {
            $controller_class = $controller;
        } else {
            $controller_class = 'App\\Controllers\\' . $controller;
        }
        
        // Instancier le contrôleur
        if (class_exists($controller_class)) {
            $controller_instance = new $controller_class();
            
            // Appeler la méthode
            if (method_exists($controller_instance, $method)) {
                return call_user_func_array([$controller_instance, $method], $this->params);
            }
        }
        
        // Contrôleur ou méthode non trouvé
        throw new Exception("Contrôleur ou méthode non trouvé: $controller@$method");
    }
}
