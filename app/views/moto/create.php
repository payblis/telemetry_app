<?php require_once APP_PATH . 'views/templates/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Ajouter une nouvelle moto</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form method="POST" action="index.php?route=moto/new" class="form-horizontal form-label-left">
                        
                        <!-- Informations Générales -->
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>1. Informations Générales</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="form-group">
                                    <label class="control-label col-md-3">Marque <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="text" name="marque" required class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Modèle <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="text" name="modele" required class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Année <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="number" name="annee" required class="form-control" min="1900" max="2100">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Cylindrée (cc) <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="number" name="cylindree" required class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Type <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <select name="type_moto" required class="form-control">
                                            <option value="MotoGP">MotoGP</option>
                                            <option value="Superbike">Superbike</option>
                                            <option value="Supersport">Supersport</option>
                                            <option value="Endurance">Endurance</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Puissance (ch) <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="number" name="puissance_moteur" required class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Couple (Nm)</label>
                                    <div class="col-md-6">
                                        <input type="number" step="0.1" name="couple_moteur" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Poids à sec (kg)</label>
                                    <div class="col-md-6">
                                        <input type="number" step="0.1" name="poids_sec" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Poids en ordre de marche (kg)</label>
                                    <div class="col-md-6">
                                        <input type="number" step="0.1" name="poids_ordre_marche" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Suspensions -->
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>2. Suspensions</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <h4>Fourche</h4>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Marque <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="text" name="fourche_marque" required class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Modèle</label>
                                    <div class="col-md-6">
                                        <input type="text" name="fourche_modele" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Précharge (tours)</label>
                                    <div class="col-md-6">
                                        <input type="number" step="0.5" name="fourche_precharge" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Compression (clics)</label>
                                    <div class="col-md-6">
                                        <input type="number" name="fourche_compression" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Détente (clics)</label>
                                    <div class="col-md-6">
                                        <input type="number" name="fourche_detente" class="form-control">
                                    </div>
                                </div>

                                <h4>Amortisseur</h4>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Marque <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="text" name="amortisseur_marque" required class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Modèle</label>
                                    <div class="col-md-6">
                                        <input type="text" name="amortisseur_modele" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Précharge (tours)</label>
                                    <div class="col-md-6">
                                        <input type="number" step="0.5" name="amortisseur_precharge" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Compression BV (clics)</label>
                                    <div class="col-md-6">
                                        <input type="number" name="amortisseur_compression_bv" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Compression HV (clics)</label>
                                    <div class="col-md-6">
                                        <input type="number" name="amortisseur_compression_hv" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Détente (clics)</label>
                                    <div class="col-md-6">
                                        <input type="number" name="amortisseur_detente" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Notes</label>
                                    <div class="col-md-6">
                                        <textarea name="suspensions_notes" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Freinage -->
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>3. Freinage</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="form-group">
                                    <label class="control-label col-md-3">Étrier avant - Marque <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="text" name="etrier_avant_marque" required class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Étrier avant - Modèle</label>
                                    <div class="col-md-6">
                                        <input type="text" name="etrier_avant_modele" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Étrier arrière - Marque</label>
                                    <div class="col-md-6">
                                        <input type="text" name="etrier_arriere_marque" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Étrier arrière - Modèle</label>
                                    <div class="col-md-6">
                                        <input type="text" name="etrier_arriere_modele" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Type plaquettes</label>
                                    <div class="col-md-6">
                                        <input type="text" name="plaquettes_type" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Type disques</label>
                                    <div class="col-md-6">
                                        <input type="text" name="disques_type" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Notes</label>
                                    <div class="col-md-6">
                                        <textarea name="freins_notes" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transmission -->
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>4. Transmission</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="form-group">
                                    <label class="control-label col-md-3">Couronne (dents) <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="number" name="couronne_dents" required class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Pignon (dents) <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="number" name="pignon_dents" required class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Type chaîne</label>
                                    <div class="col-md-6">
                                        <select name="chaine_type" class="form-control">
                                            <option value="">Sélectionner</option>
                                            <option value="520">520</option>
                                            <option value="525">525</option>
                                            <option value="530">530</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Marque chaîne</label>
                                    <div class="col-md-6">
                                        <input type="text" name="chaine_marque" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Modèle chaîne</label>
                                    <div class="col-md-6">
                                        <input type="text" name="chaine_modele" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Notes</label>
                                    <div class="col-md-6">
                                        <textarea name="transmission_notes" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Échappement -->
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>5. Échappement</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="form-group">
                                    <label class="control-label col-md-3">Marque <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="text" name="echappement_marque" required class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Modèle</label>
                                    <div class="col-md-6">
                                        <input type="text" name="echappement_modele" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Type</label>
                                    <div class="col-md-6">
                                        <select name="echappement_type" class="form-control">
                                            <option value="">Sélectionner</option>
                                            <option value="ligne_complete">Ligne complète</option>
                                            <option value="silencieux_seul">Silencieux seul</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Notes</label>
                                    <div class="col-md-6">
                                        <textarea name="echappement_notes" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Électronique -->
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>6. Électronique</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="form-group">
                                    <label class="control-label col-md-3">ECU Marque <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="text" name="ecu_marque" required class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">ECU Modèle</label>
                                    <div class="col-md-6">
                                        <input type="text" name="ecu_modele" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Capteurs installés</label>
                                    <div class="col-md-6">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="capteur_vitesse"> Vitesse
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="capteur_regime"> Régime moteur
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="capteur_temperature_pneus"> Température pneus
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="capteur_gps"> GPS
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="capteur_suspension"> Suspension
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="capteur_pression_pneus"> Pression pneus
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Autres capteurs</label>
                                    <div class="col-md-6">
                                        <textarea name="autres_capteurs" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Notes</label>
                                    <div class="col-md-6">
                                        <textarea name="electronique_notes" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pneumatiques -->
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>7. Pneumatiques</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="form-group">
                                    <label class="control-label col-md-3">Marque <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="text" name="pneu_marque" required class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Modèle</label>
                                    <div class="col-md-6">
                                        <input type="text" name="pneu_modele" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Type gomme</label>
                                    <div class="col-md-6">
                                        <select name="type_gomme" class="form-control">
                                            <option value="">Sélectionner</option>
                                            <option value="Soft">Soft</option>
                                            <option value="Medium">Medium</option>
                                            <option value="Hard">Hard</option>
                                            <option value="Rain">Rain</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Pression avant à froid (bar)</label>
                                    <div class="col-md-6">
                                        <input type="number" step="0.1" name="pression_avant_froid" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Pression arrière à froid (bar)</label>
                                    <div class="col-md-6">
                                        <input type="number" step="0.1" name="pression_arriere_froid" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Notes</label>
                                    <div class="col-md-6">
                                        <textarea name="pneumatiques_notes" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accessoires -->
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>8. Accessoires & Ergonomie</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="form-group">
                                    <label class="control-label col-md-3">Type guidon</label>
                                    <div class="col-md-6">
                                        <select name="type_guidon" class="form-control">
                                            <option value="">Sélectionner</option>
                                            <option value="standard">Standard</option>
                                            <option value="racing">Racing</option>
                                            <option value="demi-guidon">Demi-guidon</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Commandes reculées - Marque</label>
                                    <div class="col-md-6">
                                        <input type="text" name="commandes_reculees_marque" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Commandes reculées - Réglages</label>
                                    <div class="col-md-6">
                                        <textarea name="commandes_reculees_reglages" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Type selle</label>
                                    <div class="col-md-6">
                                        <select name="type_selle" class="form-control">
                                            <option value="">Sélectionner</option>
                                            <option value="standard">Standard</option>
                                            <option value="racing">Racing</option>
                                            <option value="mousse">Mousse</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Type carénage</label>
                                    <div class="col-md-6">
                                        <select name="type_carenage" class="form-control">
                                            <option value="">Sélectionner</option>
                                            <option value="origine">Origine</option>
                                            <option value="racing">Racing</option>
                                            <option value="carbone">Carbone</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Notes</label>
                                    <div class="col-md-6">
                                        <textarea name="accessoires_notes" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-3">
                                <a href="index.php?route=motos" class="btn btn-primary">Annuler</a>
                                <button type="submit" class="btn btn-success">Enregistrer</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . 'views/templates/footer.php'; ?> 