<?php

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier le rôle de l'utilisateur
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Fonction pour nettoyer les entrées utilisateur
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fonction pour générer un token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Fonction pour vérifier le token CSRF
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Fonction pour formater une date
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Fonction pour gérer les erreurs de l'API OpenAI
function handleOpenAIError($error) {
    error_log('Erreur OpenAI: ' . $error);
    return [
        'success' => false,
        'error' => 'Une erreur est survenue lors de la communication avec l\'IA'
    ];
}

// Fonction pour envoyer une requête à l'API OpenAI
function callOpenAI($prompt) {
    $apiKey = OPENAI_API_KEY;
    $url = 'https://api.openai.com/v1/chat/completions';
    
    $data = [
        'model' => 'gpt-4-turbo-preview',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'Vous êtes un expert en télémétrie moto qui aide à analyser les données et fournit des recommandations précises.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.7,
        'max_tokens' => 2000
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        return handleOpenAIError($err);
    }
    
    $result = json_decode($response, true);
    if (isset($result['error'])) {
        return handleOpenAIError($result['error']['message']);
    }
    
    return [
        'success' => true,
        'response' => $result['choices'][0]['message']['content']
    ];
}

// Fonction pour sauvegarder les données télémétriques
function saveTelemetryData($sessionId, $data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO telemetry_data 
            (tour_id, timestamp, vitesse, regime_moteur, angle_inclinaison, 
             temperature_pneu_avant, temperature_pneu_arriere,
             suspension_avant_position, suspension_arriere_position) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
        return $stmt->execute([
            $data['tour_id'],
            $data['timestamp'],
            $data['vitesse'],
            $data['regime_moteur'],
            $data['angle_inclinaison'],
            $data['temperature_pneu_avant'],
            $data['temperature_pneu_arriere'],
            $data['suspension_avant_position'],
            $data['suspension_arriere_position']
        ]);
    } catch (PDOException $e) {
        error_log('Erreur lors de la sauvegarde des données télémétriques: ' . $e->getMessage());
        return false;
    }
}

// Fonction pour récupérer les statistiques d'une session
function getSessionStats($sessionId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                MIN(temps_tour) as meilleur_tour,
                AVG(temps_tour) as moyenne_tours,
                COUNT(*) as nombre_tours
            FROM tours 
            WHERE session_id = ?
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
        return false;
    }
}

// Fonction pour formater le temps au format mm:ss.xxx
function formatLapTime($time) {
    $minutes = floor($time / 60);
    $seconds = $time % 60;
    return sprintf("%02d:%06.3f", $minutes, $seconds);
}

// Fonction pour vérifier les permissions d'accès à une session
function canAccessSession($sessionId) {
    global $pdo;
    
    if (hasRole('admin')) {
        return true;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT user_id 
            FROM sessions s
            JOIN pilotes p ON s.pilote_id = p.id
            WHERE s.id = ? AND p.user_id = ?
        ");
        $stmt->execute([$sessionId, $_SESSION['user_id']]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erreur lors de la vérification des permissions: ' . $e->getMessage());
        return false;
    }
} 