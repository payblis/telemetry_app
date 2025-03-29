<?php
/**
 * Classe AIFeedback
 * Gère les recommandations et feedbacks de l'IA
 */
class AIFeedback {
    private $db;
    private $chatGPT;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->chatGPT = new ChatGPT();
    }
    
    /**
     * Créer un nouveau feedback IA
     * @param int $sessionId ID de la session (optionnel)
     * @param string $problemDescription Description du problème
     * @param string $problemType Type de problème
     * @param string $solutionDescription Description de la solution
     * @param string $settingsChanges Changements de réglages recommandés
     * @param string $source Source du feedback (AI, COMMUNITY, EXPERT)
     * @return int ID du feedback créé
     */
    public function create($sessionId, $problemDescription, $problemType, $solutionDescription, $settingsChanges, $source = 'AI') {
        $data = [
            'session_id' => $sessionId,
            'problem_description' => $problemDescription,
            'problem_type' => $problemType,
            'solution_description' => $solutionDescription,
            'settings_changes' => $settingsChanges,
            'source' => $source
        ];
        
        return $this->db->insert('ai_feedbacks', $data);
    }
    
    /**
     * Obtenir un feedback par son ID
     * @param int $id ID du feedback
     * @return array|false Données du feedback ou false
     */
    public function getById($id) {
        $sql = "SELECT * FROM ai_feedbacks WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Obtenir tous les feedbacks d'une session
     * @param int $sessionId ID de la session
     * @return array Liste des feedbacks
     */
    public function getAllBySessionId($sessionId) {
        $sql = "SELECT * FROM ai_feedbacks WHERE session_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$sessionId]);
    }
    
    /**
     * Obtenir tous les feedbacks par type de problème
     * @param string $problemType Type de problème
     * @return array Liste des feedbacks
     */
    public function getAllByProblemType($problemType) {
        $sql = "SELECT * FROM ai_feedbacks WHERE problem_type = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$problemType]);
    }
    
    /**
     * Obtenir des recommandations pour une session et un problème
     * @param int $sessionId ID de la session
     * @param string $problem Description du problème
     * @param string $problemType Type de problème
     * @return array Recommandations
     */
    public function getRecommendations($sessionId, $problem, $problemType) {
        // Obtenir les données de la session
        $session = new Session();
        $sessionData = $session->getById($sessionId);
        
        if (!$sessionData) {
            return [
                'success' => false,
                'message' => 'Session non trouvée'
            ];
        }
        
        // Obtenir des recommandations de ChatGPT
        $recommendations = $this->chatGPT->getSettingsRecommendations($sessionData, $problem);
        
        if (!$recommendations) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la communication avec ChatGPT'
            ];
        }
        
        // Enrichir avec des données communautaires si disponibles
        $communityData = $this->getCommunityDataForProblem($problemType);
        if (!empty($communityData)) {
            $recommendations = $this->chatGPT->enrichRecommendation($recommendations, $communityData);
        }
        
        // Sauvegarder les recommandations dans la base de données
        $feedbackId = $this->create(
            $sessionId,
            $problem,
            $problemType,
            $recommendations['solution'],
            $recommendations['solution'], // Utiliser la solution comme changements de réglages
            $recommendations['source']
        );
        
        return [
            'success' => true,
            'feedback_id' => $feedbackId,
            'recommendations' => $recommendations
        ];
    }
    
    /**
     * Obtenir des données communautaires pour un type de problème
     * @param string $problemType Type de problème
     * @return string Données communautaires formatées
     */
    private function getCommunityDataForProblem($problemType) {
        // Obtenir les feedbacks experts pour ce type de problème
        $sql = "SELECT ef.*, u.telemetrician_name 
                FROM expert_feedbacks ef
                JOIN users u ON ef.expert_id = u.id
                WHERE ef.problem_type = ?
                ORDER BY ef.created_at DESC
                LIMIT 5";
        $expertFeedbacks = $this->db->fetchAll($sql, [$problemType]);
        
        // Obtenir les feedbacks IA validés positivement pour ce type de problème
        $sql = "SELECT af.*, av.validation_type
                FROM ai_feedbacks af
                JOIN ai_validations av ON af.id = av.ai_feedback_id
                WHERE af.problem_type = ? AND av.validation_type = 'POSITIVE'
                ORDER BY af.created_at DESC
                LIMIT 5";
        $validatedFeedbacks = $this->db->fetchAll($sql, [$problemType]);
        
        // Formater les données
        $communityData = "";
        
        if (!empty($expertFeedbacks)) {
            $communityData .= "Recommandations d'experts :\n\n";
            foreach ($expertFeedbacks as $feedback) {
                $communityData .= "Expert: {$feedback['telemetrician_name']}\n";
                $communityData .= "Problème: {$feedback['problem_type']}\n";
                $communityData .= "Recommandations: {$feedback['settings_recommendations']}\n\n";
            }
        }
        
        if (!empty($validatedFeedbacks)) {
            $communityData .= "Recommandations validées par la communauté :\n\n";
            foreach ($validatedFeedbacks as $feedback) {
                $communityData .= "Problème: {$feedback['problem_description']}\n";
                $communityData .= "Solution: {$feedback['solution_description']}\n\n";
            }
        }
        
        return $communityData;
    }
    
    /**
     * Valider un feedback IA
     * @param int $feedbackId ID du feedback
     * @param int $userId ID de l'utilisateur
     * @param string $validationType Type de validation (POSITIVE, NEUTRAL, NEGATIVE)
     * @param string $notes Notes (optionnel)
     * @return int ID de la validation créée
     */
    public function validate($feedbackId, $userId, $validationType, $notes = null) {
        $data = [
            'ai_feedback_id' => $feedbackId,
            'user_id' => $userId,
            'validation_type' => $validationType,
            'notes' => $notes
        ];
        
        return $this->db->insert('ai_validations', $data);
    }
    
    /**
     * Obtenir les validations d'un feedback
     * @param int $feedbackId ID du feedback
     * @return array Liste des validations
     */
    public function getValidations($feedbackId) {
        $sql = "SELECT av.*, u.username 
                FROM ai_validations av
                JOIN users u ON av.user_id = u.id
                WHERE av.ai_feedback_id = ?
                ORDER BY av.created_at DESC";
        return $this->db->fetchAll($sql, [$feedbackId]);
    }
    
    /**
     * Obtenir les statistiques de validation d'un feedback
     * @param int $feedbackId ID du feedback
     * @return array Statistiques de validation
     */
    public function getValidationStats($feedbackId) {
        $sql = "SELECT 
                SUM(CASE WHEN validation_type = 'POSITIVE' THEN 1 ELSE 0 END) as positive_count,
                SUM(CASE WHEN validation_type = 'NEUTRAL' THEN 1 ELSE 0 END) as neutral_count,
                SUM(CASE WHEN validation_type = 'NEGATIVE' THEN 1 ELSE 0 END) as negative_count,
                COUNT(*) as total_count
                FROM ai_validations
                WHERE ai_feedback_id = ?";
        $stats = $this->db->fetchOne($sql, [$feedbackId]);
        
        if ($stats && $stats['total_count'] > 0) {
            $stats['positive_percent'] = round(($stats['positive_count'] / $stats['total_count']) * 100);
            $stats['neutral_percent'] = round(($stats['neutral_count'] / $stats['total_count']) * 100);
            $stats['negative_percent'] = round(($stats['negative_count'] / $stats['total_count']) * 100);
        } else {
            $stats = [
                'positive_count' => 0,
                'neutral_count' => 0,
                'negative_count' => 0,
                'total_count' => 0,
                'positive_percent' => 0,
                'neutral_percent' => 0,
                'negative_percent' => 0
            ];
        }
        
        return $stats;
    }
}
