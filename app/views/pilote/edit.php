<?php require_once 'app/views/templates/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Modifier le pilote</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="POST" action="index.php?route=pilote/edit&id=<?php echo htmlspecialchars($pilote['id']); ?>" class="form-horizontal form-label-left">
                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Nom <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="text" name="nom" required="required" class="form-control" 
                                   value="<?php echo htmlspecialchars($pilote['nom']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Prénom <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="text" name="prenom" required="required" class="form-control" 
                                   value="<?php echo htmlspecialchars($pilote['prenom']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Âge <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="age" required="required" class="form-control" min="16" max="99"
                                   value="<?php echo htmlspecialchars($pilote['age']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Poids (kg) <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="poids" required="required" class="form-control" step="0.1" min="40" max="150"
                                   value="<?php echo htmlspecialchars($pilote['poids']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Taille (cm) <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="taille" required="required" class="form-control" min="150" max="220"
                                   value="<?php echo htmlspecialchars($pilote['taille']); ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Expérience <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <select name="experience" required="required" class="form-control">
                                <option value="">Choisir...</option>
                                <?php
                                $experiences = ['Débutant', 'Intermédiaire', 'Avancé', 'Expert', 'Professionnel'];
                                foreach ($experiences as $exp) {
                                    $selected = ($pilote['experience'] === $exp) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($exp) . '" ' . $selected . '>' . htmlspecialchars($exp) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="ln_solid"></div>

                    <div class="form-group row">
                        <div class="col-md-9 col-sm-9 offset-md-3">
                            <a href="index.php?route=pilotes" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/templates/footer.php'; ?> 