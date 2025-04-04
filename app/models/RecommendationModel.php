<?php
/**
 * Modèle pour la gestion des recommandations IA
 */
class RecommendationModel extends Model {
    protected $table = 'recommandations';
    
    /**
     * Crée une nouvelle recommandation
     * 
     * @param array $data Données de la recommandation
     * @return int|bool ID de la recommandation créée ou false en cas d'échec
     */
    public function create($data) {
        // Valider les données
        if (!isset($data['session_id']) || !isset($data['titre']) || !isset($data['texte'])) {
            return false;
        }
        
        // Préparer les données
        $fields = [
            'session_id' => $data['session_id'],
            'titre' => $data['titre'],
            'texte' => $data['texte'],
            'action_recommandee' => $data['action_recommandee'] ?? '',
            'impact_attendu' => $data['impact_attendu'] ?? '',
            'source' => $data['source'] ?? 'openai',
            'confiance' => $data['confiance'] ?? 80,
            'date_creation' => date('Y-m-d H:i:s'),
            'statut' => 'active'
        ];
        
        // Insérer dans la base de données
        return $this->insert($fields);
    }
    
    /**
     * Récupère les recommandations pour une session donnée
     * 
     * @param int $sessionId ID de la session
     * @return array Liste des recommandations
     */
    public function getBySessionId($sessionId) {
        $sql = "SELECT * FROM {$this->table} WHERE session_id = ? AND statut = 'active' ORDER BY confiance DESC, date_creation DESC";
        return $this->query($sql, [$sessionId]);
    }
    
    /**
     * Récupère les recommandations récentes pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $limit Nombre maximum de recommandations à récupérer
     * @return array Liste des recommandations
     */
    public function getRecentByUserId($userId, $limit = 5) {
        $sql = "SELECT r.*, s.circuit_id, c.nom as circuit_nom 
                FROM {$this->table} r
                JOIN sessions s ON r.session_id = s.id
                JOIN circuits c ON s.circuit_id = c.id
                WHERE s.user_id = ? AND r.statut = 'active'
                ORDER BY r.date_creation DESC
                LIMIT ?";
        return $this->query($sql, [$userId, $limit]);
    }
    
    /**
     * Met à jour le statut d'une recommandation
     * 
     * @param int $id ID de la recommandation
     * @param string $statut Nouveau statut ('active', 'applied', 'rejected')
     * @return bool Succès de l'opération
     */
    public function updateStatus($id, $statut) {
        $validStatuses = ['active', 'applied', 'rejected'];
        if (!in_array($statut, $validStatuses)) {
            return false;
        }
        
        return $this->update($id, ['statut' => $statut]);
    }
    
    /**
     * Ajoute un feedback utilisateur à une recommandation
     * 
     * @param int $id ID de la recommandation
     * @param string $feedback Feedback de l'utilisateur
     * @param int $rating Note de l'utilisateur (1-5)
     * @return bool Succès de l'opération
     */
    public function addFeedback($id, $feedback, $rating) {
        if ($rating < 1 || $rating > 5) {
            return false;
        }
        
        return $this->update($id, [
            'feedback_utilisateur' => $feedback,
            'note_utilisateur' => $rating,
            'date_feedback' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Supprime une recommandation
     * 
     * @param int $id ID de la recommandation
     * @return bool Succès de l'opération
     */
    public function delete($id) {
        // Soft delete - marquer comme supprimée plutôt que de supprimer réellement
        return $this->update($id, ['statut' => 'deleted']);
    }
    
    /**
     * Génère des recommandations pour une session donnée en utilisant l'IA
     * 
     * @param int $sessionId ID de la session
     * @return array Résultat de l'opération
     */
    public function generateRecommendations($sessionId) {
        // Récupérer les données de la session
        $sessionModel = new SessionModel();
        $session = $sessionModel->getById($sessionId);
        
        if (!$session) {
            return [
                'success' => false,
                'message' => 'Session introuvable'
            ];
        }
        
        // Récupérer les données des tours
        $tourModel = new TourModel();
        $tours = $tourModel->getBySessionId($sessionId);
        
        // Récupérer les données télémétriques agrégées
        $telemetrieModel = new TelemetrieModel();
        $telemetrie = $telemetrieModel->getAggregatedDataBySessionId($sessionId);
        
        // Initialiser le service OpenAI
        $config = require_once __DIR__ . '/../../config/config.php';
        $openai = new OpenAIService($config['openai_api_key']);
        
        // Générer les recommandations
        $recommendations = $openai->generateRecommendations($session, $tours, $telemetrie);
        
        if (isset($recommendations['error'])) {
            return [
                'success' => false,
                'message' => $recommendations['error'],
                'details' => $recommendations['details'] ?? null
            ];
        }
        
        // Enregistrer les recommandations dans la base de données
        $savedCount = 0;
        foreach ($recommendations as $recommendation) {
            $recommendation['session_id'] = $sessionId;
            if ($this->create($recommendation)) {
                $savedCount++;
            }
        }
        
        return [
            'success' => true,
            'message' => "Généré {$savedCount} recommandations avec succès",
            'count' => $savedCount
        ];
    }
    
    /**
     * Génère des recommandations communautaires basées sur des sessions similaires
     * 
     * @param int $sessionId ID de la session
     * @return array Résultat de l'opération
     */
    public function generateCommunityRecommendations($sessionId) {
        // Récupérer les données de la session
        $sessionModel = new SessionModel();
        $session = $sessionModel->getById($sessionId);
        
        if (!$session) {
            return [
                'success' => false,
                'message' => 'Session introuvable'
            ];
        }
        
        // Trouver des sessions similaires (même circuit, mêmes conditions, performances similaires)
        $similarSessions = $sessionModel->findSimilarSessions($sessionId, $session['circuit_id'], 5);
        
        if (empty($similarSessions)) {
            return [
                'success' => false,
                'message' => 'Aucune session similaire trouvée pour générer des recommandations communautaires'
            ];
        }
        
        // Récupérer les recommandations bien notées des sessions similaires
        $communityRecommendations = [];
        foreach ($similarSessions as $similarSession) {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE session_id = ? 
                    AND statut = 'applied' 
                    AND note_utilisateur >= 4
                    ORDER BY note_utilisateur DESC, confiance DESC
                    LIMIT 2";
            
            $recommendations = $this->query($sql, [$similarSession['id']]);
            
            foreach ($recommendations as $recommendation) {
                // Adapter la recommandation pour la session actuelle
                $communityRecommendations[] = [
                    'session_id' => $sessionId,
                    'titre' => $recommendation['titre'],
                    'texte' => $recommendation['texte'],
                    'action_recommandee' => $recommendation['action_recommandee'],
                    'impact_attendu' => $recommendation['impact_attendu'],
                    'source' => 'communaute',
                    'confiance' => min(90, $recommendation['note_utilisateur'] * 15 + 30),
                    'reference_session_id' => $recommendation['session_id']
                ];
            }
        }
        
        if (empty($communityRecommendations)) {
            return [
                'success' => false,
                'message' => 'Aucune recommandation communautaire pertinente trouvée'
            ];
        }
        
        // Enregistrer les recommandations communautaires
        $savedCount = 0;
        foreach ($communityRecommendations as $recommendation) {
            if ($this->create($recommendation)) {
                $savedCount++;
            }
        }
        
        return [
            'success' => true,
            'message' => "Généré {$savedCount} recommandations communautaires avec succès",
            'count' => $savedCount
        ];
    }
}
