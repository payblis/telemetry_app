<?php
/**
 * Classe Pilot
 * Gère les pilotes de l'application
 */
class Pilot {
    private $db;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Créer un nouveau pilote
     * @param string $name Nom du pilote
     * @param int $height Taille en cm
     * @param int $weight Poids en kg
     * @param string $experience Expérience
     * @param int $userId ID de l'utilisateur propriétaire
     * @return int ID du pilote créé
     */
    public function create($name, $height, $weight, $experience, $userId) {
        $data = [
            'name' => $name,
            'height' => $height,
            'weight' => $weight,
            'experience' => $experience,
            'user_id' => $userId
        ];
        
        return $this->db->insert('pilots', $data);
    }
    
    /**
     * Obtenir un pilote par son ID
     * @param int $id ID du pilote
     * @return array|false Données du pilote ou false
     */
    public function getById($id) {
        $sql = "SELECT * FROM pilots WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Mettre à jour un pilote
     * @param int $id ID du pilote
     * @param array $data Données à mettre à jour
     * @return int Nombre de lignes affectées
     */
    public function update($id, $data) {
        return $this->db->update('pilots', $data, 'id = ?', [$id]);
    }
    
    /**
     * Supprimer un pilote
     * @param int $id ID du pilote
     * @return int Nombre de lignes affectées
     */
    public function delete($id) {
        return $this->db->delete('pilots', 'id = ?', [$id]);
    }
    
    /**
     * Obtenir tous les pilotes d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return array Liste des pilotes
     */
    public function getAllByUserId($userId) {
        $sql = "SELECT * FROM pilots WHERE user_id = ? ORDER BY name";
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    /**
     * Rechercher des pilotes par nom
     * @param string $search Terme de recherche
     * @param int $userId ID de l'utilisateur (optionnel)
     * @return array Liste des pilotes correspondants
     */
    public function search($search, $userId = null) {
        if ($userId) {
            $sql = "SELECT * FROM pilots WHERE name LIKE ? AND user_id = ? ORDER BY name";
            return $this->db->fetchAll($sql, ['%' . $search . '%', $userId]);
        } else {
            $sql = "SELECT * FROM pilots WHERE name LIKE ? ORDER BY name";
            return $this->db->fetchAll($sql, ['%' . $search . '%']);
        }
    }
    
    /**
     * Vérifier si un pilote appartient à un utilisateur
     * @param int $pilotId ID du pilote
     * @param int $userId ID de l'utilisateur
     * @return bool Résultat de la vérification
     */
    public function belongsToUser($pilotId, $userId) {
        $sql = "SELECT id FROM pilots WHERE id = ? AND user_id = ?";
        $result = $this->db->fetchOne($sql, [$pilotId, $userId]);
        
        return $result !== false;
    }
}
