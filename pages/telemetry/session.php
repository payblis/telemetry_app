<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Vérifier si l'ID de la session est fourni
if (!isset($_GET['id'])) {
    header('Location: index.php?page=sessions');
    exit();
}

$sessionId = intval($_GET['id']);

// Initialiser les variables
$error = null;
$session = null;

try {
    // Charger les classes nécessaires
    require_once 'classes/Session.php';
    require_once 'classes/Pilot.php';
    require_once 'classes/Moto.php';
    require_once 'classes/Circuit.php';
    
    $sessionObj = new Session();
    
    // Vérifier si la session appartient à l'utilisateur
    if (!$sessionObj->belongsToUser($sessionId, $_SESSION['user_id'])) {
        throw new Exception("Vous n'avez pas accès à cette session.");
    }
    
    // Récupérer les données de la session
    $session = $sessionObj->getById($sessionId);
    if (!$session) {
        throw new Exception("Session non trouvée.");
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur lors de la récupération des données de télémétrie : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Télémétrie - Session <?php echo htmlspecialchars($session['date']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <div class="session-header">
                <div class="session-info">
                    <h1><?php echo date('d/m/Y', strtotime($session['date'])); ?> <?php echo date('H:i', strtotime($session['date'])); ?></h1>
                    <div class="session-details">
                        <div class="detail-item">
                            <span class="label">Circuit</span>
                            <span class="value"><?php echo htmlspecialchars($session['circuit_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Pilote</span>
                            <span class="value"><?php echo htmlspecialchars($session['pilot_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Moto</span>
                            <span class="value"><?php echo htmlspecialchars($session['moto_brand'] . ' ' . $session['moto_model']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="session-controls">
                    <button id="startSession" class="btn btn-primary">
                        <i class="bi bi-play-fill"></i> Démarrer
                    </button>
                    <button id="stopSession" class="btn btn-danger" style="display: none;">
                        <i class="bi bi-stop-fill"></i> Arrêter
                    </button>
                </div>
            </div>

            <div class="telemetry-grid">
                <!-- Compteur de vitesse -->
                <div class="gauge-container">
                    <div class="circular-gauge speed-gauge">
                        <div class="gauge-value">
                            <span id="speedValue">0</span>
                            <span class="gauge-unit">km/h</span>
                        </div>
                    </div>
                    <div class="gauge-label">Vitesse</div>
                </div>

                <!-- Compte-tours -->
                <div class="gauge-container">
                    <div class="circular-gauge rpm-gauge">
                        <div class="gauge-value">
                            <span id="rpmValue">0</span>
                            <span class="gauge-unit">tr/min</span>
                        </div>
                    </div>
                    <div class="gauge-label">Régime moteur</div>
                </div>

                <!-- Temps au tour -->
                <div class="lap-times">
                    <div class="time-section">
                        <div class="time-label">Meilleur tour</div>
                        <div class="time-display" id="bestLap">--:--:---</div>
                    </div>
                    <div class="time-section">
                        <div class="time-label">Tour actuel</div>
                        <div class="time-display" id="currentLap">00:00:000</div>
                    </div>
                    <div class="time-section">
                        <div class="time-label">Dernier tour</div>
                        <div class="time-display" id="lastLap">--:--:---</div>
                    </div>
                </div>
            </div>

            <!-- Tableau des temps -->
            <div class="lap-table-container">
                <h2>Historique des tours</h2>
                <table class="data-table" id="lapTable">
                    <thead>
                        <tr>
                            <th>Tour</th>
                            <th>Temps</th>
                            <th>Écart</th>
                            <th>S1</th>
                            <th>S2</th>
                            <th>S3</th>
                            <th>V.Max</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Les données seront ajoutées dynamiquement -->
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Variables globales
        let isSessionActive = false;
        let currentLapTime = 0;
        let lapInterval;
        let telemetryInterval;

        // Fonctions de gestion de la session
        function startSession() {
            isSessionActive = true;
            document.getElementById('startSession').style.display = 'none';
            document.getElementById('stopSession').style.display = 'inline-block';
            startTelemetry();
        }

        function stopSession() {
            isSessionActive = false;
            document.getElementById('stopSession').style.display = 'none';
            document.getElementById('startSession').style.display = 'inline-block';
            stopTelemetry();
        }

        function startTelemetry() {
            // Démarrer la mise à jour des données
            telemetryInterval = setInterval(updateTelemetryData, 100);
            // Démarrer le chrono
            lapInterval = setInterval(updateLapTime, 10);
        }

        function stopTelemetry() {
            clearInterval(telemetryInterval);
            clearInterval(lapInterval);
        }

        function updateTelemetryData() {
            // Simuler des données de télémétrie (à remplacer par des données réelles)
            const speed = Math.floor(Math.random() * 300);
            const rpm = Math.floor(Math.random() * 15000);
            
            document.getElementById('speedValue').textContent = speed;
            document.getElementById('rpmValue').textContent = rpm;
        }

        function updateLapTime() {
            currentLapTime += 10;
            const display = formatTime(currentLapTime);
            document.getElementById('currentLap').textContent = display;
        }

        function formatTime(ms) {
            const minutes = Math.floor(ms / 60000);
            const seconds = Math.floor((ms % 60000) / 1000);
            const milliseconds = ms % 1000;
            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}.${String(milliseconds).padStart(3, '0')}`;
        }

        // Event listeners
        document.getElementById('startSession').addEventListener('click', startSession);
        document.getElementById('stopSession').addEventListener('click', stopSession);
    </script>
</body>
</html> 