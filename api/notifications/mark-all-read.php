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

try {
    // Marquage de toutes les notifications comme lues
    $notification->markAllAsRead($_SESSION['user_id']);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('Erreur lors du marquage de toutes les notifications comme lues : ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors du marquage de toutes les notifications comme lues']);
} 