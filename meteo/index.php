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

// Récupérer les données météo si disponibles
$weather_data = null;
$location = '';
$weather_error = '';

if (isset($_GET['location']) && !empty($_GET['location'])) {
    $location = trim($_GET['location']);
    
    // Appel à l'API météo
    $api_key = 'demo_key'; // Remplacer par une vraie clé API en production
    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($location) . "&units=metric&appid=" . $api_key;
    
    // Simuler une réponse pour le développement
    // En production, utiliser file_get_contents($url) ou curl
    $weather_data = [
        'name' => $location,
        'main' => [
            'temp' => rand(10, 35),
            'humidity' => rand(30, 90),
            'pressure' => rand(1000, 1030)
        ],
        'wind' => [
            'speed' => rand(0, 30) / 10,
            'deg' => rand(0, 359)
        ],
        'weather' => [
            [
                'main' => ['Clear', 'Clouds', 'Rain', 'Thunderstorm'][rand(0, 3)],
                'description' => 'Simulation pour développement',
                'icon' => '01d'
            ]
        ]
    ];
}

// Traitement du formulaire de création de session
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_session'])) {
    // Récupérer les données du formulaire
    $date = $_POST['date'] ?? '';
    $type = $_POST['type'] ?? '';
    $pilote_id = $_POST['pilote_id'] ?? '';
    $moto_id = $_POST['moto_id'] ?? '';
    $circuit_id = $_POST['circuit_id'] ?? '';
    
    // Données météo
    $temperature = $_POST['temperature'] ?? '';
    $humidity = $_POST['humidity'] ?? '';
    $wind_speed = $_POST['wind_speed'] ?? '';
    $wind_direction = $_POST['wind_direction'] ?? '';
    $pressure = $_POST['pressure'] ?? '';
    $weather_condition = $_POST['weather_condition'] ?? '';
    
    // Construire la chaîne de conditions météo
    $conditions = "Température: $temperature°C, Humidité: $humidity%, Vent: $wind_speed km/h $wind_direction°, Pression: $pressure hPa, Conditions: $weather_condition";
    
    // Validation des données
    if (empty($date) || empty($type) || empty($pilote_id) || empty($moto_id) || empty($circuit_id)) {
        $error_message = 'Tous les champs obligatoires doivent être remplis.';
    } else {
        // Insérer la session dans la base de données
        $stmt = $conn->prepare("INSERT INTO sessions (date, type, pilote_id, moto_id, circuit_id, conditions) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiiis", $date, $type, $pilote_id, $moto_id, $circuit_id, $conditions);
        
        if ($stmt->execute()) {
            $session_id = $conn->insert_id;
            $success_message = 'Session créée avec succès.';
            
            // Rediriger vers la page de détails de la session
            header("Location: " . url("sessions/details.php?id=$session_id"));
            exit;
        } else {
            $error_message = 'Erreur lors de la création de la session: ' . $conn->error;
        }
    }
}

// Récupérer les pilotes, motos et circuits pour les listes déroulantes
$pilotes = [];
$motos = [];
$circuits = [];

$result = $conn->query("SELECT id, nom, prenom FROM pilotes ORDER BY nom, prenom");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pilotes[] = $row;
    }
}

$result = $conn->query("SELECT id, marque, modele FROM motos ORDER BY marque, modele");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $motos[] = $row;
    }
}

$result = $conn->query("SELECT id, nom, pays FROM circuits ORDER BY nom");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $circuits[] = $row;
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="meteo-container">
    <h1 class="meteo-title">Intégration Météo</h1>
    
    <div class="meteo-intro">
        <p>Obtenez les données météorologiques en temps réel pour votre session. Ces informations seront automatiquement enregistrées avec votre session pour une analyse plus précise des conditions de pilotage.</p>
    </div>
    
    <div class="meteo-search">
        <form method="GET" action="<?php echo url('meteo/'); ?>" class="meteo-form">
            <div class="form-group">
                <label for="location">Localisation (ville ou circuit):</label>
                <div class="search-input-group">
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>" placeholder="Ex: Le Mans, France" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <?php if ($weather_data): ?>
        <div class="meteo-results">
            <div class="meteo-card">
                <div class="meteo-header">
                    <h2><?php echo htmlspecialchars($weather_data['name']); ?></h2>
                    <div class="meteo-icon">
                        <img src="https://openweathermap.org/img/wn/<?php echo $weather_data['weather'][0]['icon']; ?>@2x.png" alt="<?php echo $weather_data['weather'][0]['main']; ?>">
                    </div>
                </div>
                
                <div class="meteo-body">
                    <div class="meteo-main">
                        <div class="meteo-temp"><?php echo round($weather_data['main']['temp']); ?>°C</div>
                        <div class="meteo-desc"><?php echo ucfirst($weather_data['weather'][0]['main']); ?></div>
                    </div>
                    
                    <div class="meteo-details">
                        <div class="meteo-detail">
                            <i class="fas fa-tint"></i>
                            <span>Humidité: <?php echo $weather_data['main']['humidity']; ?>%</span>
                        </div>
                        <div class="meteo-detail">
                            <i class="fas fa-wind"></i>
                            <span>Vent: <?php echo $weather_data['wind']['speed']; ?> m/s</span>
                        </div>
                        <div class="meteo-detail">
                            <i class="fas fa-compass"></i>
                            <span>Direction: <?php echo $weather_data['wind']['deg']; ?>°</span>
                        </div>
                        <div class="meteo-detail">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Pression: <?php echo $weather_data['main']['pressure']; ?> hPa</span>
                        </div>
                    </div>
                </div>
                
                <div class="meteo-actions">
                    <button class="btn btn-primary" id="useWeatherData">Utiliser ces données pour une nouvelle session</button>
                </div>
            </div>
        </div>
        
        <div class="session-form-container" id="sessionForm" style="display: none;">
            <h2>Créer une nouvelle session avec ces données météo</h2>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo url('meteo/'); ?>" class="session-form">
                <input type="hidden" name="create_session" value="1">
                <input type="hidden" name="temperature" value="<?php echo round($weather_data['main']['temp']); ?>">
                <input type="hidden" name="humidity" value="<?php echo $weather_data['main']['humidity']; ?>">
                <input type="hidden" name="wind_speed" value="<?php echo $weather_data['wind']['speed']; ?>">
                <input type="hidden" name="wind_direction" value="<?php echo $weather_data['wind']['deg']; ?>">
                <input type="hidden" name="pressure" value="<?php echo $weather_data['main']['pressure']; ?>">
                <input type="hidden" name="weather_condition" value="<?php echo $weather_data['weather'][0]['main']; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Type de session:</label>
                        <select id="type" name="type" required>
                            <option value="">Sélectionner un type</option>
                            <option value="course">Course</option>
                            <option value="qualification">Qualification</option>
                            <option value="free_practice">Essai libre</option>
                            <option value="entrainement">Entraînement</option>
                            <option value="track_day">Track Day</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="pilote_id">Pilote:</label>
                        <select id="pilote_id" name="pilote_id" required>
                            <option value="">Sélectionner un pilote</option>
                            <?php foreach ($pilotes as $pilote): ?>
                                <option value="<?php echo $pilote['id']; ?>"><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="moto_id">Moto:</label>
                        <select id="moto_id" name="moto_id" required>
                            <option value="">Sélectionner une moto</option>
                            <?php foreach ($motos as $moto): ?>
                                <option value="<?php echo $moto['id']; ?>"><?php echo htmlspecialchars($moto['marque'] . ' ' . $moto['modele']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="circuit_id">Circuit:</label>
                    <select id="circuit_id" name="circuit_id" required>
                        <option value="">Sélectionner un circuit</option>
                        <?php foreach ($circuits as $circuit): ?>
                            <option value="<?php echo $circuit['id']; ?>"><?php echo htmlspecialchars($circuit['nom'] . ($circuit['pays'] ? ', ' . $circuit['pays'] : '')); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Créer la session</button>
                    <button type="button" class="btn btn-secondary" id="cancelSession">Annuler</button>
                </div>
            </form>
        </div>
    <?php elseif (!empty($location)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> Impossible de récupérer les données météo pour cette localisation. Veuillez vérifier l'orthographe ou essayer une autre localisation.
        </div>
    <?php endif; ?>
    
    <div class="meteo-info">
        <h3>Pourquoi les données météo sont importantes</h3>
        <p>Les conditions météorologiques ont un impact significatif sur les performances de votre moto et votre pilotage :</p>
        <ul>
            <li><strong>Température</strong> - Affecte l'adhérence des pneus et les performances du moteur</li>
            <li><strong>Humidité</strong> - Influence la puissance du moteur et la visibilité</li>
            <li><strong>Vent</strong> - Peut affecter la stabilité de la moto, surtout dans les virages rapides</li>
            <li><strong>Pression atmosphérique</strong> - Impact sur les performances du moteur et la carburation</li>
        </ul>
        <p>En enregistrant ces données avec vos sessions, vous pourrez mieux comprendre comment adapter vos réglages en fonction des conditions météorologiques.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const useWeatherDataBtn = document.getElementById('useWeatherData');
    const sessionForm = document.getElementById('sessionForm');
    const cancelSessionBtn = document.getElementById('cancelSession');
    
    if (useWeatherDataBtn) {
        useWeatherDataBtn.addEventListener('click', function() {
            sessionForm.style.display = 'block';
            this.parentElement.style.display = 'none';
            window.scrollTo({
                top: sessionForm.offsetTop,
                behavior: 'smooth'
            });
        });
    }
    
    if (cancelSessionBtn) {
        cancelSessionBtn.addEventListener('click', function() {
            sessionForm.style.display = 'none';
            if (useWeatherDataBtn) {
                useWeatherDataBtn.parentElement.style.display = 'block';
            }
        });
    }
});
</script>

<style>
.meteo-container {
    padding: 1rem 0;
}

.meteo-title {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 0.5rem;
}

.meteo-intro {
    margin-bottom: 2rem;
    font-size: 1.1rem;
    line-height: 1.6;
}

.meteo-search {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
}

.meteo-form .form-group {
    margin-bottom: 1rem;
}

.meteo-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.search-input-group {
    display: flex;
    gap: 0.5rem;
}

.search-input-group input {
    flex: 1;
    padding: 0.8rem;
    border: 1px solid var(--light-gray);
    border-radius: var(--border-radius);
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--text-color);
}

.meteo-results {
    margin-bottom: 2rem;
}

.meteo-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    border: 1px solid var(--light-gray);
    overflow: hidden;
}

.meteo-header {
    background-color: rgba(0, 168, 255, 0.1);
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--light-gray);
}

.meteo-header h2 {
    margin: 0;
    color: var(--primary-color);
}

.meteo-icon img {
    width: 80px;
    height: 80px;
}

.meteo-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.meteo-main {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.meteo-temp {
    font-size: 3rem;
    font-weight: bold;
    color: var(--text-color);
}

.meteo-desc {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.meteo-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.meteo-detail {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.meteo-detail i {
    color: var(--primary-color);
    font-size: 1.2rem;
    width: 25px;
    text-align: center;
}

.meteo-actions {
    padding: 1.5rem;
    border-top: 1px solid var(--light-gray);
    text-align: center;
}

.session-form-container {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
}

.session-form-container h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.session-form .form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

@media (min-width: 768px) {
    .session-form .form-row {
        grid-template-columns: 1fr 1fr;
    }
}

.session-form .form-group {
    margin-bottom: 1rem;
}

.session-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.session-form input, .session-form select {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid var(--light-gray);
    border-radius: var(--border-radius);
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--text-color);
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn-secondary {
    background-color: var(--dark-gray);
    color: var(--text-color);
}

.btn-secondary:hover {
    background-color: var(--light-gray);
}

.meteo-info {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
}

.meteo-info h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.meteo-info p {
    margin-bottom: 1rem;
    line-height: 1.6;
}

.meteo-info ul {
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}

.meteo-info li {
    margin-bottom: 0.5rem;
    line-height: 1.6;
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
