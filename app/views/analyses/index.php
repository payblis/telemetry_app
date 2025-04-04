<?php
/**
 * Vue pour la page d'index des analyses IA
 */
?>

<div class="telemetrie-container">
    <div class="page-header">
        <h1>Analyses et Recommandations IA</h1>
        <div class="page-actions">
            <a href="<?= \App\Utils\View::url('/analyses/compare') ?>" class="btn btn-secondary">
                <i class="fas fa-balance-scale"></i> Comparer des sessions
            </a>
        </div>
    </div>

    <?php \App\Utils\View::showNotifications(); ?>

    <!-- Recommandations récentes -->
    <div class="card">
        <div class="card-header">
            <h3>Recommandations récentes</h3>
        </div>
        <div class="card-body">
            <?php if (empty($recommendations)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h2>Aucune recommandation</h2>
                    <p>Vous n'avez pas encore généré de recommandations. Sélectionnez une session et cliquez sur "Générer des recommandations" pour obtenir des conseils personnalisés basés sur vos données télémétriques.</p>
                </div>
            <?php else: ?>
                <div class="recommendation-list">
                    <?php foreach ($recommendations as $recommendation): ?>
                        <div class="recommendation-card mb-4">
                            <div class="recommendation-header">
                                <div class="recommendation-header-icon">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <h3><?= \App\Utils\View::escape($recommendation['titre']) ?></h3>
                            </div>
                            <div class="recommendation-body">
                                <div class="recommendation-item">
                                    <div class="recommendation-description">
                                        <?= nl2br(\App\Utils\View::escape($recommendation['texte'])) ?>
                                    </div>
                                    <?php if (!empty($recommendation['action_recommandee'])): ?>
                                        <div class="recommendation-action">
                                            Action recommandée: <?= \App\Utils\View::escape($recommendation['action_recommandee']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="recommendation-footer">
                                <div class="recommendation-source">
                                    <span>Circuit: <?= \App\Utils\View::escape($recommendation['circuit_nom']) ?></span>
                                    <span class="ml-3">Source: <?= $recommendation['source'] == 'openai' ? 'ChatGPT' : 'IA Communautaire' ?></span>
                                </div>
                                <div class="recommendation-confidence">
                                    <span class="confidence-label">Confiance:</span>
                                    <span class="confidence-value"><?= $recommendation['confiance'] ?>%</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sessions disponibles -->
    <div class="card">
        <div class="card-header">
            <h3>Sessions disponibles pour analyse</h3>
        </div>
        <div class="card-body">
            <?php if (empty($sessions)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-stopwatch"></i>
                    </div>
                    <h2>Aucune session</h2>
                    <p>Vous n'avez pas encore créé de session. Commencez par créer une nouvelle session et importer vos données télémétriques.</p>
                    <a href="<?= \App\Utils\View::url('/telemetrie/create') ?>" class="btn btn-primary">
                        Créer une Session
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Circuit</th>
                                <th>Pilote</th>
                                <th>Moto</th>
                                <th>Tours</th>
                                <th>Meilleur temps</th>
                                <th>Recommandations</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $session): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($session['date_session'])) ?></td>
                                    <td><?= \App\Utils\View::escape($session['circuit_nom']) ?></td>
                                    <td><?= \App\Utils\View::escape($session['pilote_nom'] . ' ' . $session['pilote_prenom']) ?></td>
                                    <td><?= \App\Utils\View::escape($session['moto_marque'] . ' ' . $session['moto_modele']) ?></td>
                                    <td><?= $session['nombre_tours'] ?></td>
                                    <td>
                                        <?php if ($session['meilleur_temps']): ?>
                                            <span class="lap-time"><?= gmdate('i:s.v', $session['meilleur_temps']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($session['recommandations_count'] > 0): ?>
                                            <span class="badge badge-success"><?= $session['recommandations_count'] ?> recommandations</span>
                                        <?php else: ?>
                                            <span class="badge badge-light">Aucune</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if ($session['recommandations_count'] > 0): ?>
                                                <a href="<?= \App\Utils\View::url('/analyses/view/' . $session['id']) ?>" class="btn btn-sm btn-primary" title="Voir les recommandations">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= \App\Utils\View::url('/analyses/generate/' . $session['id']) ?>" class="btn btn-sm btn-secondary" title="Générer des recommandations">
                                                <i class="fas fa-brain"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Comment ça fonctionne -->
    <div class="card">
        <div class="card-header">
            <h3>Comment fonctionnent les recommandations IA</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Analyse des données</h4>
                        <p>Notre système analyse vos données télémétriques pour identifier les tendances, les forces et les faiblesses dans votre pilotage.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h4>Intelligence artificielle</h4>
                        <p>Nous utilisons ChatGPT pour analyser vos données et générer des recommandations personnalisées basées sur les meilleures pratiques de pilotage.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Intelligence communautaire</h4>
                        <p>Notre système apprend également des expériences d'autres pilotes sur des circuits similaires pour vous offrir des recommandations éprouvées.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.feature-card {
    text-align: center;
    padding: var(--spacing-lg);
    background-color: var(--lighter-gray);
    border-radius: var(--border-radius-md);
    transition: transform var(--transition-fast) ease-in-out, box-shadow var(--transition-fast) ease-in-out;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.feature-icon {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: var(--spacing-md);
}

.feature-card h4 {
    margin-bottom: var(--spacing-sm);
}
</style>
