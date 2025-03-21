<?php require_once 'app/views/templates/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Ajouter un équipement</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="POST" action="index.php?route=equipement/new" class="form-horizontal form-label-left">
                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Nom <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="text" name="nom" required="required" class="form-control" 
                                   placeholder="Ex: Suspension Öhlins TTX GP">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Type <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <select name="type" required="required" class="form-control">
                                <option value="">Choisir...</option>
                                <option value="Suspension">Suspension</option>
                                <option value="Freinage">Freinage</option>
                                <option value="Moteur">Moteur</option>
                                <option value="Transmission">Transmission</option>
                                <option value="Échappement">Échappement</option>
                                <option value="Électronique">Électronique</option>
                                <option value="Pneumatique">Pneumatique</option>
                                <option value="Carrosserie">Carrosserie</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Fabricant <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <input type="text" name="fabricant" required="required" class="form-control" 
                                   placeholder="Ex: Öhlins">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Description <span class="required">*</span></label>
                        <div class="col-md-9 col-sm-9">
                            <textarea name="description" required="required" class="form-control" rows="4"
                                    placeholder="Description détaillée de l'équipement, ses caractéristiques et ses avantages"></textarea>
                        </div>
                    </div>

                    <div class="ln_solid"></div>

                    <div class="form-group row">
                        <div class="col-md-9 col-sm-9 offset-md-3">
                            <a href="index.php?route=equipements" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-success">Enregistrer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/templates/footer.php'; ?> 