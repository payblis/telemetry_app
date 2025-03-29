<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Vérifier si l'ID de la moto est fourni
if (!isset($_GET['id'])) {
    header('Location: index.php?page=motos');
    exit();
}

$motoId = intval($_GET['id']);

// Initialiser les variables
$error = null;
$success = null;
$moto = null;

try {
    // Charger la classe Moto
    require_once 'classes/Moto.php';
    $motoObj = new Moto();
    
    // Vérifier si la moto appartient à l'utilisateur
    if (!$motoObj->belongsToUser($motoId, $_SESSION['user_id'])) {
        throw new Exception("Vous n'avez pas accès à cette moto.");
    }
    
    // Récupérer les données de la moto
    $moto = $motoObj->getById($motoId);
    if (!$moto) {
        throw new Exception("Moto non trouvée.");
    }
    
    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Valider les données
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $engineCapacity = intval($_POST['engine_capacity'] ?? 0);
        $year = intval($_POST['year'] ?? 0);
        
        if (empty($brand) || empty($model) || $engineCapacity <= 0 || $year <= 0) {
            throw new Exception("Tous les champs sont obligatoires et doivent être valides.");
        }
        
        // Mettre à jour la moto
        $data = [
            'brand' => $brand,
            'model' => $model,
            'engine_capacity' => $engineCapacity,
            'year' => $year
        ];
        
        if ($motoObj->update($motoId, $data)) {
            $success = "Moto mise à jour avec succès !";
            // Recharger les données de la moto
            $moto = $motoObj->getById($motoId);
        } else {
            throw new Exception("Erreur lors de la mise à jour de la moto.");
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur lors de la modification de la moto : " . $e->getMessage());
}
?>

<div class="container">
    <div class="page-header">
        <h1>Modifier une Moto</h1>
        <a href="index.php?page=motos" class="btn btn-secondary">Retour à la liste</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($moto): ?>
    <form method="POST" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="brand" class="form-label">Marque</label>
                <input type="text" class="form-control" id="brand" name="brand" value="<?php echo htmlspecialchars($moto['brand']); ?>" required>
                <div class="invalid-feedback">
                    La marque est requise.
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="model" class="form-label">Modèle</label>
                <input type="text" class="form-control" id="model" name="model" value="<?php echo htmlspecialchars($moto['model']); ?>" required>
                <div class="invalid-feedback">
                    Le modèle est requis.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="engine_capacity" class="form-label">Cylindrée (cc)</label>
                <input type="number" class="form-control" id="engine_capacity" name="engine_capacity" min="50" max="2000" value="<?php echo htmlspecialchars($moto['engine_capacity']); ?>" required>
                <div class="invalid-feedback">
                    La cylindrée doit être comprise entre 50 et 2000 cc.
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="year" class="form-label">Année</label>
                <input type="number" class="form-control" id="year" name="year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($moto['year']); ?>" required>
                <div class="invalid-feedback">
                    L'année doit être comprise entre 1900 et l'année en cours.
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Mettre à jour la moto</button>
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