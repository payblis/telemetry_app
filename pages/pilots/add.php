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
        $lastname = trim($_POST['lastname'] ?? '');
        $firstname = trim($_POST['firstname'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $level = trim($_POST['level'] ?? '');
        
        if (empty($lastname) || empty($firstname) || empty($category) || empty($level)) {
            throw new Exception("Tous les champs sont obligatoires.");
        }
        
        // Créer le pilote
        $pilotId = $pilot->create([
            'user_id' => $_SESSION['user_id'],
            'lastname' => $lastname,
            'firstname' => $firstname,
            'category' => $category,
            'level' => $level,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
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
                <label for="lastname" class="form-label">Nom</label>
                <input type="text" class="form-control" id="lastname" name="lastname" required>
                <div class="invalid-feedback">
                    Le nom est requis.
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="firstname" class="form-label">Prénom</label>
                <input type="text" class="form-control" id="firstname" name="firstname" required>
                <div class="invalid-feedback">
                    Le prénom est requis.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="category" class="form-label">Catégorie</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">Sélectionner une catégorie</option>
                    <option value="MotoGP">MotoGP</option>
                    <option value="Moto2">Moto2</option>
                    <option value="Moto3">Moto3</option>
                    <option value="Superbike">Superbike</option>
                    <option value="Supersport">Supersport</option>
                    <option value="Endurance">Endurance</option>
                </select>
                <div class="invalid-feedback">
                    La catégorie est requise.
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="level" class="form-label">Niveau</label>
                <select class="form-select" id="level" name="level" required>
                    <option value="">Sélectionner un niveau</option>
                    <option value="Débutant">Débutant</option>
                    <option value="Intermédiaire">Intermédiaire</option>
                    <option value="Avancé">Avancé</option>
                    <option value="Expert">Expert</option>
                    <option value="Professionnel">Professionnel</option>
                </select>
                <div class="invalid-feedback">
                    Le niveau est requis.
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