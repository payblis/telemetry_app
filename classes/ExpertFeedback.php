<?php
/**
 * Classe ExpertFeedback
 * Gère les feedbacks des experts télémétristes
 */
class ExpertFeedback {
    private $db;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Créer un nouveau feedback expert
     * @param int $expertId ID de l'expert
     * @param string $problemType Type de problème
     * @param string $feedbackText Texte du feedback
     * @param string $settingsRecommendations Recommandations de réglages
     * @param int $motoId ID de la moto (optionnel)
     * @param int $circuitId ID du circuit (optionnel)
     * @return int ID du feedback créé
     */
    public function create($expertId, $problemType, $feedbackText, $settingsRecommendations, $motoId = null, $circuitId = null) {
        $data = [
            'expert_id' => $expertId,
            'problem_type' => $problemType,
            'feedback_text' => $feedbackText,
            'settings_recommendations' => $settingsRecommendations,
            'moto_id' => $motoId,
            'circuit_id' => $circuitId
        ];
        
        return $this->db->insert('expert_feedbacks', $data);
    }
    
    /**
     * Obtenir un feedback expert par son ID
     * @param int $id ID du feedback
     * @return array|false Données du feedback ou false
     */
    public function getById($id) {
        $sql = "SELECT ef.*, u.username, u.telemetrician_name 
                FROM expert_feedbacks ef
                JOIN users u ON ef.expert_id = u.id
                WHERE ef.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Obtenir tous les feedbacks d'un expert
     * @param int $expertId ID de l'expert
     * @return array Liste des feedbacks
     */
    public function getAllByExpertId($expertId) {
        $sql = "SELECT ef.*, 
                m.brand as moto_brand, m.model as moto_model,
                c.name as circuit_name, c.country as circuit_country
                FROM expert_feedbacks ef
                LEFT JOIN motos m ON ef.moto_id = m.id
                LEFT JOIN circuits c ON ef.circuit_id = c.id
                WHERE ef.expert_id = ?
                ORDER BY ef.created_at DESC";
        return $this->db->fetchAll($sql, [$expertId]);
    }
    
    /**
     * Obtenir tous les feedbacks par type de problème
     * @param string $problemType Type de problème
     * @return array Liste des feedbacks
     */
    public function getAllByProblemType($problemType) {
        $sql = "SELECT ef.*, u.username, u.telemetrician_name,
                m.brand as moto_brand, m.model as moto_model,
                c.name as circuit_name, c.country as circuit_country
                FROM expert_feedbacks ef
                JOIN users u ON ef.expert_id = u.id
                LEFT JOIN motos m ON ef.moto_id = m.id
                LEFT JOIN circuits c ON ef.circuit_id = c.id
                WHERE ef.problem_type = ?
                ORDER BY ef.created_at DESC";
        return $this->db->fetchAll($sql, [$problemType]);
    }
    
    /**
     * Obtenir tous les feedbacks pour une moto spécifique
     * @param int $motoId ID de la moto
     * @return array Liste des feedbacks
     */
    public function getAllByMotoId($motoId) {
        $sql = "SELECT ef.*, u.username, u.telemetrician_name,
                c.name as circuit_name, c.country as circuit_country
                FROM expert_feedbacks ef
                JOIN users u ON ef.expert_id = u.id
                LEFT JOIN circuits c ON ef.circuit_id = c.id
                WHERE ef.moto_id = ?
                ORDER BY ef.created_at DESC";
        return $this->db->fetchAll($sql, [$motoId]);
    }
    
    /**
     * Obtenir tous les feedbacks pour un circuit spécifique
     * @param int $circuitId ID du circuit
     * @return array Liste des feedbacks
     */
    public function getAllByCircuitId($circuitId) {
        $sql = "SELECT ef.*, u.username, u.telemetrician_name,
                m.brand as moto_brand, m.model as moto_model
                FROM expert_feedbacks ef
                JOIN users u ON ef.expert_id = u.id
                LEFT JOIN motos m ON ef.moto_id = m.id
                WHERE ef.circuit_id = ?
                ORDER BY ef.created_at DESC";
        return $this->db->fetchAll($sql, [$circuitId]);
    }
    
    /**
     * Mettre à jour un feedback expert
     * @param int $id ID du feedback
     * @param array $data Données à mettre à jour
     * @return int Nombre de lignes affectées
     */
    public function update($id, $data) {
        return $this->db->update('expert_feedbacks', $data, 'id = ?', [$id]);
    }
    
    /**
     * Supprimer un feedback expert
     * @param int $id ID du feedback
     * @return int Nombre de lignes affectées
     */
    public function delete($id) {
        return $this->db->delete('expert_feedbacks', 'id = ?', [$id]);
    }
    
    /**
     * Rechercher des feedbacks experts
     * @param string $search Terme de recherche
     * @return array Liste des feedbacks correspondants
     */
    public function search($search) {
        $sql = "SELECT ef.*, u.username, u.telemetrician_name,
                m.brand as moto_brand, m.model as moto_model,
                c.name as circuit_name, c.country as circuit_country
                FROM expert_feedbacks ef
                JOIN users u ON ef.expert_id = u.id
                LEFT JOIN motos m ON ef.moto_id = m.id
                LEFT JOIN circuits c ON ef.circuit_id = c.id
                WHERE ef.problem_type LIKE ? OR ef.feedback_text LIKE ? OR ef.settings_recommendations LIKE ?
                ORDER BY ef.created_at DESC";
        return $this->db->fetchAll($sql, [
            '%' . $search . '%',
            '%' . $search . '%',
            '%' . $search . '%'
        ]);
    }
}
