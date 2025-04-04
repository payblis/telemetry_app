<?php
/**
 * Vue pour l'affichage des détails d'une session
 */
?>

<div class="telemetrie-container">
    <div class="page-header">
        <h1>Session du <?= date('d/m/Y', strtotime($session['date_session'])) ?></h1>
        <div class="page-actions">
            <a href="<?= \App\Utils\View::url('/telemetrie') ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <?php if (!$session['has_telemetry']): ?>
                <a href="<?= \App\Utils\View::url('/telemetrie/import/' . $session['id']) ?>" class="btn btn-primary">
                    <i class="fas fa-file-import"></i> Importer des données
                </a>
            <?php else: ?>
                <a href="<?= \App\Utils\View::url('/telemetrie/export/' . $session['id']) ?>" class="btn btn-outline">
                    <i class="fas fa-file-export"></i> Exporter
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php \App\Utils\View::showNotifications(); ?>

    <div class="session-details">
        <div class="card">
            <div class="card-header">
                <h3>Informations générales</h3>
            </div>
            <div class="card-body">
                <div class="session-info-grid">
                    <div class="info-group">
                        <h4>Circuit</h4>
                        <div class="info-item">
                            <span class="info-label">Nom</span>
                            <span class="info-value"><?= \App\Utils\View::escape($session['circuit_nom']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Localisation</span>
                            <span class="info-value"><?= \App\Utils\View::escape($session['circuit_ville'] . ', ' . $session['circuit_pays']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Longueur</span>
                            <span class="info-value"><?= \App\Utils\View::escape($session['circuit_longueur']) ?> m</span>
                        </div>
                    </div>

                    <div class="info-group">
                        <h4>Pilote et Moto</h4>
                        <div class="info-item">
                            <span class="info-label">Pilote</span>
                            <span class="info-value"><?= \App\Utils\View::escape($session['pilote_prenom'] . ' ' . $session['pilote_nom']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Moto</span>
                            <span class="info-value"><?= \App\Utils\View::escape($session['moto_marque'] . ' ' . $session['moto_modele']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Cylindrée</span>
                            <span class="info-value"><?= \App\Utils\View::escape($session['moto_cylindree']) ?> cc</span>
                        </div>
                    </div>

                    <div class="info-group">
                        <h4>Conditions</h4>
                        <div class="info-item">
                            <span class="info-label">Météo</span>
                            <span class="info-value"><?= \App\Utils\View::escape($session['conditions_meteo']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Température air</span>
                            <span class="info-value"><?= \App\Utils\View::escape($session['temperature_air']) ?> °C</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Température piste</span>
                            <span class="info-value"><?= \App\Utils\View::escape($session['temperature_piste']) ?> °C</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Humidité</span>
                            <span class="info-value"><?= \App\Utils\View::escape($session['humidite']) ?> %</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Vent</span>
                            <span class="info-value"><?= \App\Utils\View::escape($session['vent']) ?> km/h</span>
                        </div>
                    </div>

                    <div class="info-group">
                        <h4>Horaires</h4>
                        <div class="info-item">
                            <span class="info-label">Date</span>
                            <span class="info-value"><?= date('d/m/Y', strtotime($session['date_session'])) ?></span>
                        </div>
                        <?php if ($session['heure_debut']): ?>
                            <div class="info-item">
                                <span class="info-label">Heure de début</span>
                                <span class="info-value"><?= date('H:i', strtotime($session['heure_debut'])) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($session['heure_fin']): ?>
                            <div class="info-item">
                                <span class="info-label">Heure de fin</span>
                                <span class="info-value"><?= date('H:i', strtotime($session['heure_fin'])) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($session['notes']): ?>
                    <div class="session-notes">
                        <h4>Notes</h4>
                        <div class="notes-content">
                            <?= nl2br(\App\Utils\View::escape($session['notes'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($session['has_telemetry'] && isset($telemetrie) && !empty($telemetrie['tours'])): ?>
            <div class="card">
                <div class="card-header">
                    <h3>Résumé de la session</h3>
                </div>
                <div class="card-body">
                    <div class="session-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?= $telemetrie['stats']['total_tours'] ?></div>
                            <div class="stat-label">Tours totaux</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $telemetrie['stats']['tours_valides'] ?></div>
                            <div class="stat-label">Tours valides</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= gmdate('i:s.v', $telemetrie['stats']['meilleur_tour']['temps']) ?></div>
                            <div class="stat-label">Meilleur tour</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= round($telemetrie['stats']['vitesse_max']) ?> km/h</div>
                            <div class="stat-label">Vitesse max</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= round($telemetrie['stats']['vitesse_moyenne']) ?> km/h</div>
                            <div class="stat-label">Vitesse moyenne</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= round($telemetrie['stats']['distance_totale'] / 1000, 2) ?> km</div>
                            <div class="stat-label">Distance totale</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Tours</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tour</th>
                                    <th>Temps</th>
                                    <th>Vitesse max</th>
                                    <th>Vitesse moy.</th>
                                    <th>Inclinaison max</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($telemetrie['tours'] as $tourData): ?>
                                    <?php $tour = $tourData['tour']; ?>
                                    <?php $agregation = $tourData['agregation']; ?>
                                    <tr class="<?= $tour['meilleur_tour'] ? 'best-lap' : '' ?> <?= $tour['valide'] ? '' : 'invalid-lap' ?>">
                                        <td>
                                            <?= $tour['numero_tour'] ?>
                                            <?php if ($tour['meilleur_tour']): ?>
                                                <span class="badge badge-success">Meilleur</span>
                                            <?php endif; ?>
                                            <?php if (!$tour['valide']): ?>
                                                <span class="badge badge-warning">Invalide</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= gmdate('i:s.v', $tour['temps']) ?></td>
                                        <td><?= round($agregation['vitesse_max']) ?> km/h</td>
                                        <td><?= round($agregation['vitesse_moyenne']) ?> km/h</td>
                                        <td><?= round($agregation['inclinaison_max']) ?> °</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?= \App\Utils\View::url('/telemetrie/view-tour/' . $tour['id']) ?>" class="btn btn-sm btn-outline">
                                                    <i class="fas fa-eye"></i> Voir
                                                </a>
                                                <a href="<?= \App\Utils\View::url('/telemetrie/graph/' . $tour['id'] . '/vitesse') ?>" class="btn btn-sm btn-outline">
                                                    <i class="fas fa-chart-line"></i> Graphique
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($session['has_telemetry'] && (empty($telemetrie) || empty($telemetrie['tours']))): ?>
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h2>Aucun tour détecté</h2>
                        <p>Les données télémétriques ont été importées, mais aucun tour n'a pu être détecté. Cela peut être dû à des données GPS insuffisantes ou à un problème lors de l'enregistrement.</p>
                        <a href="<?= \App\Utils\View::url('/telemetrie/import/' . $session['id']) ?>" class="btn btn-primary">
                            Réimporter les données
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-file-import"></i>
                        </div>
                        <h2>Aucune donnée télémetrique</h2>
                        <p>Cette session ne contient pas encore de données télémétriques. Importez des données pour commencer l'analyse.</p>
                        <a href="<?= \App\Utils\View::url('/telemetrie/import/' . $session['id']) ?>" class="btn btn-primary">
                            Importer des données
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($session['has_telemetry'] && isset($telemetrie) && !empty($telemetrie['tours'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Code JavaScript pour les fonctionnalités interactives
    // (sera implémenté dans le fichier JS principal)
});
</script>
<?php endif; ?>
