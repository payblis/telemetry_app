<?php
require_once '../config.php';
require_once '../database.php';

header('Content-Type: application/json');

// Vérifier si une session est active
if (!isset($_SESSION['current_session_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Aucune session active']);
    exit;
}

// Récupérer les données POST
$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'] ?? '';

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message vide']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Récupérer les informations de la session
    $stmt = $db->prepare("SELECT * FROM sessions WHERE id = ?");
    $stmt->execute([$_SESSION['current_session_id']]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtenir la réponse de ChatGPT
    $ai_response = getChatGPTResponse($message, $session);
    
    // Sauvegarder la conversation
    $stmt = $db->prepare("INSERT INTO chat_messages (session_id, user_message, ia_message) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['current_session_id'], $message, $ai_response]);
    
    echo json_encode([
        'success' => true,
        'response' => $ai_response
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function getChatGPTResponse($message, $session) {
    $client = new \GuzzleHttp\Client();
    
    $system_message = "Vous êtes un expert en moto et en réglages techniques pour la piste. ";
    $system_message .= "Vous connaissez parfaitement le circuit " . $session['nom_circuit'] . " ";
    $system_message .= "avec les caractéristiques suivantes : " . $session['donnees_circuit'];
    
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
                    'content' => $system_message
                ],
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ]
        ]
    ]);
    
    $result = json_decode($response->getBody(), true);
    return $result['choices'][0]['message']['content'];
}
?> 