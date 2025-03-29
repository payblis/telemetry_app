<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Initialiser les variables
$error = null;
$stats = null;
$sessions = null;

try {
    // Charger les classes nécessaires
    require_once 'classes/Session.php';
    require_once 'classes/TelemetryData.php';
    
    $sessionObj = new Session();
    $telemetry = new TelemetryData();
    
    // Récupérer les sessions de l'utilisateur
    $sessions = $sessionObj->getAllByUserId($_SESSION['user_id']);
    
    // Récupérer les statistiques globales
    if ($sessions) {
        $stats = [];
        foreach ($sessions as $session) {
            $telemetry->initSession($session['id'], $_SESSION['user_id']);
            $sessionStats = $telemetry->getSessionStats();
            if ($sessionStats) {
                $stats[] = array_merge($sessionStats, [
                    'date' => $session['date'],
                    'circuit_name' => $session['circuit_name'],
                    'pilot_name' => $session['pilot_name']
                ]);
            }
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur lors de la récupération des statistiques : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques et Analyse</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/telemetry.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Statistiques et Analyse</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($stats): ?>
            <!-- Résumé des performances -->
            <div class="stats-grid">
                <div class="stats-card">
                    <h3>Performances Globales</h3>
                    <div class="stats-summary">
                        <div class="stat-item">
                            <span class="stat-label">Vitesse Max</span>
                            <span class="stat-value"><?php echo max(array_column($stats, 'max_speed')); ?> km/h</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Vitesse Moyenne</span>
                            <span class="stat-value"><?php echo round(array_sum(array_column($stats, 'avg_speed')) / count($stats)); ?> km/h</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">RPM Max</span>
                            <span class="stat-value"><?php echo max(array_column($stats, 'max_rpm')); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Angle Max</span>
                            <span class="stat-value"><?php echo max(array_column($stats, 'max_lean_angle')); ?>°</span>
                        </div>
                    </div>
                </div>

                <!-- Graphique d'évolution -->
                <div class="stats-card">
                    <h3>Évolution des Performances</h3>
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>

            <!-- Tableau des sessions -->
            <div class="stats-card">
                <h3>Détails par Session</h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Circuit</th>
                                <th>Pilote</th>
                                <th>V.Max</th>
                                <th>V.Moy</th>
                                <th>RPM Max</th>
                                <th>Angle Max</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats as $stat): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($stat['date'])); ?></td>
                                <td><?php echo htmlspecialchars($stat['circuit_name']); ?></td>
                                <td><?php echo htmlspecialchars($stat['pilot_name']); ?></td>
                                <td><?php echo round($stat['max_speed']); ?> km/h</td>
                                <td><?php echo round($stat['avg_speed']); ?> km/h</td>
                                <td><?php echo round($stat['max_rpm']); ?></td>
                                <td><?php echo round($stat['max_lean_angle']); ?>°</td>
                                <td>
                                    <a href="index.php?page=telemetry/session&id=<?php echo $stat['id']; ?>" class="btn btn-primary">
                                        <i class="bi bi-graph-up"></i> Détails
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                // Données pour le graphique
                const dates = <?php echo json_encode(array_column($stats, 'date')); ?>;
                const maxSpeeds = <?php echo json_encode(array_column($stats, 'max_speed')); ?>;
                const avgSpeeds = <?php echo json_encode(array_column($stats, 'avg_speed')); ?>;

                // Configuration du graphique
                const ctx = document.getElementById('performanceChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dates.map(date => new Date(date).toLocaleDateString()),
                        datasets: [{
                            label: 'Vitesse Max',
                            data: maxSpeeds,
                            borderColor: '#e31e24',
                            tension: 0.1
                        }, {
                            label: 'Vitesse Moyenne',
                            data: avgSpeeds,
                            borderColor: '#28a745',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Évolution des Vitesses'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Vitesse (km/h)'
                                }
                            }
                        }
                    }
                });
            </script>
        <?php else: ?>
            <div class="alert alert-info">Aucune donnée statistique disponible.</div>
        <?php endif; ?>
    </div>
</body>
</html> 