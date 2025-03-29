<?php

class TelemetryData {
    private $db;
    private $sessionId;
    private $userId;

    public function __construct() {
        require_once 'Database.php';
        $this->db = new Database();
    }

    /**
     * Initialise une nouvelle session de télémétrie
     */
    public function initSession($sessionId, $userId) {
        $this->sessionId = $sessionId;
        $this->userId = $userId;
    }

    /**
     * Enregistre les données de télémétrie
     */
    public function saveTelemetryData($data) {
        try {
            $sql = "INSERT INTO telemetry_data (
                session_id, 
                timestamp,
                speed,
                rpm,
                gear,
                throttle,
                brake,
                lean_angle,
                latitude,
                longitude,
                acceleration_x,
                acceleration_y,
                acceleration_z,
                gyro_x,
                gyro_y,
                gyro_z
            ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $this->sessionId,
                $data['speed'],
                $data['rpm'],
                $data['gear'],
                $data['throttle'],
                $data['brake'],
                $data['lean_angle'],
                $data['latitude'],
                $data['longitude'],
                $data['acceleration_x'],
                $data['acceleration_y'],
                $data['acceleration_z'],
                $data['gyro_x'],
                $data['gyro_y'],
                $data['gyro_z']
            ];

            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement des données de télémétrie : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enregistre un nouveau tour
     */
    public function saveLap($lapData) {
        try {
            $sql = "INSERT INTO lap_times (
                session_id,
                lap_number,
                lap_time,
                sector1_time,
                sector2_time,
                sector3_time,
                max_speed,
                timestamp
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $params = [
                $this->sessionId,
                $lapData['lap_number'],
                $lapData['lap_time'],
                $lapData['sector1_time'],
                $lapData['sector2_time'],
                $lapData['sector3_time'],
                $lapData['max_speed']
            ];

            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement du tour : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les données de télémétrie pour un intervalle de temps
     */
    public function getTelemetryData($startTime, $endTime) {
        try {
            $sql = "SELECT * FROM telemetry_data 
                    WHERE session_id = ? 
                    AND timestamp BETWEEN ? AND ?
                    ORDER BY timestamp ASC";

            return $this->db->select($sql, [$this->sessionId, $startTime, $endTime]);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des données de télémétrie : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère tous les tours d'une session
     */
    public function getLaps() {
        try {
            $sql = "SELECT * FROM lap_times 
                    WHERE session_id = ? 
                    ORDER BY lap_number ASC";

            return $this->db->select($sql, [$this->sessionId]);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des tours : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère le meilleur tour de la session
     */
    public function getBestLap() {
        try {
            $sql = "SELECT * FROM lap_times 
                    WHERE session_id = ? 
                    ORDER BY lap_time ASC 
                    LIMIT 1";

            $result = $this->db->select($sql, [$this->sessionId]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération du meilleur tour : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère les statistiques de la session
     */
    public function getSessionStats() {
        try {
            $sql = "SELECT 
                    MAX(speed) as max_speed,
                    AVG(speed) as avg_speed,
                    MAX(rpm) as max_rpm,
                    AVG(rpm) as avg_rpm,
                    MAX(lean_angle) as max_lean_angle
                    FROM telemetry_data 
                    WHERE session_id = ?";

            $result = $this->db->select($sql, [$this->sessionId]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des statistiques : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Exporte les données de télémétrie au format CSV
     */
    public function exportToCsv($startTime, $endTime) {
        try {
            $data = $this->getTelemetryData($startTime, $endTime);
            if (empty($data)) {
                return false;
            }

            $filename = "telemetry_data_" . $this->sessionId . "_" . date('Y-m-d_H-i-s') . ".csv";
            $filepath = "../exports/" . $filename;

            $fp = fopen($filepath, 'w');
            
            // En-têtes
            fputcsv($fp, array_keys($data[0]));
            
            // Données
            foreach ($data as $row) {
                fputcsv($fp, $row);
            }
            
            fclose($fp);
            return $filename;
        } catch (Exception $e) {
            error_log("Erreur lors de l'export CSV : " . $e->getMessage());
            return false;
        }
    }
} 