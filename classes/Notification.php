<?php

class Notification {
    private $db;
    private $vapidKeys;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->vapidKeys = [
            'public' => VAPID_PUBLIC_KEY,
            'private' => VAPID_PRIVATE_KEY
        ];
    }

    /**
     * Récupère les préférences de notification d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Préférences de notification
     */
    public function getPreferences($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM notification_preferences 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        
        if ($preferences = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $preferences;
        }

        // Valeurs par défaut si aucune préférence n'existe
        return [
            'email_enabled' => true,
            'push_enabled' => false,
            'notify_session_analysis' => true,
            'notify_performance_alerts' => true,
            'notify_maintenance' => true,
            'notify_weather' => true,
            'notify_events' => true,
            'daily_summary' => false,
            'weekly_report' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00'
        ];
    }

    /**
     * Met à jour les préférences de notification d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param array $preferences Nouvelles préférences
     * @return bool Succès de la mise à jour
     */
    public function updatePreferences($userId, $preferences) {
        $stmt = $this->db->prepare("
            INSERT INTO notification_preferences (
                user_id, email_enabled, push_enabled, 
                notify_session_analysis, notify_performance_alerts,
                notify_maintenance, notify_weather, notify_events,
                daily_summary, weekly_report,
                quiet_hours_start, quiet_hours_end
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            ) ON DUPLICATE KEY UPDATE
                email_enabled = VALUES(email_enabled),
                push_enabled = VALUES(push_enabled),
                notify_session_analysis = VALUES(notify_session_analysis),
                notify_performance_alerts = VALUES(notify_performance_alerts),
                notify_maintenance = VALUES(notify_maintenance),
                notify_weather = VALUES(notify_weather),
                notify_events = VALUES(notify_events),
                daily_summary = VALUES(daily_summary),
                weekly_report = VALUES(weekly_report),
                quiet_hours_start = VALUES(quiet_hours_start),
                quiet_hours_end = VALUES(quiet_hours_end)
        ");

        return $stmt->execute([
            $userId,
            $preferences['email_enabled'],
            $preferences['push_enabled'],
            $preferences['notify_session_analysis'],
            $preferences['notify_performance_alerts'],
            $preferences['notify_maintenance'],
            $preferences['notify_weather'],
            $preferences['notify_events'],
            $preferences['daily_summary'],
            $preferences['weekly_report'],
            $preferences['quiet_hours_start'],
            $preferences['quiet_hours_end']
        ]);
    }

    /**
     * Enregistre un nouvel abonnement aux notifications push
     * 
     * @param int $userId ID de l'utilisateur
     * @param array $subscription Données d'abonnement
     * @return bool Succès de l'enregistrement
     */
    public function savePushSubscription($userId, $subscription) {
        $stmt = $this->db->prepare("
            INSERT INTO push_subscriptions (
                user_id, endpoint, auth_key, p256dh_key
            ) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                auth_key = VALUES(auth_key),
                p256dh_key = VALUES(p256dh_key)
        ");

        return $stmt->execute([
            $userId,
            $subscription['endpoint'],
            $subscription['keys']['auth'],
            $subscription['keys']['p256dh']
        ]);
    }

    /**
     * Supprime un abonnement aux notifications push
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $endpoint Point de terminaison
     * @return bool Succès de la suppression
     */
    public function removePushSubscription($userId, $endpoint) {
        $stmt = $this->db->prepare("
            DELETE FROM push_subscriptions 
            WHERE user_id = ? AND endpoint = ?
        ");

        return $stmt->execute([$userId, $endpoint]);
    }

    /**
     * Envoie une notification à un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $type Type de notification
     * @param array $data Données de la notification
     * @return bool Succès de l'envoi
     */
    public function sendNotification($userId, $type, $data) {
        $preferences = $this->getPreferences($userId);
        $success = true;

        // Vérification des heures calmes
        if ($this->isQuietHours($preferences)) {
            return false;
        }

        // Vérification si le type de notification est activé
        $preferenceKey = 'notify_' . $type;
        if (isset($preferences[$preferenceKey]) && !$preferences[$preferenceKey]) {
            return false;
        }

        // Envoi par email si activé
        if ($preferences['email_enabled']) {
            $success &= $this->sendEmailNotification($userId, $type, $data);
        }

        // Envoi par push si activé
        if ($preferences['push_enabled']) {
            $success &= $this->sendPushNotification($userId, $type, $data);
        }

        // Enregistrement de la notification dans la base de données
        $this->saveNotification($userId, $type, $data);

        return $success;
    }

    /**
     * Envoie une notification par email
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $type Type de notification
     * @param array $data Données de la notification
     * @return bool Succès de l'envoi
     */
    private function sendEmailNotification($userId, $type, $data) {
        // Récupération de l'email de l'utilisateur
        $stmt = $this->db->prepare("
            SELECT email FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        // Configuration du template en fonction du type
        $template = $this->getEmailTemplate($type);
        $subject = $this->getEmailSubject($type);

        // Préparation du contenu
        $content = $this->renderEmailTemplate($template, $data);

        // Envoi de l'email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($user['email']);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $content;

            return $mail->send();
        } catch (Exception $e) {
            error_log("Erreur envoi email : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie une notification push
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $type Type de notification
     * @param array $data Données de la notification
     * @return bool Succès de l'envoi
     */
    private function sendPushNotification($userId, $type, $data) {
        // Récupération des abonnements push de l'utilisateur
        $stmt = $this->db->prepare("
            SELECT * FROM push_subscriptions 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($subscriptions)) {
            return false;
        }

        $success = true;
        foreach ($subscriptions as $subscription) {
            try {
                $webPush = new WebPush([
                    'VAPID' => [
                        'subject' => 'mailto:' . SMTP_FROM_EMAIL,
                        'publicKey' => $this->vapidKeys['public'],
                        'privateKey' => $this->vapidKeys['private']
                    ]
                ]);

                $notification = [
                    'title' => $this->getPushTitle($type),
                    'body' => $this->getPushBody($type, $data),
                    'icon' => '/assets/images/icon-192x192.png',
                    'badge' => '/assets/images/badge.png',
                    'data' => $data
                ];

                $webPush->sendNotification(
                    Subscription::create([
                        'endpoint' => $subscription['endpoint'],
                        'keys' => [
                            'p256dh' => $subscription['p256dh_key'],
                            'auth' => $subscription['auth_key']
                        ]
                    ]),
                    json_encode($notification)
                );
            } catch (Exception $e) {
                error_log("Erreur envoi push : " . $e->getMessage());
                $success = false;

                // Si l'abonnement n'est plus valide, on le supprime
                if (strpos($e->getMessage(), 'expired') !== false || 
                    strpos($e->getMessage(), 'unsubscribed') !== false) {
                    $this->removePushSubscription($userId, $subscription['endpoint']);
                }
            }
        }

        return $success;
    }

    /**
     * Enregistre une notification dans la base de données
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $type Type de notification
     * @param array $data Données de la notification
     * @return bool Succès de l'enregistrement
     */
    private function saveNotification($userId, $type, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (
                user_id, type, data, created_at
            ) VALUES (?, ?, ?, NOW())
        ");

        return $stmt->execute([
            $userId,
            $type,
            json_encode($data)
        ]);
    }

    /**
     * Vérifie si on est dans les heures calmes
     * 
     * @param array $preferences Préférences de l'utilisateur
     * @return bool True si on est dans les heures calmes
     */
    private function isQuietHours($preferences) {
        $now = new DateTime();
        $start = DateTime::createFromFormat('H:i', $preferences['quiet_hours_start']);
        $end = DateTime::createFromFormat('H:i', $preferences['quiet_hours_end']);
        $current = DateTime::createFromFormat('H:i', $now->format('H:i'));

        if ($start > $end) {
            return !($current >= $end && $current < $start);
        } else {
            return $current >= $start && $current < $end;
        }
    }

    /**
     * Récupère le template d'email pour un type de notification
     * 
     * @param string $type Type de notification
     * @return string Template HTML
     */
    private function getEmailTemplate($type) {
        $templates = [
            'session_analysis' => 'emails/session_analysis.html',
            'performance_alerts' => 'emails/performance_alert.html',
            'maintenance' => 'emails/maintenance_reminder.html',
            'weather' => 'emails/weather_alert.html',
            'events' => 'emails/event_notification.html',
            'daily_summary' => 'emails/daily_summary.html',
            'weekly_report' => 'emails/weekly_report.html'
        ];

        $template = isset($templates[$type]) ? $templates[$type] : 'emails/default.html';
        return file_get_contents(__DIR__ . '/../templates/' . $template);
    }

    /**
     * Récupère le sujet de l'email pour un type de notification
     * 
     * @param string $type Type de notification
     * @return string Sujet de l'email
     */
    private function getEmailSubject($type) {
        $subjects = [
            'session_analysis' => 'Nouvelle analyse de session disponible',
            'performance_alerts' => 'Alerte de performance',
            'maintenance' => 'Rappel de maintenance',
            'weather' => 'Alerte météo',
            'events' => 'Nouvel événement',
            'daily_summary' => 'Résumé quotidien',
            'weekly_report' => 'Rapport hebdomadaire'
        ];

        return isset($subjects[$type]) ? $subjects[$type] : 'Notification';
    }

    /**
     * Récupère le titre de la notification push
     * 
     * @param string $type Type de notification
     * @return string Titre de la notification
     */
    private function getPushTitle($type) {
        $titles = [
            'session_analysis' => 'Nouvelle analyse disponible',
            'performance_alerts' => 'Alerte performance',
            'maintenance' => 'Maintenance',
            'weather' => 'Alerte météo',
            'events' => 'Événement',
            'daily_summary' => 'Résumé du jour',
            'weekly_report' => 'Rapport hebdomadaire'
        ];

        return isset($titles[$type]) ? $titles[$type] : 'Notification';
    }

    /**
     * Génère le corps de la notification push
     * 
     * @param string $type Type de notification
     * @param array $data Données de la notification
     * @return string Corps de la notification
     */
    private function getPushBody($type, $data) {
        switch ($type) {
            case 'session_analysis':
                return "L'analyse de votre session du " . $data['date'] . " est disponible.";
            
            case 'performance_alerts':
                return "Changement significatif de performance détecté : " . $data['message'];
            
            case 'maintenance':
                return "Rappel : " . $data['message'];
            
            case 'weather':
                return "Conditions météo pour " . $data['circuit'] . " : " . $data['conditions'];
            
            case 'events':
                return "Nouvel événement : " . $data['title'];
            
            case 'daily_summary':
                return "Votre résumé quotidien est disponible.";
            
            case 'weekly_report':
                return "Votre rapport hebdomadaire est disponible.";
            
            default:
                return $data['message'] ?? "Nouvelle notification";
        }
    }

    /**
     * Remplace les variables dans le template d'email
     * 
     * @param string $template Template HTML
     * @param array $data Données à insérer
     * @return string Template complété
     */
    private function renderEmailTemplate($template, $data) {
        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }
} 