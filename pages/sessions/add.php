<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Initialiser les variables
$error = null;
$success = null;
$pilots = [];
$motos = [];
$circuits = [];

try {
    // Charger les classes nécessaires
    require_once 'classes/Pilot.php';
    require_once 'classes/Moto.php';
    require_once 'classes/Circuit.php';
    
    $pilot = new Pilot();
    $moto = new Moto();
    $circuit = new Circuit();
    
    // Récupérer les données pour les listes déroulantes
    $pilots = $pilot->getAllByUserId($_SESSION['user_id']);
    $motos = $moto->getAllByUserId($_SESSION['user_id']);
    $circuits = $circuit->getAll();
} catch (Exception $e) {
    $error = "Erreur lors du chargement des données : " . $e->getMessage();
    error_log($error);
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Charger la classe Session
        require_once 'classes/Session.php';
        $session = new Session();
        
        // Valider les données
        $date = trim($_POST['date'] ?? '');
        $sessionType = trim($_POST['session_type'] ?? '');
        $pilotId = intval($_POST['pilot_id'] ?? 0);
        $motoId = intval($_POST['moto_id'] ?? 0);
        $circuitId = intval($_POST['circuit_id'] ?? 0);
        $weather = trim($_POST['weather'] ?? '');
        $trackTemperature = floatval($_POST['track_temperature'] ?? 0);
        $airTemperature = floatval($_POST['air_temperature'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        
        if (empty($date) || empty($sessionType) || $pilotId <= 0 || $motoId <= 0 || $circuitId <= 0) {
            throw new Exception("Les champs obligatoires doivent être remplis correctement.");
        }
        
        // Créer la session
        $sessionId = $session->create(
            $date,
            $sessionType,
            $pilotId,
            $motoId,
            $circuitId,
            $weather,
            $trackTemperature > 0 ? $trackTemperature : null,
            $airTemperature > 0 ? $airTemperature : null,
            $notes
        );
        
        if ($sessionId) {
            $success = "Session ajoutée avec succès !";
        } else {
            throw new Exception("Erreur lors de l'ajout de la session.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Erreur lors de l'ajout de la session : " . $e->getMessage());
    }
}
?>

<div class="container">
    <div class="page-header">
        <h1>Ajouter une Session</h1>
        <a href="index.php?page=sessions" class="btn btn-secondary">Retour à la liste</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" required>
                <div class="invalid-feedback">
                    La date est requise.
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="session_type" class="form-label">Type de session</label>
                <select class="form-select" id="session_type" name="session_type" required>
                    <option value="">Sélectionner le type</option>
                    <option value="RACE">Course</option>
                    <option value="QUALIFYING">Qualifications</option>
                    <option value="PRACTICE">Entraînement</option>
                    <option value="TRAINING">Training</option>
                    <option value="TRACK_DAY">Track Day</option>
                </select>
                <div class="invalid-feedback">
                    Le type de session est requis.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="pilot_id" class="form-label">Pilote</label>
                <select class="form-select" id="pilot_id" name="pilot_id" required>
                    <option value="">Sélectionner un pilote</option>
                    <?php foreach ($pilots as $pilot): ?>
                    <option value="<?php echo $pilot['id']; ?>">
                        <?php echo htmlspecialchars($pilot['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    Le pilote est requis.
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="moto_id" class="form-label">Moto</label>
                <select class="form-select" id="moto_id" name="moto_id" required>
                    <option value="">Sélectionner une moto</option>
                    <?php foreach ($motos as $moto): ?>
                    <option value="<?php echo $moto['id']; ?>">
                        <?php echo htmlspecialchars($moto['brand'] . ' ' . $moto['model']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    La moto est requise.
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="circuit_id" class="form-label">Circuit</label>
                <select class="form-select" id="circuit_id" name="circuit_id" required>
                    <option value="">Sélectionner un circuit</option>
                    <?php foreach ($circuits as $circuit): ?>
                    <option value="<?php echo $circuit['id']; ?>">
                        <?php echo htmlspecialchars($circuit['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    Le circuit est requis.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="weather" class="form-label">Conditions météo</label>
                <input type="text" class="form-control" id="weather" name="weather">
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="track_temperature" class="form-label">Température piste (°C)</label>
                <input type="number" class="form-control" id="track_temperature" name="track_temperature" step="0.1" min="-20" max="60">
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="air_temperature" class="form-label">Température air (°C)</label>
                <input type="number" class="form-control" id="air_temperature" name="air_temperature" step="0.1" min="-20" max="60">
            </div>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Ajouter la session</button>
        </div>
    </form>
</div>

<script>
// Validation du formulaire Bootstrap
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script> 