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

// Récupération de l'ID de la notification
$notification_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($notification_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de notification invalide']);
    exit;
}

try {
    // Vérification que la notification appartient à l'utilisateur
    if (!$notification->belongsToUser($notification_id, $_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Accès non autorisé']);
        exit;
    }

    // Marquage de la notification comme lue
    $notification->markAsRead($notification_id);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('Erreur lors du marquage de la notification comme lue : ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors du marquage de la notification comme lue']);
} 