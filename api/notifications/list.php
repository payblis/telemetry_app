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

// Récupération des paramètres de pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;

// Validation des paramètres
if ($page < 1 || $per_page < 1 || $per_page > 100) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres de pagination invalides']);
    exit;
}

try {
    // Calcul de l'offset
    $offset = ($page - 1) * $per_page;

    // Récupération des notifications
    $notifications = $notification->getNotifications($_SESSION['user_id'], $per_page, $offset);
    
    // Récupération du nombre total de notifications
    $total_notifications = $notification->getTotalNotifications($_SESSION['user_id']);
    
    // Calcul du nombre total de pages
    $total_pages = ceil($total_notifications / $per_page);

    // Préparation de la réponse
    $response = [
        'notifications' => $notifications,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $per_page,
            'total_pages' => $total_pages,
            'total_notifications' => $total_notifications
        ]
    ];

    echo json_encode($response);
} catch (Exception $e) {
    error_log('Erreur lors de la récupération des notifications : ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des notifications']);
} 