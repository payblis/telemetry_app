<?php
/**
 * Page de création d'un pilote
 */

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}

// Récupérer l'ID de l'utilisateur
$userId = getCurrentUserId();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $dateNaissance = $_POST['date_naissance'] ?? null;
    $nationalite = $_POST['nationalite'] ?? null;
    $taille = $_POST['taille'] ?? null;
    $poids = $_POST['poids'] ?? null;
    $experience = $_POST['experience'] ?? null;
    $categorie = $_POST['categorie'] ?? null;
    $niveau = $_POST['niveau'] ?? 'intermediaire';
    $notes = $_POST['notes'] ?? null;
    
    // Validation des champs
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = 'Le nom est requis';
    }
    
    if (empty($prenom)) {
        $errors[] = 'Le prénom est requis';
    }
    
    if (!empty($dateNaissance) && !validateDate($dateNaissance)) {
        $errors[] = 'La date de naissance n\'est pas valide';
    }
    
    if (!empty($taille) && !validateNumber($taille, 100, 250)) {
        $errors[] = 'La taille doit être comprise entre 100 et 250 cm';
    }
    
    if (!empty($poids) && !validateNumber($poids, 30, 150)) {
        $errors[] = 'Le poids doit être compris entre 30 et 150 kg';
    }
    
    // Si pas d'erreurs, créer le pilote
    if (empty($errors)) {
        $piloteId = createPilote(
            $userId,
            $nom,
            $prenom,
            $dateNaissance,
            $nationalite,
            $taille,
            $poids,
            $experience,
            $categorie,
            $niveau,
            $notes
        );
        
        if ($piloteId) {
            // Création réussie
            setFlashMessage('success', 'Le pilote a été créé avec succès.');
            redirect('index.php?page=pilotes');
        } else {
            // Échec de la création
            $errors[] = 'Une erreur est survenue lors de la création du pilote';
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="display-5">
            <i class="fas fa-user-plus"></i> Ajouter un pilote
        </h1>
        <p class="lead">Créez un nouveau profil de pilote pour vos sessions de télémétrie.</p>
    </div>
</div>

<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Informations du pilote</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="index.php?page=pilotes_create">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= $nom ?? '' ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?= $prenom ?? '' ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="date_naissance" class="form-label">Date de naissance</label>
                    <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?= $dateNaissance ?? '' ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nationalite" class="form-label">Nationalité</label>
                    <input type="text" class="form-control" id="nationalite" name="nationalite" value="<?= $nationalite ?? '' ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="taille" class="form-label">Taille (cm)</label>
                    <input type="number" class="form-control" id="taille" name="taille" value="<?= $taille ?? '' ?>" min="100" max="250">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="poids" class="form-label">Poids (kg)</label>
                    <input type="number" class="form-control" id="poids" name="poids" value="<?= $poids ?? '' ?>" min="30" max="150">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="experience" class="form-label">Expérience (années)</label>
                    <input type="number" class="form-control" id="experience" name="experience" value="<?= $experience ?? '' ?>" min="0" max="50">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="categorie" class="form-label">Catégorie</label>
                    <select class="form-select" id="categorie" name="categorie">
                        <option value="">Sélectionnez une catégorie</option>
                        <option value="loisir" <?= ($categorie ?? '') === 'loisir' ? 'selected' : '' ?>>Loisir</option>
                        <option value="amateur" <?= ($categorie ?? '') === 'amateur' ? 'selected' : '' ?>>Amateur</option>
                        <option value="competition" <?= ($categorie ?? '') === 'competition' ? 'selected' : '' ?>>Compétition</option>
                        <option value="professionnel" <?= ($categorie ?? '') === 'professionnel' ? 'selected' : '' ?>>Professionnel</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="niveau" class="form-label">Niveau</label>
                <select class="form-select" id="niveau" name="niveau">
                    <option value="debutant" <?= ($niveau ?? '') === 'debutant' ? 'selected' : '' ?>>Débutant</option>
                    <option value="intermediaire" <?= ($niveau ?? 'intermediaire') === 'intermediaire' ? 'selected' : '' ?>>Intermédiaire</option>
                    <option value="avance" <?= ($niveau ?? '') === 'avance' ? 'selected' : '' ?>>Avancé</option>
                    <option value="expert" <?= ($niveau ?? '') === 'expert' ? 'selected' : '' ?>>Expert</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?= $notes ?? '' ?></textarea>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="index.php?page=pilotes" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
