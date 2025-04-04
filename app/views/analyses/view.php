<?php
/**
 * Vue pour l'affichage détaillé des analyses et recommandations d'une session
 */
?>

<div class="telemetrie-container">
    <div class="page-header">
        <h1>Analyse IA - <?= \App\Utils\View::escape($session['circuit_nom']) ?></h1>
        <div class="page-actions">
            <a href="<?= \App\Utils\View::url('/analyses') ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour aux analyses
            </a>
            <a href="<?= \App\Utils\View::url('/telemetrie/view/' . $session['id']) ?>" class="btn btn-secondary">
                <i class="fas fa-chart-line"></i> Voir les données
            </a>
            <a href="<?= \App\Utils\View::url('/analyses/generate/' . $session['id']) ?>" class="btn btn-primary">
                <i class="fas fa-sync"></i> Régénérer
            </a>
        </div>
    </div>

    <?php \App\Utils\View::showNotifications(); ?>

    <!-- Résumé de la session -->
    <div class="card">
        <div class="card-header">
            <h3>Résumé de la session</h3>
        </div>
        <div class="card-body">
            <div class="session-info-grid">
                <div class="info-group">
                    <h4>Général</h4>
                    <div class="info-item">
                        <span class="info-label">Date</span>
                        <span class="info-value"><?= date('d/m/Y', strtotime($session['date_session'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Circuit</span>
                        <span class="info-value"><?= \App\Utils\View::escape($session['circuit_nom']) ?></span>
                    </div>
                </div>
                
                <div class="info-group">
                    <h4>Pilote & Moto</h4>
                    <div class="info-item">
                        <span class="info-label">Pilote</span>
                        <span class="info-value"><?= \App\Utils\View::escape($session['pilote_nom'] . ' ' . $session['pilote_prenom']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Moto</span>
                        <span class="info-value"><?= \App\Utils\View::escape($session['moto_marque'] . ' ' . $session['moto_modele']) ?></span>
                    </div>
                </div>
                
                <div class="info-group">
                    <h4>Performance</h4>
                    <div class="info-item">
                        <span class="info-label">Meilleur temps</span>
                        <span class="info-value"><?= $session['meilleur_temps'] ? gmdate('i:s.v', $session['meilleur_temps']) : 'N/A' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Vitesse max</span>
                        <span class="info-value"><?= $session['vitesse_max'] ? $session['vitesse_max'] . ' km/h' : 'N/A' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommandations IA -->
    <div class="card">
        <div class="card-header">
            <h3>Recommandations IA</h3>
        </div>
        <div class="card-body">
            <?php if (empty($recommendations)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <p>Aucune recommandation disponible pour cette session.</p>
                    <a href="<?= \App\Utils\View::url('/analyses/generate/' . $session['id']) ?>" class="btn btn-primary">
                        Générer des recommandations
                    </a>
                </div>
            <?php else: ?>
                <div class="recommendation-tabs">
                    <div class="tab-header">
                        <div class="tab-item active" data-tab="all">Toutes</div>
                        <div class="tab-item" data-tab="openai">ChatGPT</div>
                        <div class="tab-item" data-tab="communaute">Communauté</div>
                    </div>
                    <div class="tab-content">
                        <?php 
                        $openaiCount = 0;
                        $communityCount = 0;
                        foreach ($recommendations as $rec) {
                            if ($rec['source'] == 'openai') $openaiCount++;
                            else $communityCount++;
                        }
                        ?>
                        
                        <div class="recommendation-list">
                            <?php foreach ($recommendations as $index => $recommendation): ?>
                                <div class="recommendation-card mb-4" data-source="<?= $recommendation['source'] ?>">
                                    <div class="recommendation-header">
                                        <div class="recommendation-header-icon">
                                            <i class="fas <?= $recommendation['source'] == 'openai' ? 'fa-robot' : 'fa-users' ?>"></i>
                                        </div>
                                        <h3><?= \App\Utils\View::escape($recommendation['titre']) ?></h3>
                                        <div class="recommendation-source-badge <?= $recommendation['source'] == 'openai' ? 'ai' : 'community' ?>">
                                            <?= $recommendation['source'] == 'openai' ? 'ChatGPT' : 'Communauté' ?>
                                        </div>
                                    </div>
                                    <div class="recommendation-body">
                                        <div class="recommendation-item">
                                            <div class="recommendation-description">
                                                <?= nl2br(\App\Utils\View::escape($recommendation['texte'])) ?>
                                            </div>
                                            <?php if (!empty($recommendation['action_recommandee'])): ?>
                                                <div class="recommendation-action">
                                                    <strong>Action recommandée:</strong> <?= \App\Utils\View::escape($recommendation['action_recommandee']) ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($recommendation['impact_attendu'])): ?>
                                                <div class="recommendation-impact">
                                                    <strong>Impact attendu:</strong> <?= \App\Utils\View::escape($recommendation['impact_attendu']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="recommendation-footer">
                                        <div class="recommendation-confidence">
                                            <span class="confidence-label">Confiance:</span>
                                            <div class="confidence-bar">
                                                <div class="confidence-fill" style="width: <?= $recommendation['confiance'] ?>%;"></div>
                                            </div>
                                            <span class="confidence-value"><?= $recommendation['confiance'] ?>%</span>
                                        </div>
                                        
                                        <?php if (empty($recommendation['feedback_utilisateur'])): ?>
                                            <button class="btn btn-sm btn-outline feedback-btn" data-toggle="feedback-form-<?= $recommendation['id'] ?>">
                                                <i class="fas fa-comment"></i> Donner un feedback
                                            </button>
                                        <?php else: ?>
                                            <div class="user-feedback">
                                                <div class="rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?= $i <= $recommendation['note_utilisateur'] ? 'active' : '' ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <div class="feedback-text">
                                                    <?= nl2br(\App\Utils\View::escape($recommendation['feedback_utilisateur'])) ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (empty($recommendation['feedback_utilisateur'])): ?>
                                        <div class="feedback-form" id="feedback-form-<?= $recommendation['id'] ?>" style="display: none;">
                                            <form action="<?= \App\Utils\View::url('/analyses/feedback') ?>" method="post">
                                                <input type="hidden" name="recommendation_id" value="<?= $recommendation['id'] ?>">
                                                <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                                                
                                                <div class="form-group">
                                                    <label>Évaluation</label>
                                                    <div class="rating-input">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <input type="radio" name="rating" value="<?= $i ?>" id="rating-<?= $recommendation['id'] ?>-<?= $i ?>" <?= $i == 3 ? 'checked' : '' ?>>
                                                            <label for="rating-<?= $recommendation['id'] ?>-<?= $i ?>"><i class="fas fa-star"></i></label>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="feedback-<?= $recommendation['id'] ?>">Commentaire</label>
                                                    <textarea id="feedback-<?= $recommendation['id'] ?>" name="feedback" class="form-control" rows="3" placeholder="Votre avis sur cette recommandation..."></textarea>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Statut</label>
                                                    <div class="radio-group">
                                                        <label>
                                                            <input type="radio" name="status" value="applied">
                                                            <span>Appliquée</span>
                                                        </label>
                                                        <label>
                                                            <input type="radio" name="status" value="rejected">
                                                            <span>Rejetée</span>
                                                        </label>
                                                        <label>
                                                            <input type="radio" name="status" value="active" checked>
                                                            <span>En attente</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-primary">Envoyer</button>
                                                    <button type="button" class="btn btn-outline cancel-feedback" data-toggle="feedback-form-<?= $recommendation['id'] ?>">Annuler</button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Analyse des performances -->
    <div class="card">
        <div class="card-header">
            <h3>Analyse des performances</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-container">
                        <h4>Évolution des temps au tour</h4>
                        <canvas id="lapTimesChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <h4>Répartition des vitesses</h4>
                        <canvas id="speedDistributionChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="performance-metrics mt-4">
                <h4>Métriques clés</h4>
                <div class="metrics-grid">
                    <?php if (!empty($telemetrie)): ?>
                        <div class="metric-card">
                            <div class="metric-value"><?= round($telemetrie['inclinaison_max'] ?? 0, 1) ?>°</div>
                            <div class="metric-label">Inclinaison max</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value"><?= round($telemetrie['acceleration_max'] ?? 0, 2) ?> g</div>
                            <div class="metric-label">Accélération max</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value"><?= round($telemetrie['force_freinage_avg'] ?? 0, 1) ?>%</div>
                            <div class="metric-label">Force freinage moy.</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value"><?= round($telemetrie['vitesse_virage_avg'] ?? 0, 1) ?> km/h</div>
                            <div class="metric-label">Vitesse virage moy.</div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Données télémétriques détaillées non disponibles pour cette session.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets de recommandations
    document.querySelectorAll('.tab-item').forEach(tab => {
        tab.addEventListener('click', function() {
            // Mettre à jour l'onglet actif
            document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Filtrer les recommandations
            const filter = this.getAttribute('data-tab');
            document.querySelectorAll('.recommendation-card').forEach(card => {
                if (filter === 'all' || card.getAttribute('data-source') === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    
    // Gestion des formulaires de feedback
    document.querySelectorAll('.feedback-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const formId = this.getAttribute('data-toggle');
            const form = document.getElementById(formId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });
    });
    
    document.querySelectorAll('.cancel-feedback').forEach(btn => {
        btn.addEventListener('click', function() {
            const formId = this.getAttribute('data-toggle');
            document.getElementById(formId).style.display = 'none';
        });
    });
    
    // Graphique d'évolution des temps au tour
    const lapTimesCtx = document.getElementById('lapTimesChart').getContext('2d');
    
    // Préparer les données
    const tourNumbers = [];
    const lapTimes = [];
    const lapColors = [];
    
    <?php foreach ($tours as $tour): ?>
        tourNumbers.push(<?= $tour['numero_tour'] ?>);
        lapTimes.push(<?= $tour['temps'] ?>);
        <?php if ($tour['meilleur_tour']): ?>
            lapColors.push('#00cc66'); // Vert pour le meilleur tour
        <?php elseif (!$tour['valide']): ?>
            lapColors.push('#ffcc00'); // Jaune pour les tours invalides
        <?php else: ?>
            lapColors.push('#0066cc'); // Bleu pour les tours normaux
        <?php endif; ?>
    <?php endforeach; ?>
    
    const lapTimesChart = new Chart(lapTimesCtx, {
        type: 'line',
        data: {
            labels: tourNumbers,
            datasets: [{
                label: 'Temps au tour',
                data: lapTimes,
                backgroundColor: 'rgba(0, 102, 204, 0.1)',
                borderColor: '#0066cc',
                borderWidth: 2,
                pointBackgroundColor: lapColors,
                pointBorderColor: '#fff',
                pointRadius: 5,
                tension: 0.1
            }]
        },
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
                },
                x: {
                    title: {
                        display: true,
                        text: 'Numéro de tour'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const minutes = Math.floor(value / 60);
                            const seconds = value % 60;
                            return 'Temps: ' + minutes + ':' + (seconds < 10 ? '0' : '') + seconds.toFixed(3);
                        }
                    }
                }
            }
        }
    });
    
    // Graphique de répartition des vitesses
    const speedDistributionCtx = document.getElementById('speedDistributionChart').getContext('2d');
    
    // Simuler des données de répartition des vitesses
    // Dans une implémentation réelle, ces données viendraient de l'analyse télémétriques
    const speedDistributionChart = new Chart(speedDistributionCtx, {
        type: 'bar',
        data: {
            labels: ['0-50', '50-100', '100-150', '150-200', '200-250', '250+'],
            datasets: [{
                label: 'Répartition des vitesses (km/h)',
                data: [5, 15, 25, 30, 20, 5],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(255, 205, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ],
                borderColor: [
                    'rgb(255, 99, 132)',
                    'rgb(255, 159, 64)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(54, 162, 235)',
                    'rgb(153, 102, 255)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: '% du temps'
                    }
                }
            }
        }
    });
});
</script>

<style>
.recommendation-tabs .tab-header {
    display: flex;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: var(--spacing-md);
}

.recommendation-tabs .tab-item {
    padding: var(--spacing-sm) var(--spacing-md);
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all var(--transition-fast) ease-in-out;
}

.recommendation-tabs .tab-item.active {
    border-bottom-color: var(--primary-color);
    font-weight: 600;
}

.recommendation-tabs .tab-item:hover {
    background-color: var(--lighter-gray);
}

.recommendation-source-badge {
    font-size: 0.8rem;
    padding: 0.2rem 0.5rem;
    border-radius: var(--border-radius-sm);
    color: white;
}

.recommendation-source-badge.ai {
    background-color: #0066cc;
}

.recommendation-source-badge.community {
    background-color: #00cc66;
}

.confidence-bar {
    height: 8px;
    background-color: var(--lighter-gray);
    border-radius: 4px;
    width: 100px;
    margin: 0 var(--spacing-sm);
    overflow: hidden;
}

.confidence-fill {
    height: 100%;
    background-color: var(--primary-color);
    border-radius: 4px;
}

.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.rating-input input {
    display: none;
}

.rating-input label {
    cursor: pointer;
    font-size: 1.5rem;
    color: var(--lighter-gray);
    padding: 0 var(--spacing-xs);
}

.rating-input label:hover,
.rating-input label:hover ~ label,
.rating-input input:checked ~ label {
    color: #ffcc00;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: var(--spacing-md);
    margin-top: var(--spacing-md);
}

.metric-card {
    background-color: var(--lighter-gray);
    border-radius: var(--border-radius-md);
    padding: var(--spacing-md);
    text-align: center;
}

.metric-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary-color);
}

.metric-label {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin-top: var(--spacing-xs);
}
</style>
