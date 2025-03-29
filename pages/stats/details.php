<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/TelemetryData.php';

// Initialisation des variables
$error = null;
$session = null;
$telemetryData = null;

try {
    // Vérification de l'ID de session
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("ID de session invalide");
    }

    $sessionId = intval($_GET['id']);
    $session = new Session();
    $telemetry = new TelemetryData();

    // Récupération des données de la session
    $sessionData = $session->getById($sessionId);
    if (!$sessionData || $sessionData['user_id'] !== $_SESSION['user_id']) {
        throw new Exception("Session non trouvée ou accès non autorisé");
    }

    // Récupération des données de télémétrie
    $telemetryData = $telemetry->getSessionData($sessionId);

    // Calcul des statistiques détaillées
    $stats = [
        'vitesse' => [
            'max' => max(array_column($telemetryData, 'speed')),
            'min' => min(array_column($telemetryData, 'speed')),
            'avg' => array_sum(array_column($telemetryData, 'speed')) / count($telemetryData)
        ],
        'rpm' => [
            'max' => max(array_column($telemetryData, 'rpm')),
            'min' => min(array_column($telemetryData, 'rpm')),
            'avg' => array_sum(array_column($telemetryData, 'rpm')) / count($telemetryData)
        ],
        'angle' => [
            'max' => max(array_column($telemetryData, 'lean_angle')),
            'min' => min(array_column($telemetryData, 'lean_angle')),
            'avg' => array_sum(array_column($telemetryData, 'lean_angle')) / count($telemetryData)
        ],
        'acceleration' => [
            'max' => max(array_column($telemetryData, 'acceleration')),
            'min' => min(array_column($telemetryData, 'acceleration')),
            'avg' => array_sum(array_column($telemetryData, 'acceleration')) / count($telemetryData)
        ]
    ];

} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur stats détails : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails des statistiques - Session <?php echo htmlspecialchars($sessionData['date']); ?></title>
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
            <div class="stats-header">
                <h1>Statistiques détaillées - Session du <?php echo htmlspecialchars(date('d/m/Y', strtotime($sessionData['date']))); ?></h1>
                <p>
                    Circuit : <?php echo htmlspecialchars($sessionData['circuit_name']); ?> |
                    Pilote : <?php echo htmlspecialchars($sessionData['pilot_name']); ?> |
                    Moto : <?php echo htmlspecialchars($sessionData['moto_brand'] . ' ' . $sessionData['moto_model']); ?>
                </p>
            </div>

            <div class="stats-grid">
                <!-- Graphique d'évolution de la vitesse -->
                <div class="stats-card">
                    <h3>Évolution de la vitesse</h3>
                    <div class="chart-container">
                        <canvas id="speedChart"></canvas>
                    </div>
                </div>

                <!-- Graphique d'évolution du régime moteur -->
                <div class="stats-card">
                    <h3>Évolution du régime moteur</h3>
                    <div class="chart-container">
                        <canvas id="rpmChart"></canvas>
                    </div>
                </div>

                <!-- Résumé des statistiques -->
                <div class="stats-card">
                    <h3>Résumé des performances</h3>
                    <div class="stats-summary">
                        <div class="stat-item">
                            <span class="stat-label">Vitesse max</span>
                            <span class="stat-value"><?php echo round($stats['vitesse']['max']); ?> km/h</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Vitesse moyenne</span>
                            <span class="stat-value"><?php echo round($stats['vitesse']['avg']); ?> km/h</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">RPM max</span>
                            <span class="stat-value"><?php echo round($stats['rpm']['max']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Angle max</span>
                            <span class="stat-value"><?php echo round($stats['angle']['max']); ?>°</span>
                        </div>
                    </div>
                </div>

                <!-- Analyse des secteurs -->
                <div class="stats-card">
                    <h3>Analyse par secteur</h3>
                    <div class="chart-container">
                        <canvas id="sectorChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tableau des temps par tour -->
            <div class="stats-card">
                <h3>Temps par tour</h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tour</th>
                                <th>Temps</th>
                                <th>Secteur 1</th>
                                <th>Secteur 2</th>
                                <th>Secteur 3</th>
                                <th>Vitesse max</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($telemetryData as $lap): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($lap['lap_number']); ?></td>
                                <td><?php echo htmlspecialchars($lap['lap_time']); ?></td>
                                <td><?php echo htmlspecialchars($lap['sector1_time']); ?></td>
                                <td><?php echo htmlspecialchars($lap['sector2_time']); ?></td>
                                <td><?php echo htmlspecialchars($lap['sector3_time']); ?></td>
                                <td><?php echo htmlspecialchars($lap['max_speed']); ?> km/h</td>
                                <td>
                                    <span class="performance-indicator <?php echo $lap['performance_class']; ?>"></span>
                                    <?php echo htmlspecialchars($lap['performance']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                // Données pour les graphiques
                const speedData = <?php echo json_encode(array_column($telemetryData, 'speed')); ?>;
                const rpmData = <?php echo json_encode(array_column($telemetryData, 'rpm')); ?>;
                const timeLabels = <?php echo json_encode(array_column($telemetryData, 'timestamp')); ?>;
                const sectorData = {
                    labels: ['Secteur 1', 'Secteur 2', 'Secteur 3'],
                    data: [
                        <?php echo json_encode(array_column($telemetryData, 'sector1_time')); ?>,
                        <?php echo json_encode(array_column($telemetryData, 'sector2_time')); ?>,
                        <?php echo json_encode(array_column($telemetryData, 'sector3_time')); ?>
                    ]
                };

                // Configuration des graphiques
                const speedChart = new Chart(document.getElementById('speedChart'), {
                    type: 'line',
                    data: {
                        labels: timeLabels,
                        datasets: [{
                            label: 'Vitesse (km/h)',
                            data: speedData,
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });

                const rpmChart = new Chart(document.getElementById('rpmChart'), {
                    type: 'line',
                    data: {
                        labels: timeLabels,
                        datasets: [{
                            label: 'RPM',
                            data: rpmData,
                            borderColor: 'rgb(255, 99, 132)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });

                const sectorChart = new Chart(document.getElementById('sectorChart'), {
                    type: 'bar',
                    data: {
                        labels: sectorData.labels,
                        datasets: [{
                            label: 'Temps moyen par secteur',
                            data: sectorData.data,
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.2)',
                                'rgba(255, 99, 132, 0.2)',
                                'rgba(255, 206, 86, 0.2)'
                            ],
                            borderColor: [
                                'rgb(75, 192, 192)',
                                'rgb(255, 99, 132)',
                                'rgb(255, 206, 86)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            </script>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html> 