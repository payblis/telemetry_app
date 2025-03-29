<?php
/**
 * Classe Moto
 * Gère les motos de l'application
 */
class Moto {
    private $db;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Créer une nouvelle moto
     * @param string $brand Marque de la moto
     * @param string $model Modèle de la moto
     * @param int $engineCapacity Cylindrée en cc
     * @param int $year Année de fabrication
     * @param int $userId ID de l'utilisateur propriétaire
     * @return int ID de la moto créée
     */
    public function create($brand, $model, $engineCapacity, $year, $userId) {
        $data = [
            'brand' => $brand,
            'model' => $model,
            'engine_capacity' => $engineCapacity,
            'year' => $year,
            'user_id' => $userId
        ];
        
        return $this->db->insert('motos', $data);
    }
    
    /**
     * Obtenir une moto par son ID
     * @param int $id ID de la moto
     * @return array|false Données de la moto ou false
     */
    public function getById($id) {
        $sql = "SELECT * FROM motos WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Mettre à jour une moto
     * @param int $id ID de la moto
     * @param array $data Données à mettre à jour
     * @return int Nombre de lignes affectées
     */
    public function update($id, $data) {
        return $this->db->update('motos', $data, 'id = ?', [$id]);
    }
    
    /**
     * Supprimer une moto
     * @param int $id ID de la moto
     * @return int Nombre de lignes affectées
     */
    public function delete($id) {
        return $this->db->delete('motos', 'id = ?', [$id]);
    }
    
    /**
     * Obtenir toutes les motos d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return array Liste des motos
     */
    public function getAllByUserId($userId) {
        $sql = "SELECT * FROM motos WHERE user_id = ? ORDER BY brand, model";
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    /**
     * Rechercher des motos par marque ou modèle
     * @param string $search Terme de recherche
     * @param int $userId ID de l'utilisateur (optionnel)
     * @return array Liste des motos correspondantes
     */
    public function search($search, $userId = null) {
        if ($userId) {
            $sql = "SELECT * FROM motos WHERE (brand LIKE ? OR model LIKE ?) AND user_id = ? ORDER BY brand, model";
            return $this->db->fetchAll($sql, ['%' . $search . '%', '%' . $search . '%', $userId]);
        } else {
            $sql = "SELECT * FROM motos WHERE brand LIKE ? OR model LIKE ? ORDER BY brand, model";
            return $this->db->fetchAll($sql, ['%' . $search . '%', '%' . $search . '%']);
        }
    }
    
    /**
     * Vérifier si une moto appartient à un utilisateur
     * @param int $motoId ID de la moto
     * @param int $userId ID de l'utilisateur
     * @return bool Résultat de la vérification
     */
    public function belongsToUser($motoId, $userId) {
        $sql = "SELECT id FROM motos WHERE id = ? AND user_id = ?";
        $result = $this->db->fetchOne($sql, [$motoId, $userId]);
        
        return $result !== false;
    }
    
    /**
     * Ajouter un réglage à une moto
     * @param int $motoId ID de la moto
     * @param string $name Nom du réglage
     * @param string $value Valeur du réglage
     * @param string $unit Unité de mesure (optionnel)
     * @param string $type Type de réglage (SUSPENSION, TRANSMISSION, ENGINE, TIRES, OTHER)
     * @return int ID du réglage créé
     */
    public function addSetting($motoId, $name, $value, $unit = null, $type = 'OTHER') {
        $data = [
            'moto_id' => $motoId,
            'setting_name' => $name,
            'setting_value' => $value,
            'setting_unit' => $unit,
            'setting_type' => $type
        ];
        
        return $this->db->insert('moto_settings', $data);
    }
    
    /**
     * Obtenir tous les réglages d'une moto
     * @param int $motoId ID de la moto
     * @param string $type Type de réglage (optionnel)
     * @return array Liste des réglages
     */
    public function getSettings($motoId, $type = null) {
        if ($type) {
            $sql = "SELECT * FROM moto_settings WHERE moto_id = ? AND setting_type = ? ORDER BY setting_name";
            return $this->db->fetchAll($sql, [$motoId, $type]);
        } else {
            $sql = "SELECT * FROM moto_settings WHERE moto_id = ? ORDER BY setting_type, setting_name";
            return $this->db->fetchAll($sql, [$motoId]);
        }
    }
    
    /**
     * Mettre à jour un réglage
     * @param int $settingId ID du réglage
     * @param string $value Nouvelle valeur
     * @return int Nombre de lignes affectées
     */
    public function updateSetting($settingId, $value) {
        $data = [
            'setting_value' => $value
        ];
        
        return $this->db->update('moto_settings', $data, 'id = ?', [$settingId]);
    }
    
    /**
     * Supprimer un réglage
     * @param int $settingId ID du réglage
     * @return int Nombre de lignes affectées
     */
    public function deleteSetting($settingId) {
        return $this->db->delete('moto_settings', 'id = ?', [$settingId]);
    }
}
