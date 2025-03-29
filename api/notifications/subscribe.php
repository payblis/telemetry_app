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

if (!isset($data['subscription']) || !is_array($data['subscription'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

$subscription = $data['subscription'];

// Validation des champs requis
$required_fields = ['endpoint', 'keys'];
foreach ($required_fields as $field) {
    if (!isset($subscription[$field])) {
        http_response_code(400);
        echo json_encode(['error' => 'Données d\'abonnement incomplètes']);
        exit;
    }
}

try {
    // Sauvegarde de l'abonnement
    $notification->saveSubscription(
        $_SESSION['user_id'],
        $subscription['endpoint'],
        $subscription['keys']['p256dh'],
        $subscription['keys']['auth']
    );

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('Erreur lors de l\'enregistrement de l\'abonnement : ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de l\'enregistrement de l\'abonnement']);
} 