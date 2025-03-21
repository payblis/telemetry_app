<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/session_handler.php';

// Vérifier l'authentification
if (!$auth->isLoggedIn()) {
    header('Location: /login.php');
    exit();
}

// Vérifier l'ID de session
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /sessions/');
    exit();
}

$sessionId = (int)$_GET['id'];

// Vérifier les permissions d'accès
if (!canAccessSession($sessionId)) {
    header('Location: /sessions/');
    exit();
}

// Récupérer les détails de la session
$sessionHandler = new SessionHandler($pdo);
$sessionDetails = $sessionHandler->getSessionDetails($sessionId);

if (!$sessionDetails) {
    header('Location: /sessions/');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Télémétrie - Session #<?php echo htmlspecialchars($sessionId); ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 2rem;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            padding: 1rem;
        }
        
        .telemetry-error {
            display: none;
            background-color: #fee2e2;
            border: 1px solid #ef4444;
            color: #b91c1c;
            padding: 1rem;
            margin: 1rem;
            border-radius: 0.375rem;
        }
        
        .session-info {
            background-color: #f3f4f6;
            padding: 1rem;
            margin: 1rem;
            border-radius: 0.375rem;
        }
        
        .session-info h2 {
            margin-top: 0;
        }
        
        .session-info dl {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .session-info dt {
            font-weight: bold;
            color: #374151;
        }
        
        .session-info dd {
            margin: 0;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Télémétrie - Session #<?php echo htmlspecialchars($sessionId); ?></h1>
            <nav>
                <a href="/sessions/" class="button">← Retour aux sessions</a>
                <a href="/sessions/details.php?id=<?php echo htmlspecialchars($sessionId); ?>" class="button">Détails de la session</a>
            </nav>
        </header>

        <div class="session-info">
            <h2>Informations de la session</h2>
            <dl>
                <div>
                    <dt>Circuit</dt>
                    <dd><?php echo htmlspecialchars($sessionDetails['circuit_nom']); ?></dd>
                </div>
                <div>
                    <dt>Pilote</dt>
                    <dd><?php echo htmlspecialchars($sessionDetails['pilote_nom']); ?></dd>
                </div>
                <div>
                    <dt>Date</dt>
                    <dd><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($sessionDetails['date_session']))); ?></dd>
                </div>
                <div>
                    <dt>Moto</dt>
                    <dd><?php echo htmlspecialchars($sessionDetails['moto_modele']); ?></dd>
                </div>
                <div>
                    <dt>Conditions</dt>
                    <dd><?php echo htmlspecialchars($sessionDetails['conditions_meteo']); ?></dd>
                </div>
                <div>
                    <dt>Température</dt>
                    <dd><?php echo htmlspecialchars($sessionDetails['temperature']) . '°C'; ?></dd>
                </div>
            </dl>
        </div>

        <div id="telemetryError" class="telemetry-error"></div>

        <div class="charts-grid">
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

    <script src="/assets/js/telemetry.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const visualizer = new TelemetryVisualizer(<?php echo json_encode($sessionId); ?>);
            visualizer.initialize();

            // Nettoyer lors de la navigation
            window.addEventListener('beforeunload', function() {
                visualizer.destroy();
            });
        });
    </script>
</body>
</html> 