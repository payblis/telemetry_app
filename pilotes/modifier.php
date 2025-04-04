<?php
$page_title = 'Modifier un Pilote';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

try {
    // Vérification que le pilote appartient à l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT * FROM pilotes WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $pilote = $stmt->fetch();

    if (!$pilote) {
        header('Location: index.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("
            UPDATE pilotes SET 
                nom = ?, prenom = ?, pseudo = ?, taille = ?, poids = ?, 
                experience_annees = ?, niveau = ?, style_pilotage = ?, 
                licence = ?, licence_numero = ?, licence_date_expiration = ?, 
                commentaires = ?
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([
            $_POST['nom'],
            $_POST['prenom'],
            $_POST['pseudo'],
            $_POST['taille'],
            $_POST['poids'],
            $_POST['experience_annees'],
            $_POST['niveau'],
            $_POST['style_pilotage'],
            isset($_POST['licence']) ? 1 : 0,
            $_POST['licence_numero'] ?? null,
            $_POST['licence_date_expiration'] ?? null,
            $_POST['commentaires'] ?? null,
            $pilote['id'],
            $_SESSION['user_id']
        ]);

        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Erreur lors de la modification du pilote : " . $e->getMessage();
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Modifier un Pilote</h5>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Retour
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($pilote['nom']); ?>" required>
                    <div class="invalid-feedback">
                        Veuillez entrer le nom du pilote.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($pilote['prenom']); ?>" required>
                    <div class="invalid-feedback">
                        Veuillez entrer le prénom du pilote.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pseudo" class="form-label">Pseudo</label>
                    <input type="text" class="form-control" id="pseudo" name="pseudo" value="<?php echo htmlspecialchars($pilote['pseudo']); ?>" required>
                    <div class="invalid-feedback">
                        Veuillez entrer le pseudo du pilote.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="niveau" class="form-label">Niveau</label>
                    <select class="form-select" id="niveau" name="niveau" required>
                        <option value="">Sélectionner un niveau</option>
                        <option value="Débutant" <?php echo $pilote['niveau'] === 'Débutant' ? 'selected' : ''; ?>>Débutant</option>
                        <option value="Intermédiaire" <?php echo $pilote['niveau'] === 'Intermédiaire' ? 'selected' : ''; ?>>Intermédiaire</option>
                        <option value="Avancé" <?php echo $pilote['niveau'] === 'Avancé' ? 'selected' : ''; ?>>Avancé</option>
                        <option value="Expert" <?php echo $pilote['niveau'] === 'Expert' ? 'selected' : ''; ?>>Expert</option>
                        <option value="Professionnel" <?php echo $pilote['niveau'] === 'Professionnel' ? 'selected' : ''; ?>>Professionnel</option>
                    </select>
                    <div class="invalid-feedback">
                        Veuillez sélectionner le niveau du pilote.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="taille" class="form-label">Taille (cm)</label>
                    <input type="number" class="form-control" id="taille" name="taille" value="<?php echo htmlspecialchars($pilote['taille']); ?>" min="100" max="250" required>
                    <div class="invalid-feedback">
                        Veuillez entrer une taille valide (entre 100 et 250 cm).
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="poids" class="form-label">Poids (kg)</label>
                    <input type="number" class="form-control" id="poids" name="poids" value="<?php echo htmlspecialchars($pilote['poids']); ?>" min="30" max="200" required>
                    <div class="invalid-feedback">
                        Veuillez entrer un poids valide (entre 30 et 200 kg).
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="experience_annees" class="form-label">Années d'expérience</label>
                    <input type="number" class="form-control" id="experience_annees" name="experience_annees" value="<?php echo htmlspecialchars($pilote['experience_annees']); ?>" min="0" max="50" required>
                    <div class="invalid-feedback">
                        Veuillez entrer un nombre d'années valide.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="style_pilotage" class="form-label">Style de pilotage</label>
                    <select class="form-select" id="style_pilotage" name="style_pilotage" required>
                        <option value="">Sélectionner un style</option>
                        <option value="Smooth" <?php echo $pilote['style_pilotage'] === 'Smooth' ? 'selected' : ''; ?>>Smooth</option>
                        <option value="Aggressif" <?php echo $pilote['style_pilotage'] === 'Aggressif' ? 'selected' : ''; ?>>Aggressif</option>
                        <option value="Mixte" <?php echo $pilote['style_pilotage'] === 'Mixte' ? 'selected' : ''; ?>>Mixte</option>
                    </select>
                    <div class="invalid-feedback">
                        Veuillez sélectionner le style de pilotage.
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="licence" name="licence" <?php echo $pilote['licence'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="licence">
                        Possède une licence
                    </label>
                </div>
            </div>

            <div class="row licence-fields" style="display: <?php echo $pilote['licence'] ? 'block' : 'none'; ?>;">
                <div class="col-md-6 mb-3">
                    <label for="licence_numero" class="form-label">Numéro de licence</label>
                    <input type="text" class="form-control" id="licence_numero" name="licence_numero" value="<?php echo htmlspecialchars($pilote['licence_numero'] ?? ''); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="licence_date_expiration" class="form-label">Date d'expiration</label>
                    <input type="date" class="form-control" id="licence_date_expiration" name="licence_date_expiration" value="<?php echo $pilote['licence_date_expiration'] ? date('Y-m-d', strtotime($pilote['licence_date_expiration'])) : ''; ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="commentaires" class="form-label">Commentaires</label>
                <textarea class="form-control" id="commentaires" name="commentaires" rows="3"><?php echo htmlspecialchars($pilote['commentaires'] ?? ''); ?></textarea>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Validation des formulaires Bootstrap
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
    })()

    // Gestion de l'affichage des champs de licence
    document.getElementById('licence').addEventListener('change', function() {
        const licenceFields = document.querySelector('.licence-fields');
        licenceFields.style.display = this.checked ? 'block' : 'none';
    });
</script>

<?php require_once '../includes/footer.php'; ?> 