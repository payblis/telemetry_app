<?php require_once 'app/views/templates/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Modifier l'équipement</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="POST" action="index.php?route=equipement/edit&id=<?php echo htmlspecialchars($equipement['id']); ?>" class="form-horizontal form-label-left">
                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Nom <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="text" name="nom" required="required" class="form-control" 
                                   value="<?php echo htmlspecialchars($equipement['nom']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Type <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <select name="type" required="required" class="form-control">
                                <option value="">Choisir...</option>
                                <?php
                                $types = [
                                    'Suspension',
                                    'Freinage',
                                    'Moteur',
                                    'Transmission',
                                    'Échappement',
                                    'Électronique',
                                    'Pneumatique',
                                    'Carrosserie',
                                    'Autre'
                                ];
                                foreach ($types as $type) {
                                    $selected = ($equipement['type'] === $type) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($type) . '" ' . $selected . '>' . htmlspecialchars($type) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Fabricant <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="text" name="fabricant" required="required" class="form-control" 
                                   value="<?php echo htmlspecialchars($equipement['fabricant']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Description <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <textarea name="description" required="required" class="form-control" rows="4"><?php echo htmlspecialchars($equipement['description']); ?></textarea>
                        </div>
                    </div>

                    <div class="ln_solid"></div>

                    <div class="form-group row">
                        <div class="col-md-9 col-sm-9 offset-md-3">
                            <a href="index.php?route=equipements" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/templates/footer.php'; ?> 