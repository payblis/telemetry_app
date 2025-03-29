<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/TelemetryData.php';
require_once __DIR__ . '/../../classes/Analysis.php';

// Initialisation des variables
$error = null;
$sessionId = null;
$sessionData = null;
$telemetryData = null;
$analysis = null;
$recommendations = [];

try {
    // Vérification de l'ID de session
    if (isset($_GET['session_id']) && is_numeric($_GET['session_id'])) {
        $sessionId = intval($_GET['session_id']);
        $session = new Session();
        $telemetry = new TelemetryData();
        $analyzer = new Analysis();

        // Récupération des données de la session
        $sessionData = $session->getById($sessionId);
        if (!$sessionData || $sessionData['user_id'] !== $_SESSION['user_id']) {
            throw new Exception("Session non trouvée ou accès non autorisé");
        }

        // Récupération des données de télémétrie
        $telemetryData = $telemetry->getSessionData($sessionId);

        // Analyse des performances
        $analysis = $analyzer->analyzeSession($sessionId, $telemetryData);
        
        // Génération des recommandations
        $recommendations = $analyzer->generateRecommendations($analysis);
    } else {
        // Si aucune session n'est sélectionnée, afficher la liste des sessions
        $session = new Session();
        $sessions = $session->getAllByUserId($_SESSION['user_id']);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur analyse performances : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse des performances</title>
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
        <?php elseif (!$sessionId): ?>
            <!-- Sélection de la session à analyser -->
            <div class="stats-card">
                <h2>Sélectionner une session à analyser</h2>
                <div class="stats-grid">
                    <?php foreach ($sessions as $s): ?>
                    <div class="session-card">
                        <h3><?php echo htmlspecialchars(date('d/m/Y', strtotime($s['date']))); ?></h3>
                        <p>
                            Circuit : <?php echo htmlspecialchars($s['circuit_name']); ?><br>
                            Pilote : <?php echo htmlspecialchars($s['pilot_name']); ?><br>
                            Moto : <?php echo htmlspecialchars($s['moto_brand'] . ' ' . $s['moto_model']); ?>
                        </p>
                        <a href="?session_id=<?php echo $s['id']; ?>" class="btn btn-primary">Analyser</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Affichage de l'analyse -->
            <div class="stats-header">
                <h1>Analyse des performances - Session du <?php echo htmlspecialchars(date('d/m/Y', strtotime($sessionData['date']))); ?></h1>
                <p>
                    Circuit : <?php echo htmlspecialchars($sessionData['circuit_name']); ?> |
                    Pilote : <?php echo htmlspecialchars($sessionData['pilot_name']); ?> |
                    Moto : <?php echo htmlspecialchars($sessionData['moto_brand'] . ' ' . $sessionData['moto_model']); ?>
                </p>
            </div>

            <!-- Score global et résumé -->
            <div class="stats-card">
                <h2>Score global de performance</h2>
                <div class="performance-score">
                    <div class="score-circle" style="--score: <?php echo $analysis['global_score']; ?>">
                        <span class="score-value"><?php echo round($analysis['global_score']); ?></span>
                        <span class="score-label">/ 100</span>
                    </div>
                    <div class="score-details">
                        <div class="score-item">
                            <span class="score-label">Pilotage</span>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $analysis['riding_score']; ?>%"></div>
                            </div>
                        </div>
                        <div class="score-item">
                            <span class="score-label">Régularité</span>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $analysis['consistency_score']; ?>%"></div>
                            </div>
                        </div>
                        <div class="score-item">
                            <span class="score-label">Trajectoires</span>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $analysis['line_score']; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Points forts et axes d'amélioration -->
            <div class="stats-grid">
                <div class="stats-card">
                    <h3>Points forts</h3>
                    <ul class="strength-list">
                        <?php foreach ($analysis['strengths'] as $strength): ?>
                        <li class="strength-item">
                            <span class="strength-icon">✓</span>
                            <?php echo htmlspecialchars($strength); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="stats-card">
                    <h3>Axes d'amélioration</h3>
                    <ul class="improvement-list">
                        <?php foreach ($analysis['improvements'] as $improvement): ?>
                        <li class="improvement-item">
                            <span class="improvement-icon">↗</span>
                            <?php echo htmlspecialchars($improvement); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Recommandations détaillées -->
            <div class="stats-card">
                <h3>Recommandations détaillées</h3>
                <div class="recommendations">
                    <?php foreach ($recommendations as $category => $items): ?>
                    <div class="recommendation-category">
                        <h4><?php echo htmlspecialchars($category); ?></h4>
                        <ul class="recommendation-list">
                            <?php foreach ($items as $item): ?>
                            <li class="recommendation-item">
                                <div class="recommendation-header">
                                    <span class="recommendation-priority <?php echo $item['priority']; ?>">
                                        <?php echo strtoupper($item['priority']); ?>
                                    </span>
                                    <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                </div>
                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                                <?php if (isset($item['action_items'])): ?>
                                <ul class="action-items">
                                    <?php foreach ($item['action_items'] as $action): ?>
                                    <li><?php echo htmlspecialchars($action); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Graphiques d'analyse -->
            <div class="stats-grid">
                <!-- Évolution des temps par tour -->
                <div class="stats-card">
                    <h3>Évolution des temps par tour</h3>
                    <div class="chart-container">
                        <canvas id="lapTimesChart"></canvas>
                    </div>
                </div>

                <!-- Analyse des trajectoires -->
                <div class="stats-card">
                    <h3>Analyse des trajectoires</h3>
                    <div class="chart-container">
                        <canvas id="trajectoryChart"></canvas>
                    </div>
                </div>
            </div>

            <script>
                // Configuration des graphiques d'analyse
                const lapTimesChart = new Chart(document.getElementById('lapTimesChart'), {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_column($telemetryData, 'lap_number')); ?>,
                        datasets: [{
                            label: 'Temps par tour',
                            data: <?php echo json_encode(array_column($telemetryData, 'lap_time')); ?>,
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1
                        }]
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
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Numéro du tour'
                                }
                            }
                        }
                    }
                });

                const trajectoryChart = new Chart(document.getElementById('trajectoryChart'), {
                    type: 'scatter',
                    data: {
                        datasets: [{
                            label: 'Trajectoire idéale',
                            data: <?php echo json_encode($analysis['ideal_trajectory']); ?>,
                            borderColor: 'rgba(75, 192, 192, 0.5)',
                            backgroundColor: 'rgba(75, 192, 192, 0.1)',
                            showLine: true
                        }, {
                            label: 'Trajectoire réelle',
                            data: <?php echo json_encode($analysis['actual_trajectory']); ?>,
                            borderColor: 'rgba(255, 99, 132, 0.5)',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            showLine: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: 'linear',
                                position: 'bottom',
                                title: {
                                    display: true,
                                    text: 'Position X'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Position Y'
                                }
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