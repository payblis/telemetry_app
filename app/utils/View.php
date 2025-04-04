<?php
/**
 * Classe utilitaire pour la gestion des vues
 * 
 * Fournit les fonctionnalités pour le rendu des vues
 */
namespace App\Utils;

class View {
    /**
     * Données à passer à la vue
     */
    protected static $data = [];
    
    /**
     * Définir une variable pour la vue
     * 
     * @param string $key Nom de la variable
     * @param mixed $value Valeur de la variable
     */
    public static function set($key, $value) {
        self::$data[$key] = $value;
    }
    
    /**
     * Définir plusieurs variables pour la vue
     * 
     * @param array $data Tableau associatif de variables
     */
    public static function setMultiple($data) {
        self::$data = array_merge(self::$data, $data);
    }
    
    /**
     * Rendre une vue
     * 
     * @param string $view Nom de la vue
     * @param array $data Données supplémentaires à passer à la vue
     * @param bool $return Retourner le contenu au lieu de l'afficher
     * @return string|void Contenu HTML de la vue si $return est true
     */
    public static function render($view, $data = [], $return = false) {
        // Fusionner les données supplémentaires avec les données existantes
        $viewData = array_merge(self::$data, $data);
        
        // Extraire les données pour les rendre disponibles dans la vue
        extract($viewData);
        
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
        
        if ($return) {
            return $content;
        }
        
        echo $content;
    }
    
    /**
     * Rendre une vue avec un layout
     * 
     * @param string $view Nom de la vue
     * @param array $data Données supplémentaires à passer à la vue
     * @param string $layout Nom du layout à utiliser
     * @param bool $return Retourner le contenu au lieu de l'afficher
     * @return string|void Contenu HTML de la vue avec layout si $return est true
     */
    public static function renderWithLayout($view, $data = [], $layout = 'default', $return = false) {
        // Rendre le contenu de la vue
        $content = self::render($view, $data, true);
        
        // Ajouter le contenu aux données
        $layoutData = array_merge(self::$data, $data, ['content' => $content]);
        
        // Rendre le layout
        return self::render('layouts/' . $layout, $layoutData, $return);
    }
    
    /**
     * Inclure une partie de vue
     * 
     * @param string $part Nom de la partie à inclure
     * @param array $data Données supplémentaires à passer à la partie
     */
    public static function includePart($part, $data = []) {
        // Fusionner les données supplémentaires avec les données existantes
        $partData = array_merge(self::$data, $data);
        
        // Extraire les données pour les rendre disponibles dans la partie
        extract($partData);
        
        // Chemin complet de la partie
        $partPath = VIEWS_PATH . '/parts/' . $part . '.php';
        
        // Vérifier si la partie existe
        if (!file_exists($partPath)) {
            throw new \Exception("Partie de vue non trouvée: {$part}");
        }
        
        // Inclure la partie
        include $partPath;
    }
    
    /**
     * Échapper une chaîne pour l'affichage HTML
     * 
     * @param string $string Chaîne à échapper
     * @return string Chaîne échappée
     */
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Formater une date
     * 
     * @param string $date Date à formater
     * @param string $format Format de sortie
     * @return string Date formatée
     */
    public static function formatDate($date, $format = 'd/m/Y H:i') {
        if (empty($date)) {
            return '';
        }
        
        $datetime = new \DateTime($date);
        return $datetime->format($format);
    }
    
    /**
     * Tronquer une chaîne à une longueur donnée
     * 
     * @param string $string Chaîne à tronquer
     * @param int $length Longueur maximale
     * @param string $append Texte à ajouter si la chaîne est tronquée
     * @return string Chaîne tronquée
     */
    public static function truncate($string, $length = 100, $append = '...') {
        if (strlen($string) <= $length) {
            return $string;
        }
        
        return substr($string, 0, $length) . $append;
    }
    
    /**
     * Générer une URL complète
     * 
     * @param string $path Chemin relatif
     * @return string URL complète
     */
    public static function url($path = '') {
        return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * Générer une URL pour un asset
     * 
     * @param string $path Chemin relatif de l'asset
     * @return string URL complète de l'asset
     */
    public static function asset($path) {
        return rtrim(ASSETS_URL, '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * Afficher un message de notification
     * 
     * @param string $type Type de message (success, info, warning, error)
     * @param string $message Contenu du message
     */
    public static function notification($type, $message) {
        echo '<div class="notification notification-' . $type . '">' . self::escape($message) . '</div>';
    }
    
    /**
     * Afficher les messages de notification stockés en session
     */
    public static function showNotifications() {
        if (isset($_SESSION['notifications'])) {
            foreach ($_SESSION['notifications'] as $notification) {
                self::notification($notification['type'], $notification['message']);
            }
            
            // Supprimer les notifications après les avoir affichées
            unset($_SESSION['notifications']);
        }
    }
    
    /**
     * Ajouter un message de notification en session
     * 
     * @param string $type Type de message (success, info, warning, error)
     * @param string $message Contenu du message
     */
    public static function addNotification($type, $message) {
        if (!isset($_SESSION['notifications'])) {
            $_SESSION['notifications'] = [];
        }
        
        $_SESSION['notifications'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
}
