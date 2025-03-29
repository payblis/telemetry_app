<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Vérifier si l'ID du circuit est fourni
if (!isset($_GET['id'])) {
    header('Location: index.php?page=circuits');
    exit();
}

$circuitId = intval($_GET['id']);

// Initialiser les variables
$error = null;
$success = null;
$circuit = null;

try {
    // Charger la classe Circuit
    require_once 'classes/Circuit.php';
    $circuitObj = new Circuit();
    
    // Vérifier si le circuit appartient à l'utilisateur
    if (!$circuitObj->belongsToUser($circuitId, $_SESSION['user_id'])) {
        throw new Exception("Vous n'avez pas accès à ce circuit.");
    }
    
    // Récupérer les données du circuit
    $circuit = $circuitObj->getById($circuitId);
    if (!$circuit) {
        throw new Exception("Circuit non trouvé.");
    }
    
    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Valider les données
        $name = trim($_POST['name'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $length = floatval($_POST['length'] ?? 0);
        $width = floatval($_POST['width'] ?? 0);
        $cornersCount = intval($_POST['corners_count'] ?? 0);
        
        if (empty($name) || empty($country) || $length <= 0 || $width <= 0 || $cornersCount <= 0) {
            throw new Exception("Tous les champs sont obligatoires et doivent être valides.");
        }
        
        // Mettre à jour le circuit
        $data = [
            'name' => $name,
            'country' => $country,
            'length' => $length,
            'width' => $width,
            'corners_count' => $cornersCount
        ];
        
        if ($circuitObj->update($circuitId, $data)) {
            $success = "Circuit mis à jour avec succès !";
            // Recharger les données du circuit
            $circuit = $circuitObj->getById($circuitId);
        } else {
            throw new Exception("Erreur lors de la mise à jour du circuit.");
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur lors de la modification du circuit : " . $e->getMessage());
}
?>

<div class="container">
    <div class="page-header">
        <h1>Modifier un Circuit</h1>
        <a href="index.php?page=circuits" class="btn btn-secondary">Retour à la liste</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($circuit): ?>
    <form method="POST" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Nom du circuit</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($circuit['name']); ?>" required>
                <div class="invalid-feedback">
                    Le nom est requis.
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="country" class="form-label">Pays</label>
                <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($circuit['country']); ?>" required>
                <div class="invalid-feedback">
                    Le pays est requis.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="length" class="form-label">Longueur (mètres)</label>
                <input type="number" class="form-control" id="length" name="length" min="100" max="10000" step="0.1" value="<?php echo htmlspecialchars($circuit['length']); ?>" required>
                <div class="invalid-feedback">
                    La longueur doit être comprise entre 100 et 10000 mètres.
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="width" class="form-label">Largeur (mètres)</label>
                <input type="number" class="form-control" id="width" name="width" min="5" max="50" step="0.1" value="<?php echo htmlspecialchars($circuit['width']); ?>" required>
                <div class="invalid-feedback">
                    La largeur doit être comprise entre 5 et 50 mètres.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="corners_count" class="form-label">Nombre de virages</label>
                <input type="number" class="form-control" id="corners_count" name="corners_count" min="1" max="50" value="<?php echo htmlspecialchars($circuit['corners_count']); ?>" required>
                <div class="invalid-feedback">
                    Le nombre de virages doit être compris entre 1 et 50.
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Mettre à jour le circuit</button>
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