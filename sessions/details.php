<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/session_handler.php';

// Vérifier l'authentification
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

// Vérifier l'ID de la session
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../sessions.php');
    exit();
}

$sessionId = (int)$_GET['id'];

// Vérifier les permissions d'accès
if (!canAccessSession($sessionId)) {
    header('Location: ../sessions.php');
    exit();
}

// Récupérer les détails de la session
$result = $sessionHandler->getSessionDetails($sessionId);

if (!$result['success']) {
    header('Location: ../sessions.php');
    exit();
}

$session = $result['session'];

// Traitement du formulaire d'analyse
$analysisResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['problem'])) {
    $analysisResult = $sessionHandler->analyzeProblem($sessionId, $_POST['problem']);
}

// Traitement du feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback'])) {
    $sessionHandler->saveFeedback($sessionId, [
        'categorie' => $_POST['categorie'],
        'probleme' => $_POST['probleme'],
        'solution' => $_POST['solution'],
        'succes' => $_POST['succes'] === '1',
        'commentaire' => $_POST['commentaire']
    ]);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Session - TéléMoto AI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo">TéléMoto AI</div>
                <ul>
                    <li><a href="../index.php">Tableau de bord</a></li>
                    <li><a href="../sessions.php">Sessions</a></li>
                    <li><a href="../logout.php">Déconnexion</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <div class="session-header">
                <h1>Session - <?php echo htmlspecialchars($session['circuit_nom']); ?></h1>
                <div class="session-meta">
                    <p>
                        <strong>Date :</strong> 
                        <?php echo formatDate($session['date_session']); ?>
                    </p>
                    <p>
                        <strong>Pilote :</strong> 
                        <?php echo htmlspecialchars($session['pilote_nom']); ?>
                    </p>
                    <p>
                        <strong>Moto :</strong> 
                        <?php echo htmlspecialchars($session['moto_marque'] . ' ' . $session['moto_modele']); ?>
                    </p>
                </div>
            </div>

            <div class="session-grid">
                <div class="card">
                    <h2>Conditions</h2>
                    <ul class="info-list">
                        <li>
                            <strong>Météo :</strong> 
                            <?php echo htmlspecialchars($session['conditions_meteo']); ?>
                        </li>
                        <li>
                            <strong>Température :</strong> 
                            <?php echo htmlspecialchars($session['temperature']); ?>°C
                        </li>
                        <li>
                            <strong>Humidité :</strong> 
                            <?php echo htmlspecialchars($session['humidite']); ?>%
                        </li>
                    </ul>
                </div>

                <div class="card">
                    <h2>Réglages actuels</h2>
                    <div class="settings-grid">
                        <div class="settings-column">
                            <h3>Avant</h3>
                            <ul class="info-list">
                                <li>
                                    <strong>Précharge :</strong> 
                                    <?php echo htmlspecialchars($session['reglages']['precharge_avant']); ?> mm
                                </li>
                                <li>
                                    <strong>Compression :</strong> 
                                    <?php echo htmlspecialchars($session['reglages']['compression_avant']); ?> clicks
                                </li>
                                <li>
                                    <strong>Détente :</strong> 
                                    <?php echo htmlspecialchars($session['reglages']['detente_avant']); ?> clicks
                                </li>
                                <li>
                                    <strong>Pression pneu :</strong> 
                                    <?php echo htmlspecialchars($session['reglages']['pression_avant']); ?> bar
                                </li>
                            </ul>
                        </div>
                        <div class="settings-column">
                            <h3>Arrière</h3>
                            <ul class="info-list">
                                <li>
                                    <strong>Précharge :</strong> 
                                    <?php echo htmlspecialchars($session['reglages']['precharge_arriere']); ?> mm
                                </li>
                                <li>
                                    <strong>Compression :</strong> 
                                    <?php echo htmlspecialchars($session['reglages']['compression_arriere']); ?> clicks
                                </li>
                                <li>
                                    <strong>Détente :</strong> 
                                    <?php echo htmlspecialchars($session['reglages']['detente_arriere']); ?> clicks
                                </li>
                                <li>
                                    <strong>Pression pneu :</strong> 
                                    <?php echo htmlspecialchars($session['reglages']['pression_arriere']); ?> bar
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card full-width">
                    <h2>Données Télémétriques</h2>
                    <div class="telemetry-charts">
                        <div class="chart-container">
                            <canvas id="speedChart"></canvas>
                        </div>
                        <div class="chart-container">
                            <canvas id="rpmChart"></canvas>
                        </div>
                        <div class="chart-container">
                            <canvas id="suspensionChart"></canvas>
                        </div>
                        <div class="chart-container">
                            <canvas id="temperatureChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2>Assistant IA</h2>
                    <form method="POST" action="" class="ai-form">
                        <div class="form-group">
                            <label for="problem">Décrivez votre problème ou ressenti</label>
                            <textarea id="problem" name="problem" required rows="4"></textarea>
                        </div>
                        <button type="submit" class="button">Obtenir des recommandations</button>
                    </form>

                    <?php if ($analysisResult && $analysisResult['success']): ?>
                    <div class="ai-response">
                        <h3>Recommandations</h3>
                        <?php if ($analysisResult['source'] === 'internal'): ?>
                            <?php foreach ($analysisResult['suggestions'] as $suggestion): ?>
                            <div class="suggestion">
                                <p><?php echo nl2br(htmlspecialchars($suggestion['solution'])); ?></p>
                                <p class="confidence">Confiance : <?php echo $suggestion['confiance'] * 100; ?>%</p>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="suggestion">
                                <?php echo nl2br(htmlspecialchars($analysisResult['recommendations'])); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="feedback-form">
                            <input type="hidden" name="feedback" value="1">
                            <input type="hidden" name="probleme" value="<?php echo htmlspecialchars($_POST['problem']); ?>">
                            <input type="hidden" name="solution" value="<?php echo htmlspecialchars($analysisResult['source'] === 'internal' ? $analysisResult['suggestions'][0]['solution'] : $analysisResult['recommendations']); ?>">
                            
                            <div class="form-group">
                                <label>Les recommandations ont-elles été utiles ?</label>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="succes" value="1" required> Oui
                                    </label>
                                    <label>
                                        <input type="radio" name="succes" value="0" required> Non
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="commentaire">Commentaire (optionnel)</label>
                                <textarea id="commentaire" name="commentaire" rows="2"></textarea>
                            </div>

                            <button type="submit" class="button">Envoyer le feedback</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> TéléMoto AI - Tous droits réservés</p>
        </footer>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Charger les données télémétriques
            loadTelemetryData(<?php echo $sessionId; ?>);
            
            // Configuration des graphiques
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'second'
                        }
                    }
                }
            };
            
            // Créer les graphiques vides
            const speedChart = new Chart(document.getElementById('speedChart'), {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Vitesse (km/h)',
                        borderColor: '#2563eb',
                        data: []
                    }]
                },
                options: chartOptions
            });
            
            const rpmChart = new Chart(document.getElementById('rpmChart'), {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Régime moteur (tr/min)',
                        borderColor: '#dc2626',
                        data: []
                    }]
                },
                options: chartOptions
            });
            
            const suspensionChart = new Chart(document.getElementById('suspensionChart'), {
                type: 'line',
                data: {
                    datasets: [
                        {
                            label: 'Suspension avant (mm)',
                            borderColor: '#059669',
                            data: []
                        },
                        {
                            label: 'Suspension arrière (mm)',
                            borderColor: '#d97706',
                            data: []
                        }
                    ]
                },
                options: chartOptions
            });
            
            const temperatureChart = new Chart(document.getElementById('temperatureChart'), {
                type: 'line',
                data: {
                    datasets: [
                        {
                            label: 'Température pneu avant (°C)',
                            borderColor: '#7c3aed',
                            data: []
                        },
                        {
                            label: 'Température pneu arrière (°C)',
                            borderColor: '#db2777',
                            data: []
                        }
                    ]
                },
                options: chartOptions
            });
            
            // Mettre à jour les graphiques avec les données
            window.updateCharts = function(data) {
                speedChart.data.datasets[0].data = data.speed;
                rpmChart.data.datasets[0].data = data.rpm;
                suspensionChart.data.datasets[0].data = data.suspensionFront;
                suspensionChart.data.datasets[1].data = data.suspensionRear;
                temperatureChart.data.datasets[0].data = data.temperatureFront;
                temperatureChart.data.datasets[1].data = data.temperatureRear;
                
                speedChart.update();
                rpmChart.update();
                suspensionChart.update();
                temperatureChart.update();
            };
        });
    </script>
</body>
</html> 