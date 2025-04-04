<?php
/**
 * Classe de base pour tous les contrôleurs
 * 
 * Fournit les fonctionnalités communes à tous les contrôleurs
 */
namespace App\Controllers;

class Controller {
    /**
     * Données à passer à la vue
     */
    protected $data = [];
    
    /**
     * Utilisateur actuellement connecté
     */
    protected $user = null;
    
    /**
     * Constructeur
     */
    public function __construct() {
        // Vérifier si l'utilisateur est connecté
        $this->checkAuth();
        
        // Ajouter des données communes à toutes les vues
        $this->data['app_name'] = APP_NAME;
        $this->data['app_version'] = APP_VERSION;
        $this->data['user'] = $this->user;
        $this->data['current_year'] = date('Y');
    }
    
    /**
     * Vérifier l'authentification de l'utilisateur
     */
    protected function checkAuth() {
        if (isset($_SESSION['user_id'])) {
            // Charger l'utilisateur depuis la base de données
            $userModel = new \App\Models\UserModel();
            $this->user = $userModel->find($_SESSION['user_id']);
            
            // Mettre à jour la dernière activité
            $_SESSION['last_activity'] = time();
        }
    }
    
    /**
     * Rendre une vue
     * 
     * @param string $view Nom de la vue
     * @param array $data Données supplémentaires à passer à la vue
     * @return string Contenu HTML de la vue
     */
    protected function render($view, $data = []) {
        // Fusionner les données supplémentaires avec les données existantes
        $this->data = array_merge($this->data, $data);
        
        // Extraire les données pour les rendre disponibles dans la vue
        extract($this->data);
        
        // Chemin complet de la vue
        $viewPath = VIEWS_PATH . '/' . $view . '.php';
        
        // Vérifier si la vue existe
        if (!file_exists($viewPath)) {
            throw new \Exception("Vue non trouvée: {$view}");
        }
        
        // Démarrer la mise en tampon de sortie
        ob_start();
        
        // Inclure la vue
        include $viewPath;
        
        // Récupérer le contenu et nettoyer le tampon
        $content = ob_get_clean();
        
        return $content;
    }
    
    /**
     * Afficher une vue avec le layout par défaut
     * 
     * @param string $view Nom de la vue
     * @param array $data Données supplémentaires à passer à la vue
     * @param string $layout Nom du layout à utiliser
     */
    protected function view($view, $data = [], $layout = 'default') {
        // Rendre le contenu de la vue
        $content = $this->render($view, $data);
        
        // Ajouter le contenu aux données
        $this->data['content'] = $content;
        
        // Rendre le layout
        echo $this->render('layouts/' . $layout);
    }
    
    /**
     * Rediriger vers une autre URL
     * 
     * @param string $url URL de destination
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Répondre avec du JSON
     * 
     * @param mixed $data Données à encoder en JSON
     * @param int $status Code de statut HTTP
     */
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Vérifier si la requête est une requête AJAX
     * 
     * @return bool La requête est AJAX ou non
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     * 
     * @param bool $redirect Rediriger vers la page de connexion si non connecté
     * @return bool L'utilisateur est connecté ou non
     */
    protected function isLoggedIn($redirect = false) {
        $isLoggedIn = $this->user !== null;
        
        if (!$isLoggedIn && $redirect) {
            $this->redirect(BASE_URL . '/login');
        }
        
        return $isLoggedIn;
    }
    
    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     * 
     * @param string|array $roles Rôle(s) requis
     * @param bool $redirect Rediriger si l'utilisateur n'a pas le rôle requis
     * @return bool L'utilisateur a le rôle requis ou non
     */
    protected function hasRole($roles, $redirect = false) {
        // Vérifier d'abord si l'utilisateur est connecté
        if (!$this->isLoggedIn($redirect)) {
            return false;
        }
        
        // Convertir en tableau si ce n'est pas déjà le cas
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        // Vérifier si l'utilisateur a l'un des rôles requis
        $hasRole = in_array($this->user['role'], $roles);
        
        if (!$hasRole && $redirect) {
            // Rediriger vers une page d'accès refusé
            $this->redirect(BASE_URL . '/access-denied');
        }
        
        return $hasRole;
    }
    
    /**
     * Obtenir les données de la requête POST
     * 
     * @param string $key Clé spécifique à récupérer (optionnel)
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed Données POST
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }
    
    /**
     * Obtenir les données de la requête GET
     * 
     * @param string $key Clé spécifique à récupérer (optionnel)
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed Données GET
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }
    
    /**
     * Valider les données de formulaire
     * 
     * @param array $data Données à valider
     * @param array $rules Règles de validation
     * @return array Erreurs de validation (vide si aucune erreur)
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            // Diviser les règles multiples
            $fieldRules = explode('|', $rule);
            
            foreach ($fieldRules as $fieldRule) {
                // Vérifier si la règle a des paramètres
                if (strpos($fieldRule, ':') !== false) {
                    list($ruleName, $ruleParam) = explode(':', $fieldRule, 2);
                } else {
                    $ruleName = $fieldRule;
                    $ruleParam = null;
                }
                
                // Appliquer la règle
                switch ($ruleName) {
                    case 'required':
                        if (!isset($data[$field]) || trim($data[$field]) === '') {
                            $errors[$field][] = "Le champ {$field} est obligatoire.";
                        }
                        break;
                        
                    case 'email':
                        if (isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "Le champ {$field} doit être une adresse email valide.";
                        }
                        break;
                        
                    case 'min':
                        if (isset($data[$field]) && strlen($data[$field]) < $ruleParam) {
                            $errors[$field][] = "Le champ {$field} doit contenir au moins {$ruleParam} caractères.";
                        }
                        break;
                        
                    case 'max':
                        if (isset($data[$field]) && strlen($data[$field]) > $ruleParam) {
                            $errors[$field][] = "Le champ {$field} ne doit pas dépasser {$ruleParam} caractères.";
                        }
                        break;
                        
                    case 'numeric':
                        if (isset($data[$field]) && !is_numeric($data[$field])) {
                            $errors[$field][] = "Le champ {$field} doit être un nombre.";
                        }
                        break;
                        
                    case 'date':
                        if (isset($data[$field]) && strtotime($data[$field]) === false) {
                            $errors[$field][] = "Le champ {$field} doit être une date valide.";
                        }
                        break;
                        
                    case 'matches':
                        if (isset($data[$field]) && (!isset($data[$ruleParam]) || $data[$field] !== $data[$ruleParam])) {
                            $errors[$field][] = "Le champ {$field} doit correspondre au champ {$ruleParam}.";
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Générer un jeton CSRF
     * 
     * @return string Jeton CSRF
     */
    protected function generateCsrfToken() {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    /**
     * Vérifier un jeton CSRF
     * 
     * @param string $token Jeton à vérifier
     * @return bool Le jeton est valide ou non
     */
    protected function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
