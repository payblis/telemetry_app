<?php
// Configuration de l'API OpenAI
define('OPENAI_API_KEY', 'votre-cle-api');
define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');
define('OPENAI_MODEL', 'gpt-4');

/**
 * Fonction pour envoyer une requête à l'API ChatGPT
 * 
 * @param string $prompt Le message à envoyer à ChatGPT
 * @param array $context Contexte supplémentaire pour la requête
 * @return array Réponse de l'API
 */
function chatGPT($prompt, $context = []) {
    // Construire les messages pour l'API
    $messages = [];
    
    // Ajouter un message système pour définir le rôle de l'IA
    $messages[] = [
        'role' => 'system',
        'content' => 'Tu es un expert en réglage de motos de course. Tu dois fournir des recommandations précises et techniques pour aider à optimiser les performances d\'une moto en fonction des problèmes rencontrés par le pilote. Tes réponses doivent être concises et directement applicables.'
    ];
    
    // Ajouter le contexte si fourni
    if (!empty($context)) {
        foreach ($context as $message) {
            $messages[] = $message;
        }
    }
    
    // Ajouter le prompt de l'utilisateur
    $messages[] = [
        'role' => 'user',
        'content' => $prompt
    ];
    
    // Préparer les données pour l'API
    $data = [
        'model' => OPENAI_MODEL,
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 500
    ];
    
    // Initialiser cURL
    $ch = curl_init(OPENAI_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY
    ]);
    
    // Exécuter la requête
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Vérifier les erreurs
    if ($error) {
        return [
            'success' => false,
            'error' => $error
        ];
    }
    
    // Décoder la réponse
    $responseData = json_decode($response, true);
    
    // Vérifier si la réponse est valide
    if (isset($responseData['error'])) {
        return [
            'success' => false,
            'error' => $responseData['error']['message']
        ];
    }
    
    // Extraire la réponse
    if (isset($responseData['choices'][0]['message']['content'])) {
        return [
            'success' => true,
            'message' => $responseData['choices'][0]['message']['content']
        ];
    }
    
    return [
        'success' => false,
        'error' => 'Réponse inattendue de l\'API'
    ];
}

/**
 * Fonction pour générer une suggestion de réglage basée sur un problème
 * 
 * @param string $probleme Description du problème rencontré
 * @param array $contexte Contexte de la session (pilote, moto, circuit, conditions)
 * @return array Réponse contenant la suggestion
 */
function genererSuggestionReglage($probleme, $contexte) {
    // Construire le prompt pour ChatGPT
    $prompt = "Je rencontre le problème suivant avec ma moto : $probleme\n\n";
    
    // Ajouter le contexte
    $prompt .= "Contexte :\n";
    $prompt .= "- Pilote : " . $contexte['pilote'] . "\n";
    $prompt .= "- Moto : " . $contexte['moto'] . "\n";
    $prompt .= "- Circuit : " . $contexte['circuit'] . "\n";
    $prompt .= "- Conditions : " . $contexte['conditions'] . "\n";
    
    if (!empty($contexte['reglages_actuels'])) {
        $prompt .= "- Réglages actuels : " . $contexte['reglages_actuels'] . "\n";
    }
    
    $prompt .= "\nQuelle est ta recommandation précise pour résoudre ce problème ? Donne-moi une solution technique détaillée que je peux appliquer immédiatement.";
    
    // Appeler l'API ChatGPT
    return chatGPT($prompt);
}

/**
 * Fonction pour analyser les chronos d'une session
 * 
 * @param array $chronos Liste des chronos de la session
 * @param array $contexte Contexte de la session
 * @return array Analyse des chronos
 */
function analyserChronos($chronos, $contexte) {
    // Construire le prompt pour ChatGPT
    $prompt = "Voici les chronos d'une session de pilotage :\n";
    
    foreach ($chronos as $index => $chrono) {
        $prompt .= "Tour " . ($index + 1) . " : " . $chrono . "\n";
    }
    
    $prompt .= "\nContexte :\n";
    $prompt .= "- Pilote : " . $contexte['pilote'] . "\n";
    $prompt .= "- Moto : " . $contexte['moto'] . "\n";
    $prompt .= "- Circuit : " . $contexte['circuit'] . "\n";
    $prompt .= "- Conditions : " . $contexte['conditions'] . "\n";
    
    $prompt .= "\nPeux-tu analyser ces chronos et me donner des observations pertinentes ? Y a-t-il une progression, une régression, ou des anomalies ? Quelles recommandations générales pourrais-tu faire pour améliorer les performances ?";
    
    // Appeler l'API ChatGPT
    return chatGPT($prompt);
}

/**
 * Fonction pour obtenir des informations sur un circuit
 * 
 * @param string $nomCircuit Nom du circuit
 * @return array Informations sur le circuit
 */
function obtenirInfosCircuit($nomCircuit) {
    // Construire le prompt pour ChatGPT
    $prompt = "Peux-tu me donner des informations détaillées sur le circuit de $nomCircuit ? Je souhaite connaître :\n";
    $prompt .= "- Sa localisation\n";
    $prompt .= "- Sa longueur\n";
    $prompt .= "- Le nombre de virages\n";
    $prompt .= "- Les caractéristiques principales de chaque virage (angle, vitesse estimée, rapport conseillé)\n";
    $prompt .= "- Les zones de freinage importantes\n";
    $prompt .= "- Les difficultés particulières\n";
    $prompt .= "- Les réglages généralement recommandés pour ce circuit\n";
    
    // Appeler l'API ChatGPT
    return chatGPT($prompt);
}
?>
