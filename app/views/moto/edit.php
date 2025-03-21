<?php require_once 'app/views/templates/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Modifier la moto</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="POST" action="index.php?route=moto/edit&id=<?php echo htmlspecialchars($moto['id']); ?>" class="form-horizontal form-label-left">
                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Marque <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="text" name="marque" required="required" class="form-control" 
                                   value="<?php echo htmlspecialchars($moto['marque']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Modèle <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="text" name="modele" required="required" class="form-control" 
                                   value="<?php echo htmlspecialchars($moto['modele']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Année <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="annee" required="required" class="form-control" 
                                   min="2000" max="<?php echo date('Y') + 1; ?>"
                                   value="<?php echo htmlspecialchars($moto['annee']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Cylindrée (cc) <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="cylindree" required="required" class="form-control" 
                                   min="125" max="2000" step="1"
                                   value="<?php echo htmlspecialchars($moto['cylindree']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Poids (kg) <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="poids" required="required" class="form-control" 
                                   min="100" max="400" step="0.1"
                                   value="<?php echo htmlspecialchars($moto['poids']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Puissance (ch) <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="puissance" required="required" class="form-control" 
                                   min="15" max="300" step="1"
                                   value="<?php echo htmlspecialchars($moto['puissance']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Équipements</label>
                        <div class="col-md-9 col-sm-9">
                            <div class="checkbox-group">
                                <?php foreach ($equipements as $equipement): ?>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="equipements[]" 
                                               value="<?php echo $equipement['id']; ?>"
                                               <?php echo in_array($equipement['id'], $moto['equipement_ids']) ? 'checked' : ''; ?>>
                                        <?php echo htmlspecialchars($equipement['nom']); ?>
                                        <?php if (!empty($equipement['description'])): ?>
                                            <small class="text-muted">(<?php echo htmlspecialchars($equipement['description']); ?>)</small>
                                        <?php endif; ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="ln_solid"></div>

                    <div class="form-group row">
                        <div class="col-md-9 col-sm-9 offset-md-3">
                            <a href="index.php?route=motos" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.checkbox-group {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 4px;
}
.checkbox {
    margin-bottom: 10px;
}
.checkbox:last-child {
    margin-bottom: 0;
}
</style>

<?php require_once 'app/views/templates/footer.php'; ?> 