<?php
require_once '../../includes/init.php';

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode([
        'success' => false,
        'error' => 'Méthode non autorisée'
    ]));
}

// Récupération et validation des données
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['action']) || !isset($data['notificationId'])) {
    http_response_code(400);
    exit(json_encode([
        'success' => false,
        'error' => 'Données invalides'
    ]));
}

try {
    // Vérification de l'authentification
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        exit(json_encode([
            'success' => false,
            'error' => 'Non authentifié'
        ]));
    }

    // Vérification que la notification appartient à l'utilisateur
    $notificationId = filter_var($data['notificationId'], FILTER_VALIDATE_INT);
    if (!$notificationId || !$notification->belongsToUser($notificationId, $_SESSION['user_id'])) {
        http_response_code(403);
        exit(json_encode([
            'success' => false,
            'error' => 'Accès non autorisé'
        ]));
    }

    // Traitement de l'action
    switch ($data['action']) {
        case 'view':
            // Marquer comme vue
            $notification->markAsViewed($notificationId);
            break;

        case 'click':
            // Marquer comme cliquée
            $notification->markAsClicked($notificationId);
            break;

        case 'close':
            // Marquer comme fermée
            $notification->markAsClosed($notificationId);
            break;

        default:
            throw new Exception('Action non supportée');
    }

    // Enregistrement des données de suivi
    $notification->trackAction($notificationId, [
        'action' => $data['action'],
        'timestamp' => time(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'platform' => $data['platform'] ?? null,
        'device_type' => $data['deviceType'] ?? null
    ]);

    // Réponse de succès
    exit(json_encode([
        'success' => true
    ]));

} catch (Exception $e) {
    error_log('Erreur lors du suivi de la notification: ' . $e->getMessage());
    http_response_code(500);
    exit(json_encode([
        'success' => false,
        'error' => 'Erreur interne du serveur'
    ]));
} 