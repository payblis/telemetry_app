<?php

namespace App\Models;

class Session extends Model
{
    protected $table = 'sessions';

    public function getRecent(int $limit = 5): array
    {
        $sql = "
            SELECT 
                s.*,
                p.name as pilot_name,
                c.nom as circuit_name,
                MIN(t.temps_tour) as best_lap
            FROM sessions s
            LEFT JOIN pilotes p ON s.pilote_id = p.id
            LEFT JOIN circuits c ON s.circuit_id = c.id
            LEFT JOIN tours t ON s.id = t.session_id
            GROUP BY s.id
            ORDER BY s.date_session DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }

    public function getBestLapTimes(int $limit = 5): array
    {
        $sql = "
            SELECT 
                t.temps_tour,
                s.date_session,
                p.name as pilot_name,
                c.nom as circuit_name
            FROM tours t
            JOIN sessions s ON t.session_id = s.id
            JOIN pilotes p ON s.pilote_id = p.id
            JOIN circuits c ON s.circuit_id = c.id
            ORDER BY t.temps_tour ASC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getDetailedStats(int $sessionId): array
    {
        $sql = "
            SELECT 
                s.*,
                p.name as pilot_name,
                c.nom as circuit_name,
                COUNT(t.id) as total_laps,
                MIN(t.temps_tour) as best_lap,
                AVG(t.temps_tour) as avg_lap,
                MAX(td.valeur) as max_speed,
                AVG(td.valeur) as avg_speed
            FROM sessions s
            LEFT JOIN pilotes p ON s.pilote_id = p.id
            LEFT JOIN circuits c ON s.circuit_id = c.id
            LEFT JOIN tours t ON s.id = t.session_id
            LEFT JOIN telemetry_data td ON t.id = td.tour_id AND td.type_donnee = 'speed'
            WHERE s.id = ?
            GROUP BY s.id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sessionId]);
        return $stmt->fetch();
    }

    public function getTelemetryGraphData(int $sessionId, string $dataType = 'speed'): array
    {
        $sql = "
            SELECT 
                td.timestamp,
                td.valeur
            FROM telemetry_data td
            JOIN tours t ON td.tour_id = t.id
            WHERE t.session_id = ? AND td.type_donnee = ?
            ORDER BY td.timestamp ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sessionId, $dataType]);
        return $stmt->fetchAll();
    }

    public function getLatestTelemetryData(): array
    {
        $sql = "
            SELECT 
                td.*,
                t.numero_tour,
                s.id as session_id
            FROM telemetry_data td
            JOIN tours t ON td.tour_id = t.id
            JOIN sessions s ON t.session_id = s.id
            WHERE s.id = (
                SELECT id FROM sessions ORDER BY date_session DESC LIMIT 1
            )
            ORDER BY td.timestamp DESC
            LIMIT 100
        ";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getAIRecommendations(int $sessionId): array
    {
        // Get session data for AI context
        $sessionData = $this->getDetailedStats($sessionId);
        
        // Get telemetry data for analysis
        $telemetryData = $this->getTelemetryGraphData($sessionId);
        
        // TODO: Implement AI analysis using OpenAI API
        // For now, return dummy recommendations
        return [
            [
                'title' => 'Braking Analysis',
                'description' => 'Consider braking later into Turn 3, current data shows early braking pattern.'
            ],
            [
                'title' => 'Speed Optimization',
                'description' => 'Maintain higher speed through the middle sector, particularly between turns 5 and 7.'
            ],
            [
                'title' => 'Line Improvement',
                'description' => 'Adjust racing line in final sector to optimize exit speed onto main straight.'
            ]
        ];
    }

    public function create(array $data)
    {
        try {
            $this->db->beginTransaction();

            // Create session
            $sessionId = parent::create($data);

            // Initialize telemetry data if provided
            if (isset($data['telemetry_data'])) {
                $this->importTelemetryData($sessionId['id'], $data['telemetry_data']);
            }

            $this->db->commit();
            return $sessionId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function importTelemetryData(int $sessionId, array $telemetryData)
    {
        // Create tour record
        $stmt = $this->db->prepare("
            INSERT INTO tours (session_id, numero_tour, temps_tour)
            VALUES (?, ?, ?)
        ");

        foreach ($telemetryData as $lap) {
            $stmt->execute([
                $sessionId,
                $lap['lap_number'],
                $lap['lap_time']
            ]);

            $tourId = $this->db->lastInsertId();

            // Insert telemetry data points
            $dataStmt = $this->db->prepare("
                INSERT INTO telemetry_data (tour_id, timestamp, type_donnee, valeur)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($lap['data_points'] as $point) {
                $dataStmt->execute([
                    $tourId,
                    $point['timestamp'],
                    $point['type'],
                    $point['value']
                ]);
            }
        }
    }
} 