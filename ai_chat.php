<?php
/**
 * Contrôleur pour la gestion des interactions avec l'IA
 */

// Inclure les fichiers nécessaires
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/chatgpt.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'classes/Database.php';
require_once 'classes/ChatGPT.php';
require_once 'classes/AIFeedback.php';
require_once 'classes/Session.php';
require_once 'classes/Pilot.php';
require_once 'classes/Moto.php';
require_once 'classes/Circuit.php';

// Vérifier si l'utilisateur est connecté
checkLogin();

// Initialiser les objets
$db = Database::getInstance();
$chatGPT = new ChatGPT();
$aiFeedback = new AIFeedback();
$sessionObj = new Session();
$pilotObj = new Pilot();
$motoObj = new Moto();
$circuitObj = new Circuit();

// Récupérer les données pour les formulaires
$sessions = $sessionObj->getAll();
$pilots = $pilotObj->getAll();
$motos = $motoObj->getAll();
$circuits = $circuitObj->getAll();

// Initialiser les variables
$messages = [];
$aiResponse = null;

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['problem']) && isset($_POST['problem_type'])) {
    // Récupérer les données du formulaire
    $problem = $_POST['problem'];
    $problemType = $_POST['problem_type'];
    $sessionId = isset($_POST['session_id']) && !empty($_POST['session_id']) ? $_POST['session_id'] : null;
    $motoId = $_POST['moto_id'];
    $circuitId = $_POST['circuit_id'];
    $pilotId = $_POST['pilot_id'];
    $weather = $_POST['weather'];
    $trackTemperature = $_POST['track_temperature'];
    $airTemperature = $_POST['air_temperature'];
    
    // Récupérer les détails des entités sélectionnées
    $moto = $motoObj->getById($motoId);
    $circuit = $circuitObj->getById($circuitId);
    $pilot = $pilotObj->getById($pilotId);
    
    // Préparer les données de session pour l'IA
    $sessionData = [
        'moto_brand' => $moto['brand'],
        'moto_model' => $moto['model'],
        'circuit_name' => $circuit['name'],
        'circuit_country' => $circuit['country'],
        'pilot_name' => $pilot['name'],
        'weather' => $weather,
        'track_temperature' => $trackTemperature,
        'air_temperature' => $airTemperature
    ];
    
    // Enregistrer le message de l'utilisateur
    $userId = $_SESSION['user_id'];
    $sql = "INSERT INTO chat_messages (user_id, content, is_user, session_id) VALUES (?, ?, 1, ?)";
    $db->execute($sql, [$userId, $problem, $sessionId]);
    
    // Obtenir des recommandations de l'IA
    $recommendations = $aiFeedback->getRecommendations($sessionId, $problem, $problemType);
    
    if ($recommendations['success']) {
        $aiResponse = $recommendations['recommendations'];
        
        // Enregistrer la réponse de l'IA
        $sql = "INSERT INTO chat_messages (user_id, content, is_user, session_id, ai_feedback_id) VALUES (?, ?, 0, ?, ?)";
        $db->execute($sql, [$userId, $aiResponse['solution'], $sessionId, $recommendations['feedback_id']]);
    } else {
        // Gérer l'erreur
        $error = $recommendations['message'];
    }
}

// Récupérer l'historique des messages
$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM chat_messages WHERE user_id = ? ORDER BY created_at ASC";
$messages = $db->fetchAll($sql, [$userId]);

// Inclure la vue
include 'pages/ai_chat.php';
