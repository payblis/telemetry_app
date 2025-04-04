<?php
/**
 * Vue pour la liste des sessions
 */
?>

<div class="telemetrie-container">
    <div class="page-header">
        <h1>Mes Sessions</h1>
        <div class="page-actions">
            <a href="<?= \App\Utils\View::url('/telemetrie/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Session
            </a>
        </div>
    </div>

    <?php \App\Utils\View::showNotifications(); ?>

    <?php if (empty($sessions)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-stopwatch"></i>
            </div>
            <h2>Aucune session</h2>
            <p>Vous n'avez pas encore créé de session. Commencez par créer une nouvelle session pour importer vos données télémétriques.</p>
            <a href="<?= \App\Utils\View::url('/telemetrie/create') ?>" class="btn btn-primary">
                Créer une Session
            </a>
        </div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($sessions as $session): ?>
                <div class="card">
                    <div class="card-header">
                        <h3><?= date('d/m/Y', strtotime($session['date_session'])) ?></h3>
                        <span class="badge <?= $session['has_telemetry'] ? 'badge-success' : 'badge-warning' ?>">
                            <?= $session['has_telemetry'] ? 'Données importées' : 'En attente de données' ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="session-info">
                            <div class="info-item">
                                <span class="info-label">Circuit</span>
                                <span class="info-value"><?= \App\Utils\View::escape($session['circuit_nom']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Pilote</span>
                                <span class="info-value"><?= \App\Utils\View::escape($session['pilote_nom'] . ' ' . $session['pilote_prenom']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Moto</span>
                                <span class="info-value"><?= \App\Utils\View::escape($session['moto_marque'] . ' ' . $session['moto_modele']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Météo</span>
                                <span class="info-value"><?= \App\Utils\View::escape($session['conditions_meteo']) ?></span>
                            </div>
                            <?php if ($session['has_telemetry']): ?>
                                <div class="info-item">
                                    <span class="info-label">Tours</span>
                                    <span class="info-value"><?= $session['tours_count'] ?> tours</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Meilleur tour</span>
                                    <span class="info-value"><?= $session['best_lap_time'] ? gmdate('i:s.v', $session['best_lap_time']) : 'N/A' ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="<?= \App\Utils\View::url('/telemetrie/view/' . $session['id']) ?>" class="btn btn-outline">
                            <i class="fas fa-eye"></i> Voir
                        </a>
                        <?php if (!$session['has_telemetry']): ?>
                            <a href="<?= \App\Utils\View::url('/telemetrie/import/' . $session['id']) ?>" class="btn btn-primary">
                                <i class="fas fa-file-import"></i> Importer
                            </a>
                        <?php else: ?>
                            <a href="<?= \App\Utils\View::url('/telemetrie/export/' . $session['id']) ?>" class="btn btn-outline">
                                <i class="fas fa-file-export"></i> Exporter
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
