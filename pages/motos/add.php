<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Initialiser les variables
$error = null;
$success = null;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Charger la classe Moto
        require_once 'classes/Moto.php';
        $moto = new Moto();
        
        // Valider les données
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $engineCapacity = intval($_POST['engine_capacity'] ?? 0);
        $year = intval($_POST['year'] ?? 0);
        
        if (empty($brand) || empty($model) || $engineCapacity <= 0 || $year <= 0) {
            throw new Exception("Tous les champs sont obligatoires et doivent être valides.");
        }
        
        // Créer la moto
        $motoId = $moto->create(
            $brand,
            $model,
            $engineCapacity,
            $year,
            $_SESSION['user_id']
        );
        
        if ($motoId) {
            $success = "Moto ajoutée avec succès !";
        } else {
            throw new Exception("Erreur lors de l'ajout de la moto.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Erreur lors de l'ajout de la moto : " . $e->getMessage());
    }
}
?>

<div class="container">
    <div class="page-header">
        <h1>Ajouter une Moto</h1>
        <a href="index.php?page=motos" class="btn btn-secondary">Retour à la liste</a>
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
                <label for="brand" class="form-label">Marque</label>
                <input type="text" class="form-control" id="brand" name="brand" required>
                <div class="invalid-feedback">
                    La marque est requise.
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="model" class="form-label">Modèle</label>
                <input type="text" class="form-control" id="model" name="model" required>
                <div class="invalid-feedback">
                    Le modèle est requis.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="engine_capacity" class="form-label">Cylindrée (cc)</label>
                <input type="number" class="form-control" id="engine_capacity" name="engine_capacity" min="50" max="2000" required>
                <div class="invalid-feedback">
                    La cylindrée doit être comprise entre 50 et 2000 cc.
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="year" class="form-label">Année</label>
                <input type="number" class="form-control" id="year" name="year" min="1900" max="<?php echo date('Y'); ?>" required>
                <div class="invalid-feedback">
                    L'année doit être comprise entre 1900 et l'année en cours.
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Ajouter la moto</button>
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