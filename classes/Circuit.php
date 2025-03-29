<?php
/**
 * Classe Circuit
 * Gère les circuits de l'application
 */
class Circuit {
    private $db;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Créer un nouveau circuit
     * @param string $name Nom du circuit
     * @param string $country Pays
     * @param int $length Longueur en mètres
     * @param int $width Largeur en mètres (optionnel)
     * @param int $cornersCount Nombre de virages (optionnel)
     * @return int ID du circuit créé
     */
    public function create($name, $country, $length, $width = null, $cornersCount = null) {
        $data = [
            'name' => $name,
            'country' => $country,
            'length' => $length,
            'width' => $width,
            'corners_count' => $cornersCount
        ];
        
        return $this->db->insert('circuits', $data);
    }
    
    /**
     * Obtenir un circuit par son ID
     * @param int $id ID du circuit
     * @return array|false Données du circuit ou false
     */
    public function getById($id) {
        $sql = "SELECT * FROM circuits WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Mettre à jour un circuit
     * @param int $id ID du circuit
     * @param array $data Données à mettre à jour
     * @return int Nombre de lignes affectées
     */
    public function update($id, $data) {
        return $this->db->update('circuits', $data, 'id = ?', [$id]);
    }
    
    /**
     * Supprimer un circuit
     * @param int $id ID du circuit
     * @return int Nombre de lignes affectées
     */
    public function delete($id) {
        return $this->db->delete('circuits', 'id = ?', [$id]);
    }
    
    /**
     * Obtenir tous les circuits
     * @param string $country Filtrer par pays (optionnel)
     * @return array Liste des circuits
     */
    public function getAll($country = null) {
        if ($country) {
            $sql = "SELECT * FROM circuits WHERE country = ? ORDER BY name";
            return $this->db->fetchAll($sql, [$country]);
        } else {
            $sql = "SELECT * FROM circuits ORDER BY country, name";
            return $this->db->fetchAll($sql);
        }
    }
    
    /**
     * Rechercher des circuits par nom
     * @param string $search Terme de recherche
     * @return array Liste des circuits correspondants
     */
    public function search($search) {
        $sql = "SELECT * FROM circuits WHERE name LIKE ? OR country LIKE ? ORDER BY name";
        return $this->db->fetchAll($sql, ['%' . $search . '%', '%' . $search . '%']);
    }
    
    /**
     * Ajouter un virage à un circuit
     * @param int $circuitId ID du circuit
     * @param int $cornerNumber Numéro du virage
     * @param string $cornerType Type de virage (LEFT, RIGHT, CHICANE)
     * @param int $angle Angle en degrés (optionnel)
     * @param int $estimatedSpeed Vitesse estimée en km/h (optionnel)
     * @param int $recommendedGear Rapport conseillé (optionnel)
     * @param string $notes Notes (optionnel)
     * @return int ID du virage créé
     */
    public function addCorner($circuitId, $cornerNumber, $cornerType, $angle = null, $estimatedSpeed = null, $recommendedGear = null, $notes = null) {
        $data = [
            'circuit_id' => $circuitId,
            'corner_number' => $cornerNumber,
            'corner_type' => $cornerType,
            'angle' => $angle,
            'estimated_speed' => $estimatedSpeed,
            'recommended_gear' => $recommendedGear,
            'notes' => $notes
        ];
        
        return $this->db->insert('circuit_corners', $data);
    }
    
    /**
     * Obtenir tous les virages d'un circuit
     * @param int $circuitId ID du circuit
     * @return array Liste des virages
     */
    public function getCorners($circuitId) {
        $sql = "SELECT * FROM circuit_corners WHERE circuit_id = ? ORDER BY corner_number";
        return $this->db->fetchAll($sql, [$circuitId]);
    }
    
    /**
     * Mettre à jour un virage
     * @param int $cornerId ID du virage
     * @param array $data Données à mettre à jour
     * @return int Nombre de lignes affectées
     */
    public function updateCorner($cornerId, $data) {
        return $this->db->update('circuit_corners', $data, 'id = ?', [$cornerId]);
    }
    
    /**
     * Supprimer un virage
     * @param int $cornerId ID du virage
     * @return int Nombre de lignes affectées
     */
    public function deleteCorner($cornerId) {
        return $this->db->delete('circuit_corners', 'id = ?', [$cornerId]);
    }
    
    /**
     * Importer les détails d'un circuit depuis ChatGPT
     * @param int $circuitId ID du circuit
     * @param array $cornersData Données des virages
     * @return bool Succès de l'importation
     */
    public function importCornersFromChatGPT($circuitId, $cornersData) {
        try {
            // Supprimer les virages existants
            $this->db->delete('circuit_corners', 'circuit_id = ?', [$circuitId]);
            
            // Ajouter les nouveaux virages
            foreach ($cornersData as $corner) {
                $this->addCorner(
                    $circuitId,
                    $corner['number'],
                    $corner['type'],
                    $corner['angle'] ?? null,
                    $corner['estimated_speed'] ?? null,
                    $corner['recommended_gear'] ?? null,
                    $corner['notes'] ?? null
                );
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
