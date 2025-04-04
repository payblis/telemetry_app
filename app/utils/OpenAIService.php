<?php
/**
 * Classe pour l'intégration avec l'API OpenAI
 */
class OpenAIService {
    private $api_key;
    private $model;
    private $base_url = 'https://api.openai.com/v1';
    
    /**
     * Constructeur
     * 
     * @param string $api_key Clé API OpenAI
     * @param string $model Modèle à utiliser (par défaut: gpt-4)
     */
    public function __construct($api_key, $model = 'gpt-4') {
        $this->api_key = $api_key;
        $this->model = $model;
    }
    
    /**
     * Envoie une requête à l'API OpenAI
     * 
     * @param array $messages Messages à envoyer
     * @param float $temperature Température (créativité) de 0 à 1
     * @param int $max_tokens Nombre maximum de tokens à générer
     * @return array Réponse de l'API
     */
    public function sendChatRequest($messages, $temperature = 0.7, $max_tokens = 1000) {
        $url = $this->base_url . '/chat/completions';
        
        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $max_tokens
        ];
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ];
        
        $response = $this->makeRequest($url, $data, $headers);
        
        return $response;
    }
    
    /**
     * Génère des recommandations de réglages basées sur les données télémétriques
     * 
     * @param array $session Données de la session
     * @param array $tours Données des tours
     * @param array $telemetrie Données télémétriques
     * @return array Recommandations générées
     */
    public function generateRecommendations($session, $tours, $telemetrie) {
        // Préparer le contexte avec les données télémétriques
        $context = $this->prepareContext($session, $tours, $telemetrie);
        
        // Construire les messages pour l'API
        $messages = [
            [
                'role' => 'system',
                'content' => 'Vous êtes un expert en télémétrie moto et en réglages de motos de course. Votre tâche est d\'analyser les données télémétriques fournies et de proposer des recommandations précises pour améliorer les performances. Vos recommandations doivent être basées sur les données et être spécifiques, actionnables et justifiées par les données.'
            ],
            [
                'role' => 'user',
                'content' => $context
            ]
        ];
        
        // Envoyer la requête à l'API
        $response = $this->sendChatRequest($messages, 0.5, 2000);
        
        // Traiter la réponse
        if (isset($response['choices'][0]['message']['content'])) {
            $recommendations = $this->parseRecommendations($response['choices'][0]['message']['content']);
            return $recommendations;
        }
        
        return [
            'error' => 'Impossible de générer des recommandations',
            'details' => $response
        ];
    }
    
    /**
     * Prépare le contexte pour l'API OpenAI avec les données télémétriques
     * 
     * @param array $session Données de la session
     * @param array $tours Données des tours
     * @param array $telemetrie Données télémétriques
     * @return string Contexte formaté
     */
    private function prepareContext($session, $tours, $telemetrie) {
        $context = "## Données de session\n";
        $context .= "Circuit: {$session['circuit_nom']}\n";
        $context .= "Date: {$session['date_session']}\n";
        $context .= "Pilote: {$session['pilote_nom']} {$session['pilote_prenom']}\n";
        $context .= "Expérience: {$session['pilote_experience']} ans\n";
        $context .= "Moto: {$session['moto_marque']} {$session['moto_modele']} ({$session['moto_annee']})\n";
        $context .= "Cylindrée: {$session['moto_cylindree']} cc\n\n";
        
        $context .= "## Réglages actuels\n";
        if (!empty($session['reglages'])) {
            $reglages = json_decode($session['reglages'], true);
            foreach ($reglages as $key => $value) {
                $context .= "{$key}: {$value}\n";
            }
        } else {
            $context .= "Aucun réglage spécifié\n";
        }
        $context .= "\n";
        
        $context .= "## Statistiques de la session\n";
        $context .= "Nombre de tours: {$session['nombre_tours']}\n";
        $context .= "Meilleur temps: " . gmdate('i:s.v', $session['meilleur_temps']) . "\n";
        $context .= "Temps moyen: " . gmdate('i:s.v', $session['temps_moyen']) . "\n";
        $context .= "Vitesse max: {$session['vitesse_max']} km/h\n";
        $context .= "Vitesse moyenne: " . round($session['vitesse_moyenne'], 1) . " km/h\n\n";
        
        $context .= "## Données des tours\n";
        foreach ($tours as $index => $tour) {
            if ($index >= 5) {
                $context .= "... (plus de tours disponibles)\n";
                break;
            }
            
            $status = $tour['meilleur_tour'] ? "MEILLEUR TOUR" : ($tour['valide'] ? "Valide" : "Invalide");
            $context .= "Tour {$tour['numero_tour']}: " . gmdate('i:s.v', $tour['temps']) . " - {$status}\n";
            $context .= "  Vitesse max: {$tour['vitesse_max']} km/h\n";
            $context .= "  Vitesse moyenne: " . round($tour['vitesse_moyenne'], 1) . " km/h\n";
            $context .= "  Accélération max: " . round($tour['acceleration_max'], 2) . " g\n";
            $context .= "  Inclinaison max: " . round($tour['inclinaison_max'], 1) . "°\n\n";
        }
        
        $context .= "## Données télémétriques agrégées\n";
        // Ajouter des statistiques agrégées sur les données télémétriques
        if (!empty($telemetrie)) {
            // Calculer des statistiques sur les virages
            $context .= "Virages à droite - Inclinaison moyenne: " . round($telemetrie['inclinaison_droite_avg'], 1) . "°\n";
            $context .= "Virages à gauche - Inclinaison moyenne: " . round($telemetrie['inclinaison_gauche_avg'], 1) . "°\n";
            $context .= "Vitesse moyenne en virage: " . round($telemetrie['vitesse_virage_avg'], 1) . " km/h\n";
            $context .= "Vitesse moyenne en ligne droite: " . round($telemetrie['vitesse_ligne_droite_avg'], 1) . " km/h\n";
            $context .= "Temps moyen de freinage: " . round($telemetrie['temps_freinage_avg'], 2) . " s\n";
            $context .= "Force de freinage moyenne: " . round($telemetrie['force_freinage_avg'], 1) . "%\n";
            $context .= "Accélération moyenne en sortie de virage: " . round($telemetrie['acceleration_sortie_virage_avg'], 2) . " g\n";
        } else {
            $context .= "Données télémétriques détaillées non disponibles\n";
        }
        
        $context .= "\n## Problèmes identifiés par le pilote\n";
        if (!empty($session['problemes'])) {
            $context .= $session['problemes'] . "\n";
        } else {
            $context .= "Aucun problème spécifique signalé\n";
        }
        
        $context .= "\nVeuillez analyser ces données et fournir 3 à 5 recommandations de réglages pour améliorer les performances. Pour chaque recommandation, indiquez:\n";
        $context .= "1. Le titre de la recommandation\n";
        $context .= "2. Une explication détaillée basée sur les données\n";
        $context .= "3. L'action recommandée précise (réglage spécifique)\n";
        $context .= "4. L'impact attendu sur les performances\n";
        
        return $context;
    }
    
    /**
     * Parse les recommandations à partir de la réponse de l'API
     * 
     * @param string $content Contenu de la réponse
     * @return array Recommandations structurées
     */
    private function parseRecommendations($content) {
        $recommendations = [];
        
        // Diviser le contenu en sections de recommandations
        $pattern = '/(?:Recommandation|RECOMMANDATION)\s*(\d+|[A-Z])?\s*:?\s*(.*?)(?=(?:Recommandation|RECOMMANDATION)|$)/is';
        
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $index => $match) {
                $title = trim($match[2]);
                $fullText = trim($match[0]);
                
                // Extraire l'explication et l'action recommandée
                $explanation = '';
                $action = '';
                $impact = '';
                
                if (preg_match('/Explication\s*:?\s*(.*?)(?=Action|Impact|Recommandation|$)/is', $fullText, $expMatch)) {
                    $explanation = trim($expMatch[1]);
                }
                
                if (preg_match('/Action\s*:?\s*(.*?)(?=Impact|Explication|Recommandation|$)/is', $fullText, $actMatch)) {
                    $action = trim($actMatch[1]);
                }
                
                if (preg_match('/Impact\s*:?\s*(.*?)(?=Action|Explication|Recommandation|$)/is', $fullText, $impMatch)) {
                    $impact = trim($impMatch[1]);
                }
                
                $recommendations[] = [
                    'titre' => $title,
                    'texte' => $explanation,
                    'action_recommandee' => $action,
                    'impact_attendu' => $impact,
                    'source' => 'openai',
                    'confiance' => 85 // Valeur par défaut
                ];
            }
        } else {
            // Si le pattern ne fonctionne pas, essayer une approche plus simple
            $paragraphs = explode("\n\n", $content);
            foreach ($paragraphs as $index => $paragraph) {
                if (strlen(trim($paragraph)) > 50) { // Ignorer les paragraphes trop courts
                    $recommendations[] = [
                        'titre' => "Recommandation " . ($index + 1),
                        'texte' => trim($paragraph),
                        'action_recommandee' => '',
                        'impact_attendu' => '',
                        'source' => 'openai',
                        'confiance' => 70 // Confiance plus faible car parsing moins précis
                    ];
                }
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Effectue une requête HTTP vers l'API
     * 
     * @param string $url URL de l'API
     * @param array $data Données à envoyer
     * @param array $headers En-têtes HTTP
     * @return array Réponse décodée
     */
    private function makeRequest($url, $data, $headers) {
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        
        curl_close($ch);
        
        if ($error) {
            return [
                'error' => $error,
                'info' => $info
            ];
        }
        
        return json_decode($response, true);
    }
}
