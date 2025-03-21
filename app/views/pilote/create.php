<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Ajouter un pilote</h1>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="index.php?route=pilote/new">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="poids" class="form-label">Poids (kg)</label>
                                <input type="number" step="0.1" class="form-control" id="poids" name="poids">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="taille" class="form-label">Taille (cm)</label>
                                <input type="number" class="form-control" id="taille" name="taille">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="age" class="form-label">Âge</label>
                                <input type="number" class="form-control" id="age" name="age">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="experience" class="form-label">Expérience</label>
                            <textarea class="form-control" id="experience" name="experience" rows="4"></textarea>
                            <small class="text-muted">Décrivez l'expérience du pilote (championnats, podiums, etc.)</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php?route=dashboard" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Aide</h5>
                </div>
                <div class="card-body">
                    <p>Les informations du pilote sont importantes pour :</p>
                    <ul>
                        <li>Adapter les réglages de la moto</li>
                        <li>Optimiser les performances</li>
                        <li>Suivre l'évolution du pilote</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div> 