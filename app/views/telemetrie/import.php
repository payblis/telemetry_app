<?php require_once 'app/views/templates/header.php'; ?>

<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Import des données télémétriques</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Nouvelle session</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <form class="form-horizontal form-label-left" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Pilote <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select name="pilote_id" class="form-control" required>
                                        <option value="">Sélectionnez un pilote</option>
                                        <?php foreach ($pilotes as $pilote): ?>
                                            <option value="<?php echo $pilote['id']; ?>">
                                                <?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Moto <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select name="moto_id" class="form-control" required>
                                        <option value="">Sélectionnez une moto</option>
                                        <?php foreach ($motos as $moto): ?>
                                            <option value="<?php echo $moto['id']; ?>">
                                                <?php echo htmlspecialchars($moto['marque'] . ' ' . $moto['modele']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Circuit <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select name="circuit_id" class="form-control" required>
                                        <option value="">Sélectionnez un circuit</option>
                                        <?php foreach ($circuits as $circuit): ?>
                                            <option value="<?php echo $circuit['id']; ?>">
                                                <?php echo htmlspecialchars($circuit['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Date et heure <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="datetime-local" name="date_session" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Conditions <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select name="conditions" class="form-control" required>
                                        <option value="">Sélectionnez les conditions</option>
                                        <option value="Sec">Sec</option>
                                        <option value="Humide">Humide</option>
                                        <option value="Pluie légère">Pluie légère</option>
                                        <option value="Pluie forte">Pluie forte</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Température (°C) <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="number" name="temperature" class="form-control" required min="-20" max="50" step="0.1">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Humidité (%) <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="number" name="humidite" class="form-control" required min="0" max="100" step="0.1">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Fichier de données <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="file" name="telemetrie_file" class="form-control" required accept=".csv">
                                    <small class="text-muted">Format attendu : fichier CSV avec en-têtes</small>
                                </div>
                            </div>

                            <div class="ln_solid"></div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-3">
                                    <a href="index.php?route=telemetrie" class="btn btn-default">Annuler</a>
                                    <button type="submit" class="btn btn-success">Importer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Format du fichier CSV</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <p>Le fichier CSV doit contenir les colonnes suivantes dans cet ordre :</p>
                        <ol>
                            <li><strong>timestamp</strong> : Horodatage (format UNIX timestamp)</li>
                            <li><strong>vitesse</strong> : Vitesse en km/h</li>
                            <li><strong>regime_moteur</strong> : Régime moteur en tr/min</li>
                            <li><strong>acceleration</strong> : Accélération en g</li>
                            <li><strong>angle_inclinaison</strong> : Angle d'inclinaison en degrés</li>
                            <li><strong>temperature_pneu_avant</strong> : Température du pneu avant en °C</li>
                            <li><strong>temperature_pneu_arriere</strong> : Température du pneu arrière en °C</li>
                            <li><strong>pression_pneu_avant</strong> : Pression du pneu avant en bar</li>
                            <li><strong>pression_pneu_arriere</strong> : Pression du pneu arrière en bar</li>
                            <li><strong>force_freinage</strong> : Force de freinage en N</li>
                            <li><strong>position_gps_lat</strong> : Latitude GPS</li>
                            <li><strong>position_gps_long</strong> : Longitude GPS</li>
                        </ol>
                        <p>Exemple de ligne :</p>
                        <pre>1623456789,125.4,9500,1.2,45.3,65.2,68.5,2.1,2.2,850.3,48.8566,2.3522</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/templates/footer.php'; ?> 