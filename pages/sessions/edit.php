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
$success = null;
$session = null;
$pilots = [];
$motos = [];
$circuits = [];

try {
    // Charger les classes nécessaires
    require_once 'classes/Session.php';
    require_once 'classes/Pilot.php';
    require_once 'classes/Moto.php';
    require_once 'classes/Circuit.php';
    
    $sessionObj = new Session();
    $pilotObj = new Pilot();
    $motoObj = new Moto();
    $circuitObj = new Circuit();
    
    // Vérifier si la session appartient à l'utilisateur
    if (!$sessionObj->belongsToUser($sessionId, $_SESSION['user_id'])) {
        throw new Exception("Vous n'avez pas accès à cette session.");
    }
    
    // Récupérer les données de la session
    $session = $sessionObj->getById($sessionId);
    if (!$session) {
        throw new Exception("Session non trouvée.");
    }
    
    // Récupérer les données pour les listes déroulantes
    $pilots = $pilotObj->getAllByUserId($_SESSION['user_id']);
    $motos = $motoObj->getAllByUserId($_SESSION['user_id']);
    $circuits = $circuitObj->getAllByUserId($_SESSION['user_id']);
    
    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Valider les données
        $date = trim($_POST['date'] ?? '');
        $sessionType = trim($_POST['session_type'] ?? '');
        $pilotId = intval($_POST['pilot_id'] ?? 0);
        $motoId = intval($_POST['moto_id'] ?? 0);
        $circuitId = intval($_POST['circuit_id'] ?? 0);
        $weather = trim($_POST['weather'] ?? '');
        $trackTemp = floatval($_POST['track_temp'] ?? 0);
        $airTemp = floatval($_POST['air_temp'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        
        if (empty($date) || empty($sessionType) || $pilotId <= 0 || $motoId <= 0 || $circuitId <= 0) {
            throw new Exception("Les champs obligatoires doivent être remplis.");
        }
        
        // Mettre à jour la session
        $data = [
            'date' => $date,
            'session_type' => $sessionType,
            'pilot_id' => $pilotId,
            'moto_id' => $motoId,
            'circuit_id' => $circuitId,
            'weather' => $weather,
            'track_temp' => $trackTemp,
            'air_temp' => $airTemp,
            'notes' => $notes
        ];
        
        if ($sessionObj->update($sessionId, $data)) {
            $success = "Session mise à jour avec succès !";
            // Recharger les données de la session
            $session = $sessionObj->getById($sessionId);
        } else {
            throw new Exception("Erreur lors de la mise à jour de la session.");
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur lors de la modification de la session : " . $e->getMessage());
}
?>

<div class="container">
    <div class="page-header">
        <h1>Modifier une Session</h1>
        <a href="index.php?page=sessions" class="btn btn-secondary">Retour à la liste</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($session): ?>
    <form method="POST" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($session['date']); ?>" required>
                <div class="invalid-feedback">
                    La date est requise.
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="session_type" class="form-label">Type de session</label>
                <select class="form-select" id="session_type" name="session_type" required>
                    <option value="">Sélectionner le type</option>
                    <option value="Entraînement" <?php echo $session['session_type'] === 'Entraînement' ? 'selected' : ''; ?>>Entraînement</option>
                    <option value="Qualification" <?php echo $session['session_type'] === 'Qualification' ? 'selected' : ''; ?>>Qualification</option>
                    <option value="Course" <?php echo $session['session_type'] === 'Course' ? 'selected' : ''; ?>>Course</option>
                    <option value="Test" <?php echo $session['session_type'] === 'Test' ? 'selected' : ''; ?>>Test</option>
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
                    <option value="">Sélectionner le pilote</option>
                    <?php foreach ($pilots as $pilot): ?>
                        <option value="<?php echo $pilot['id']; ?>" <?php echo $pilot['id'] == $session['pilot_id'] ? 'selected' : ''; ?>>
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
                    <option value="">Sélectionner la moto</option>
                    <?php foreach ($motos as $moto): ?>
                        <option value="<?php echo $moto['id']; ?>" <?php echo $moto['id'] == $session['moto_id'] ? 'selected' : ''; ?>>
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
                    <option value="">Sélectionner le circuit</option>
                    <?php foreach ($circuits as $circuit): ?>
                        <option value="<?php echo $circuit['id']; ?>" <?php echo $circuit['id'] == $session['circuit_id'] ? 'selected' : ''; ?>>
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
                <select class="form-select" id="weather" name="weather">
                    <option value="">Sélectionner les conditions</option>
                    <option value="Ensoleillé" <?php echo $session['weather'] === 'Ensoleillé' ? 'selected' : ''; ?>>Ensoleillé</option>
                    <option value="Nuageux" <?php echo $session['weather'] === 'Nuageux' ? 'selected' : ''; ?>>Nuageux</option>
                    <option value="Pluvieux" <?php echo $session['weather'] === 'Pluvieux' ? 'selected' : ''; ?>>Pluvieux</option>
                    <option value="Mixte" <?php echo $session['weather'] === 'Mixte' ? 'selected' : ''; ?>>Mixte</option>
                </select>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="track_temp" class="form-label">Température piste (°C)</label>
                <input type="number" class="form-control" id="track_temp" name="track_temp" min="-10" max="60" step="0.1" value="<?php echo htmlspecialchars($session['track_temp']); ?>">
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="air_temp" class="form-label">Température air (°C)</label>
                <input type="number" class="form-control" id="air_temp" name="air_temp" min="-10" max="45" step="0.1" value="<?php echo htmlspecialchars($session['air_temp']); ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($session['notes']); ?></textarea>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Mettre à jour la session</button>
        </div>
    </form>
    <?php endif; ?>
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