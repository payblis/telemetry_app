<?php
/**
 * Classe ChatGPT
 * Gère l'intégration avec l'API ChatGPT
 */
class ChatGPT {
    private $apiKey;
    private $apiUrl = 'https://api.openai.com/v1/chat/completions';
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->apiKey = OPENAI_API_KEY;
    }
    
    /**
     * Envoyer une requête à l'API ChatGPT
     * @param array $messages Messages à envoyer
     * @param string $model Modèle à utiliser (par défaut: gpt-3.5-turbo)
     * @param float $temperature Température (créativité) de 0 à 1
     * @return array|false Réponse de l'API ou false en cas d'erreur
     */
    public function sendRequest($messages, $model = 'gpt-3.5-turbo', $temperature = 0.7) {
        // Vérifier si la clé API est configurée
        if (empty($this->apiKey) || $this->apiKey === 'votre_clé_api_ici') {
            return false;
        }
        
        // Préparer les données de la requête
        $data = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => 1000
        ];
        
        // Initialiser cURL
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        // Exécuter la requête
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Vérifier si la requête a réussi
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return false;
    }
    
    /**
     * Obtenir des recommandations de réglages pour une session
     * @param array $sessionData Données de la session
     * @param string $problem Description du problème
     * @return array|false Recommandations ou false en cas d'erreur
     */
    public function getSettingsRecommendations($sessionData, $problem) {
        // Construire le contexte pour ChatGPT
        $messages = [
            [
                'role' => 'system',
                'content' => 'Vous êtes un expert en télémétrie moto qui aide à optimiser les réglages pour améliorer les performances sur circuit. Donnez des recommandations précises et techniques basées sur les informations fournies.'
            ],
            [
                'role' => 'user',
                'content' => "Je rencontre un problème avec ma moto sur circuit. Voici les détails :\n\n" .
                             "Moto: {$sessionData['moto_brand']} {$sessionData['moto_model']}\n" .
                             "Circuit: {$sessionData['circuit_name']} ({$sessionData['circuit_country']})\n" .
                             "Pilote: {$sessionData['pilot_name']}\n" .
                             "Conditions: {$sessionData['weather']}, piste à {$sessionData['track_temperature']}°C, air à {$sessionData['air_temperature']}°C\n\n" .
                             "Problème: {$problem}\n\n" .
                             "Quels réglages me recommandez-vous pour résoudre ce problème? Donnez-moi des recommandations précises pour les suspensions, la transmission, et autres paramètres pertinents."
            ]
        ];
        
        // Envoyer la requête à ChatGPT
        $response = $this->sendRequest($messages);
        
        if ($response && isset($response['choices'][0]['message']['content'])) {
            return [
                'problem' => $problem,
                'solution' => $response['choices'][0]['message']['content'],
                'source' => 'AI'
            ];
        }
        
        return false;
    }
    
    /**
     * Importer les détails d'un circuit
     * @param string $circuitName Nom du circuit
     * @param string $country Pays du circuit
     * @return array|false Détails du circuit ou false en cas d'erreur
     */
    public function importCircuitDetails($circuitName, $country) {
        // Construire le contexte pour ChatGPT
        $messages = [
            [
                'role' => 'system',
                'content' => 'Vous êtes un expert en circuits moto qui fournit des informations détaillées sur les circuits. Donnez des informations précises et techniques sur le circuit demandé, en particulier sur ses virages.'
            ],
            [
                'role' => 'user',
                'content' => "Donnez-moi des informations détaillées sur le circuit {$circuitName} en {$country}. " .
                             "Je souhaite connaître sa longueur, sa largeur, et surtout des détails sur chacun de ses virages (numéro, type, angle, vitesse estimée, rapport conseillé). " .
                             "Formatez votre réponse de manière structurée pour que je puisse facilement extraire ces informations."
            ]
        ];
        
        // Envoyer la requête à ChatGPT
        $response = $this->sendRequest($messages);
        
        if ($response && isset($response['choices'][0]['message']['content'])) {
            // Analyser la réponse pour extraire les informations
            $content = $response['choices'][0]['message']['content'];
            
            // Extraction basique des informations (à améliorer selon le format de réponse)
            $circuitInfo = [
                'name' => $circuitName,
                'country' => $country,
                'details' => $content,
                'corners' => []
            ];
            
            // Tenter d'extraire la longueur
            if (preg_match('/longueur\s*:?\s*(\d+[\.,]?\d*)\s*(km|m)/i', $content, $matches)) {
                $length = floatval(str_replace(',', '.', $matches[1]));
                if (strtolower($matches[2]) === 'km') {
                    $length *= 1000; // Convertir en mètres
                }
                $circuitInfo['length'] = $length;
            }
            
            // Tenter d'extraire les virages (exemple simplifié)
            preg_match_all('/virage\s*(\d+)\s*:?\s*([^,\n]+)/i', $content, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $cornerNumber = intval($match[1]);
                $description = trim($match[2]);
                
                // Déterminer le type de virage
                $type = 'RIGHT'; // Par défaut
                if (stripos($description, 'gauche') !== false) {
                    $type = 'LEFT';
                } elseif (stripos($description, 'chicane') !== false) {
                    $type = 'CHICANE';
                }
                
                $circuitInfo['corners'][] = [
                    'number' => $cornerNumber,
                    'type' => $type,
                    'description' => $description
                ];
            }
            
            return $circuitInfo;
        }
        
        return false;
    }
    
    /**
     * Enrichir une recommandation avec des données communautaires
     * @param array $recommendation Recommandation initiale
     * @param array $communityData Données communautaires
     * @return array Recommandation enrichie
     */
    public function enrichRecommendation($recommendation, $communityData) {
        // Construire le contexte pour ChatGPT
        $messages = [
            [
                'role' => 'system',
                'content' => 'Vous êtes un expert en télémétrie moto qui aide à optimiser les réglages en combinant l\'IA et les données communautaires. Votre tâche est d\'enrichir une recommandation initiale avec des données provenant d\'experts.'
            ],
            [
                'role' => 'user',
                'content' => "Voici une recommandation initiale pour un problème de moto :\n\n" .
                             "Problème: {$recommendation['problem']}\n" .
                             "Solution initiale: {$recommendation['solution']}\n\n" .
                             "Voici des données communautaires d'experts sur des problèmes similaires :\n\n" .
                             $communityData . "\n\n" .
                             "Enrichissez la recommandation initiale avec ces données communautaires pour fournir une solution plus précise et efficace."
            ]
        ];
        
        // Envoyer la requête à ChatGPT
        $response = $this->sendRequest($messages);
        
        if ($response && isset($response['choices'][0]['message']['content'])) {
            return [
                'problem' => $recommendation['problem'],
                'solution' => $response['choices'][0]['message']['content'],
                'source' => 'COMMUNITY'
            ];
        }
        
        return $recommendation; // Retourner la recommandation initiale en cas d'erreur
    }
}
