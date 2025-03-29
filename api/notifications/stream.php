<?php
require_once '../../includes/init.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Non authentifié');
}

// Configuration des en-têtes SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Désactive la mise en mémoire tampon pour Nginx

// Fonction pour envoyer un événement SSE
function sendEvent($event, $data) {
    echo "event: {$event}\n";
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

// Fonction pour vérifier si la connexion est toujours active
function isConnectionActive() {
    return connection_aborted() === 0;
}

try {
    // Envoi d'un événement de connexion initial
    sendEvent('connected', [
        'message' => 'Connexion SSE établie',
        'timestamp' => time()
    ]);

    // Boucle principale
    while (isConnectionActive()) {
        // Vérification des nouvelles notifications
        $notifications = $notification->getNewNotifications($_SESSION['user_id']);
        if (!empty($notifications)) {
            sendEvent('notifications', [
                'notifications' => $notifications
            ]);
        }

        // Vérification du nombre de notifications non lues
        $unread_count = $notification->getUnreadCount($_SESSION['user_id']);
        sendEvent('unread_count', [
            'count' => $unread_count
        ]);

        // Attente de 5 secondes avant la prochaine vérification
        sleep(5);
    }
} catch (Exception $e) {
    error_log('Erreur SSE : ' . $e->getMessage());
    sendEvent('error', [
        'message' => 'Une erreur est survenue',
        'timestamp' => time()
    ]);
} 