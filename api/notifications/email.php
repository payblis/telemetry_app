<?php
require_once '../../includes/init.php';
require_once '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

if (!$data || !isset($data['subject']) || !isset($data['message'])) {
    http_response_code(400);
    exit(json_encode([
        'success' => false,
        'error' => 'Données invalides'
    ]));
}

try {
    // Configuration de PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->isHTML(true);

    // Récupération des destinataires
    $recipients = [];
    if (isset($data['userId'])) {
        // Envoi à un utilisateur spécifique
        $user_data = $user->getById($data['userId']);
        if ($user_data && $user_data['email_notifications']) {
            $recipients[] = [
                'id' => $user_data['id'],
                'email' => $user_data['email'],
                'name' => $user_data['name']
            ];
        }
    } else {
        // Envoi à tous les utilisateurs qui ont activé les notifications par email
        $recipients = $user->getAllWithEmailNotifications();
    }

    // Préparation du contenu de l'email
    $subject = $data['subject'];
    $htmlMessage = $notification->getEmailTemplate($data['template'] ?? 'default', [
        'subject' => $subject,
        'message' => $data['message'],
        'data' => $data['data'] ?? [],
        'user' => $_SESSION['user']
    ]);

    // Création de la version texte du message
    $textMessage = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $data['message']));

    // Envoi des emails
    $results = [];
    foreach ($recipients as $recipient) {
        try {
            $mail->clearAddresses();
            $mail->addAddress($recipient['email'], $recipient['name']);
            $mail->Subject = $subject;
            $mail->Body = $htmlMessage;
            $mail->AltBody = $textMessage;

            // Ajout des pièces jointes si présentes
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    if (isset($attachment['path']) && file_exists($attachment['path'])) {
                        $mail->addAttachment(
                            $attachment['path'],
                            $attachment['name'] ?? basename($attachment['path'])
                        );
                    }
                }
            }

            // Envoi de l'email
            $mail->send();

            $results[] = [
                'user_id' => $recipient['id'],
                'email' => $recipient['email'],
                'status' => 'success'
            ];
        } catch (Exception $e) {
            $results[] = [
                'user_id' => $recipient['id'],
                'email' => $recipient['email'],
                'status' => 'failed',
                'reason' => $e->getMessage()
            ];
        }
    }

    // Enregistrement de l'envoi dans la base de données
    $notificationId = $notification->create([
        'type' => 'email',
        'title' => $subject,
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
    error_log('Erreur lors de l\'envoi des notifications par email: ' . $e->getMessage());
    http_response_code(500);
    exit(json_encode([
        'success' => false,
        'error' => 'Erreur interne du serveur'
    ]));
} 