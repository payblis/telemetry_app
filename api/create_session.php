<?php
require_once '../config.php';
require_once '../database.php';

header('Content-Type: application/json');

// Récupérer les données POST
$data = json_decode(file_get_contents('php://input'), true);
$circuit = $data['circuit'] ?? '';

if (empty($circuit)) {
    http_response_code(400);
    echo json_encode(['error' => 'Circuit non spécifié']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Récupérer les informations du circuit via l'API OpenAI
    $circuit_info = getCircuitInfo($circuit);
    
    // Créer une nouvelle session
    $stmt = $db->prepare("INSERT INTO sessions (nom_circuit, donnees_circuit) VALUES (?, ?)");
    $stmt->execute([$circuit, json_encode($circuit_info)]);
    
    $session_id = $db->lastInsertId();
    $_SESSION['current_session_id'] = $session_id;
    
    echo json_encode([
        'success' => true,
        'session_id' => $session_id,
        'circuitInfo' => $circuit_info
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function getCircuitInfo($circuit) {
    $client = new \GuzzleHttp\Client();
    
    $response = $client->post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . OPENAI_API_KEY,
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Vous êtes un expert en circuits moto. Donnez des informations techniques précises sur le circuit demandé.'
                ],
                [
                    'role' => 'user',
                    'content' => "Donnez-moi les informations techniques du circuit {$circuit} : longueur, nombre de virages, type d'adhérence, conditions météo habituelles."
                ]
            ]
        ]
    ]);
    
    $result = json_decode($response->getBody(), true);
    return $result['choices'][0]['message']['content'];
}
?> 