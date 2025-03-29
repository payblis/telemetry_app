<?php
require_once '../../includes/init.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;

class NotificationWebSocket implements \Ratchet\MessageComponentInterface {
    protected $clients;
    protected $userConnections;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
    }

    public function onOpen(\Ratchet\ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Nouvelle connexion! ({$conn->resourceId})\n";
    }

    public function onMessage(\Ratchet\ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if (!$data) {
            $from->send(json_encode(['error' => 'Message invalide']));
            return;
        }

        switch ($data['type']) {
            case 'authenticate':
                $this->handleAuthentication($from, $data);
                break;

            case 'ping':
                $from->send(json_encode(['type' => 'pong']));
                break;

            default:
                $from->send(json_encode(['error' => 'Type de message non supporté']));
        }
    }

    public function onClose(\Ratchet\ConnectionInterface $conn) {
        $this->clients->detach($conn);
        
        // Suppression de la connexion de l'utilisateur
        foreach ($this->userConnections as $userId => $connection) {
            if ($connection === $conn) {
                unset($this->userConnections[$userId]);
                break;
            }
        }

        echo "Connexion {$conn->resourceId} fermée\n";
    }

    public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
        echo "Une erreur est survenue: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function handleAuthentication($conn, $data) {
        if (!isset($data['token'])) {
            $conn->send(json_encode(['error' => 'Token manquant']));
            return;
        }

        try {
            // Vérification du token
            $userId = $notification->validateWebSocketToken($data['token']);
            
            if ($userId) {
                // Stockage de la connexion
                $this->userConnections[$userId] = $conn;
                
                // Envoi d'un message de confirmation
                $conn->send(json_encode([
                    'type' => 'authenticated',
                    'userId' => $userId
                ]));

                // Envoi des notifications en attente
                $this->sendPendingNotifications($conn, $userId);
            } else {
                $conn->send(json_encode(['error' => 'Token invalide']));
            }
        } catch (Exception $e) {
            error_log('Erreur d\'authentification WebSocket : ' . $e->getMessage());
            $conn->send(json_encode(['error' => 'Erreur d\'authentification']));
        }
    }

    protected function sendPendingNotifications($conn, $userId) {
        try {
            $notifications = $notification->getNewNotifications($userId);
            if (!empty($notifications)) {
                $conn->send(json_encode([
                    'type' => 'notifications',
                    'notifications' => $notifications
                ]));
            }

            $unread_count = $notification->getUnreadCount($userId);
            $conn->send(json_encode([
                'type' => 'unread_count',
                'count' => $unread_count
            ]));
        } catch (Exception $e) {
            error_log('Erreur lors de l\'envoi des notifications en attente : ' . $e->getMessage());
        }
    }

    public function sendToUser($userId, $message) {
        if (isset($this->userConnections[$userId])) {
            $this->userConnections[$userId]->send(json_encode($message));
        }
    }

    public function broadcast($message, $excludeUserId = null) {
        foreach ($this->userConnections as $userId => $conn) {
            if ($userId !== $excludeUserId) {
                $conn->send(json_encode($message));
            }
        }
    }
}

// Création du serveur WebSocket
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new NotificationWebSocket()
        )
    ),
    8080
);

echo "Serveur WebSocket démarré sur le port 8080\n";
$server->run(); 