<?php require_once APP_PATH . 'views/templates/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Ajouter une moto</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form id="motoForm" method="POST" action="index.php?route=moto/new" class="form-horizontal form-label-left">
                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Marque <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="text" name="marque" id="marque" required="required" class="form-control" placeholder="Ex: Honda, Yamaha, etc.">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Modèle <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="text" name="modele" id="modele" required="required" class="form-control" placeholder="Ex: CBR 1000RR-R">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Année <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="annee" id="annee" required="required" class="form-control" 
                                   min="2000" max="<?php echo date('Y') + 1; ?>" 
                                   value="<?php echo date('Y'); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Cylindrée (cc) <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="cylindree" id="cylindree" required="required" class="form-control" min="0" step="1">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Poids (kg) <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="poids" id="poids" required="required" class="form-control" min="0" step="0.1">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Puissance (ch) <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="puissance" id="puissance" required="required" class="form-control" min="0" step="0.1">
                        </div>
                    </div>

                    <?php if (isset($equipements) && !empty($equipements)): ?>
                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Équipements</label>
                        <div class="col-md-9 col-sm-9">
                            <?php foreach ($equipements as $equipement): ?>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="equipements[]" value="<?php echo $equipement['id']; ?>">
                                    <?php echo htmlspecialchars($equipement['nom']); ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="ln_solid"></div>

                    <div class="form-group row">
                        <div class="col-md-9 col-sm-9 offset-md-3">
                            <button type="button" id="fetchSpecs" class="btn btn-info">
                                <i class="fa fa-search"></i> Récupérer les spécifications
                            </button>
                            <div id="fetchStatus" class="mt-2"></div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-9 col-sm-9 offset-md-3">
                            <a href="index.php?route=motos" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-success">Enregistrer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('fetchSpecs').addEventListener('click', function() {
    const marque = document.getElementById('marque').value;
    const modele = document.getElementById('modele').value;
    const annee = document.getElementById('annee').value;
    const statusDiv = document.getElementById('fetchStatus');

    if (!marque || !modele || !annee) {
        statusDiv.innerHTML = '<div class="alert alert-warning">Veuillez remplir la marque, le modèle et l\'année.</div>';
        return;
    }

    statusDiv.innerHTML = '<div class="alert alert-info">Récupération des spécifications en cours...</div>';

    fetch('index.php?route=moto/specs', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            marque: marque,
            modele: modele,
            annee: annee
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('cylindree').value = data.specs.cylindree || '';
            document.getElementById('poids').value = data.specs.poids || '';
            document.getElementById('puissance').value = data.specs.puissance || '';
            statusDiv.innerHTML = '<div class="alert alert-success">Spécifications récupérées avec succès.</div>';
        } else {
            statusDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
        }
    })
    .catch(error => {
        statusDiv.innerHTML = '<div class="alert alert-danger">Erreur lors de la récupération des spécifications.</div>';
        console.error('Error:', error);
    });
});
</script>

<?php require_once APP_PATH . 'views/templates/footer.php'; ?> 