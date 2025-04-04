<?php
/**
 * Vue pour le tableau de bord principal
 */
?>

<div class="dashboard-container">
    <!-- Widget de bienvenue -->
    <div class="widget widget-xl">
        <div class="widget-header">
            <h3>Bienvenue, <?= \App\Utils\View::escape($_SESSION['user_name']) ?></h3>
            <div class="widget-actions">
                <span class="date-display"><?= date('d/m/Y') ?></span>
            </div>
        </div>
        <div class="widget-body">
            <div class="quick-stats">
                <div class="stat-widget">
                    <div class="stat-widget-value"><?= $stats['sessions_count'] ?? 0 ?></div>
                    <div class="stat-widget-label">Sessions</div>
                </div>
                <div class="stat-widget">
                    <div class="stat-widget-value"><?= $stats['pilotes_count'] ?? 0 ?></div>
                    <div class="stat-widget-label">Pilotes</div>
                </div>
                <div class="stat-widget">
                    <div class="stat-widget-value"><?= $stats['motos_count'] ?? 0 ?></div>
                    <div class="stat-widget-label">Motos</div>
                </div>
                <div class="stat-widget">
                    <div class="stat-widget-value"><?= $stats['circuits_count'] ?? 0 ?></div>
                    <div class="stat-widget-label">Circuits</div>
                </div>
                <div class="stat-widget">
                    <div class="stat-widget-value"><?= gmdate('i:s', $stats['best_lap_time'] ?? 0) ?></div>
                    <div class="stat-widget-label">Meilleur tour</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Widget de sessions récentes -->
    <div class="widget widget-md">
        <div class="widget-header">
            <h3>Sessions récentes</h3>
            <div class="widget-actions">
                <a href="<?= \App\Utils\View::url('/telemetrie') ?>" class="btn btn-sm btn-outline">Voir tout</a>
            </div>
        </div>
        <div class="widget-body">
            <?php if (empty($recent_sessions)): ?>
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
                <ul class="recent-sessions">
                    <?php foreach ($recent_sessions as $session): ?>
                        <li class="session-item">
                            <div class="session-icon">
                                <i class="fas fa-stopwatch"></i>
                            </div>
                            <div class="session-details">
                                <div class="session-title"><?= \App\Utils\View::escape($session['circuit_nom']) ?></div>
                                <div class="session-meta">
                                    <div class="session-date"><?= date('d/m/Y', strtotime($session['date_session'])) ?></div>
                                    <div class="session-pilote"><?= \App\Utils\View::escape($session['pilote_nom'] . ' ' . $session['pilote_prenom']) ?></div>
                                </div>
                            </div>
                            <div class="session-actions">
                                <a href="<?= \App\Utils\View::url('/telemetrie/view/' . $session['id']) ?>" class="btn btn-sm btn-outline">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Widget de performance -->
    <div class="widget widget-md">
        <div class="widget-header">
            <h3>Performance</h3>
            <div class="widget-actions">
                <select id="performance-period" class="form-control form-control-sm">
                    <option value="week">Cette semaine</option>
                    <option value="month">Ce mois</option>
                    <option value="year">Cette année</option>
                </select>
            </div>
        </div>
        <div class="widget-body">
            <div class="performance-chart">
                <canvas id="performanceChart"></canvas>
            </div>
            <div class="performance-stats">
                <div class="performance-stat">
                    <div class="performance-stat-value"><?= $stats['avg_lap_time'] ? gmdate('i:s', $stats['avg_lap_time']) : 'N/A' ?></div>
                    <div class="performance-stat-label">Temps moyen</div>
                </div>
                <div class="performance-stat">
                    <div class="performance-stat-value"><?= $stats['best_lap_time'] ? gmdate('i:s', $stats['best_lap_time']) : 'N/A' ?></div>
                    <div class="performance-stat-label">Meilleur temps</div>
                </div>
                <div class="performance-stat">
                    <div class="performance-stat-value"><?= $stats['total_distance'] ? round($stats['total_distance'] / 1000, 1) . ' km' : 'N/A' ?></div>
                    <div class="performance-stat-label">Distance totale</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Widget de recommandations -->
    <div class="widget widget-sm">
        <div class="widget-header">
            <h3>Recommandations IA</h3>
        </div>
        <div class="widget-body">
            <?php if (empty($recommendations)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <p>Aucune recommandation disponible pour le moment. Importez des données télémétriques pour obtenir des recommandations personnalisées.</p>
                </div>
            <?php else: ?>
                <ul class="recommendation-list">
                    <?php foreach ($recommendations as $recommendation): ?>
                        <li class="recommendation-list-item">
                            <div class="recommendation-icon">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <div class="recommendation-content">
                                <div class="recommendation-text"><?= \App\Utils\View::escape($recommendation['texte']) ?></div>
                                <div class="recommendation-meta"><?= date('d/m/Y', strtotime($recommendation['date_creation'])) ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="widget-footer">
            <a href="<?= \App\Utils\View::url('/analyses') ?>" class="btn btn-sm btn-outline">Voir toutes les analyses</a>
        </div>
    </div>
    
    <!-- Widget météo -->
    <div class="widget widget-xs">
        <div class="widget-header">
            <h3>Météo</h3>
        </div>
        <div class="widget-body">
            <div class="weather-widget">
                <div class="weather-icon">
                    <i class="fas fa-sun"></i>
                </div>
                <div class="weather-temp">22°C</div>
                <div class="weather-desc">Ensoleillé</div>
                <div class="weather-details">
                    <div class="weather-detail">
                        <div class="weather-detail-label">Humidité</div>
                        <div class="weather-detail-value">45%</div>
                    </div>
                    <div class="weather-detail">
                        <div class="weather-detail-label">Vent</div>
                        <div class="weather-detail-value">10 km/h</div>
                    </div>
                    <div class="weather-detail">
                        <div class="weather-detail-label">Piste</div>
                        <div class="weather-detail-value">28°C</div>
                    </div>
                    <div class="weather-detail">
                        <div class="weather-detail-label">Visibilité</div>
                        <div class="weather-detail-value">Excellente</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Widget de progression -->
    <div class="widget widget-sm">
        <div class="widget-header">
            <h3>Progression</h3>
        </div>
        <div class="widget-body">
            <div class="progress-widget">
                <div class="progress-title">Objectifs de performance</div>
                <div class="progress-items">
                    <div class="progress-item">
                        <div class="progress-item-header">
                            <div class="progress-item-label">Temps au tour</div>
                            <div class="progress-item-value">75%</div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: 75%"></div>
                        </div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-item-header">
                            <div class="progress-item-label">Vitesse en virage</div>
                            <div class="progress-item-value">60%</div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: 60%"></div>
                        </div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-item-header">
                            <div class="progress-item-label">Freinage</div>
                            <div class="progress-item-value">85%</div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: 85%"></div>
                        </div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-item-header">
                            <div class="progress-item-label">Accélération</div>
                            <div class="progress-item-value">70%</div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: 70%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Widget de circuit -->
    <div class="widget widget-lg">
        <div class="widget-header">
            <h3>Circuits récents</h3>
        </div>
        <div class="widget-body">
            <?php if (empty($recent_circuits)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-road"></i>
                    </div>
                    <p>Aucun circuit récent. Ajoutez des circuits pour commencer à enregistrer vos sessions.</p>
                    <a href="<?= \App\Utils\View::url('/circuits/create') ?>" class="btn btn-primary">Ajouter un circuit</a>
                </div>
            <?php else: ?>
                <div class="card-grid">
                    <?php foreach ($recent_circuits as $circuit): ?>
                        <div class="data-card">
                            <div class="data-card-header">
                                <h4><?= \App\Utils\View::escape($circuit['nom']) ?></h4>
                            </div>
                            <div class="data-card-body">
                                <div class="circuit-info">
                                    <div class="circuit-detail">
                                        <div class="circuit-label">Localisation</div>
                                        <div class="circuit-value"><?= \App\Utils\View::escape($circuit['ville'] . ', ' . $circuit['pays']) ?></div>
                                    </div>
                                    <div class="circuit-detail">
                                        <div class="circuit-label">Longueur</div>
                                        <div class="circuit-value"><?= \App\Utils\View::escape($circuit['longueur']) ?> m</div>
                                    </div>
                                    <div class="circuit-detail">
                                        <div class="circuit-label">Virages</div>
                                        <div class="circuit-value"><?= \App\Utils\View::escape($circuit['nombre_virages']) ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="data-card-footer">
                                <a href="<?= \App\Utils\View::url('/circuits/view/' . $circuit['id']) ?>" class="btn btn-sm btn-outline">Voir détails</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique de performance
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Temps au tour',
                data: [105, 102, 98, 97, 95, 94, 93],
                borderColor: '#0066cc',
                backgroundColor: 'rgba(0, 102, 204, 0.1)',
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    reverse: true,
                    ticks: {
                        callback: function(value) {
                            const minutes = Math.floor(value / 60);
                            const seconds = value % 60;
                            return minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const minutes = Math.floor(value / 60);
                            const seconds = value % 60;
                            return 'Temps: ' + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                        }
                    }
                }
            }
        }
    });
    
    // Changement de période pour le graphique de performance
    document.getElementById('performance-period').addEventListener('change', function() {
        const period = this.value;
        let labels, data;
        
        if (period === 'week') {
            labels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
            data = [105, 102, 98, 97, 95, 94, 93];
        } else if (period === 'month') {
            labels = ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'];
            data = [100, 97, 95, 93];
        } else {
            labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
            data = [110, 108, 105, 103, 100, 98, 97, 96, 95, 94, 93, 92];
        }
        
        performanceChart.data.labels = labels;
        performanceChart.data.datasets[0].data = data;
        performanceChart.update();
    });
});
</script>
