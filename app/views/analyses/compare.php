<?php
/**
 * Vue pour la comparaison des performances entre deux sessions
 */
?>

<div class="telemetrie-container">
    <div class="page-header">
        <h1>Comparaison de performances</h1>
        <div class="page-actions">
            <a href="<?= \App\Utils\View::url('/analyses') ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour aux analyses
            </a>
        </div>
    </div>

    <?php \App\Utils\View::showNotifications(); ?>

    <!-- Sélection des sessions à comparer -->
    <div class="card">
        <div class="card-header">
            <h3>Sélectionner les sessions à comparer</h3>
        </div>
        <div class="card-body">
            <form action="<?= \App\Utils\View::url('/analyses/compare') ?>" method="get" class="form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="session1">Session 1</label>
                        <select id="session1" name="session1" class="form-control" required>
                            <option value="">Sélectionner une session</option>
                            <?php foreach ($sessions as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= isset($_GET['session1']) && $_GET['session1'] == $s['id'] ? 'selected' : '' ?>>
                                    <?= date('d/m/Y', strtotime($s['date_session'])) ?> - 
                                    <?= \App\Utils\View::escape($s['circuit_nom']) ?> - 
                                    <?= $s['meilleur_temps'] ? gmdate('i:s.v', $s['meilleur_temps']) : 'N/A' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="session2">Session 2</label>
                        <select id="session2" name="session2" class="form-control" required>
                            <option value="">Sélectionner une session</option>
                            <?php foreach ($sessions as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= isset($_GET['session2']) && $_GET['session2'] == $s['id'] ? 'selected' : '' ?>>
                                    <?= date('d/m/Y', strtotime($s['date_session'])) ?> - 
                                    <?= \App\Utils\View::escape($s['circuit_nom']) ?> - 
                                    <?= $s['meilleur_temps'] ? gmdate('i:s.v', $s['meilleur_temps']) : 'N/A' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Comparer</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($session1 && $session2 && $comparison): ?>
        <!-- Résultats de la comparaison -->
        <div class="card">
            <div class="card-header">
                <h3>Résultats de la comparaison</h3>
            </div>
            <div class="card-body">
                <div class="comparison-header">
                    <div class="session-card session1">
                        <h4>Session 1</h4>
                        <div class="session-info">
                            <div class="info-item">
                                <span class="info-label">Date</span>
                                <span class="info-value"><?= date('d/m/Y', strtotime($session1['date_session'])) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Circuit</span>
                                <span class="info-value"><?= \App\Utils\View::escape($session1['circuit_nom']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Pilote</span>
                                <span class="info-value"><?= \App\Utils\View::escape($session1['pilote_nom'] . ' ' . $session1['pilote_prenom']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Moto</span>
                                <span class="info-value"><?= \App\Utils\View::escape($session1['moto_marque'] . ' ' . $session1['moto_modele']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Meilleur temps</span>
                                <span class="info-value"><?= $session1['meilleur_temps'] ? gmdate('i:s.v', $session1['meilleur_temps']) : 'N/A' ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="comparison-vs">
                        <div class="vs-circle">VS</div>
                    </div>
                    
                    <div class="session-card session2">
                        <h4>Session 2</h4>
                        <div class="session-info">
                            <div class="info-item">
                                <span class="info-label">Date</span>
                                <span class="info-value"><?= date('d/m/Y', strtotime($session2['date_session'])) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Circuit</span>
                                <span class="info-value"><?= \App\Utils\View::escape($session2['circuit_nom']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Pilote</span>
                                <span class="info-value"><?= \App\Utils\View::escape($session2['pilote_nom'] . ' ' . $session2['pilote_prenom']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Moto</span>
                                <span class="info-value"><?= \App\Utils\View::escape($session2['moto_marque'] . ' ' . $session2['moto_modele']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Meilleur temps</span>
                                <span class="info-value"><?= $session2['meilleur_temps'] ? gmdate('i:s.v', $session2['meilleur_temps']) : 'N/A' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="comparison-metrics">
                    <div class="metric-card <?= $comparison['differences']['meilleur_temps'] < 0 ? 'better' : ($comparison['differences']['meilleur_temps'] > 0 ? 'worse' : '') ?>">
                        <div class="metric-title">Meilleur temps</div>
                        <div class="metric-value">
                            <?php if ($comparison['differences']['meilleur_temps'] < 0): ?>
                                <i class="fas fa-arrow-down"></i> <?= gmdate('i:s.v', abs($comparison['differences']['meilleur_temps'])) ?>
                            <?php elseif ($comparison['differences']['meilleur_temps'] > 0): ?>
                                <i class="fas fa-arrow-up"></i> <?= gmdate('i:s.v', abs($comparison['differences']['meilleur_temps'])) ?>
                            <?php else: ?>
                                <i class="fas fa-equals"></i> Identique
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="metric-card <?= $comparison['differences']['vitesse_max'] > 0 ? 'better' : ($comparison['differences']['vitesse_max'] < 0 ? 'worse' : '') ?>">
                        <div class="metric-title">Vitesse max</div>
                        <div class="metric-value">
                            <?php if ($comparison['differences']['vitesse_max'] > 0): ?>
                                <i class="fas fa-arrow-up"></i> <?= abs($comparison['differences']['vitesse_max']) ?> km/h
                            <?php elseif ($comparison['differences']['vitesse_max'] < 0): ?>
                                <i class="fas fa-arrow-down"></i> <?= abs($comparison['differences']['vitesse_max']) ?> km/h
                            <?php else: ?>
                                <i class="fas fa-equals"></i> Identique
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="metric-card <?= $comparison['differences']['vitesse_moyenne'] > 0 ? 'better' : ($comparison['differences']['vitesse_moyenne'] < 0 ? 'worse' : '') ?>">
                        <div class="metric-title">Vitesse moyenne</div>
                        <div class="metric-value">
                            <?php if ($comparison['differences']['vitesse_moyenne'] > 0): ?>
                                <i class="fas fa-arrow-up"></i> <?= round(abs($comparison['differences']['vitesse_moyenne']), 1) ?> km/h
                            <?php elseif ($comparison['differences']['vitesse_moyenne'] < 0): ?>
                                <i class="fas fa-arrow-down"></i> <?= round(abs($comparison['differences']['vitesse_moyenne']), 1) ?> km/h
                            <?php else: ?>
                                <i class="fas fa-equals"></i> Identique
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="metric-card <?= $comparison['differences']['temps_moyen'] < 0 ? 'better' : ($comparison['differences']['temps_moyen'] > 0 ? 'worse' : '') ?>">
                        <div class="metric-title">Temps moyen</div>
                        <div class="metric-value">
                            <?php if ($comparison['differences']['temps_moyen'] < 0): ?>
                                <i class="fas fa-arrow-down"></i> <?= gmdate('i:s.v', abs($comparison['differences']['temps_moyen'])) ?>
                            <?php elseif ($comparison['differences']['temps_moyen'] > 0): ?>
                                <i class="fas fa-arrow-up"></i> <?= gmdate('i:s.v', abs($comparison['differences']['temps_moyen'])) ?>
                            <?php else: ?>
                                <i class="fas fa-equals"></i> Identique
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Graphiques de comparaison -->
        <div class="card">
            <div class="card-header">
                <h3>Graphiques comparatifs</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h4>Comparaison des temps au tour</h4>
                            <canvas id="lapTimesComparisonChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h4>Comparaison des vitesses</h4>
                            <canvas id="speedComparisonChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Analyse des forces et faiblesses -->
        <div class="card">
            <div class="card-header">
                <h3>Analyse des forces et faiblesses</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="strengths-card">
                            <h4><i class="fas fa-plus-circle"></i> Forces</h4>
                            <?php if (empty($comparison['strengths'])): ?>
                                <p>Aucune force significative identifiée.</p>
                            <?php else: ?>
                                <ul class="strengths-list">
                                    <?php foreach ($comparison['strengths'] as $strength): ?>
                                        <li><?= $strength ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="weaknesses-card">
                            <h4><i class="fas fa-minus-circle"></i> Faiblesses</h4>
                            <?php if (empty($comparison['weaknesses'])): ?>
                                <p>Aucune faiblesse significative identifiée.</p>
                            <?php else: ?>
                                <ul class="weaknesses-list">
                                    <?php foreach ($comparison['weaknesses'] as $weakness): ?>
                                        <li><?= $weakness ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif (isset($_GET['session1']) || isset($_GET['session2'])): ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h2>Impossible de comparer</h2>
                    <p>Veuillez sélectionner deux sessions valides pour effectuer une comparaison.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($session1 && $session2 && $comparison): ?>
        // Graphique de comparaison des temps au tour
        const lapTimesCtx = document.getElementById('lapTimesComparisonChart').getContext('2d');
        const lapTimesData = <?= json_encode($comparison['lapTimesData']) ?>;
        
        const lapTimesChart = new Chart(lapTimesCtx, {
            type: 'line',
            data: lapTimesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        reverse: true, // Les temps plus bas sont meilleurs
                        ticks: {
                            callback: function(value) {
                                const minutes = Math.floor(value / 60);
                                const seconds = value % 60;
                                return minutes + ':' + (seconds < 10 ? '0' : '') + seconds.toFixed(1);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                if (value === null) return 'Pas de données';
                                const minutes = Math.floor(value / 60);
                                const seconds = value % 60;
                                return context.dataset.label + ': ' + minutes + ':' + (seconds < 10 ? '0' : '') + seconds.toFixed(3);
                            }
                        }
                    }
                }
            }
        });
        
        // Graphique de comparaison des vitesses
        const speedCtx = document.getElementById('speedComparisonChart').getContext('2d');
        const speedData = <?= json_encode($comparison['speedData']) ?>;
        
        const speedChart = new Chart(speedCtx, {
            type: 'bar',
            data: speedData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Vitesse (km/h)'
                        }
                    }
                }
            }
        });
    <?php endif; ?>
});
</script>

<style>
.comparison-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.session-card {
    flex: 1;
    background-color: var(--lighter-gray);
    border-radius: var(--border-radius-md);
    padding: var(--spacing-md);
}

.session-card h4 {
    text-align: center;
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-sm);
    border-bottom: 1px solid var(--border-color);
}

.session1 {
    border-left: 4px solid #0066cc;
}

.session2 {
    border-left: 4px solid #e30613;
}

.comparison-vs {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0 var(--spacing-md);
}

.vs-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: var(--dark-gray);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
}

.comparison-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
}

.metric-card {
    background-color: var(--lighter-gray);
    border-radius: var(--border-radius-md);
    padding: var(--spacing-md);
    text-align: center;
    border-left: 4px solid var(--border-color);
}

.metric-card.better {
    border-left-color: #00cc66;
}

.metric-card.worse {
    border-left-color: #e30613;
}

.metric-title {
    font-weight: 600;
    margin-bottom: var(--spacing-sm);
}

.metric-value {
    font-size: 1.2rem;
}

.metric-card.better .metric-value {
    color: #00cc66;
}

.metric-card.worse .metric-value {
    color: #e30613;
}

.strengths-card, .weaknesses-card {
    background-color: var(--lighter-gray);
    border-radius: var(--border-radius-md);
    padding: var(--spacing-md);
    height: 100%;
}

.strengths-card h4 {
    color: #00cc66;
    margin-bottom: var(--spacing-md);
}

.weaknesses-card h4 {
    color: #e30613;
    margin-bottom: var(--spacing-md);
}

.strengths-list, .weaknesses-list {
    padding-left: var(--spacing-lg);
}

.strengths-list li, .weaknesses-list li {
    margin-bottom: var(--spacing-sm);
}
</style>
