<?php
// Activation du rapport d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/error.log');

// Gestionnaire d'erreurs personnalisé
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_message = date('[Y-m-d H:i:s]') . " Erreur $errno : $errstr dans $errfile à la ligne $errline\n";
    error_log($error_message);
    
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo "<div style='background-color: #fee2e2; border: 1px solid #dc2626; color: #dc2626; padding: 1rem; margin: 1rem;'>";
        echo "<strong>Erreur :</strong> " . htmlspecialchars($errstr);
        echo "<br><strong>Fichier :</strong> " . htmlspecialchars($errfile);
        echo "<br><strong>Ligne :</strong> " . $errline;
        echo "</div>";
    } else {
        // En production, afficher un message générique
        echo "<div style='text-align: center; padding: 2rem;'>";
        echo "<h1>Une erreur est survenue</h1>";
        echo "<p>Nous nous excusons pour la gêne occasionnée. Notre équipe technique a été notifiée.</p>";
        echo "</div>";
    }
    
    return true;
}

// Gestionnaire d'exceptions personnalisé
function customExceptionHandler($exception) {
    $error_message = date('[Y-m-d H:i:s]') . " Exception non capturée : " . $exception->getMessage() . 
                    " dans " . $exception->getFile() . " à la ligne " . $exception->getLine() . "\n";
    error_log($error_message);
    
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo "<div style='background-color: #fee2e2; border: 1px solid #dc2626; color: #dc2626; padding: 1rem; margin: 1rem;'>";
        echo "<strong>Exception :</strong> " . htmlspecialchars($exception->getMessage());
        echo "<br><strong>Fichier :</strong> " . htmlspecialchars($exception->getFile());
        echo "<br><strong>Ligne :</strong> " . $exception->getLine();
        echo "<br><strong>Trace :</strong><pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        // En production, afficher un message générique
        echo "<div style='text-align: center; padding: 2rem;'>";
        echo "<h1>Une erreur est survenue</h1>";
        echo "<p>Nous nous excusons pour la gêne occasionnée. Notre équipe technique a été notifiée.</p>";
        echo "</div>";
    }
}

// Gestionnaire d'erreurs fatales
function fatalErrorHandler() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $error_message = date('[Y-m-d H:i:s]') . " Erreur fatale : " . $error['message'] . 
                        " dans " . $error['file'] . " à la ligne " . $error['line'] . "\n";
        error_log($error_message);
        
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            echo "<div style='background-color: #fee2e2; border: 1px solid #dc2626; color: #dc2626; padding: 1rem; margin: 1rem;'>";
            echo "<strong>Erreur fatale :</strong> " . htmlspecialchars($error['message']);
            echo "<br><strong>Fichier :</strong> " . htmlspecialchars($error['file']);
            echo "<br><strong>Ligne :</strong> " . $error['line'];
            echo "</div>";
        } else {
            // En production, afficher un message générique
            echo "<div style='text-align: center; padding: 2rem;'>";
            echo "<h1>Une erreur critique est survenue</h1>";
            echo "<p>Nous nous excusons pour la gêne occasionnée. Notre équipe technique a été notifiée.</p>";
            echo "</div>";
        }
    }
}

// Enregistrement des gestionnaires
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');
register_shutdown_function('fatalErrorHandler');

// Fonction pour logger les erreurs personnalisées
function logCustomError($message, $context = []) {
    $error_message = date('[Y-m-d H:i:s]') . " Erreur personnalisée : " . $message;
    if (!empty($context)) {
        $error_message .= "\nContexte : " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    $error_message .= "\n";
    error_log($error_message);
}

// Fonction pour logger les accès
function logAccess($user_id, $action, $details = []) {
    $log_message = date('[Y-m-d H:i:s]') . " Utilisateur $user_id : $action";
    if (!empty($details)) {
        $log_message .= " - " . json_encode($details, JSON_UNESCAPED_UNICODE);
    }
    $log_message .= "\n";
    error_log($log_message, 3, LOGS_PATH . '/access.log');
}

// Fonction pour logger les tentatives de connexion
function logLoginAttempt($username, $success, $ip) {
    $log_message = date('[Y-m-d H:i:s]') . " Tentative de connexion - " .
                  "Utilisateur: $username, " .
                  "Succès: " . ($success ? 'Oui' : 'Non') . ", " .
                  "IP: $ip\n";
    error_log($log_message, 3, LOGS_PATH . '/login.log');
}

// Fonction pour nettoyer les anciens logs
function cleanOldLogs($days = 30) {
    $files = glob(LOGS_PATH . '/*.log');
    $now = time();
    
    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) >= 60 * 60 * 24 * $days) {
                unlink($file);
            }
        }
    }
} 