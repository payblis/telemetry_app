<?php
/**
 * Fonctions utilitaires pour l'application
 */

/**
 * Nettoyer une entrée utilisateur
 * @param string $data Donnée à nettoyer
 * @return string Donnée nettoyée
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Rediriger vers une URL
 * @param string $url URL de redirection
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Générer un jeton CSRF
 * @return string Jeton CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier un jeton CSRF
 * @param string $token Jeton à vérifier
 * @return bool Résultat de la vérification
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Afficher un message d'alerte
 * @param string $message Message à afficher
 * @param string $type Type d'alerte (success, danger, warning, info)
 */
function setAlert($message, $type = 'info') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Récupérer et afficher un message d'alerte
 * @return string HTML du message d'alerte
 */
function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        
        return '<div class="alert alert-' . $alert['type'] . '">' . $alert['message'] . '</div>';
    }
    return '';
}

/**
 * Formater une date
 * @param string $date Date à formater
 * @param string $format Format souhaité
 * @return string Date formatée
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Vérifier si une chaîne est un JSON valide
 * @param string $string Chaîne à vérifier
 * @return bool Résultat de la vérification
 */
function isValidJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Tronquer un texte à une certaine longueur
 * @param string $text Texte à tronquer
 * @param int $length Longueur maximale
 * @param string $append Texte à ajouter à la fin
 * @return string Texte tronqué
 */
function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length) . $append;
    }
    return $text;
}

/**
 * Générer une chaîne aléatoire
 * @param int $length Longueur de la chaîne
 * @return string Chaîne aléatoire
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Vérifier si une requête est en AJAX
 * @return bool Résultat de la vérification
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Envoyer une réponse JSON
 * @param array $data Données à envoyer
 * @param int $status Code de statut HTTP
 */
function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
