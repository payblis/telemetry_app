<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$page_title = 'Nouvelle session';

// Récupération des données pour les select
try {
    $pilotes = $pdo->query("SELECT id, nom, prenom FROM pilotes WHERE user_id = " . $_SESSION['user_id'])->fetchAll();
    $motos = $pdo->query("SELECT id, marque, modele FROM motos WHERE pilote_id IN (SELECT id FROM pilotes WHERE user_id = " . $_SESSION['user_id'] . ")")->fetchAll();
    $circuits = $pdo->query("SELECT id, nom, pays FROM circuits ORDER BY nom")->fetchAll();
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des données: " . $e->getMessage());
    $_SESSION['flash_message'] = "Une erreur est survenue lors du chargement des données.";
    $_SESSION['flash_type'] = "danger";
    header('Location: list.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $pilote_id = filter_input(INPUT_POST, 'pilote_id', FILTER_VALIDATE_INT);
    $moto_id = filter_input(INPUT_POST, 'moto_id', FILTER_VALIDATE_INT);
    $circuit_id = filter_input(INPUT_POST, 'circuit_id', FILTER_VALIDATE_INT);
    $date_session = filter_input(INPUT_POST, 'date_session', FILTER_SANITIZE_STRING);
    $type_session = filter_input(INPUT_POST, 'type_session', FILTER_SANITIZE_STRING);
    $meteo = filter_input(INPUT_POST, 'meteo', FILTER_SANITIZE_STRING);
    $temperature_air = filter_input(INPUT_POST, 'temperature_air', FILTER_VALIDATE_FLOAT);
    $temperature_piste = filter_input(INPUT_POST, 'temperature_piste', FILTER_VALIDATE_FLOAT);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
    
    // Vérification des données
    if (!$pilote_id || !$moto_id || !$circuit_id || !$date_session || !$type_session) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO sessions (pilote_id, moto_id, circuit_id, date_session, type_session, meteo, temperature_air, temperature_piste, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$pilote_id, $moto_id, $circuit_id, $date_session, $type_session, $meteo, $temperature_air, $temperature_piste, $notes])) {
                $_SESSION['flash_message'] = "La session a été créée avec succès.";
                $_SESSION['flash_type'] = "success";
                header('Location: list.php');
                exit;
            } else {
                $error = "Une erreur est survenue lors de la création de la session.";
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la création de la session: " . $e->getMessage());
            $error = "Une erreur est survenue lors de la création de la session.";
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title mb-0">Nouvelle session</h2>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pilote_id" class="form-label">Pilote *</label>
                                <select class="form-select" id="pilote_id" name="pilote_id" required>
                                    <option value="">Sélectionner un pilote</option>
                                    <?php foreach ($pilotes as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" <?php echo isset($_POST['pilote_id']) && $_POST['pilote_id'] == $p['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['prenom'] . ' ' . $p['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="moto_id" class="form-label">Moto *</label>
                                <select class="form-select" id="moto_id" name="moto_id" required>
                                    <option value="">Sélectionner une moto</option>
                                    <?php foreach ($motos as $m): ?>
                                        <option value="<?php echo $m['id']; ?>" <?php echo isset($_POST['moto_id']) && $_POST['moto_id'] == $m['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($m['marque'] . ' ' . $m['modele']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="circuit_id" class="form-label">Circuit *</label>
                                <select class="form-select" id="circuit_id" name="circuit_id" required>
                                    <option value="">Sélectionner un circuit</option>
                                    <?php foreach ($circuits as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo isset($_POST['circuit_id']) && $_POST['circuit_id'] == $c['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['nom'] . ' (' . $c['pays'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date_session" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="date_session" name="date_session" required
                                       value="<?php echo isset($_POST['date_session']) ? htmlspecialchars($_POST['date_session']) : date('Y-m-d'); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type_session" class="form-label">Type de session *</label>
                                <select class="form-select" id="type_session" name="type_session" required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="free practice" <?php echo isset($_POST['type_session']) && $_POST['type_session'] == 'free practice' ? 'selected' : ''; ?>>Free Practice</option>
                                    <option value="qualification" <?php echo isset($_POST['type_session']) && $_POST['type_session'] == 'qualification' ? 'selected' : ''; ?>>Qualification</option>
                                    <option value="course" <?php echo isset($_POST['type_session']) && $_POST['type_session'] == 'course' ? 'selected' : ''; ?>>Course</option>
                                    <option value="trackday" <?php echo isset($_POST['type_session']) && $_POST['type_session'] == 'trackday' ? 'selected' : ''; ?>>Trackday</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="meteo" class="form-label">Météo</label>
                                <input type="text" class="form-control" id="meteo" name="meteo"
                                       value="<?php echo isset($_POST['meteo']) ? htmlspecialchars($_POST['meteo']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="temperature_air" class="form-label">Température air (°C)</label>
                                <input type="number" step="0.1" class="form-control" id="temperature_air" name="temperature_air"
                                       value="<?php echo isset($_POST['temperature_air']) ? htmlspecialchars($_POST['temperature_air']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="temperature_piste" class="form-label">Température piste (°C)</label>
                                <input type="number" step="0.1" class="form-control" id="temperature_piste" name="temperature_piste"
                                       value="<?php echo isset($_POST['temperature_piste']) ? htmlspecialchars($_POST['temperature_piste']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Créer la session</button>
                            <a href="list.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
