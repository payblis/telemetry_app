<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Vérifier que la requête est bien une requête AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    header('HTTP/1.0 403 Forbidden');
    exit('Accès interdit');
}

// Vérifier l'authentification
if (!$auth->isLoggedIn()) {
    header('HTTP/1.0 401 Unauthorized');
    exit('Non autorisé');
}

// Vérifier l'ID de la session
if (!isset($_GET['session_id']) || !is_numeric($_GET['session_id'])) {
    header('HTTP/1.0 400 Bad Request');
    exit('ID de session invalide');
}

$sessionId = (int)$_GET['session_id'];

// Vérifier les permissions d'accès
if (!canAccessSession($sessionId)) {
    header('HTTP/1.0 403 Forbidden');
    exit('Accès non autorisé à cette session');
}

try {
    // Récupérer les tours de la session
    $stmt = $pdo->prepare("
        SELECT id 
        FROM tours 
        WHERE session_id = ? 
        ORDER BY numero_tour ASC
    ");
    $stmt->execute([$sessionId]);
    $tours = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Initialiser les tableaux de données
    $data = [
        'speed' => [],
        'rpm' => [],
        'suspensionFront' => [],
        'suspensionRear' => [],
        'temperatureFront' => [],
        'temperatureRear' => []
    ];
    
    // Récupérer les données télémétriques pour chaque tour
    foreach ($tours as $tourId) {
        $stmt = $pdo->prepare("
            SELECT 
                timestamp,
                vitesse,
                regime_moteur,
                suspension_avant_position,
                suspension_arriere_position,
                temperature_pneu_avant,
                temperature_pneu_arriere
            FROM telemetry_data
            WHERE tour_id = ?
            ORDER BY timestamp ASC
        ");
        $stmt->execute([$tourId]);
        
        while ($row = $stmt->fetch()) {
            $timestamp = strtotime($row['timestamp']) * 1000; // Convertir en millisecondes pour JavaScript
            
            $data['speed'][] = [
                'x' => $timestamp,
                'y' => $row['vitesse']
            ];
            
            $data['rpm'][] = [
                'x' => $timestamp,
                'y' => $row['regime_moteur']
            ];
            
            $data['suspensionFront'][] = [
                'x' => $timestamp,
                'y' => $row['suspension_avant_position']
            ];
            
            $data['suspensionRear'][] = [
                'x' => $timestamp,
                'y' => $row['suspension_arriere_position']
            ];
            
            $data['temperatureFront'][] = [
                'x' => $timestamp,
                'y' => $row['temperature_pneu_avant']
            ];
            
            $data['temperatureRear'][] = [
                'x' => $timestamp,
                'y' => $row['temperature_pneu_arriere']
            ];
        }
    }
    
    // Envoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch (PDOException $e) {
    logCustomError('Erreur lors de la récupération des données télémétriques', ['error' => $e->getMessage()]);
    header('HTTP/1.0 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'error' => 'Une erreur est survenue lors de la récupération des données'
    ]);
} 