<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/TelemetryData.php';

// Initialisation des variables
$error = null;
$sessions = [];
$comparisonData = [];

try {
    $session = new Session();
    $telemetry = new TelemetryData();

    // Récupération des sessions à comparer
    if (isset($_GET['sessions']) && is_array($_GET['sessions'])) {
        foreach ($_GET['sessions'] as $sessionId) {
            if (is_numeric($sessionId)) {
                $sessionData = $session->getById(intval($sessionId));
                if ($sessionData && $sessionData['user_id'] === $_SESSION['user_id']) {
                    $sessions[] = $sessionData;
                    $telemetryData = $telemetry->getSessionData(intval($sessionId));
                    
                    // Calcul des statistiques pour chaque session
                    $comparisonData[$sessionId] = [
                        'vitesse_max' => max(array_column($telemetryData, 'speed')),
                        'vitesse_moy' => array_sum(array_column($telemetryData, 'speed')) / count($telemetryData),
                        'rpm_max' => max(array_column($telemetryData, 'rpm')),
                        'rpm_moy' => array_sum(array_column($telemetryData, 'rpm')) / count($telemetryData),
                        'angle_max' => max(array_column($telemetryData, 'lean_angle')),
                        'meilleur_tour' => min(array_column($telemetryData, 'lap_time')),
                        'tour_moyen' => array_sum(array_column($telemetryData, 'lap_time')) / count($telemetryData),
                        'acceleration_max' => max(array_column($telemetryData, 'acceleration')),
                        'temps_secteur1' => min(array_column($telemetryData, 'sector1_time')),
                        'temps_secteur2' => min(array_column($telemetryData, 'sector2_time')),
                        'temps_secteur3' => min(array_column($telemetryData, 'sector3_time')),
                        'data' => $telemetryData
                    ];
                }
            }
        }
    }

    if (empty($sessions)) {
        // Si aucune session n'est sélectionnée, récupérer toutes les sessions de l'utilisateur
        $sessions = $session->getAllByUserId($_SESSION['user_id']);
    }

} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur comparaison stats : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparaison des sessions</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/stats.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>

    <main class="container">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <!-- Formulaire de sélection des sessions -->
            <div class="stats-card">
                <h2>Sélectionner les sessions à comparer</h2>
                <form action="" method="get" class="stats-controls">
                    <?php foreach ($sessions as $s): ?>
                    <div class="form-check">
                        <input type="checkbox" 
                               name="sessions[]" 
                               value="<?php echo $s['id']; ?>"
                               <?php echo in_array($s['id'], array_keys($comparisonData)) ? 'checked' : ''; ?>
                               class="form-check-input">
                        <label class="form-check-label">
                            <?php echo htmlspecialchars(date('d/m/Y', strtotime($s['date'])) . 
                                  ' - ' . $s['circuit_name'] . 
                                  ' - ' . $s['pilot_name']); ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-primary">Comparer</button>
                </form>
            </div>

            <?php if (!empty($comparisonData)): ?>
                <!-- Graphiques de comparaison -->
                <div class="stats-grid">
                    <!-- Comparaison des vitesses -->
                    <div class="stats-card">
                        <h3>Comparaison des vitesses</h3>
                        <div class="chart-container">
                            <canvas id="speedCompareChart"></canvas>
                        </div>
                    </div>

                    <!-- Comparaison des temps par secteur -->
                    <div class="stats-card">
                        <h3>Comparaison des temps par secteur</h3>
                        <div class="chart-container">
                            <canvas id="sectorCompareChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tableau comparatif -->
                <div class="stats-card">
                    <h3>Tableau comparatif</h3>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Métrique</th>
                                    <?php foreach ($sessions as $s): ?>
                                        <?php if (isset($comparisonData[$s['id']])): ?>
                                        <th><?php echo htmlspecialchars(date('d/m/Y', strtotime($s['date']))); ?></th>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Vitesse max</td>
                                    <?php foreach ($sessions as $s): ?>
                                        <?php if (isset($comparisonData[$s['id']])): ?>
                                        <td><?php echo round($comparisonData[$s['id']]['vitesse_max']); ?> km/h</td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td>Vitesse moyenne</td>
                                    <?php foreach ($sessions as $s): ?>
                                        <?php if (isset($comparisonData[$s['id']])): ?>
                                        <td><?php echo round($comparisonData[$s['id']]['vitesse_moy']); ?> km/h</td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td>RPM max</td>
                                    <?php foreach ($sessions as $s): ?>
                                        <?php if (isset($comparisonData[$s['id']])): ?>
                                        <td><?php echo round($comparisonData[$s['id']]['rpm_max']); ?></td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td>Angle max</td>
                                    <?php foreach ($sessions as $s): ?>
                                        <?php if (isset($comparisonData[$s['id']])): ?>
                                        <td><?php echo round($comparisonData[$s['id']]['angle_max']); ?>°</td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td>Meilleur tour</td>
                                    <?php foreach ($sessions as $s): ?>
                                        <?php if (isset($comparisonData[$s['id']])): ?>
                                        <td><?php echo htmlspecialchars($comparisonData[$s['id']]['meilleur_tour']); ?></td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <script>
                    // Données pour les graphiques de comparaison
                    const sessionsData = <?php echo json_encode($comparisonData); ?>;
                    const sessionDates = <?php echo json_encode(array_map(function($s) {
                        return date('d/m/Y', strtotime($s['date']));
                    }, array_filter($sessions, function($s) use ($comparisonData) {
                        return isset($comparisonData[$s['id']]);
                    }))); ?>;

                    // Configuration du graphique de comparaison des vitesses
                    const speedCompareChart = new Chart(document.getElementById('speedCompareChart'), {
                        type: 'line',
                        data: {
                            labels: Array.from({length: 100}, (_, i) => i + 1),
                            datasets: Object.entries(sessionsData).map(([sessionId, data], index) => ({
                                label: sessionDates[index],
                                data: data.data.map(d => d.speed),
                                borderColor: `hsl(${index * 360 / Object.keys(sessionsData).length}, 70%, 50%)`,
                                tension: 0.1
                            }))
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Vitesse (km/h)'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Points de mesure'
                                    }
                                }
                            }
                        }
                    });

                    // Configuration du graphique de comparaison des secteurs
                    const sectorCompareChart = new Chart(document.getElementById('sectorCompareChart'), {
                        type: 'bar',
                        data: {
                            labels: ['Secteur 1', 'Secteur 2', 'Secteur 3'],
                            datasets: Object.entries(sessionsData).map(([sessionId, data], index) => ({
                                label: sessionDates[index],
                                data: [
                                    data.temps_secteur1,
                                    data.temps_secteur2,
                                    data.temps_secteur3
                                ],
                                backgroundColor: `hsla(${index * 360 / Object.keys(sessionsData).length}, 70%, 50%, 0.2)`,
                                borderColor: `hsl(${index * 360 / Object.keys(sessionsData).length}, 70%, 50%)`,
                                borderWidth: 1
                            }))
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Temps (secondes)'
                                    }
                                }
                            }
                        }
                    });
                </script>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html> 