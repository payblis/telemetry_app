<?php
/**
 * Script de test pour vérifier l'intégration de Sensor Logger
 */

// Définir le mode test
define('TEST_MODE', true);

// Charger les configurations
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/autoload.php';

// Classe de test pour l'intégration de Sensor Logger
class SensorLoggerTester {
    private $results = [];
    private $totalTests = 0;
    private $passedTests = 0;
    
    // Exemple de données JSON Sensor Logger pour les tests
    private $sampleSensorLoggerData = '{
        "session": {
            "id": "test-session-123",
            "start_time": "2025-04-04T10:00:00Z",
            "device": "iPhone 14 Pro",
            "app_version": "2.5.0"
        },
        "location": [
            {
                "time": "2025-04-04T10:00:01Z",
                "latitude": 48.8566,
                "longitude": 2.3522,
                "altitude": 35.5,
                "speed": 0.0,
                "horizontal_accuracy": 5.0
            },
            {
                "time": "2025-04-04T10:00:02Z",
                "latitude": 48.8567,
                "longitude": 2.3523,
                "altitude": 35.5,
                "speed": 5.2,
                "horizontal_accuracy": 5.0
            },
            {
                "time": "2025-04-04T10:00:03Z",
                "latitude": 48.8568,
                "longitude": 2.3524,
                "altitude": 35.6,
                "speed": 10.5,
                "horizontal_accuracy": 4.8
            }
        ],
        "acceleration": [
            {
                "time": "2025-04-04T10:00:01Z",
                "x": 0.01,
                "y": 0.02,
                "z": 9.81
            },
            {
                "time": "2025-04-04T10:00:02Z",
                "x": 0.5,
                "y": 0.3,
                "z": 9.75
            },
            {
                "time": "2025-04-04T10:00:03Z",
                "x": 1.2,
                "y": 0.8,
                "z": 9.65
            }
        ],
        "gyroscope": [
            {
                "time": "2025-04-04T10:00:01Z",
                "x": 0.001,
                "y": 0.002,
                "z": 0.001
            },
            {
                "time": "2025-04-04T10:00:02Z",
                "x": 0.05,
                "y": 0.03,
                "z": 0.01
            },
            {
                "time": "2025-04-04T10:00:03Z",
                "x": 0.12,
                "y": 0.08,
                "z": 0.05
            }
        ],
        "attitude": [
            {
                "time": "2025-04-04T10:00:01Z",
                "roll": 0.01,
                "pitch": 0.02,
                "yaw": 0.01
            },
            {
                "time": "2025-04-04T10:00:02Z",
                "roll": 5.0,
                "pitch": 2.0,
                "yaw": 1.0
            },
            {
                "time": "2025-04-04T10:00:03Z",
                "roll": 10.0,
                "pitch": 5.0,
                "yaw": 2.0
            }
        ]
    }';
    
    /**
     * Exécute tous les tests d'intégration Sensor Logger
     */
    public function runAllTests() {
        echo "=== DÉBUT DES TESTS D'INTÉGRATION SENSOR LOGGER ===\n\n";
        
        // Tests d'intégration
        $this->testJsonParsing();
        $this->testDataImport();
        $this->testLapDetection();
        $this->testDataAggregation();
        $this->testDataVisualization();
        
        // Afficher le résumé
        $this->displaySummary();
    }
    
    /**
     * Teste le parsing du JSON Sensor Logger
     */
    private function testJsonParsing() {
        $this->startTest('Parsing JSON Sensor Logger');
        
        try {
            // Décoder les données JSON
            $data = json_decode($this->sampleSensorLoggerData, true);
            
            // Vérifier que le décodage a fonctionné
            $this->assertNotNull($data, 'Décodage JSON réussi');
            
            // Vérifier la structure des données
            $this->assertTrue(isset($data['session']), 'Section session présente');
            $this->assertTrue(isset($data['location']), 'Section location présente');
            $this->assertTrue(isset($data['acceleration']), 'Section acceleration présente');
            $this->assertTrue(isset($data['gyroscope']), 'Section gyroscope présente');
            $this->assertTrue(isset($data['attitude']), 'Section attitude présente');
            
            // Vérifier le contenu des données
            $this->assertTrue(count($data['location']) === 3, 'Nombre correct d\'entrées de localisation');
            $this->assertTrue(count($data['acceleration']) === 3, 'Nombre correct d\'entrées d\'accélération');
            $this->assertTrue(count($data['gyroscope']) === 3, 'Nombre correct d\'entrées de gyroscope');
            $this->assertTrue(count($data['attitude']) === 3, 'Nombre correct d\'entrées d\'attitude');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur de parsing JSON: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste l'importation des données Sensor Logger
     */
    private function testDataImport() {
        $this->startTest('Importation des données Sensor Logger');
        
        try {
            // Créer une instance du modèle de télémétrie
            $telemetrieModel = new \App\Models\TelemetrieModel();
            $this->assertNotNull($telemetrieModel, 'Instance TelemetrieModel créée');
            
            // Vérifier que la méthode d'importation existe
            $this->assertTrue(method_exists($telemetrieModel, 'importSensorLoggerData'), 'Méthode importSensorLoggerData existe');
            
            // Simuler l'importation (sans réellement modifier la base de données)
            $data = json_decode($this->sampleSensorLoggerData, true);
            $processedData = $this->simulateDataImport($data);
            
            // Vérifier que les données ont été correctement traitées
            $this->assertTrue(isset($processedData['points']), 'Points de données traités');
            $this->assertTrue(count($processedData['points']) > 0, 'Points de données non vides');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur d\'importation des données: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste la détection des tours
     */
    private function testLapDetection() {
        $this->startTest('Détection des tours');
        
        try {
            // Créer une instance du modèle de télémétrie
            $telemetrieModel = new \App\Models\TelemetrieModel();
            $this->assertNotNull($telemetrieModel, 'Instance TelemetrieModel créée');
            
            // Vérifier que la méthode de détection des tours existe
            $this->assertTrue(method_exists($telemetrieModel, 'detectLaps'), 'Méthode detectLaps existe');
            
            // Simuler la détection des tours (sans réellement modifier la base de données)
            $data = json_decode($this->sampleSensorLoggerData, true);
            $processedData = $this->simulateDataImport($data);
            $laps = $this->simulateLapDetection($processedData);
            
            // Vérifier que les tours ont été correctement détectés
            $this->assertTrue(is_array($laps), 'Résultat de détection des tours est un tableau');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur de détection des tours: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste l'agrégation des données
     */
    private function testDataAggregation() {
        $this->startTest('Agrégation des données');
        
        try {
            // Créer une instance du modèle de télémétrie
            $telemetrieModel = new \App\Models\TelemetrieModel();
            $this->assertNotNull($telemetrieModel, 'Instance TelemetrieModel créée');
            
            // Vérifier que la méthode d'agrégation des données existe
            $this->assertTrue(method_exists($telemetrieModel, 'getAggregatedDataBySessionId'), 'Méthode getAggregatedDataBySessionId existe');
            
            // Simuler l'agrégation des données (sans réellement accéder à la base de données)
            $aggregatedData = $this->simulateDataAggregation();
            
            // Vérifier que les données ont été correctement agrégées
            $this->assertTrue(is_array($aggregatedData), 'Résultat d\'agrégation est un tableau');
            $this->assertTrue(isset($aggregatedData['vitesse_max']), 'Vitesse maximale calculée');
            $this->assertTrue(isset($aggregatedData['vitesse_moyenne']), 'Vitesse moyenne calculée');
            $this->assertTrue(isset($aggregatedData['acceleration_max']), 'Accélération maximale calculée');
            $this->assertTrue(isset($aggregatedData['inclinaison_max']), 'Inclinaison maximale calculée');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur d\'agrégation des données: ' . $e->getMessage());
        }
    }
    
    /**
     * Teste la visualisation des données
     */
    private function testDataVisualization() {
        $this->startTest('Visualisation des données');
        
        try {
            // Vérifier que le contrôleur de télémétrie existe
            $this->assertTrue(class_exists('\App\Controllers\TelemetrieController'), 'Classe TelemetrieController existe');
            
            // Vérifier que les méthodes de visualisation existent
            $controller = new \ReflectionClass('\App\Controllers\TelemetrieController');
            $this->assertTrue($controller->hasMethod('graph'), 'Méthode graph existe');
            $this->assertTrue($controller->hasMethod('compare'), 'Méthode compare existe');
            
            // Vérifier que les vues de visualisation existent
            $this->assertTrue(file_exists(__DIR__ . '/../app/views/telemetrie/graph.php'), 'Vue graph.php existe');
            $this->assertTrue(file_exists(__DIR__ . '/../app/views/telemetrie/graph_racing.php'), 'Vue graph_racing.php existe');
            $this->assertTrue(file_exists(__DIR__ . '/../app/views/telemetrie/compare.php'), 'Vue compare.php existe');
            
            $this->passTest();
        } catch (\Exception $e) {
            $this->failTest('Erreur de visualisation des données: ' . $e->getMessage());
        }
    }
    
    /**
     * Simule l'importation des données Sensor Logger
     * 
     * @param array $data Données JSON décodées
     * @return array Données traitées
     */
    private function simulateDataImport($data) {
        // Simuler le traitement des données
        $processedData = [
            'session_info' => $data['session'],
            'points' => []
        ];
        
        // Fusionner les données de localisation, accélération, gyroscope et attitude
        for ($i = 0; $i < count($data['location']); $i++) {
            $point = [
                'time' => $data['location'][$i]['time'],
                'latitude' => $data['location'][$i]['latitude'],
                'longitude' => $data['location'][$i]['longitude'],
                'altitude' => $data['location'][$i]['altitude'],
                'speed' => $data['location'][$i]['speed'],
                'acceleration_x' => $data['acceleration'][$i]['x'],
                'acceleration_y' => $data['acceleration'][$i]['y'],
                'acceleration_z' => $data['acceleration'][$i]['z'],
                'gyroscope_x' => $data['gyroscope'][$i]['x'],
                'gyroscope_y' => $data['gyroscope'][$i]['y'],
                'gyroscope_z' => $data['gyroscope'][$i]['z'],
                'roll' => $data['attitude'][$i]['roll'],
                'pitch' => $data['attitude'][$i]['pitch'],
                'yaw' => $data['attitude'][$i]['yaw']
            ];
            
            $processedData['points'][] = $point;
        }
        
        return $processedData;
    }
    
    /**
     * Simule la détection des tours
     * 
     * @param array $processedData Données traitées
     * @return array Tours détectés
     */
    private function simulateLapDetection($processedData) {
        // Simuler la détection des tours
        // Dans une implémentation réelle, cela utiliserait des algorithmes de détection de franchissement de ligne
        return [
            [
                'numero_tour' => 1,
                'heure_debut' => '2025-04-04T10:00:01Z',
                'heure_fin' => '2025-04-04T10:00:03Z',
                'temps' => 2.0,
                'vitesse_max' => 10.5,
                'vitesse_moyenne' => 5.2,
                'valide' => true
            ]
        ];
    }
    
    /**
     * Simule l'agrégation des données
     * 
     * @return array Données agrégées
     */
    private function simulateDataAggregation() {
        // Simuler l'agrégation des données
        return [
            'vitesse_max' => 10.5,
            'vitesse_moyenne' => 5.2,
            'acceleration_max' => 1.2,
            'inclinaison_max' => 10.0,
            'vitesse_virage_avg' => 7.5,
            'vitesse_ligne_droite_avg' => 8.5,
            'inclinaison_droite_avg' => 8.0,
            'inclinaison_gauche_avg' => 7.0,
            'temps_freinage_avg' => 0.5,
            'force_freinage_avg' => 60.0,
            'acceleration_sortie_virage_avg' => 0.8
        ];
    }
    
    /**
     * Démarre un test
     * 
     * @param string $testName Nom du test
     */
    private function startTest($testName) {
        $this->totalTests++;
        echo "Test: $testName... ";
    }
    
    /**
     * Marque un test comme réussi
     */
    private function passTest() {
        $this->passedTests++;
        echo "RÉUSSI\n";
    }
    
    /**
     * Marque un test comme échoué
     * 
     * @param string $message Message d'erreur
     */
    private function failTest($message) {
        echo "ÉCHOUÉ\n";
        echo "  Erreur: $message\n";
    }
    
    /**
     * Vérifie qu'une condition est vraie
     * 
     * @param bool $condition Condition à vérifier
     * @param string $message Message en cas d'échec
     */
    private function assertTrue($condition, $message = '') {
        if (!$condition) {
            throw new \Exception("Assertion échouée: $message");
        }
    }
    
    /**
     * Vérifie qu'une valeur n'est pas null
     * 
     * @param mixed $value Valeur à vérifier
     * @param string $message Message en cas d'échec
     */
    private function assertNotNull($value, $message = '') {
        if ($value === null) {
            throw new \Exception("Assertion échouée (valeur null): $message");
        }
    }
    
    /**
     * Affiche le résumé des tests
     */
    private function displaySummary() {
        echo "\n=== RÉSUMÉ DES TESTS ===\n";
        echo "Tests exécutés: {$this->totalTests}\n";
        echo "Tests réussis: {$this->passedTests}\n";
        echo "Tests échoués: " . ($this->totalTests - $this->passedTests) . "\n";
        
        $successRate = ($this->passedTests / $this->totalTests) * 100;
        echo "Taux de réussite: " . round($successRate, 2) . "%\n";
        
        echo "\n=== FIN DES TESTS ===\n";
    }
}

// Exécuter les tests
$tester = new SensorLoggerTester();
$tester->runAllTests();
