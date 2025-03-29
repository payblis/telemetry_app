<?php
/**
 * Contrôleur pour la validation des recommandations IA
 */

// Inclure les fichiers nécessaires
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'classes/Database.php';
require_once 'classes/AIFeedback.php';

// Vérifier si l'utilisateur est connecté
checkLogin();

// Initialiser les objets
$aiFeedback = new AIFeedback();

// Vérifier si les paramètres sont présents
if (isset($_GET['id']) && isset($_GET['type'])) {
    $feedbackId = $_GET['id'];
    $validationType = $_GET['type'];
    $userId = $_SESSION['user_id'];
    
    // Valider le type
    if (!in_array($validationType, ['POSITIVE', 'NEUTRAL', 'NEGATIVE'])) {
        $validationType = 'NEUTRAL';
    }
    
    // Enregistrer la validation
    $notes = isset($_POST['notes']) ? $_POST['notes'] : null;
    $validationId = $aiFeedback->validate($feedbackId, $userId, $validationType, $notes);
    
    // Rediriger vers la page de chat
    header('Location: index.php?page=ai_chat&validation=success');
    exit;
} else {
    // Rediriger vers la page de chat si les paramètres sont manquants
    header('Location: index.php?page=ai_chat&error=missing_params');
    exit;
}
