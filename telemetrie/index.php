<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/auth_functions.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que l'utilisateur est connecté
requireLogin();

// Connexion à la base de données
$conn = getDBConnection();

// Récupérer les sessions de l'utilisateur
$user_id = $_SESSION['user_id'];
$sql = "SELECT s.id, s.date, s.type, 
        p.nom as pilote_nom, p.prenom as pilote_prenom,
        m.marque as moto_marque, m.modele as moto_modele,
        c.nom as circuit_nom
        FROM sessions s
        JOIN pilotes p ON s.pilote_id = p.id
        JOIN motos m ON s.moto_id = m.id
        JOIN circuits c ON s.circuit_id = c.id
        ORDER BY s.date DESC, s.created_at DESC";

$result = $conn->query($sql);
$sessions = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
}

// Traitement du formulaire d'importation
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_data'])) {
    $session_id = intval($_POST['session_id'] ?? 0);
    $device_type = $_POST['device_type'] ?? '';
    
    // Simuler l'importation de données (en production, traiter le fichier uploadé)
    $file_uploaded = isset($_FILES['data_file']) && $_FILES['data_file']['error'] === UPLOAD_ERR_OK;
    
    if (empty($session_id) || empty($device_type) || !$file_uploaded) {
        $error_message = 'Tous les champs obligatoires doivent être remplis et un fichier doit être sélectionné.';
    } else {
        // Simuler le traitement des données
        $success_message = 'Données importées avec succès.';
        
        // Rediriger pour éviter la soumission multiple du formulaire
        header("Location: " . url("telemetrie/?success=1"));
        exit;
    }
}

// Message de succès après redirection
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = 'Données importées avec succès.';
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="telemetrie-container">
    <h1 class="telemetrie-title">Importation de Données de Télémétrie</h1>
    
    <div class="telemetrie-intro">
        <p>Importez les données de vos appareils de télémétrie pour analyser en détail vos performances sur circuit. Compatible avec les formats CSV, JSON et les fichiers spécifiques des principaux systèmes de télémétrie.</p>
    </div>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <div class="telemetrie-form-container">
        <h2>Importer des données</h2>
        
        <form method="POST" action="<?php echo url('telemetrie/'); ?>" enctype="multipart/form-data" class="telemetrie-form">
            <input type="hidden" name="import_data" value="1">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="session_id">Session associée:</label>
                    <select id="session_id" name="session_id" required>
                        <option value="">Sélectionner une session</option>
                        <?php foreach ($sessions as $session): ?>
                            <option value="<?php echo $session['id']; ?>">
                                <?php echo date('d/m/Y', strtotime($session['date'])) . ' - ' . $session['circuit_nom'] . ' - ' . $session['moto_marque'] . ' ' . $session['moto_modele']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="device_type">Type d'appareil:</label>
                    <select id="device_type" name="device_type" required>
                        <option value="">Sélectionner un type d'appareil</option>
                        <option value="aim">AiM (MXL, Solo, etc.)</option>
                        <option value="2d">2D Data Recording</option>
                        <option value="gps">GPS (GPX, KML)</option>
                        <option value="gopro">GoPro Telemetry</option>
                        <option value="motec">MoTeC</option>
                        <option value="starlane">Starlane</option>
                        <option value="custom">Format personnalisé</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="data_file">Fichier de données:</label>
                <input type="file" id="data_file" name="data_file" required>
                <small class="form-text">Formats acceptés: CSV, JSON, GPX, KML, et formats spécifiques aux appareils sélectionnés.</small>
            </div>
            
            <div class="form-group custom-format-options" style="display: none;">
                <label for="data_format">Format des données:</label>
                <textarea id="data_format" name="data_format" rows="3" placeholder="Décrivez le format de vos données (colonnes, séparateurs, etc.)"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Importer</button>
            </div>
        </form>
    </div>
    
    <div class="telemetrie-devices">
        <h2>Appareils compatibles</h2>
        
        <div class="devices-grid">
            <div class="device-card">
                <div class="device-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <div class="device-content">
                    <h3>AiM</h3>
                    <p>Compatible avec MXL, Solo, EVO4, etc.</p>
                    <ul>
                        <li>Formats: XRK, DRK, CSV</li>
                        <li>Données: GPS, accélération, vitesse, RPM, température</li>
                    </ul>
                </div>
            </div>
            
            <div class="device-card">
                <div class="device-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="device-content">
                    <h3>2D Data Recording</h3>
                    <p>Systèmes de télémétrie professionnels</p>
                    <ul>
                        <li>Formats: 2D, CSV, JSON</li>
                        <li>Données: GPS, accélération, vitesse, RPM, suspensions</li>
                    </ul>
                </div>
            </div>
            
            <div class="device-card">
                <div class="device-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="device-content">
                    <h3>GPS</h3>
                    <p>Trackers GPS et applications mobiles</p>
                    <ul>
                        <li>Formats: GPX, KML, CSV</li>
                        <li>Données: position, vitesse, altitude, temps</li>
                    </ul>
                </div>
            </div>
            
            <div class="device-card">
                <div class="device-icon">
                    <i class="fas fa-video"></i>
                </div>
                <div class="device-content">
                    <h3>GoPro</h3>
                    <p>Caméras GoPro avec télémétrie</p>
                    <ul>
                        <li>Formats: GPMF, CSV</li>
                        <li>Données: GPS, accélération, vitesse, altitude</li>
                    </ul>
                </div>
            </div>
            
            <div class="device-card">
                <div class="device-icon">
                    <i class="fas fa-microchip"></i>
                </div>
                <div class="device-content">
                    <h3>MoTeC</h3>
                    <p>Systèmes d'acquisition de données MoTeC</p>
                    <ul>
                        <li>Formats: LD, CSV</li>
                        <li>Données: moteur, suspensions, GPS, accélération</li>
                    </ul>
                </div>
            </div>
            
            <div class="device-card">
                <div class="device-icon">
                    <i class="fas fa-stopwatch"></i>
                </div>
                <div class="device-content">
                    <h3>Starlane</h3>
                    <p>Chronos et systèmes d'acquisition Starlane</p>
                    <ul>
                        <li>Formats: STC, CSV</li>
                        <li>Données: temps au tour, vitesse, RPM, température</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="telemetrie-examples">
        <h2>Exemples de visualisations</h2>
        
        <div class="examples-grid">
            <div class="example-card">
                <h3>Trajectoire GPS</h3>
                <div class="example-image">
                    <img src="<?php echo url('images/telemetrie/gps_track.jpg'); ?>" alt="Trajectoire GPS">
                </div>
                <p>Visualisez votre trajectoire sur le circuit avec code couleur selon la vitesse.</p>
            </div>
            
            <div class="example-card">
                <h3>Graphique d'accélération</h3>
                <div class="example-image">
                    <img src="<?php echo url('images/telemetrie/acceleration.jpg'); ?>" alt="Graphique d'accélération">
                </div>
                <p>Analysez les forces G longitudinales et latérales pour optimiser votre pilotage.</p>
            </div>
            
            <div class="example-card">
                <h3>Données moteur</h3>
                <div class="example-image">
                    <img src="<?php echo url('images/telemetrie/engine_data.jpg'); ?>" alt="Données moteur">
                </div>
                <p>Suivez les RPM, la température et d'autres paramètres moteur tout au long du circuit.</p>
            </div>
            
            <div class="example-card">
                <h3>Comparaison de tours</h3>
                <div class="example-image">
                    <img src="<?php echo url('images/telemetrie/lap_comparison.jpg'); ?>" alt="Comparaison de tours">
                </div>
                <p>Comparez différents tours pour identifier les zones d'amélioration.</p>
            </div>
        </div>
    </div>
    
    <div class="telemetrie-info">
        <h2>Comment utiliser les données de télémétrie</h2>
        
        <div class="info-steps">
            <div class="info-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>Collecte des données</h3>
                    <p>Configurez votre appareil de télémétrie sur votre moto et enregistrez les données pendant votre session sur circuit.</p>
                </div>
            </div>
            
            <div class="info-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>Importation</h3>
                    <p>Téléchargez les données depuis votre appareil et importez-les dans TeleMoto en les associant à la session correspondante.</p>
                </div>
            </div>
            
            <div class="info-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>Analyse</h3>
                    <p>Utilisez les outils de visualisation pour analyser vos performances : trajectoires, points de freinage, accélération, etc.</p>
                </div>
            </div>
            
            <div class="info-step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h3>Optimisation</h3>
                    <p>Identifiez les zones d'amélioration et ajustez vos réglages ou votre technique de pilotage en conséquence.</p>
                </div>
            </div>
            
            <div class="info-step">
                <div class="step-number">5</div>
                <div class="step-content">
                    <h3>Comparaison</h3>
                    <p>Comparez les données entre différentes sessions pour suivre votre progression et l'efficacité des modifications apportées.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Afficher les options de format personnalisé si nécessaire
    const deviceTypeSelect = document.getElementById('device_type');
    const customFormatOptions = document.querySelector('.custom-format-options');
    
    if (deviceTypeSelect && customFormatOptions) {
        deviceTypeSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customFormatOptions.style.display = 'block';
            } else {
                customFormatOptions.style.display = 'none';
            }
        });
    }
});
</script>

<style>
.telemetrie-container {
    padding: 1rem 0;
}

.telemetrie-title {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 0.5rem;
}

.telemetrie-intro {
    margin-bottom: 2rem;
    font-size: 1.1rem;
    line-height: 1.6;
}

.telemetrie-form-container {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
}

.telemetrie-form-container h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.telemetrie-form .form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

@media (min-width: 768px) {
    .telemetrie-form .form-row {
        grid-template-columns: 1fr 1fr;
    }
}

.telemetrie-form .form-group {
    margin-bottom: 1.5rem;
}

.telemetrie-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.telemetrie-form input[type="file"],
.telemetrie-form select,
.telemetrie-form textarea {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid var(--light-gray);
    border-radius: var(--border-radius);
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--text-color);
}

.telemetrie-form textarea {
    resize: vertical;
}

.form-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: var(--dark-gray);
}

.form-actions {
    margin-top: 1.5rem;
}

.telemetrie-devices {
    margin-bottom: 2rem;
}

.telemetrie-devices h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.devices-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.device-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
    display: flex;
    gap: 1rem;
    transition: transform 0.3s, border-color 0.3s;
}

.device-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary-color);
}

.device-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
}

.device-content {
    flex: 1;
}

.device-content h3 {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.device-content p {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.device-content ul {
    margin-left: 1.5rem;
    font-size: 0.9rem;
}

.device-content li {
    margin-bottom: 0.25rem;
}

.telemetrie-examples {
    margin-bottom: 2rem;
}

.telemetrie-examples h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.examples-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.example-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    border: 1px solid var(--light-gray);
    transition: transform 0.3s, border-color 0.3s;
}

.example-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary-color);
}

.example-card h3 {
    color: var(--primary-color);
    padding: 1rem;
    margin: 0;
    border-bottom: 1px solid var(--light-gray);
}

.example-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.example-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.example-card p {
    padding: 1rem;
    margin: 0;
    font-size: 0.9rem;
}

.telemetrie-info {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
}

.telemetrie-info h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.info-steps {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-step {
    display: flex;
    gap: 1.5rem;
}

.step-number {
    width: 40px;
    height: 40px;
    background-color: var(--primary-color);
    color: #000;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
}

.step-content {
    flex: 1;
}

.step-content h3 {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.step-content p {
    line-height: 1.6;
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
