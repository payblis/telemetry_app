<?php
require_once '../../includes/init.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

// Récupération des préférences
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $preferences = $notification->getPreferences($_SESSION['user_id']);
        echo json_encode($preferences);
    } catch (Exception $e) {
        error_log('Erreur lors de la récupération des préférences : ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la récupération des préférences']);
    }
}

// Mise à jour des préférences
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }

    // Validation des champs requis
    $required_fields = [
        'email_enabled',
        'push_enabled',
        'session_analysis',
        'performance_alerts',
        'maintenance',
        'weather',
        'events',
        'daily_summary',
        'weekly_report',
        'quiet_hours_start',
        'quiet_hours_end'
    ];

    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => 'Données incomplètes']);
            exit;
        }
    }

    // Validation des heures calmes
    if ($data['quiet_hours_start'] >= $data['quiet_hours_end']) {
        http_response_code(400);
        echo json_encode(['error' => 'L\'heure de fin doit être postérieure à l\'heure de début']);
        exit;
    }

    try {
        // Mise à jour des préférences
        $notification->updatePreferences($_SESSION['user_id'], $data);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log('Erreur lors de la mise à jour des préférences : ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la mise à jour des préférences']);
    }
}

// Méthode non autorisée
else {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
} 