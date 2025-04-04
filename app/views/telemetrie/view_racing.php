<?php
/**
 * Vue pour l'affichage détaillé d'une session de télémétrie avec le thème racing
 */
?>

<div class="telemetrie-container">
    <div class="page-header">
        <h1>Session - <?= \App\Utils\View::escape($session['circuit_nom']) ?></h1>
        <div class="page-actions">
            <a href="<?= \App\Utils\View::url('/telemetrie') ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour aux sessions
            </a>
            <a href="<?= \App\Utils\View::url('/telemetrie/edit/' . $session['id']) ?>" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="<?= \App\Utils\View::url('/telemetrie/export/' . $session['id']) ?>" class="btn btn-primary">
                <i class="fas fa-file-export"></i> Exporter
            </a>
        </div>
    </div>

    <?php \App\Utils\View::showNotifications(); ?>

    <!-- Informations de la session -->
    <div class="card">
        <div class="card-header">
            <h3>Informations de la session</h3>
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
                        <span class="info-label">Heure de début</span>
                        <span class="info-value"><?= date('H:i', strtotime($session['heure_debut'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Durée</span>
                        <span class="info-value"><?= gmdate('H:i:s', $session['duree_totale']) ?></span>
                    </div>
                </div>
                
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
                        <span class="info-value"><?= $session['circuit_longueur'] ?> m</span>
                    </div>
                </div>
                
                <div class="info-group">
                    <h4>Pilote</h4>
                    <div class="info-item">
                        <span class="info-label">Nom</span>
                        <span class="info-value"><?= \App\Utils\View::escape($session['pilote_nom'] . ' ' . $session['pilote_prenom']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Catégorie</span>
                        <span class="info-value"><?= \App\Utils\View::escape($session['pilote_categorie']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Expérience</span>
                        <span class="info-value"><?= \App\Utils\View::escape($session['pilote_experience']) ?> ans</span>
                    </div>
                </div>
                
                <div class="info-group">
                    <h4>Moto</h4>
                    <div class="info-item">
                        <span class="info-label">Marque & Modèle</span>
                        <span class="info-value"><?= \App\Utils\View::escape($session['moto_marque'] . ' ' . $session['moto_modele']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Année</span>
                        <span class="info-value"><?= $session['moto_annee'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Cylindrée</span>
                        <span class="info-value"><?= $session['moto_cylindree'] ?> cc</span>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($session['notes'])): ?>
                <div class="session-notes">
                    <h4>Notes</h4>
                    <div class="notes-content">
                        <?= nl2br(\App\Utils\View::escape($session['notes'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistiques de la session -->
    <div class="card">
        <div class="card-header">
            <h3>Statistiques</h3>
        </div>
        <div class="card-body">
            <div class="session-stats">
                <div class="stat-item">
                    <div class="stat-value"><?= $session['nombre_tours'] ?></div>
                    <div class="stat-label">Tours</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $session['meilleur_temps'] ? gmdate('i:s.v', $session['meilleur_temps']) : 'N/A' ?></div>
                    <div class="stat-label">Meilleur temps</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $session['temps_moyen'] ? gmdate('i:s.v', $session['temps_moyen']) : 'N/A' ?></div>
                    <div class="stat-label">Temps moyen</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $session['vitesse_max'] ? $session['vitesse_max'] . ' km/h' : 'N/A' ?></div>
                    <div class="stat-label">Vitesse max</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $session['vitesse_moyenne'] ? round($session['vitesse_moyenne'], 1) . ' km/h' : 'N/A' ?></div>
                    <div class="stat-label">Vitesse moyenne</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $session['distance_totale'] ? round($session['distance_totale'] / 1000, 1) . ' km' : 'N/A' ?></div>
                    <div class="stat-label">Distance totale</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique d'évolution des temps au tour -->
    <div class="card">
        <div class="card-header">
            <h3>Évolution des temps au tour</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="lapTimesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Liste des tours -->
    <div class="card">
        <div class="card-header">
            <h3>Tours</h3>
        </div>
        <div class="card-body">
            <?php if (empty($tours)): ?>
                <div class="empty-state">
                    <p>Aucun tour enregistré pour cette session.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Temps</th>
                                <th>Heure</th>
                                <th>Vitesse max</th>
                                <th>Vitesse moy.</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tours as $tour): ?>
                                <tr class="<?= $tour['meilleur_tour'] ? 'best-lap' : ($tour['valide'] ? '' : 'invalid-lap') ?>">
                                    <td><?= $tour['numero_tour'] ?></td>
                                    <td>
                                        <span class="lap-time <?= $tour['meilleur_tour'] ? 'best' : '' ?>">
                                            <?= gmdate('i:s.v', $tour['temps']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('H:i:s', strtotime($tour['heure_debut'])) ?></td>
                                    <td><?= $tour['vitesse_max'] ? $tour['vitesse_max'] . ' km/h' : 'N/A' ?></td>
                                    <td><?= $tour['vitesse_moyenne'] ? round($tour['vitesse_moyenne'], 1) . ' km/h' : 'N/A' ?></td>
                                    <td>
                                        <?php if ($tour['meilleur_tour']): ?>
                                            <span class="badge badge-success">Meilleur tour</span>
                                        <?php elseif ($tour['valide']): ?>
                                            <span class="badge badge-info">Valide</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Invalide</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= \App\Utils\View::url('/telemetrie/view-tour/' . $tour['id']) ?>" class="btn btn-sm btn-outline" title="Détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= \App\Utils\View::url('/telemetrie/graph/' . $tour['id'] . '/vitesse') ?>" class="btn btn-sm btn-outline" title="Graphique">
                                                <i class="fas fa-chart-line"></i>
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
                <div class="recommendation-card">
                    <div class="recommendation-header">
                        <div class="recommendation-header-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3>Analyse de performance</h3>
                    </div>
                    <div class="recommendation-body">
                        <?php foreach ($recommendations as $recommendation): ?>
                            <div class="recommendation-item">
                                <div class="recommendation-title">
                                    <i class="fas fa-check-circle"></i>
                                    <?= \App\Utils\View::escape($recommendation['titre']) ?>
                                </div>
                                <div class="recommendation-description">
                                    <?= nl2br(\App\Utils\View::escape($recommendation['texte'])) ?>
                                </div>
                                <?php if (!empty($recommendation['action_recommandee'])): ?>
                                    <div class="recommendation-action">
                                        Action recommandée: <?= \App\Utils\View::escape($recommendation['action_recommandee']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="recommendation-footer">
                        <div class="recommendation-source">
                            Source: <?= $recommendations[0]['source'] == 'openai' ? 'ChatGPT' : 'IA Communautaire' ?>
                        </div>
                        <div class="recommendation-confidence">
                            <span class="confidence-label">Confiance:</span>
                            <span class="confidence-value"><?= $recommendations[0]['confiance'] ?>%</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique d'évolution des temps au tour
    const ctx = document.getElementById('lapTimesChart').getContext('2d');
    
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
    
    const lapTimesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: tourNumbers,
            datasets: [{
                label: 'Temps au tour',
                data: lapTimes,
                backgroundColor: lapColors,
                borderColor: lapColors,
                borderWidth: 1
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
                            return minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
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
});
</script>
