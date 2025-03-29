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

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

// Validation des champs requis
$required_fields = ['user_id', 'type', 'title', 'message'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => 'Données incomplètes']);
        exit;
    }
}

try {
    // Vérification des permissions (seul un administrateur peut envoyer des notifications)
    if (!$user->isAdmin($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Accès non autorisé']);
        exit;
    }

    // Vérification des préférences de l'utilisateur
    $preferences = $notification->getPreferences($data['user_id']);
    if (!$preferences['push_enabled']) {
        http_response_code(400);
        echo json_encode(['error' => 'Les notifications push sont désactivées pour cet utilisateur']);
        exit;
    }

    // Vérification des heures calmes
    $current_time = date('H:i:s');
    if ($current_time >= $preferences['quiet_hours_start'] && $current_time <= $preferences['quiet_hours_end']) {
        http_response_code(400);
        echo json_encode(['error' => 'Les notifications sont en mode silencieux pour cet utilisateur']);
        exit;
    }

    // Récupération de l'abonnement push
    $subscription = $notification->getSubscription($data['user_id']);
    if (!$subscription) {
        http_response_code(400);
        echo json_encode(['error' => 'Aucun abonnement push trouvé pour cet utilisateur']);
        exit;
    }

    // Préparation de la notification
    $notification_data = [
        'title' => $data['title'],
        'message' => $data['message'],
        'url' => $data['url'] ?? null,
        'icon' => $data['icon'] ?? '/assets/icons/notification.png',
        'badge' => $data['badge'] ?? '/assets/icons/badge.png',
        'data' => $data['data'] ?? [],
        'requireInteraction' => $data['requireInteraction'] ?? false,
        'silent' => $data['silent'] ?? false,
        'tag' => $data['tag'] ?? null,
        'renotify' => $data['renotify'] ?? true,
        'timestamp' => time()
    ];

    // Envoi de la notification push
    $result = $notification->sendPushNotification(
        $subscription['endpoint'],
        $subscription['p256dh'],
        $subscription['auth'],
        $notification_data
    );

    if ($result) {
        // Enregistrement de la notification dans la base de données
        $notification->createNotification(
            $data['user_id'],
            $data['type'],
            $data['title'],
            $data['message'],
            $data['url'] ?? null
        );

        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Échec de l\'envoi de la notification push');
    }
} catch (Exception $e) {
    error_log('Erreur lors de l\'envoi de la notification : ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de l\'envoi de la notification']);
} 