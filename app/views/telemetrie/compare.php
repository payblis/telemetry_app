<?php
/**
 * Vue pour la comparaison de deux tours
 */
?>

<div class="telemetrie-container">
    <div class="page-header">
        <h1>Comparaison de Tours - <?= ucfirst($type) ?></h1>
        <div class="page-actions">
            <a href="<?= \App\Utils\View::url('/telemetrie/view/' . $tour1['session_id']) ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour à la session
            </a>
        </div>
    </div>

    <?php \App\Utils\View::showNotifications(); ?>

    <div class="card">
        <div class="card-header">
            <h3>Informations des tours</h3>
        </div>
        <div class="card-body">
            <div class="comparison-info">
                <div class="comparison-tour">
                    <h4>Tour 1</h4>
                    <div class="tour-info-grid">
                        <div class="info-item">
                            <span class="info-label">Session</span>
                            <span class="info-value"><?= date('d/m/Y', strtotime($session1['date_session'])) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tour</span>
                            <span class="info-value"><?= $tour1['numero_tour'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Temps</span>
                            <span class="info-value"><?= gmdate('i:s.v', $tour1['temps']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Statut</span>
                            <span class="info-value">
                                <?php if ($tour1['meilleur_tour']): ?>
                                    <span class="badge badge-success">Meilleur tour</span>
                                <?php elseif ($tour1['valide']): ?>
                                    <span class="badge badge-info">Valide</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Invalide</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="comparison-divider">
                    <div class="vs-badge">VS</div>
                </div>
                
                <div class="comparison-tour">
                    <h4>Tour 2</h4>
                    <div class="tour-info-grid">
                        <div class="info-item">
                            <span class="info-label">Session</span>
                            <span class="info-value"><?= date('d/m/Y', strtotime($session2['date_session'])) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tour</span>
                            <span class="info-value"><?= $tour2['numero_tour'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Temps</span>
                            <span class="info-value"><?= gmdate('i:s.v', $tour2['temps']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Statut</span>
                            <span class="info-value">
                                <?php if ($tour2['meilleur_tour']): ?>
                                    <span class="badge badge-success">Meilleur tour</span>
                                <?php elseif ($tour2['valide']): ?>
                                    <span class="badge badge-info">Valide</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Invalide</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="comparison-diff">
                <h4>Différence</h4>
                <?php 
                $diff = $tour1['temps'] - $tour2['temps'];
                $diffClass = $diff < 0 ? 'positive' : ($diff > 0 ? 'negative' : 'neutral');
                $diffSign = $diff < 0 ? '-' : '+';
                $diffAbs = abs($diff);
                ?>
                <div class="diff-value <?= $diffClass ?>">
                    <?= $diff == 0 ? 'Égalité' : $diffSign . ' ' . gmdate('i:s.v', $diffAbs) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Graphique comparatif - <?= ucfirst($type) ?></h3>
            <div class="card-actions">
                <div class="btn-group">
                    <a href="<?= \App\Utils\View::url('/telemetrie/compare/' . $tour1['id'] . '/' . $tour2['id'] . '?type=vitesse') ?>" class="btn btn-sm <?= $type == 'vitesse' ? 'btn-primary' : 'btn-outline' ?>">
                        Vitesse
                    </a>
                    <a href="<?= \App\Utils\View::url('/telemetrie/compare/' . $tour1['id'] . '/' . $tour2['id'] . '?type=acceleration') ?>" class="btn btn-sm <?= $type == 'acceleration' ? 'btn-primary' : 'btn-outline' ?>">
                        Accélération
                    </a>
                    <a href="<?= \App\Utils\View::url('/telemetrie/compare/' . $tour1['id'] . '/' . $tour2['id'] . '?type=inclinaison') ?>" class="btn btn-sm <?= $type == 'inclinaison' ? 'btn-primary' : 'btn-outline' ?>">
                        Inclinaison
                    </a>
                    <a href="<?= \App\Utils\View::url('/telemetrie/compare/' . $tour1['id'] . '/' . $tour2['id'] . '?type=regime_moteur') ?>" class="btn btn-sm <?= $type == 'regime_moteur' ? 'btn-primary' : 'btn-outline' ?>">
                        Régime moteur
                    </a>
                    <a href="<?= \App\Utils\View::url('/telemetrie/compare/' . $tour1['id'] . '/' . $tour2['id'] . '?type=freinage') ?>" class="btn btn-sm <?= $type == 'freinage' ? 'btn-primary' : 'btn-outline' ?>">
                        Freinage
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="graph-container">
                <canvas id="comparisonChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Analyse comparative</h3>
        </div>
        <div class="card-body">
            <div class="comparison-analysis">
                <p class="analysis-intro">L'analyse comparative permet d'identifier les différences de performance entre les deux tours :</p>
                
                <div class="analysis-metrics">
                    <div class="metric-item">
                        <div class="metric-header">
                            <h5>Vitesse</h5>
                        </div>
                        <div class="metric-comparison">
                            <div class="metric-tour">
                                <span class="metric-label">Tour 1</span>
                                <span class="metric-value">Max: <strong id="tour1-vitesse-max">--</strong> km/h</span>
                                <span class="metric-value">Moy: <strong id="tour1-vitesse-moy">--</strong> km/h</span>
                            </div>
                            <div class="metric-tour">
                                <span class="metric-label">Tour 2</span>
                                <span class="metric-value">Max: <strong id="tour2-vitesse-max">--</strong> km/h</span>
                                <span class="metric-value">Moy: <strong id="tour2-vitesse-moy">--</strong> km/h</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="metric-item">
                        <div class="metric-header">
                            <h5>Accélération</h5>
                        </div>
                        <div class="metric-comparison">
                            <div class="metric-tour">
                                <span class="metric-label">Tour 1</span>
                                <span class="metric-value">Max: <strong id="tour1-acceleration-max">--</strong> g</span>
                            </div>
                            <div class="metric-tour">
                                <span class="metric-label">Tour 2</span>
                                <span class="metric-value">Max: <strong id="tour2-acceleration-max">--</strong> g</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="metric-item">
                        <div class="metric-header">
                            <h5>Inclinaison</h5>
                        </div>
                        <div class="metric-comparison">
                            <div class="metric-tour">
                                <span class="metric-label">Tour 1</span>
                                <span class="metric-value">Max: <strong id="tour1-inclinaison-max">--</strong> °</span>
                            </div>
                            <div class="metric-tour">
                                <span class="metric-label">Tour 2</span>
                                <span class="metric-value">Max: <strong id="tour2-inclinaison-max">--</strong> °</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="analysis-conclusion">
                    <h5>Conclusion</h5>
                    <p id="analysis-conclusion-text">Les données comparatives seront analysées et affichées ici.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données du graphique
    const graphData = <?= json_encode($graphData) ?>;
    
    // Configuration du graphique
    const ctx = document.getElementById('comparisonChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: graphData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Comparaison <?= ucfirst($type) ?> - Tour <?= $tour1['numero_tour'] ?> vs Tour <?= $tour2['numero_tour'] ?>',
                    font: {
                        size: 16
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    position: 'top',
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Temps (secondes)'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: getYAxisLabel('<?= $type ?>')
                    },
                    beginAtZero: true
                }
            }
        }
    });
    
    // Fonction pour obtenir le label de l'axe Y en fonction du type de données
    function getYAxisLabel(type) {
        switch(type) {
            case 'vitesse':
                return 'Vitesse (km/h)';
            case 'acceleration':
                return 'Accélération (g)';
            case 'inclinaison':
                return 'Inclinaison (degrés)';
            case 'regime_moteur':
                return 'Régime moteur (tr/min)';
            case 'freinage':
                return 'Force de freinage (%)';
            default:
                return '';
        }
    }
    
    // Simuler le chargement des métriques comparatives
    // Dans une implémentation réelle, ces données viendraient du serveur
    setTimeout(function() {
        // Tour 1
        document.getElementById('tour1-vitesse-max').textContent = '215';
        document.getElementById('tour1-vitesse-moy').textContent = '142';
        document.getElementById('tour1-acceleration-max').textContent = '1.8';
        document.getElementById('tour1-inclinaison-max').textContent = '48';
        
        // Tour 2
        document.getElementById('tour2-vitesse-max').textContent = '218';
        document.getElementById('tour2-vitesse-moy').textContent = '145';
        document.getElementById('tour2-acceleration-max').textContent = '1.9';
        document.getElementById('tour2-inclinaison-max').textContent = '51';
        
        // Conclusion
        document.getElementById('analysis-conclusion-text').textContent = 
            'Le Tour 2 montre une amélioration globale des performances avec une vitesse moyenne plus élevée (+3 km/h) ' +
            'et une meilleure utilisation de l\'inclinaison (+3°). Les points d\'amélioration se situent principalement ' +
            'dans les virages 3 et 5 où la vitesse de passage est significativement plus élevée.';
    }, 1000);
});
</script>
