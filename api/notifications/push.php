<?php
require_once '../../includes/init.php';
require_once '../../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode([
        'success' => false,
        'error' => 'Méthode non autorisée'
    ]));
}

// Vérification de l'authentification et des permissions
if (!isset($_SESSION['user_id']) || !$user->hasPermission('send_notifications')) {
    http_response_code(403);
    exit(json_encode([
        'success' => false,
        'error' => 'Accès non autorisé'
    ]));
}

// Récupération et validation des données
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['title']) || !isset($data['message'])) {
    http_response_code(400);
    exit(json_encode([
        'success' => false,
        'error' => 'Données invalides'
    ]));
}

try {
    // Configuration de WebPush
    $auth = [
        'VAPID' => [
            'subject' => VAPID_SUBJECT,
            'publicKey' => VAPID_PUBLIC_KEY,
            'privateKey' => VAPID_PRIVATE_KEY
        ]
    ];

    $webPush = new WebPush($auth);

    // Récupération des souscriptions
    $subscriptions = [];
    if (isset($data['userId'])) {
        // Envoi à un utilisateur spécifique
        $subscriptions = $notification->getUserSubscriptions($data['userId']);
    } else {
        // Envoi à tous les utilisateurs
        $subscriptions = $notification->getAllSubscriptions();
    }

    // Préparation de la notification
    $notificationData = [
        'title' => $data['title'],
        'message' => $data['message'],
        'icon' => $data['icon'] ?? null,
        'badge' => $data['badge'] ?? null,
        'tag' => $data['tag'] ?? 'default',
        'data' => $data['data'] ?? [],
        'actions' => $data['actions'] ?? [],
        'requireInteraction' => $data['requireInteraction'] ?? false,
        'renotify' => $data['renotify'] ?? false,
        'silent' => $data['silent'] ?? false,
        'timestamp' => time()
    ];

    // Envoi des notifications
    $results = [];
    foreach ($subscriptions as $subscription) {
        // Conversion de la souscription
        $sub = Subscription::create([
            'endpoint' => $subscription['endpoint'],
            'publicKey' => $subscription['public_key'],
            'authToken' => $subscription['auth_token'],
            'contentEncoding' => $subscription['content_encoding']
        ]);

        // Envoi de la notification
        $result = $webPush->sendOneNotification(
            $sub,
            json_encode($notificationData)
        );

        // Traitement du résultat
        if ($result->isSuccess()) {
            $results[] = [
                'subscription' => $subscription['id'],
                'status' => 'success'
            ];
        } else {
            $results[] = [
                'subscription' => $subscription['id'],
                'status' => 'failed',
                'reason' => $result->getReason()
            ];

            // Si la souscription n'est plus valide, la supprimer
            if ($result->isSubscriptionExpired()) {
                $notification->deleteSubscription($subscription['id']);
            }
        }
    }

    // Enregistrement de l'envoi dans la base de données
    $notificationId = $notification->create([
        'type' => 'push',
        'title' => $data['title'],
        'message' => $data['message'],
        'data' => json_encode($data['data'] ?? []),
        'sent_at' => date('Y-m-d H:i:s'),
        'sent_by' => $_SESSION['user_id']
    ]);

    // Enregistrement des résultats d'envoi
    foreach ($results as $result) {
        $notification->trackDelivery($notificationId, $result);
    }

    // Réponse de succès
    exit(json_encode([
        'success' => true,
        'notification_id' => $notificationId,
        'results' => $results
    ]));

} catch (Exception $e) {
    error_log('Erreur lors de l\'envoi des notifications push: ' . $e->getMessage());
    http_response_code(500);
    exit(json_encode([
        'success' => false,
        'error' => 'Erreur interne du serveur'
    ]));
} 