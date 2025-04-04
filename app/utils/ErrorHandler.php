<?php
/**
 * Classe utilitaire pour la gestion des erreurs et exceptions
 * 
 * Fournit des fonctionnalités pour la journalisation et l'affichage des erreurs
 */
namespace App\Utils;

class ErrorHandler {
    /**
     * Initialiser le gestionnaire d'erreurs
     */
    public static function init() {
        // Définir le gestionnaire d'exceptions
        set_exception_handler([self::class, 'handleException']);
        
        // Définir le gestionnaire d'erreurs
        set_error_handler([self::class, 'handleError']);
        
        // Définir le gestionnaire de fin d'exécution
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    /**
     * Gérer une exception
     * 
     * @param \Throwable $exception Exception à gérer
     */
    public static function handleException($exception) {
        // Journaliser l'exception
        self::logException($exception);
        
        // Afficher l'erreur selon le mode de débogage
        if (DEBUG_MODE) {
            self::displayException($exception);
        } else {
            self::displayErrorPage(500);
        }
    }
    
    /**
     * Gérer une erreur PHP
     * 
     * @param int $errno Niveau de l'erreur
     * @param string $errstr Message d'erreur
     * @param string $errfile Fichier où l'erreur s'est produite
     * @param int $errline Ligne où l'erreur s'est produite
     * @return bool True pour empêcher le gestionnaire d'erreurs standard de PHP de s'exécuter
     */
    public static function handleError($errno, $errstr, $errfile, $errline) {
        // Ignorer les erreurs qui sont supprimées par @
        if (error_reporting() === 0) {
            return true;
        }
        
        // Convertir l'erreur en exception
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    
    /**
     * Gérer la fin d'exécution du script
     */
    public static function handleShutdown() {
        // Récupérer la dernière erreur
        $error = error_get_last();
        
        // Vérifier s'il y a eu une erreur fatale
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            // Créer une exception à partir de l'erreur
            $exception = new \ErrorException(
                $error['message'], 
                0, 
                $error['type'], 
                $error['file'], 
                $error['line']
            );
            
            // Gérer l'exception
            self::handleException($exception);
        }
    }
    
    /**
     * Journaliser une exception
     * 
     * @param \Throwable $exception Exception à journaliser
     */
    protected static function logException($exception) {
        // Préparer le message de log
        $message = sprintf(
            "[%s] %s: %s in %s on line %d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        // Ajouter des informations sur la requête
        $message .= sprintf(
            "\nRequest: %s %s\nReferer: %s\nUser Agent: %s\nIP: %s",
            $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
            $_SERVER['REQUEST_URI'] ?? 'Unknown',
            $_SERVER['HTTP_REFERER'] ?? 'Unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        );
        
        // Chemin du fichier de log
        $logFile = ROOT_PATH . '/storage/logs/error_' . date('Y-m-d') . '.log';
        
        // Créer le répertoire de logs si nécessaire
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        // Écrire dans le fichier de log
        file_put_contents($logFile, $message . "\n\n", FILE_APPEND);
    }
    
    /**
     * Afficher une exception en mode débogage
     * 
     * @param \Throwable $exception Exception à afficher
     */
    protected static function displayException($exception) {
        // Nettoyer la sortie précédente
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        // Définir l'en-tête HTTP
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        
        // Afficher l'exception
        echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur Système</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; padding: 20px; }
        .error-container { max-width: 1000px; margin: 0 auto; background: #f8f8f8; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        .error-title { color: #e74c3c; margin-top: 0; }
        .error-message { font-size: 18px; margin-bottom: 20px; }
        .error-details { background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 3px; overflow: auto; }
        .error-location { margin: 15px 0; padding: 10px; background: #f1f1f1; border-left: 4px solid #e74c3c; }
        .error-trace { font-family: monospace; white-space: pre-wrap; font-size: 14px; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-title">Erreur Système</h1>
        <div class="error-message">' . htmlspecialchars($exception->getMessage()) . '</div>
        <div class="error-location">
            <strong>Type:</strong> ' . get_class($exception) . '<br>
            <strong>Fichier:</strong> ' . htmlspecialchars($exception->getFile()) . '<br>
            <strong>Ligne:</strong> ' . $exception->getLine() . '
        </div>
        <h2>Trace d\'exécution:</h2>
        <div class="error-details">
            <pre class="error-trace">' . htmlspecialchars($exception->getTraceAsString()) . '</pre>
        </div>
    </div>
</body>
</html>';
        
        // Terminer l'exécution
        exit;
    }
    
    /**
     * Afficher une page d'erreur
     * 
     * @param int $code Code d'erreur HTTP
     */
    public static function displayErrorPage($code) {
        // Nettoyer la sortie précédente
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        // Définir l'en-tête HTTP
        http_response_code($code);
        
        // Messages d'erreur selon le code
        $messages = [
            400 => 'Requête incorrecte',
            401 => 'Authentification requise',
            403 => 'Accès interdit',
            404 => 'Page non trouvée',
            500 => 'Erreur interne du serveur',
            503 => 'Service temporairement indisponible'
        ];
        
        // Message par défaut
        $message = $messages[$code] ?? 'Une erreur est survenue';
        
        // Chemin de la vue d'erreur
        $errorView = VIEWS_PATH . '/errors/' . $code . '.php';
        
        // Vérifier si une vue spécifique existe pour ce code d'erreur
        if (file_exists($errorView)) {
            include $errorView;
        } else {
            // Afficher une page d'erreur générique
            echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur ' . $code . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; padding: 20px; text-align: center; }
        .error-container { max-width: 600px; margin: 50px auto; background: #f8f8f8; border: 1px solid #ddd; padding: 30px; border-radius: 5px; }
        .error-code { font-size: 72px; margin: 0; color: #e74c3c; }
        .error-message { font-size: 24px; margin: 20px 0; }
        .error-description { margin-bottom: 30px; }
        .error-link { display: inline-block; padding: 10px 20px; background: #3498db; color: #fff; text-decoration: none; border-radius: 3px; }
        .error-link:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">' . $code . '</h1>
        <div class="error-message">' . htmlspecialchars($message) . '</div>
        <div class="error-description">
            Nous sommes désolés, mais une erreur s\'est produite lors du traitement de votre demande.
        </div>
        <a href="' . BASE_URL . '" class="error-link">Retour à l\'accueil</a>
    </div>
</body>
</html>';
        }
        
        // Terminer l'exécution
        exit;
    }
}
