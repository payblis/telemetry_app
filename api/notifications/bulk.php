<?php
require_once '../../includes/init.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Récupération et validation des données
$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data) || !isset($data['action']) || !isset($data['notification_ids'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

// Validation des IDs de notification
$notification_ids = array_filter($data['notification_ids'], function($id) {
    return is_numeric($id) && $id > 0;
});

if (empty($notification_ids)) {
    http_response_code(400);
    echo json_encode(['error' => 'Aucun ID de notification valide']);
    exit;
}

try {
    // Vérification que toutes les notifications appartiennent à l'utilisateur
    foreach ($notification_ids as $id) {
        if (!$notification->belongsToUser($id, $_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès non autorisé']);
            exit;
        }
    }

    // Exécution de l'action demandée
    switch ($data['action']) {
        case 'mark_read':
            $notification->markMultipleAsRead($notification_ids);
            $message = 'Notifications marquées comme lues';
            break;

        case 'mark_unread':
            $notification->markMultipleAsUnread($notification_ids);
            $message = 'Notifications marquées comme non lues';
            break;

        case 'delete':
            $notification->deleteMultipleNotifications($notification_ids);
            $message = 'Notifications supprimées';
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action non supportée']);
            exit;
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'affected_ids' => $notification_ids
    ]);
} catch (Exception $e) {
    error_log('Erreur lors de l\'action en masse sur les notifications : ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de l\'action en masse sur les notifications']);
} 