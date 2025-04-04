<?php
/**
 * Modèle pour la gestion des données télémétriques
 */
namespace App\Models;

class TelemetrieModel extends Model {
    protected $table = 'telemetrie_donnees';
    
    /**
     * Importer des données depuis un fichier JSON Sensor Logger
     * 
     * @param string $filePath Chemin du fichier JSON
     * @param int $sessionId ID de la session
     * @return array Résultat de l'importation
     */
    public function importSensorLoggerData($filePath, $sessionId) {
        try {
            // Vérifier si le fichier existe
            if (!file_exists($filePath)) {
                throw new \Exception("Le fichier n'existe pas");
            }
            
            // Lire le contenu du fichier
            $jsonContent = file_get_contents($filePath);
            if (!$jsonContent) {
                throw new \Exception("Impossible de lire le fichier");
            }
            
            // Décoder le JSON
            $data = json_decode($jsonContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Format JSON invalide: " . json_last_error_msg());
            }
            
            // Vérifier la structure du fichier Sensor Logger
            if (!isset($data['sensorData']) || !is_array($data['sensorData'])) {
                throw new \Exception("Format Sensor Logger invalide: données de capteurs manquantes");
            }
            
            // Récupérer la session
            $sessionModel = new SessionModel();
            $session = $sessionModel->find($sessionId);
            if (!$session) {
                throw new \Exception("Session introuvable");
            }
            
            // Démarrer une transaction
            $this->db->beginTransaction();
            
            // Statistiques d'importation
            $stats = [
                'total_records' => count($data['sensorData']),
                'imported_records' => 0,
                'errors' => 0,
                'tours_detectes' => 0
            ];
            
            // Traiter les données
            $this->processAndStoreSensorData($data, $sessionId, $stats);
            
            // Générer les tours
            $this->detectAndCreateLaps($sessionId, $stats);
            
            // Agréger les données par tour
            $this->aggregateDataByLap($sessionId);
            
            // Valider la transaction
            $this->db->commit();
            
            return [
                'success' => true,
                'stats' => $stats,
                'message' => "Importation réussie: {$stats['imported_records']} enregistrements importés, {$stats['tours_detectes']} tours détectés"
            ];
            
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            
            return [
                'success' => false,
                'message' => "Erreur lors de l'importation: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Traiter et stocker les données des capteurs
     * 
     * @param array $data Données JSON décodées
     * @param int $sessionId ID de la session
     * @param array &$stats Statistiques d'importation (référence)
     */
    private function processAndStoreSensorData($data, $sessionId, &$stats) {
        // Préparer la requête d'insertion
        $stmt = $this->db->prepare("
            INSERT INTO telemetrie_donnees (
                session_id, timestamp, latitude, longitude, altitude, 
                vitesse, acceleration_x, acceleration_y, acceleration_z,
                gyroscope_x, gyroscope_y, gyroscope_z,
                inclinaison, angle_direction, temperature_ambiante,
                pression_avant, pression_arriere, force_freinage,
                regime_moteur, rapport_engage, angle_papillon,
                temperature_moteur, temperature_pneu_avant, temperature_pneu_arriere
            ) VALUES (
                :session_id, :timestamp, :latitude, :longitude, :altitude,
                :vitesse, :acceleration_x, :acceleration_y, :acceleration_z,
                :gyroscope_x, :gyroscope_y, :gyroscope_z,
                :inclinaison, :angle_direction, :temperature_ambiante,
                :pression_avant, :pression_arriere, :force_freinage,
                :regime_moteur, :rapport_engage, :angle_papillon,
                :temperature_moteur, :temperature_pneu_avant, :temperature_pneu_arriere
            )
        ");
        
        // Traiter chaque enregistrement
        foreach ($data['sensorData'] as $record) {
            try {
                // Extraire les données des capteurs
                $timestamp = isset($record['timestamp']) ? $record['timestamp'] : null;
                
                // Données GPS
                $latitude = $this->extractSensorValue($record, 'location', 'latitude');
                $longitude = $this->extractSensorValue($record, 'location', 'longitude');
                $altitude = $this->extractSensorValue($record, 'location', 'altitude');
                $vitesse = $this->extractSensorValue($record, 'location', 'speed');
                
                // Accéléromètre
                $acceleration_x = $this->extractSensorValue($record, 'accelerometer', 'x');
                $acceleration_y = $this->extractSensorValue($record, 'accelerometer', 'y');
                $acceleration_z = $this->extractSensorValue($record, 'accelerometer', 'z');
                
                // Gyroscope
                $gyroscope_x = $this->extractSensorValue($record, 'gyroscope', 'x');
                $gyroscope_y = $this->extractSensorValue($record, 'gyroscope', 'y');
                $gyroscope_z = $this->extractSensorValue($record, 'gyroscope', 'z');
                
                // Autres capteurs
                $inclinaison = $this->extractSensorValue($record, 'orientation', 'roll');
                $angle_direction = $this->extractSensorValue($record, 'orientation', 'yaw');
                $temperature_ambiante = $this->extractSensorValue($record, 'environment', 'temperature');
                
                // Données spécifiques à la moto (peuvent être nulles si non disponibles)
                $pression_avant = $this->extractSensorValue($record, 'custom', 'frontPressure');
                $pression_arriere = $this->extractSensorValue($record, 'custom', 'rearPressure');
                $force_freinage = $this->extractSensorValue($record, 'custom', 'brakingForce');
                $regime_moteur = $this->extractSensorValue($record, 'custom', 'rpm');
                $rapport_engage = $this->extractSensorValue($record, 'custom', 'gear');
                $angle_papillon = $this->extractSensorValue($record, 'custom', 'throttle');
                $temperature_moteur = $this->extractSensorValue($record, 'custom', 'engineTemp');
                $temperature_pneu_avant = $this->extractSensorValue($record, 'custom', 'frontTireTemp');
                $temperature_pneu_arriere = $this->extractSensorValue($record, 'custom', 'rearTireTemp');
                
                // Insérer les données
                $stmt->execute([
                    'session_id' => $sessionId,
                    'timestamp' => $timestamp,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'altitude' => $altitude,
                    'vitesse' => $vitesse,
                    'acceleration_x' => $acceleration_x,
                    'acceleration_y' => $acceleration_y,
                    'acceleration_z' => $acceleration_z,
                    'gyroscope_x' => $gyroscope_x,
                    'gyroscope_y' => $gyroscope_y,
                    'gyroscope_z' => $gyroscope_z,
                    'inclinaison' => $inclinaison,
                    'angle_direction' => $angle_direction,
                    'temperature_ambiante' => $temperature_ambiante,
                    'pression_avant' => $pression_avant,
                    'pression_arriere' => $pression_arriere,
                    'force_freinage' => $force_freinage,
                    'regime_moteur' => $regime_moteur,
                    'rapport_engage' => $rapport_engage,
                    'angle_papillon' => $angle_papillon,
                    'temperature_moteur' => $temperature_moteur,
                    'temperature_pneu_avant' => $temperature_pneu_avant,
                    'temperature_pneu_arriere' => $temperature_pneu_arriere
                ]);
                
                $stats['imported_records']++;
                
            } catch (\Exception $e) {
                $stats['errors']++;
                // Continuer malgré l'erreur
                continue;
            }
        }
    }
    
    /**
     * Extraire la valeur d'un capteur des données JSON
     * 
     * @param array $record Enregistrement de données
     * @param string $sensorType Type de capteur
     * @param string $valueName Nom de la valeur
     * @return float|null Valeur du capteur ou null si non disponible
     */
    private function extractSensorValue($record, $sensorType, $valueName) {
        if (isset($record[$sensorType]) && isset($record[$sensorType][$valueName])) {
            return $record[$sensorType][$valueName];
        }
        return null;
    }
    
    /**
     * Détecter et créer les tours
     * 
     * @param int $sessionId ID de la session
     * @param array &$stats Statistiques d'importation (référence)
     */
    private function detectAndCreateLaps($sessionId, &$stats) {
        // Récupérer les données de la session
        $stmt = $this->db->prepare("
            SELECT * FROM telemetrie_donnees 
            WHERE session_id = :session_id 
            ORDER BY timestamp ASC
        ");
        $stmt->execute(['session_id' => $sessionId]);
        $data = $stmt->fetchAll();
        
        if (count($data) < 10) {
            // Pas assez de données pour détecter des tours
            return;
        }
        
        // Récupérer les informations du circuit
        $stmt = $this->db->prepare("
            SELECT c.* FROM circuits c
            JOIN sessions s ON c.id = s.circuit_id
            WHERE s.id = :session_id
        ");
        $stmt->execute(['session_id' => $sessionId]);
        $circuit = $stmt->fetch();
        
        if (!$circuit) {
            // Circuit non trouvé
            return;
        }
        
        // Détecter la ligne de départ/arrivée
        $startLine = $this->detectStartLine($data);
        
        if (!$startLine) {
            // Ligne de départ/arrivée non détectée
            return;
        }
        
        // Détecter les tours
        $laps = $this->detectLaps($data, $startLine);
        
        // Insérer les tours dans la base de données
        $stmt = $this->db->prepare("
            INSERT INTO tours (
                session_id, numero_tour, heure_debut, heure_fin, 
                temps, valide, meilleur_tour, notes
            ) VALUES (
                :session_id, :numero_tour, :heure_debut, :heure_fin,
                :temps, :valide, :meilleur_tour, :notes
            )
        ");
        
        $bestLapTime = PHP_INT_MAX;
        $bestLapId = null;
        
        foreach ($laps as $index => $lap) {
            $lapNumber = $index + 1;
            $lapTime = $lap['end_time'] - $lap['start_time'];
            $valid = $lapTime > 0 && $lapTime < 600; // Moins de 10 minutes
            
            $stmt->execute([
                'session_id' => $sessionId,
                'numero_tour' => $lapNumber,
                'heure_debut' => date('Y-m-d H:i:s', $lap['start_time']),
                'heure_fin' => date('Y-m-d H:i:s', $lap['end_time']),
                'temps' => $lapTime,
                'valide' => $valid ? 1 : 0,
                'meilleur_tour' => 0, // Sera mis à jour plus tard
                'notes' => ''
            ]);
            
            $lapId = $this->db->lastInsertId();
            
            // Vérifier si c'est le meilleur tour
            if ($valid && $lapTime < $bestLapTime) {
                $bestLapTime = $lapTime;
                $bestLapId = $lapId;
            }
            
            $stats['tours_detectes']++;
        }
        
        // Mettre à jour le meilleur tour
        if ($bestLapId) {
            $stmt = $this->db->prepare("
                UPDATE tours SET meilleur_tour = 1 WHERE id = :id
            ");
            $stmt->execute(['id' => $bestLapId]);
        }
    }
    
    /**
     * Détecter la ligne de départ/arrivée
     * 
     * @param array $data Données télémétriques
     * @return array|null Coordonnées de la ligne de départ/arrivée ou null si non détectée
     */
    private function detectStartLine($data) {
        // Algorithme simplifié pour détecter la ligne de départ/arrivée
        // Dans une implémentation réelle, cela serait plus complexe
        
        // Utiliser le premier point avec des coordonnées GPS valides
        foreach ($data as $point) {
            if (!empty($point['latitude']) && !empty($point['longitude'])) {
                return [
                    'latitude' => $point['latitude'],
                    'longitude' => $point['longitude']
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Détecter les tours
     * 
     * @param array $data Données télémétriques
     * @param array $startLine Coordonnées de la ligne de départ/arrivée
     * @return array Liste des tours détectés
     */
    private function detectLaps($data, $startLine) {
        $laps = [];
        $currentLap = null;
        $lastPoint = null;
        $threshold = 0.0001; // Seuil de proximité pour la ligne de départ/arrivée
        
        foreach ($data as $point) {
            if (empty($point['latitude']) || empty($point['longitude'])) {
                continue;
            }
            
            // Calculer la distance par rapport à la ligne de départ/arrivée
            $distance = $this->calculateDistance(
                $point['latitude'], $point['longitude'],
                $startLine['latitude'], $startLine['longitude']
            );
            
            // Vérifier si on franchit la ligne de départ/arrivée
            if ($distance < $threshold) {
                if ($currentLap === null) {
                    // Début du premier tour
                    $currentLap = [
                        'start_time' => strtotime($point['timestamp']),
                        'start_index' => array_search($point, $data)
                    ];
                } else if ($lastPoint && $this->calculateDistance(
                    $lastPoint['latitude'], $lastPoint['longitude'],
                    $startLine['latitude'], $startLine['longitude']
                ) >= $threshold) {
                    // Fin d'un tour et début du suivant
                    $currentLap['end_time'] = strtotime($point['timestamp']);
                    $currentLap['end_index'] = array_search($point, $data);
                    $laps[] = $currentLap;
                    
                    // Commencer un nouveau tour
                    $currentLap = [
                        'start_time' => strtotime($point['timestamp']),
                        'start_index' => array_search($point, $data)
                    ];
                }
            }
            
            $lastPoint = $point;
        }
        
        // Ajouter le dernier tour s'il est incomplet
        if ($currentLap && !isset($currentLap['end_time']) && $lastPoint) {
            $currentLap['end_time'] = strtotime($lastPoint['timestamp']);
            $currentLap['end_index'] = array_search($lastPoint, $data);
            $laps[] = $currentLap;
        }
        
        return $laps;
    }
    
    /**
     * Calculer la distance entre deux points GPS
     * 
     * @param float $lat1 Latitude du premier point
     * @param float $lon1 Longitude du premier point
     * @param float $lat2 Latitude du deuxième point
     * @param float $lon2 Longitude du deuxième point
     * @return float Distance en mètres
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; // Rayon de la Terre en mètres
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;
        
        return $distance;
    }
    
    /**
     * Agréger les données par tour
     * 
     * @param int $sessionId ID de la session
     */
    private function aggregateDataByLap($sessionId) {
        // Récupérer les tours
        $stmt = $this->db->prepare("
            SELECT * FROM tours 
            WHERE session_id = :session_id 
            ORDER BY numero_tour
        ");
        $stmt->execute(['session_id' => $sessionId]);
        $tours = $stmt->fetchAll();
        
        foreach ($tours as $tour) {
            // Récupérer les données du tour
            $stmt = $this->db->prepare("
                SELECT * FROM telemetrie_donnees 
                WHERE session_id = :session_id 
                AND timestamp BETWEEN :debut AND :fin
                ORDER BY timestamp
            ");
            $stmt->execute([
                'session_id' => $sessionId,
                'debut' => $tour['heure_debut'],
                'fin' => $tour['heure_fin']
            ]);
            $tourData = $stmt->fetchAll();
            
            if (count($tourData) < 2) {
                continue; // Pas assez de données
            }
            
            // Calculer les agrégations
            $vitesseMax = 0;
            $vitesseMoy = 0;
            $accelerationMax = 0;
            $freinageMax = 0;
            $inclinaisonMax = 0;
            $regimeMoteurMax = 0;
            $distanceParcourue = 0;
            
            $vitesseSum = 0;
            $count = count($tourData);
            $lastPoint = null;
            
            foreach ($tourData as $point) {
                // Vitesse
                $vitesse = floatval($point['vitesse']);
                $vitesseSum += $vitesse;
                if ($vitesse > $vitesseMax) {
                    $vitesseMax = $vitesse;
                }
                
                // Accélération
                $acceleration = sqrt(
                    pow(floatval($point['acceleration_x']), 2) +
                    pow(floatval($point['acceleration_y']), 2) +
                    pow(floatval($point['acceleration_z']), 2)
                );
                if ($acceleration > $accelerationMax) {
                    $accelerationMax = $acceleration;
                }
                
                // Freinage
                $freinage = floatval($point['force_freinage']);
                if ($freinage > $freinageMax) {
                    $freinageMax = $freinage;
                }
                
                // Inclinaison
                $inclinaison = abs(floatval($point['inclinaison']));
                if ($inclinaison > $inclinaisonMax) {
                    $inclinaisonMax = $inclinaison;
                }
                
                // Régime moteur
                $regimeMoteur = floatval($point['regime_moteur']);
                if ($regimeMoteur > $regimeMoteurMax) {
                    $regimeMoteurMax = $regimeMoteur;
                }
                
                // Distance parcourue
                if ($lastPoint && !empty($point['latitude']) && !empty($point['longitude']) &&
                    !empty($lastPoint['latitude']) && !empty($lastPoint['longitude'])) {
                    $distance = $this->calculateDistance(
                        $point['latitude'], $point['longitude'],
                        $lastPoint['latitude'], $lastPoint['longitude']
                    );
                    $distanceParcourue += $distance;
                }
                
                $lastPoint = $point;
            }
            
            $vitesseMoy = $vitesseSum / $count;
            
            // Insérer les données agrégées
            $stmt = $this->db->prepare("
                INSERT INTO telemetrie_agregee (
                    tour_id, vitesse_max, vitesse_moyenne, acceleration_max,
                    freinage_max, inclinaison_max, regime_moteur_max, distance_parcourue
                ) VALUES (
                    :tour_id, :vitesse_max, :vitesse_moyenne, :acceleration_max,
                    :freinage_max, :inclinaison_max, :regime_moteur_max, :distance_parcourue
                )
            ");
            
            $stmt->execute([
                'tour_id' => $tour['id'],
                'vitesse_max' => $vitesseMax,
                'vitesse_moyenne' => $vitesseMoy,
                'acceleration_max' => $accelerationMax,
                'freinage_max' => $freinageMax,
                'inclinaison_max' => $inclinaisonMax,
                'regime_moteur_max' => $regimeMoteurMax,
                'distance_parcourue' => $distanceParcourue
            ]);
        }
    }
    
    /**
     * Récupérer les données télémétriques d'un tour
     * 
     * @param int $tourId ID du tour
     * @return array Données télémétriques
     */
    public function getTourData($tourId) {
        // Récupérer les informations du tour
        $stmt = $this->db->prepare("
            SELECT t.*, s.id as session_id, s.circuit_id, s.pilote_id, s.moto_id
            FROM tours t
            JOIN sessions s ON t.session_id = s.id
            WHERE t.id = :tour_id
        ");
        $stmt->execute(['tour_id' => $tourId]);
        $tour = $stmt->fetch();
        
        if (!$tour) {
            return null;
        }
        
        // Récupérer les données télémétriques du tour
        $stmt = $this->db->prepare("
            SELECT * FROM telemetrie_donnees 
            WHERE session_id = :session_id 
            AND timestamp BETWEEN :debut AND :fin
            ORDER BY timestamp
        ");
        $stmt->execute([
            'session_id' => $tour['session_id'],
            'debut' => $tour['heure_debut'],
            'fin' => $tour['heure_fin']
        ]);
        $telemetrie = $stmt->fetchAll();
        
        // Récupérer les données agrégées
        $stmt = $this->db->prepare("
            SELECT * FROM telemetrie_agregee 
            WHERE tour_id = :tour_id
        ");
        $stmt->execute(['tour_id' => $tourId]);
        $agregation = $stmt->fetch();
        
        return [
            'tour' => $tour,
            'telemetrie' => $telemetrie,
            'agregation' => $agregation
        ];
    }
    
    /**
     * Récupérer les données télémétriques d'une session
     * 
     * @param int $sessionId ID de la session
     * @return array Données télémétriques
     */
    public function getSessionData($sessionId) {
        // Récupérer les informations de la session
        $sessionModel = new SessionModel();
        $session = $sessionModel->find($sessionId);
        
        if (!$session) {
            return null;
        }
        
        // Récupérer les tours de la session
        $stmt = $this->db->prepare("
            SELECT * FROM tours 
            WHERE session_id = :session_id 
            ORDER BY numero_tour
        ");
        $stmt->execute(['session_id' => $sessionId]);
        $tours = $stmt->fetchAll();
        
        // Récupérer les agrégations pour chaque tour
        $toursData = [];
        foreach ($tours as $tour) {
            $stmt = $this->db->prepare("
                SELECT * FROM telemetrie_agregee 
                WHERE tour_id = :tour_id
            ");
            $stmt->execute(['tour_id' => $tour['id']]);
            $agregation = $stmt->fetch();
            
            $toursData[] = [
                'tour' => $tour,
                'agregation' => $agregation
            ];
        }
        
        // Récupérer les statistiques globales de la session
        $stats = $this->calculateSessionStats($sessionId, $tours);
        
        return [
            'session' => $session,
            'tours' => $toursData,
            'stats' => $stats
        ];
    }
    
    /**
     * Calculer les statistiques d'une session
     * 
     * @param int $sessionId ID de la session
     * @param array $tours Tours de la session
     * @return array Statistiques de la session
     */
    private function calculateSessionStats($sessionId, $tours) {
        $stats = [
            'total_tours' => count($tours),
            'tours_valides' => 0,
            'meilleur_tour' => null,
            'temps_total' => 0,
            'vitesse_max' => 0,
            'vitesse_moyenne' => 0,
            'distance_totale' => 0
        ];
        
        $vitesseSum = 0;
        $vitesseCount = 0;
        
        foreach ($tours as $tour) {
            if ($tour['valide']) {
                $stats['tours_valides']++;
                $stats['temps_total'] += $tour['temps'];
                
                if ($tour['meilleur_tour'] || ($stats['meilleur_tour'] === null || $tour['temps'] < $stats['meilleur_tour']['temps'])) {
                    $stats['meilleur_tour'] = $tour;
                }
                
                // Récupérer les agrégations du tour
                $stmt = $this->db->prepare("
                    SELECT * FROM telemetrie_agregee 
                    WHERE tour_id = :tour_id
                ");
                $stmt->execute(['tour_id' => $tour['id']]);
                $agregation = $stmt->fetch();
                
                if ($agregation) {
                    if ($agregation['vitesse_max'] > $stats['vitesse_max']) {
                        $stats['vitesse_max'] = $agregation['vitesse_max'];
                    }
                    
                    $vitesseSum += $agregation['vitesse_moyenne'];
                    $vitesseCount++;
                    
                    $stats['distance_totale'] += $agregation['distance_parcourue'];
                }
            }
        }
        
        if ($vitesseCount > 0) {
            $stats['vitesse_moyenne'] = $vitesseSum / $vitesseCount;
        }
        
        return $stats;
    }
    
    /**
     * Exporter les données télémétriques d'une session au format JSON
     * 
     * @param int $sessionId ID de la session
     * @return string|null Chemin du fichier JSON ou null en cas d'erreur
     */
    public function exportSessionData($sessionId) {
        try {
            // Récupérer les données de la session
            $data = $this->getSessionData($sessionId);
            
            if (!$data) {
                return null;
            }
            
            // Récupérer les données télémétriques complètes
            $stmt = $this->db->prepare("
                SELECT * FROM telemetrie_donnees 
                WHERE session_id = :session_id 
                ORDER BY timestamp
            ");
            $stmt->execute(['session_id' => $sessionId]);
            $telemetrie = $stmt->fetchAll();
            
            // Préparer les données pour l'export
            $exportData = [
                'session' => $data['session'],
                'tours' => $data['tours'],
                'stats' => $data['stats'],
                'telemetrie' => $telemetrie
            ];
            
            // Créer le répertoire d'export si nécessaire
            $exportDir = STORAGE_PATH . '/exports';
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
            }
            
            // Générer le nom du fichier
            $filename = 'session_' . $sessionId . '_' . date('Ymd_His') . '.json';
            $filePath = $exportDir . '/' . $filename;
            
            // Écrire les données dans le fichier
            file_put_contents($filePath, json_encode($exportData, JSON_PRETTY_PRINT));
            
            return $filePath;
            
        } catch (\Exception $e) {
            return null;
        }
    }
}
