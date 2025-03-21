<?php require_once 'app/views/templates/header.php'; ?>

<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Télémétrie</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Dernières sessions</h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li>
                                <a href="index.php?route=telemetrie/import" class="btn btn-primary">
                                    <i class="fa fa-upload"></i> Importer des données
                                </a>
                            </li>
                            <li>
                                <a href="index.php?route=telemetrie/historique" class="btn btn-info">
                                    <i class="fa fa-history"></i> Historique complet
                                </a>
                            </li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <?php if (empty($sessions)): ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Aucune session de télémétrie n'a été enregistrée.
                                <a href="index.php?route=telemetrie/import" class="alert-link">Importer des données</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped jambo_table">
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