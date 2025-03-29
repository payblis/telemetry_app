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
        // Charger la classe Pilot
        require_once 'classes/Pilot.php';
        $pilot = new Pilot();
        
        // Valider les données
        $name = trim($_POST['name'] ?? '');
        $height = intval($_POST['height'] ?? 0);
        $weight = intval($_POST['weight'] ?? 0);
        $experience = trim($_POST['experience'] ?? '');
        
        if (empty($name) || $height <= 0 || $weight <= 0 || empty($experience)) {
            throw new Exception("Tous les champs sont obligatoires et doivent être valides.");
        }
        
        // Créer le pilote
        $pilotId = $pilot->create(
            $name,
            $height,
            $weight,
            $experience,
            $_SESSION['user_id']
        );
        
        if ($pilotId) {
            $success = "Pilote ajouté avec succès !";
        } else {
            throw new Exception("Erreur lors de l'ajout du pilote.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Erreur lors de l'ajout du pilote : " . $e->getMessage());
    }
}
?>

<div class="container">
    <div class="page-header">
        <h1>Ajouter un Pilote</h1>
        <a href="index.php?page=pilots" class="btn btn-secondary">Retour à la liste</a>
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
                <label for="name" class="form-label">Nom du pilote</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="invalid-feedback">
                    Le nom est requis.
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="experience" class="form-label">Expérience</label>
                <select class="form-select" id="experience" name="experience" required>
                    <option value="">Sélectionner l'expérience</option>
                    <option value="Débutant">Débutant</option>
                    <option value="Intermédiaire">Intermédiaire</option>
                    <option value="Avancé">Avancé</option>
                    <option value="Expert">Expert</option>
                    <option value="Professionnel">Professionnel</option>
                </select>
                <div class="invalid-feedback">
                    L'expérience est requise.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="height" class="form-label">Taille (cm)</label>
                <input type="number" class="form-control" id="height" name="height" min="100" max="250" required>
                <div class="invalid-feedback">
                    La taille doit être comprise entre 100 et 250 cm.
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="weight" class="form-label">Poids (kg)</label>
                <input type="number" class="form-control" id="weight" name="weight" min="30" max="200" required>
                <div class="invalid-feedback">
                    Le poids doit être compris entre 30 et 200 kg.
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Ajouter le pilote</button>
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