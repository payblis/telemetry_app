<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

// Headers pour l'API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Inclure les classes nécessaires
require_once '../classes/TelemetryData.php';
require_once '../classes/Session.php';

// Initialiser les objets
$telemetry = new TelemetryData();
$sessionObj = new Session();

// Récupérer la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Récupérer l'action demandée
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Vérifier si l'ID de session est fourni
if (!isset($_GET['session_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de session manquant']);
    exit();
}

$sessionId = intval($_GET['session_id']);

// Vérifier si la session appartient à l'utilisateur
if (!$sessionObj->belongsToUser($sessionId, $_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé à cette session']);
    exit();
}

// Initialiser la session de télémétrie
$telemetry->initSession($sessionId, $_SESSION['user_id']);

// Router les requêtes
switch ($method) {
    case 'GET':
        handleGetRequest($action, $telemetry);
        break;
    
    case 'POST':
        handlePostRequest($action, $telemetry);
        break;
    
    case 'OPTIONS':
        http_response_code(200);
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}

/**
 * Gère les requêtes GET
 */
function handleGetRequest($action, $telemetry) {
    switch ($action) {
        case 'laps':
            $laps = $telemetry->getLaps();
            echo json_encode(['success' => true, 'data' => $laps]);
            break;

        case 'best_lap':
            $bestLap = $telemetry->getBestLap();
            echo json_encode(['success' => true, 'data' => $bestLap]);
            break;

        case 'stats':
            $stats = $telemetry->getSessionStats();
            echo json_encode(['success' => true, 'data' => $stats]);
            break;

        case 'data':
            $startTime = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d H:i:s', strtotime('-1 minute'));
            $endTime = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d H:i:s');
            $data = $telemetry->getTelemetryData($startTime, $endTime);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'export':
            $startTime = $_GET['start'] ?? '';
            $endTime = $_GET['end'] ?? '';
            if (!$startTime || !$endTime) {
                http_response_code(400);
                echo json_encode(['error' => 'Paramètres de temps manquants']);
                break;
            }
            $filename = $telemetry->exportToCsv($startTime, $endTime);
            if ($filename) {
                echo json_encode(['success' => true, 'filename' => $filename]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de l\'export']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action non reconnue']);
            break;
    }
}

/**
 * Gère les requêtes POST
 */
function handlePostRequest($action, $telemetry) {
    // Récupérer les données POST
    $postData = json_decode(file_get_contents('php://input'), true);

    if (!$postData) {
        http_response_code(400);
        echo json_encode(['error' => 'Données invalides']);
        return;
    }

    switch ($action) {
        case 'save_data':
            if ($telemetry->saveTelemetryData($postData)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de l\'enregistrement des données']);
            }
            break;

        case 'save_lap':
            if ($telemetry->saveLap($postData)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de l\'enregistrement du tour']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action non reconnue']);
            break;
    }
} 