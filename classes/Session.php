<?php
/**
 * Classe Session
 * Gère les sessions d'entraînement et de course
 */
class Session {
    private $db;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Créer une nouvelle session
     * @param string $date Date de la session (format YYYY-MM-DD)
     * @param string $sessionType Type de session (RACE, QUALIFYING, PRACTICE, TRAINING, TRACK_DAY)
     * @param int $pilotId ID du pilote
     * @param int $motoId ID de la moto
     * @param int $circuitId ID du circuit
     * @param string $weather Conditions météo (optionnel)
     * @param float $trackTemperature Température de la piste (optionnel)
     * @param float $airTemperature Température de l'air (optionnel)
     * @param string $notes Notes (optionnel)
     * @return int ID de la session créée
     */
    public function create($date, $sessionType, $pilotId, $motoId, $circuitId, $weather = null, $trackTemperature = null, $airTemperature = null, $notes = null) {
        $data = [
            'date' => $date,
            'session_type' => $sessionType,
            'pilot_id' => $pilotId,
            'moto_id' => $motoId,
            'circuit_id' => $circuitId,
            'weather' => $weather,
            'track_temperature' => $trackTemperature,
            'air_temperature' => $airTemperature,
            'notes' => $notes
        ];
        
        return $this->db->insert('sessions', $data);
    }
    
    /**
     * Obtenir une session par son ID
     * @param int $id ID de la session
     * @return array|false Données de la session ou false
     */
    public function getById($id) {
        $sql = "SELECT s.*, p.name as pilot_name, m.brand as moto_brand, m.model as moto_model, 
                c.name as circuit_name, c.country as circuit_country
                FROM sessions s
                JOIN pilots p ON s.pilot_id = p.id
                JOIN motos m ON s.moto_id = m.id
                JOIN circuits c ON s.circuit_id = c.id
                WHERE s.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Mettre à jour une session
     * @param int $id ID de la session
     * @param array $data Données à mettre à jour
     * @return int Nombre de lignes affectées
     */
    public function update($id, $data) {
        return $this->db->update('sessions', $data, 'id = ?', [$id]);
    }
    
    /**
     * Supprimer une session
     * @param int $id ID de la session
     * @return int Nombre de lignes affectées
     */
    public function delete($id) {
        return $this->db->delete('sessions', 'id = ?', [$id]);
    }
    
    /**
     * Obtenir toutes les sessions d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @param string $sessionType Filtrer par type de session (optionnel)
     * @return array Liste des sessions
     */
    public function getAllByUserId($userId, $sessionType = null) {
        if ($sessionType) {
            $sql = "SELECT s.*, p.name as pilot_name, m.brand as moto_brand, m.model as moto_model, 
                    c.name as circuit_name, c.country as circuit_country
                    FROM sessions s
                    JOIN pilots p ON s.pilot_id = p.id
                    JOIN motos m ON s.moto_id = m.id
                    JOIN circuits c ON s.circuit_id = c.id
                    WHERE p.user_id = ? AND s.session_type = ?
                    ORDER BY s.date DESC";
            return $this->db->fetchAll($sql, [$userId, $sessionType]);
        } else {
            $sql = "SELECT s.*, p.name as pilot_name, m.brand as moto_brand, m.model as moto_model, 
                    c.name as circuit_name, c.country as circuit_country
                    FROM sessions s
                    JOIN pilots p ON s.pilot_id = p.id
                    JOIN motos m ON s.moto_id = m.id
                    JOIN circuits c ON s.circuit_id = c.id
                    WHERE p.user_id = ?
                    ORDER BY s.date DESC";
            return $this->db->fetchAll($sql, [$userId]);
        }
    }
    
    /**
     * Obtenir toutes les sessions d'un pilote
     * @param int $pilotId ID du pilote
     * @return array Liste des sessions
     */
    public function getAllByPilotId($pilotId) {
        $sql = "SELECT s.*, m.brand as moto_brand, m.model as moto_model, 
                c.name as circuit_name, c.country as circuit_country
                FROM sessions s
                JOIN motos m ON s.moto_id = m.id
                JOIN circuits c ON s.circuit_id = c.id
                WHERE s.pilot_id = ?
                ORDER BY s.date DESC";
        return $this->db->fetchAll($sql, [$pilotId]);
    }
    
    /**
     * Obtenir toutes les sessions d'un circuit
     * @param int $circuitId ID du circuit
     * @return array Liste des sessions
     */
    public function getAllByCircuitId($circuitId) {
        $sql = "SELECT s.*, p.name as pilot_name, m.brand as moto_brand, m.model as moto_model
                FROM sessions s
                JOIN pilots p ON s.pilot_id = p.id
                JOIN motos m ON s.moto_id = m.id
                WHERE s.circuit_id = ?
                ORDER BY s.date DESC";
        return $this->db->fetchAll($sql, [$circuitId]);
    }
    
    /**
     * Ajouter un temps au tour
     * @param int $sessionId ID de la session
     * @param int $lapNumber Numéro du tour
     * @param float $timeSeconds Temps en secondes
     * @param string $notes Notes (optionnel)
     * @return int ID du temps au tour créé
     */
    public function addLapTime($sessionId, $lapNumber, $timeSeconds, $notes = null) {
        $data = [
            'session_id' => $sessionId,
            'lap_number' => $lapNumber,
            'time_seconds' => $timeSeconds,
            'notes' => $notes
        ];
        
        return $this->db->insert('lap_times', $data);
    }
    
    /**
     * Obtenir tous les temps au tour d'une session
     * @param int $sessionId ID de la session
     * @return array Liste des temps au tour
     */
    public function getLapTimes($sessionId) {
        $sql = "SELECT * FROM lap_times WHERE session_id = ? ORDER BY lap_number";
        return $this->db->fetchAll($sql, [$sessionId]);
    }
    
    /**
     * Mettre à jour un temps au tour
     * @param int $lapTimeId ID du temps au tour
     * @param float $timeSeconds Nouveau temps en secondes
     * @param string $notes Nouvelles notes (optionnel)
     * @return int Nombre de lignes affectées
     */
    public function updateLapTime($lapTimeId, $timeSeconds, $notes = null) {
        $data = [
            'time_seconds' => $timeSeconds
        ];
        
        if ($notes !== null) {
            $data['notes'] = $notes;
        }
        
        return $this->db->update('lap_times', $data, 'id = ?', [$lapTimeId]);
    }
    
    /**
     * Supprimer un temps au tour
     * @param int $lapTimeId ID du temps au tour
     * @return int Nombre de lignes affectées
     */
    public function deleteLapTime($lapTimeId) {
        return $this->db->delete('lap_times', 'id = ?', [$lapTimeId]);
    }
    
    /**
     * Obtenir le meilleur temps au tour d'une session
     * @param int $sessionId ID de la session
     * @return array|false Données du meilleur temps ou false
     */
    public function getBestLapTime($sessionId) {
        $sql = "SELECT * FROM lap_times WHERE session_id = ? ORDER BY time_seconds ASC LIMIT 1";
        return $this->db->fetchOne($sql, [$sessionId]);
    }
    
    /**
     * Calculer le temps moyen au tour d'une session
     * @param int $sessionId ID de la session
     * @return float|false Temps moyen ou false
     */
    public function getAverageLapTime($sessionId) {
        $sql = "SELECT AVG(time_seconds) as average FROM lap_times WHERE session_id = ?";
        $result = $this->db->fetchOne($sql, [$sessionId]);
        
        return $result ? $result['average'] : false;
    }
}
