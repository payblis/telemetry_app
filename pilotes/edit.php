<?php
// edit.php
$page_title = 'Edit Pilot';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

try {
    // Get pilot information
    $stmt = $pdo->prepare("SELECT * FROM pilotes WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $pilote = $stmt->fetch();

    if (!$pilote) {
        header('Location: list.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate required fields
        $required_fields = ['nom', 'prenom', 'pseudo', 'niveau', 'style_pilotage', 'experience_annees', 'taille', 'poids'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields");
            }
        }

        // Prepare data
        $data = [
            'id' => $pilote['id'],
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'pseudo' => $_POST['pseudo'],
            'niveau' => $_POST['niveau'],
            'style_pilotage' => $_POST['style_pilotage'],
            'experience_annees' => $_POST['experience_annees'],
            'taille' => $_POST['taille'],
            'poids' => $_POST['poids'],
            'licence' => isset($_POST['licence']) ? 1 : 0,
            'licence_numero' => $_POST['licence_numero'] ?? null,
            'licence_date_expiration' => !empty($_POST['licence_date_expiration']) ? $_POST['licence_date_expiration'] : null,
            'commentaires' => $_POST['commentaires'] ?? null
        ];

        // Update database
        $stmt = $pdo->prepare("
            UPDATE pilotes SET
                nom = :nom,
                prenom = :prenom,
                pseudo = :pseudo,
                niveau = :niveau,
                style_pilotage = :style_pilotage,
                experience_annees = :experience_annees,
                taille = :taille,
                poids = :poids,
                licence = :licence,
                licence_numero = :licence_numero,
                licence_date_expiration = :licence_date_expiration,
                commentaires = :commentaires
            WHERE id = :id AND user_id = :user_id
        ");

        $data['user_id'] = $_SESSION['user_id'];
        $stmt->execute($data);
        header('Location: list.php?success=3');
        exit;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="content-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Edit Pilot</h5>
            <a href="list.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nom" class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($pilote['nom']); ?>" required>
                        <div class="invalid-feedback">Please enter the last name</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="prenom" class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($pilote['prenom']); ?>" required>
                        <div class="invalid-feedback">Please enter the first name</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pseudo" class="form-label">Nickname *</label>
                        <input type="text" class="form-control" id="pseudo" name="pseudo" value="<?php echo htmlspecialchars($pilote['pseudo']); ?>" required>
                        <div class="invalid-feedback">Please enter a nickname</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="niveau" class="form-label">Level *</label>
                        <select class="form-select" id="niveau" name="niveau" required>
                            <option value="">Select a level</option>
                            <option value="Débutant" <?php echo $pilote['niveau'] === 'Débutant' ? 'selected' : ''; ?>>Beginner</option>
                            <option value="Intermédiaire" <?php echo $pilote['niveau'] === 'Intermédiaire' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="Avancé" <?php echo $pilote['niveau'] === 'Avancé' ? 'selected' : ''; ?>>Advanced</option>
                            <option value="Expert" <?php echo $pilote['niveau'] === 'Expert' ? 'selected' : ''; ?>>Expert</option>
                            <option value="Professionnel" <?php echo $pilote['niveau'] === 'Professionnel' ? 'selected' : ''; ?>>Professional</option>
                        </select>
                        <div class="invalid-feedback">Please select a level</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="style_pilotage" class="form-label">Riding Style *</label>
                        <select class="form-select" id="style_pilotage" name="style_pilotage" required>
                            <option value="">Select a style</option>
                            <option value="Smooth" <?php echo $pilote['style_pilotage'] === 'Smooth' ? 'selected' : ''; ?>>Smooth</option>
                            <option value="Aggressive" <?php echo $pilote['style_pilotage'] === 'Aggressive' ? 'selected' : ''; ?>>Aggressive</option>
                            <option value="Balanced" <?php echo $pilote['style_pilotage'] === 'Balanced' ? 'selected' : ''; ?>>Balanced</option>
                        </select>
                        <div class="invalid-feedback">Please select a riding style</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="experience_annees" class="form-label">Years of Experience *</label>
                        <input type="number" class="form-control" id="experience_annees" name="experience_annees" min="0" value="<?php echo htmlspecialchars($pilote['experience_annees']); ?>" required>
                        <div class="invalid-feedback">Please enter years of experience</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="taille" class="form-label">Height (cm) *</label>
                        <input type="number" class="form-control" id="taille" name="taille" min="100" max="250" value="<?php echo htmlspecialchars($pilote['taille']); ?>" required>
                        <div class="invalid-feedback">Please enter a valid height</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="poids" class="form-label">Weight (kg) *</label>
                        <input type="number" class="form-control" id="poids" name="poids" min="30" max="200" value="<?php echo htmlspecialchars($pilote['poids']); ?>" required>
                        <div class="invalid-feedback">Please enter a valid weight</div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="licence" name="licence" <?php echo $pilote['licence'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="licence">Has Racing License</label>
                    </div>
                </div>

                <div id="licenseFields" style="display: <?php echo $pilote['licence'] ? 'block' : 'none'; ?>;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="licence_numero" class="form-label">License Number</label>
                            <input type="text" class="form-control" id="licence_numero" name="licence_numero" value="<?php echo htmlspecialchars($pilote['licence_numero'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="licence_date_expiration" class="form-label">Expiration Date</label>
                            <input type="date" class="form-control" id="licence_date_expiration" name="licence_date_expiration" value="<?php echo $pilote['licence_date_expiration'] ? date('Y-m-d', strtotime($pilote['licence_date_expiration'])) : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="commentaires" class="form-label">Comments</label>
                    <textarea class="form-control" id="commentaires" name="commentaires" rows="3"><?php echo htmlspecialchars($pilote['commentaires'] ?? ''); ?></textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Show/hide license fields
    document.getElementById('licence').addEventListener('change', function() {
        document.getElementById('licenseFields').style.display = this.checked ? 'block' : 'none';
    });

    // Form validation
    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>

<?php require_once '../includes/footer.php'; ?>