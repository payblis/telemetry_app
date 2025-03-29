<?php

class Analysis {
    private $sessionId;
    private $telemetryData;
    private $analysis;

    /**
     * Analyse une session de pilotage à partir des données de télémétrie
     * 
     * @param int $sessionId ID de la session
     * @param array $telemetryData Données de télémétrie
     * @return array Résultats de l'analyse
     */
    public function analyzeSession($sessionId, $telemetryData) {
        $this->sessionId = $sessionId;
        $this->telemetryData = $telemetryData;

        // Calcul des scores de performance
        $ridingScore = $this->calculateRidingScore();
        $consistencyScore = $this->calculateConsistencyScore();
        $lineScore = $this->calculateLineScore();
        $globalScore = ($ridingScore + $consistencyScore + $lineScore) / 3;

        // Identification des points forts et axes d'amélioration
        $strengths = $this->identifyStrengths();
        $improvements = $this->identifyImprovements();

        // Analyse des trajectoires
        $trajectoryAnalysis = $this->analyzeTrajectories();

        $this->analysis = [
            'global_score' => $globalScore,
            'riding_score' => $ridingScore,
            'consistency_score' => $consistencyScore,
            'line_score' => $lineScore,
            'strengths' => $strengths,
            'improvements' => $improvements,
            'ideal_trajectory' => $trajectoryAnalysis['ideal'],
            'actual_trajectory' => $trajectoryAnalysis['actual']
        ];

        return $this->analysis;
    }

    /**
     * Calcule le score de pilotage basé sur différents critères
     * 
     * @return float Score de pilotage (0-100)
     */
    private function calculateRidingScore() {
        $scores = [];

        // Analyse de la vitesse
        $speeds = array_column($this->telemetryData, 'speed');
        $maxSpeed = max($speeds);
        $avgSpeed = array_sum($speeds) / count($speeds);
        $speedScore = min(100, ($avgSpeed / $maxSpeed) * 100);
        $scores[] = $speedScore;

        // Analyse du régime moteur
        $rpms = array_column($this->telemetryData, 'rpm');
        $maxRpm = max($rpms);
        $optimalRpmRange = [8000, 12000]; // Plage optimale de RPM
        $rpmScore = $this->calculateRpmEfficiency($rpms, $optimalRpmRange);
        $scores[] = $rpmScore;

        // Analyse des angles d'inclinaison
        $angles = array_column($this->telemetryData, 'lean_angle');
        $maxAngle = max($angles);
        $optimalAngleRange = [30, 50]; // Plage optimale d'angle en degrés
        $angleScore = $this->calculateAngleEfficiency($angles, $optimalAngleRange);
        $scores[] = $angleScore;

        // Moyenne pondérée des scores
        return array_sum($scores) / count($scores);
    }

    /**
     * Calcule le score de régularité
     * 
     * @return float Score de régularité (0-100)
     */
    private function calculateConsistencyScore() {
        $scores = [];

        // Analyse de la régularité des temps au tour
        $lapTimes = array_column($this->telemetryData, 'lap_time');
        $avgLapTime = array_sum($lapTimes) / count($lapTimes);
        $lapTimeVariance = $this->calculateVariance($lapTimes);
        $consistencyScore = 100 - min(100, ($lapTimeVariance / $avgLapTime) * 100);
        $scores[] = $consistencyScore;

        // Analyse de la régularité des trajectoires
        $trajectoryVariance = $this->calculateTrajectoryConsistency();
        $trajectoryScore = 100 - min(100, $trajectoryVariance);
        $scores[] = $trajectoryScore;

        // Moyenne des scores
        return array_sum($scores) / count($scores);
    }

    /**
     * Calcule le score de trajectoire
     * 
     * @return float Score de trajectoire (0-100)
     */
    private function calculateLineScore() {
        $scores = [];

        // Analyse de l'adhérence à la ligne idéale
        $idealLine = $this->getIdealLine();
        $actualLines = $this->getActualLines();
        $lineDeviation = $this->calculateLineDeviation($actualLines, $idealLine);
        $lineScore = 100 - min(100, ($lineDeviation * 10));
        $scores[] = $lineScore;

        // Analyse des points de corde
        $apexScore = $this->calculateApexAccuracy();
        $scores[] = $apexScore;

        // Analyse des phases d'accélération/freinage
        $brakingScore = $this->analyzeBrakingPoints();
        $scores[] = $brakingScore;

        // Moyenne des scores
        return array_sum($scores) / count($scores);
    }

    /**
     * Identifie les points forts du pilotage
     * 
     * @return array Liste des points forts
     */
    private function identifyStrengths() {
        $strengths = [];

        // Analyse des vitesses de passage
        $speeds = array_column($this->telemetryData, 'speed');
        $maxSpeed = max($speeds);
        if ($maxSpeed > 200) {
            $strengths[] = "Excellente vitesse de pointe atteinte (" . round($maxSpeed) . " km/h)";
        }

        // Analyse de la régularité
        $lapTimes = array_column($this->telemetryData, 'lap_time');
        $lapTimeVariance = $this->calculateVariance($lapTimes);
        if ($lapTimeVariance < 2) {
            $strengths[] = "Grande régularité dans les temps au tour";
        }

        // Analyse des trajectoires
        $trajectoryVariance = $this->calculateTrajectoryConsistency();
        if ($trajectoryVariance < 1.5) {
            $strengths[] = "Excellente constance dans les trajectoires";
        }

        // Analyse des freinages
        $brakingScore = $this->analyzeBrakingPoints();
        if ($brakingScore > 85) {
            $strengths[] = "Très bonne gestion des phases de freinage";
        }

        return $strengths;
    }

    /**
     * Identifie les axes d'amélioration
     * 
     * @return array Liste des axes d'amélioration
     */
    private function identifyImprovements() {
        $improvements = [];

        // Analyse des vitesses de passage
        $speeds = array_column($this->telemetryData, 'speed');
        $avgSpeed = array_sum($speeds) / count($speeds);
        if ($avgSpeed < 120) {
            $improvements[] = "Augmenter la vitesse moyenne en travaillant sur la confiance en sortie de virage";
        }

        // Analyse de la régularité
        $lapTimes = array_column($this->telemetryData, 'lap_time');
        $lapTimeVariance = $this->calculateVariance($lapTimes);
        if ($lapTimeVariance > 3) {
            $improvements[] = "Travailler sur la régularité des temps au tour";
        }

        // Analyse des trajectoires
        $trajectoryVariance = $this->calculateTrajectoryConsistency();
        if ($trajectoryVariance > 2) {
            $improvements[] = "Améliorer la précision et la répétabilité des trajectoires";
        }

        // Analyse des freinages
        $brakingScore = $this->analyzeBrakingPoints();
        if ($brakingScore < 70) {
            $improvements[] = "Optimiser les points de freinage pour gagner en efficacité";
        }

        return $improvements;
    }

    /**
     * Analyse les trajectoires et compare avec la ligne idéale
     * 
     * @return array Données des trajectoires idéales et réelles
     */
    private function analyzeTrajectories() {
        // Récupération des coordonnées de trajectoire
        $actualTrajectory = [];
        foreach ($this->telemetryData as $data) {
            $actualTrajectory[] = [
                'x' => $data['position_x'],
                'y' => $data['position_y']
            ];
        }

        // Génération de la trajectoire idéale (simulation)
        $idealTrajectory = $this->generateIdealTrajectory();

        return [
            'ideal' => $idealTrajectory,
            'actual' => $actualTrajectory
        ];
    }

    /**
     * Génère des recommandations basées sur l'analyse
     * 
     * @param array $analysis Résultats de l'analyse
     * @return array Recommandations par catégorie
     */
    public function generateRecommendations($analysis) {
        $recommendations = [];

        // Recommandations sur le pilotage
        $recommendations['Pilotage'] = $this->generateRidingRecommendations();

        // Recommandations sur la régularité
        $recommendations['Régularité'] = $this->generateConsistencyRecommendations();

        // Recommandations sur les trajectoires
        $recommendations['Trajectoires'] = $this->generateLineRecommendations();

        // Recommandations sur la sécurité
        $recommendations['Sécurité'] = $this->generateSafetyRecommendations();

        return $recommendations;
    }

    /**
     * Génère des recommandations sur le pilotage
     * 
     * @return array Liste des recommandations
     */
    private function generateRidingRecommendations() {
        $recommendations = [];

        // Analyse des vitesses
        $speeds = array_column($this->telemetryData, 'speed');
        $maxSpeed = max($speeds);
        $avgSpeed = array_sum($speeds) / count($speeds);

        if ($avgSpeed < 0.7 * $maxSpeed) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Optimisation de la vitesse moyenne',
                'description' => 'La vitesse moyenne est significativement inférieure à la vitesse maximale.',
                'action_items' => [
                    'Travailler sur l\'accélération en sortie de virage',
                    'Optimiser les trajectoires pour maintenir la vitesse',
                    'Améliorer la confiance dans les phases rapides'
                ]
            ];
        }

        // Analyse du régime moteur
        $rpms = array_column($this->telemetryData, 'rpm');
        $rpmEfficiency = $this->calculateRpmEfficiency($rpms, [8000, 12000]);

        if ($rpmEfficiency < 75) {
            $recommendations[] = [
                'priority' => 'medium',
                'title' => 'Gestion du régime moteur',
                'description' => 'Le régime moteur n\'est pas utilisé de manière optimale.',
                'action_items' => [
                    'Optimiser les changements de rapport',
                    'Utiliser la plage de régime optimale',
                    'Travailler sur le timing des rétrogradages'
                ]
            ];
        }

        return $recommendations;
    }

    /**
     * Génère des recommandations sur la régularité
     * 
     * @return array Liste des recommandations
     */
    private function generateConsistencyRecommendations() {
        $recommendations = [];

        // Analyse de la régularité des temps au tour
        $lapTimes = array_column($this->telemetryData, 'lap_time');
        $lapTimeVariance = $this->calculateVariance($lapTimes);

        if ($lapTimeVariance > 2) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Amélioration de la régularité',
                'description' => 'Les temps au tour montrent une variation importante.',
                'action_items' => [
                    'Travailler sur la concentration',
                    'Maintenir un rythme constant',
                    'Identifier les sections avec le plus de variation'
                ]
            ];
        }

        // Analyse de la régularité des trajectoires
        $trajectoryVariance = $this->calculateTrajectoryConsistency();

        if ($trajectoryVariance > 1.5) {
            $recommendations[] = [
                'priority' => 'medium',
                'title' => 'Constance des trajectoires',
                'description' => 'Les trajectoires manquent de répétabilité.',
                'action_items' => [
                    'Utiliser des points de repère visuels',
                    'Pratiquer des exercices de précision',
                    'Analyser les vidéos des sessions'
                ]
            ];
        }

        return $recommendations;
    }

    /**
     * Génère des recommandations sur les trajectoires
     * 
     * @return array Liste des recommandations
     */
    private function generateLineRecommendations() {
        $recommendations = [];

        // Analyse des points de corde
        $apexScore = $this->calculateApexAccuracy();

        if ($apexScore < 80) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Optimisation des points de corde',
                'description' => 'Les points de corde ne sont pas atteints de manière optimale.',
                'action_items' => [
                    'Travailler sur le placement de la moto en entrée de virage',
                    'Identifier les points de corde idéaux',
                    'Améliorer la précision des trajectoires'
                ]
            ];
        }

        // Analyse des phases d'accélération
        $accelerationScore = $this->analyzeBrakingPoints();

        if ($accelerationScore < 70) {
            $recommendations[] = [
                'priority' => 'medium',
                'title' => 'Gestion de l\'accélération',
                'description' => 'Les phases d\'accélération peuvent être optimisées.',
                'action_items' => [
                    'Anticiper les sorties de virage',
                    'Travailler sur la progressivité de l\'accélération',
                    'Optimiser les points de réaccélération'
                ]
            ];
        }

        return $recommendations;
    }

    /**
     * Génère des recommandations sur la sécurité
     * 
     * @return array Liste des recommandations
     */
    private function generateSafetyRecommendations() {
        $recommendations = [];

        // Analyse des angles d'inclinaison
        $angles = array_column($this->telemetryData, 'lean_angle');
        $maxAngle = max($angles);

        if ($maxAngle > 55) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Gestion des angles d\'inclinaison',
                'description' => 'Les angles d\'inclinaison sont parfois excessifs.',
                'action_items' => [
                    'Privilégier des trajectoires plus ouvertes',
                    'Travailler sur le transfert de masse',
                    'Maintenir une marge de sécurité'
                ]
            ];
        }

        // Analyse des freinages
        $brakingForces = array_column($this->telemetryData, 'brake_force');
        $maxBraking = max($brakingForces);

        if ($maxBraking > 0.9) {
            $recommendations[] = [
                'priority' => 'medium',
                'title' => 'Gestion du freinage',
                'description' => 'Les freinages sont parfois trop brutaux.',
                'action_items' => [
                    'Travailler sur la progressivité du freinage',
                    'Anticiper les phases de freinage',
                    'Utiliser les deux freins de manière équilibrée'
                ]
            ];
        }

        return $recommendations;
    }

    /**
     * Calcule la variance d'un ensemble de données
     * 
     * @param array $data Données
     * @return float Variance
     */
    private function calculateVariance($data) {
        $mean = array_sum($data) / count($data);
        $variance = 0;

        foreach ($data as $value) {
            $variance += pow($value - $mean, 2);
        }

        return $variance / count($data);
    }

    /**
     * Calcule l'efficacité du régime moteur
     * 
     * @param array $rpms Données RPM
     * @param array $optimalRange Plage optimale [min, max]
     * @return float Score d'efficacité (0-100)
     */
    private function calculateRpmEfficiency($rpms, $optimalRange) {
        $inRange = 0;
        $total = count($rpms);

        foreach ($rpms as $rpm) {
            if ($rpm >= $optimalRange[0] && $rpm <= $optimalRange[1]) {
                $inRange++;
            }
        }

        return ($inRange / $total) * 100;
    }

    /**
     * Calcule l'efficacité des angles d'inclinaison
     * 
     * @param array $angles Angles d'inclinaison
     * @param array $optimalRange Plage optimale [min, max]
     * @return float Score d'efficacité (0-100)
     */
    private function calculateAngleEfficiency($angles, $optimalRange) {
        $inRange = 0;
        $total = count($angles);

        foreach ($angles as $angle) {
            if ($angle >= $optimalRange[0] && $angle <= $optimalRange[1]) {
                $inRange++;
            }
        }

        return ($inRange / $total) * 100;
    }

    /**
     * Calcule la consistance des trajectoires
     * 
     * @return float Score de consistance (0-100)
     */
    private function calculateTrajectoryConsistency() {
        $positions = array_map(function($data) {
            return ['x' => $data['position_x'], 'y' => $data['position_y']];
        }, $this->telemetryData);

        $variance = 0;
        $previousPos = null;

        foreach ($positions as $pos) {
            if ($previousPos) {
                $distance = sqrt(pow($pos['x'] - $previousPos['x'], 2) + pow($pos['y'] - $previousPos['y'], 2));
                $variance += $distance;
            }
            $previousPos = $pos;
        }

        return $variance / count($positions);
    }

    /**
     * Calcule la précision des points de corde
     * 
     * @return float Score de précision (0-100)
     */
    private function calculateApexAccuracy() {
        // Simulation de points de corde idéaux
        $idealApexes = $this->getIdealApexes();
        $actualPositions = array_map(function($data) {
            return ['x' => $data['position_x'], 'y' => $data['position_y']];
        }, $this->telemetryData);

        $totalDeviation = 0;
        foreach ($idealApexes as $apex) {
            $minDeviation = PHP_FLOAT_MAX;
            foreach ($actualPositions as $pos) {
                $deviation = sqrt(pow($pos['x'] - $apex['x'], 2) + pow($pos['y'] - $apex['y'], 2));
                $minDeviation = min($minDeviation, $deviation);
            }
            $totalDeviation += $minDeviation;
        }

        $averageDeviation = $totalDeviation / count($idealApexes);
        return max(0, 100 - ($averageDeviation * 10));
    }

    /**
     * Analyse les points de freinage
     * 
     * @return float Score de freinage (0-100)
     */
    private function analyzeBrakingPoints() {
        $brakingForces = array_column($this->telemetryData, 'brake_force');
        $speeds = array_column($this->telemetryData, 'speed');

        $score = 0;
        $count = 0;

        for ($i = 1; $i < count($brakingForces); $i++) {
            if ($brakingForces[$i] > 0.1) { // Seuil de détection de freinage
                $speedReduction = $speeds[$i-1] - $speeds[$i];
                $efficiency = $speedReduction / $brakingForces[$i];
                $score += min(100, $efficiency);
                $count++;
            }
        }

        return $count > 0 ? $score / $count : 0;
    }

    /**
     * Génère une trajectoire idéale (simulation)
     * 
     * @return array Points de la trajectoire idéale
     */
    private function generateIdealTrajectory() {
        // Simulation simple d'une trajectoire idéale
        $idealPoints = [];
        $steps = 100;

        for ($i = 0; $i < $steps; $i++) {
            $t = $i / ($steps - 1);
            $idealPoints[] = [
                'x' => cos(2 * M_PI * $t) * 100,
                'y' => sin(2 * M_PI * $t) * 100
            ];
        }

        return $idealPoints;
    }

    /**
     * Récupère les points de corde idéaux (simulation)
     * 
     * @return array Points de corde idéaux
     */
    private function getIdealApexes() {
        // Simulation de points de corde
        return [
            ['x' => 50, 'y' => 0],
            ['x' => 0, 'y' => 50],
            ['x' => -50, 'y' => 0],
            ['x' => 0, 'y' => -50]
        ];
    }

    /**
     * Récupère la ligne idéale (simulation)
     * 
     * @return array Points de la ligne idéale
     */
    private function getIdealLine() {
        return $this->generateIdealTrajectory();
    }

    /**
     * Récupère les lignes réelles
     * 
     * @return array Points des lignes réelles
     */
    private function getActualLines() {
        return array_map(function($data) {
            return ['x' => $data['position_x'], 'y' => $data['position_y']];
        }, $this->telemetryData);
    }

    /**
     * Calcule la déviation par rapport à la ligne idéale
     * 
     * @param array $actualLines Points des lignes réelles
     * @param array $idealLine Points de la ligne idéale
     * @return float Déviation moyenne
     */
    private function calculateLineDeviation($actualLines, $idealLine) {
        $totalDeviation = 0;
        $count = 0;

        foreach ($actualLines as $actual) {
            $minDeviation = PHP_FLOAT_MAX;
            foreach ($idealLine as $ideal) {
                $deviation = sqrt(pow($actual['x'] - $ideal['x'], 2) + pow($actual['y'] - $ideal['y'], 2));
                $minDeviation = min($minDeviation, $deviation);
            }
            $totalDeviation += $minDeviation;
            $count++;
        }

        return $count > 0 ? $totalDeviation / $count : 0;
    }
} 