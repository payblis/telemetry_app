<?php require_once 'app/views/templates/header.php'; ?>

<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Historique des sessions de télémétrie</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Filtres</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <form method="GET" class="form-horizontal form-label-left">
                            <input type="hidden" name="route" value="telemetrie/historique">
                            
                            <div class="form-group">
                                <label class="control-label col-md-2">Pilote</label>
                                <div class="col-md-4">
                                    <select name="pilote_id" class="form-control">
                                        <option value="">Tous les pilotes</option>
                                        <?php foreach ($pilotes as $pilote): ?>
                                            <option value="<?php echo $pilote['id']; ?>" 
                                                <?php echo isset($_GET['pilote_id']) && $_GET['pilote_id'] == $pilote['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <label class="control-label col-md-2">Moto</label>
                                <div class="col-md-4">
                                    <select name="moto_id" class="form-control">
                                        <option value="">Toutes les motos</option>
                                        <?php foreach ($motos as $moto): ?>
                                            <option value="<?php echo $moto['id']; ?>"
                                                <?php echo isset($_GET['moto_id']) && $_GET['moto_id'] == $moto['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($moto['marque'] . ' ' . $moto['modele']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-2">Circuit</label>
                                <div class="col-md-4">
                                    <select name="circuit_id" class="form-control">
                                        <option value="">Tous les circuits</option>
                                        <?php foreach ($circuits as $circuit): ?>
                                            <option value="<?php echo $circuit['id']; ?>"
                                                <?php echo isset($_GET['circuit_id']) && $_GET['circuit_id'] == $circuit['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($circuit['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <label class="control-label col-md-2">Période</label>
                                <div class="col-md-4">
                                    <div class="input-daterange input-group">
                                        <input type="date" class="form-control" name="date_debut" 
                                               value="<?php echo isset($_GET['date_debut']) ? $_GET['date_debut'] : ''; ?>">
                                        <span class="input-group-addon">à</span>
                                        <input type="date" class="form-control" name="date_fin"
                                               value="<?php echo isset($_GET['date_fin']) ? $_GET['date_fin'] : ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="ln_solid"></div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-3">
                                    <a href="index.php?route=telemetrie/historique" class="btn btn-default">Réinitialiser</a>
                                    <button type="submit" class="btn btn-primary">Filtrer</button>
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
                        <h2>Sessions</h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li>
                                <a href="index.php?route=telemetrie/import" class="btn btn-primary">
                                    <i class="fa fa-upload"></i> Nouvelle session
                                </a>
                            </li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <?php if (empty($sessions)): ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Aucune session ne correspond aux critères sélectionnés.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped jambo_table bulk_action">
                                    <thead>
                                        <tr class="headings">
                                            <th class="column-title">Date</th>
                                            <th class="column-title">Pilote</th>
                                            <th class="column-title">Moto</th>
                                            <th class="column-title">Circuit</th>
                                            <th class="column-title">Conditions</th>
                                            <th class="column-title">Données</th>
                                            <th class="column-title no-link last"><span class="nobr">Actions</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sessions as $session): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($session['date_session']))); ?></td>
                                                <td><?php echo htmlspecialchars($session['pilote_prenom'] . ' ' . $session['pilote_nom']); ?></td>
                                                <td><?php echo htmlspecialchars($session['moto_marque'] . ' ' . $session['moto_modele']); ?></td>
                                                <td><?php echo htmlspecialchars($session['circuit_nom']); ?></td>
                                                <td>
                                                    <span class="label label-info">
                                                        <?php echo htmlspecialchars($session['conditions']); ?>
                                                    </span>
                                                    <br>
                                                    <small>
                                                        <?php echo htmlspecialchars($session['temperature']); ?>°C, 
                                                        <?php echo htmlspecialchars($session['humidite']); ?>% humidité
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-green">
                                                        <?php echo number_format($session['total_donnees'], 0, ',', ' '); ?> points
                                                    </span>
                                                </td>
                                                <td class="last">
                                                    <a href="index.php?route=telemetrie/analyse&session_id=<?php echo $session['id']; ?>" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fa fa-line-chart"></i> Analyser
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/templates/footer.php'; ?> 