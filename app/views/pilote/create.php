<?php require_once 'app/views/templates/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Ajouter un pilote</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="POST" action="index.php?route=pilote/new" class="form-horizontal form-label-left">
                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Nom <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="text" name="nom" required="required" class="form-control" placeholder="Nom du pilote">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Prénom <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="text" name="prenom" required="required" class="form-control" placeholder="Prénom du pilote">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Âge <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="age" required="required" class="form-control" min="16" max="99">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Poids (kg) <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="poids" required="required" class="form-control" step="0.1" min="40" max="150">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Taille (cm) <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="number" name="taille" required="required" class="form-control" min="150" max="220">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Expérience <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <select name="experience" required="required" class="form-control">
                                <option value="">Choisir...</option>
                                <option value="Débutant">Débutant</option>
                                <option value="Intermédiaire">Intermédiaire</option>
                                <option value="Avancé">Avancé</option>
                                <option value="Expert">Expert</option>
                                <option value="Professionnel">Professionnel</option>
                            </select>
                        </div>
                    </div>

                    <div class="ln_solid"></div>

                    <div class="form-group row">
                        <div class="col-md-9 col-sm-9 offset-md-3">
                            <a href="index.php?route=pilotes" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-success">Enregistrer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/templates/footer.php'; ?> 