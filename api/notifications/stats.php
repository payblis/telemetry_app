<?php
require_once '../../includes/init.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

try {
    // Récupération des statistiques
    $stats = [
        'total' => $notification->getTotalNotifications($_SESSION['user_id']),
        'unread' => $notification->getUnreadCount($_SESSION['user_id']),
        'by_type' => $notification->getNotificationsByType($_SESSION['user_id']),
        'by_date' => $notification->getNotificationsByDate($_SESSION['user_id']),
        'preferences' => [
            'email_enabled' => $notification->getEmailEnabledCount($_SESSION['user_id']),
            'push_enabled' => $notification->getPushEnabledCount($_SESSION['user_id'])
        ]
    ];

    echo json_encode($stats);
} catch (Exception $e) {
    error_log('Erreur lors de la récupération des statistiques : ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des statistiques']);
} 