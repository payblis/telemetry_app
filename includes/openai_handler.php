<?php
require_once 'config/config.php';
require_once 'config/openai.php';

class OpenAIHandler {
    private $apiKey;
    private $model;
    private $baseUrl = 'https://api.openai.com/v1';
    
    public function __construct() {
        $this->apiKey = OPENAI_API_KEY;
        $this->model = 'gpt-4-turbo-preview';
    }
    
    // Fonction principale pour analyser les données télémétriques
    public function analyzeTelemetryData($data, $context) {
        $prompt = $this->buildTelemetryPrompt($data, $context);
        return $this->makeRequest('/chat/completions', [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Vous êtes un expert en télémétrie moto qui analyse les données et fournit des recommandations précises pour améliorer les performances.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 2000
        ]);
    }
    
    // Construire le prompt pour l'analyse télémétrique
    private function buildTelemetryPrompt($data, $context) {
        $prompt = "Analyse des données télémétriques pour la session :\n\n";
        
        // Informations sur la moto
        $prompt .= "Moto :\n";
        $prompt .= "- Marque : {$context['moto']['marque']}\n";
        $prompt .= "- Modèle : {$context['moto']['modele']}\n";
        $prompt .= "- Année : {$context['moto']['annee']}\n";
        $prompt .= "- Puissance : {$context['moto']['puissance']} ch\n\n";
        
        // Informations sur le pilote
        $prompt .= "Pilote :\n";
        $prompt .= "- Nom : {$context['pilote']['nom']}\n";
        $prompt .= "- Expérience : {$context['pilote']['experience']}\n\n";
        
        // Informations sur le circuit
        $prompt .= "Circuit :\n";
        $prompt .= "- Nom : {$context['circuit']['nom']}\n";
        $prompt .= "- Longueur : {$context['circuit']['longueur']} m\n";
        $prompt .= "- Conditions : {$context['session']['conditions_meteo']}\n\n";
        
        // Données télémétriques
        $prompt .= "Données télémétriques :\n";
        foreach ($data as $metric => $value) {
            $prompt .= "- $metric : $value\n";
        }
        
        $prompt .= "\nVeuillez analyser ces données et fournir :\n";
        $prompt .= "1. Une analyse détaillée des performances\n";
        $prompt .= "2. Des recommandations spécifiques pour les réglages\n";
        $prompt .= "3. Des suggestions d'amélioration pour le pilotage\n";
        
        return $prompt;
    }
    
    // Obtenir des recommandations de réglages
    public function getSetupRecommendations($problem, $context) {
        $prompt = $this->buildSetupPrompt($problem, $context);
        return $this->makeRequest('/chat/completions', [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Vous êtes un expert en réglages moto qui fournit des recommandations précises basées sur les problèmes rencontrés.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1500
        ]);
    }
    
    // Construire le prompt pour les recommandations de réglages
    private function buildSetupPrompt($problem, $context) {
        $prompt = "Problème signalé : $problem\n\n";
        
        $prompt .= "Configuration actuelle :\n";
        $prompt .= "- Précharge avant : {$context['reglages']['precharge_avant']}\n";
        $prompt .= "- Précharge arrière : {$context['reglages']['precharge_arriere']}\n";
        $prompt .= "- Compression avant : {$context['reglages']['compression_avant']}\n";
        $prompt .= "- Compression arrière : {$context['reglages']['compression_arriere']}\n";
        $prompt .= "- Détente avant : {$context['reglages']['detente_avant']}\n";
        $prompt .= "- Détente arrière : {$context['reglages']['detente_arriere']}\n";
        $prompt .= "- Pression pneu avant : {$context['reglages']['pression_avant']} bar\n";
        $prompt .= "- Pression pneu arrière : {$context['reglages']['pression_arriere']} bar\n\n";
        
        $prompt .= "Conditions :\n";
        $prompt .= "- Température piste : {$context['conditions']['temperature']}°C\n";
        $prompt .= "- Humidité : {$context['conditions']['humidite']}%\n";
        $prompt .= "- État piste : {$context['conditions']['etat_piste']}\n\n";
        
        $prompt .= "Veuillez fournir :\n";
        $prompt .= "1. Une analyse du problème\n";
        $prompt .= "2. Des recommandations de réglages précises\n";
        $prompt .= "3. Les effets attendus des modifications proposées\n";
        
        return $prompt;
    }
    
    // Faire une requête à l'API OpenAI
    private function makeRequest($endpoint, $data) {
        try {
            $ch = curl_init($this->baseUrl . $endpoint);
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]);
            
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            
            if ($err) {
                throw new Exception('Erreur cURL : ' . $err);
            }
            
            $result = json_decode($response, true);
            
            if (isset($result['error'])) {
                throw new Exception('Erreur API : ' . $result['error']['message']);
            }
            
            return [
                'success' => true,
                'response' => $result['choices'][0]['message']['content']
            ];
            
        } catch (Exception $e) {
            logCustomError('Erreur OpenAI', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Une erreur est survenue lors de la communication avec l\'IA'
            ];
        }
    }
    
    // Enrichir la base de connaissances interne
    public function enrichKnowledgeBase($feedback) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO ia_internal_knowledge 
                (categorie, probleme, solution, confiance)
                VALUES (?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $feedback['categorie'],
                $feedback['probleme'],
                $feedback['solution'],
                $feedback['confiance']
            ]);
            
        } catch (PDOException $e) {
            logCustomError('Erreur lors de l\'enrichissement de la base de connaissances', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    // Obtenir des suggestions de la base de connaissances interne
    public function getInternalSuggestions($problem) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT solution, confiance
                FROM ia_internal_knowledge
                WHERE probleme LIKE ?
                ORDER BY confiance DESC
                LIMIT 5
            ");
            
            $stmt->execute(['%' . $problem . '%']);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            logCustomError('Erreur lors de la récupération des suggestions internes', ['error' => $e->getMessage()]);
            return [];
        }
    }
}

// Créer une instance de la classe OpenAIHandler
$openai = new OpenAIHandler(); 