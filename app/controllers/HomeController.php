<?php
class HomeController {
    public function index() {
        // Charger les données nécessaires pour la page d'accueil
        $pageTitle = "Télémétrie IA - Accueil";
        
        // Inclure la vue
        require_once APP_PATH . 'views/templates/header.php';
        require_once APP_PATH . 'views/home/index.php';
        require_once APP_PATH . 'views/templates/footer.php';
    }
} 