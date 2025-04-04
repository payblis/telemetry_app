<?php
/**
 * Vue pour l'affichage graphique des données télémétriques avec le thème racing
 */
?>

<div class="telemetrie-container">
    <div class="page-header">
        <h1>Visualisation Graphique - <?= ucfirst($type) ?></h1>
        <div class="page-actions">
            <a href="<?= \App\Utils\View::url('/telemetrie/view/' . $tour['session_id']) ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour à la session
            </a>
            <div class="btn-group ml-2">
                <a href="<?= \App\Utils\View::url('/telemetrie/graph/' . $tour['id'] . '/vitesse') ?>" class="btn <?= $type == 'vitesse' ? 'btn-primary' : 'btn-outline' ?>">
                    Vitesse
                </a>
                <a href="<?= \App\Utils\View::url('/telemetrie/graph/' . $tour['id'] . '/acceleration') ?>" class="btn <?= $type == 'acceleration' ? 'btn-primary' : 'btn-outline' ?>">
                    Accélération
                </a>
                <a href="<?= \App\Utils\View::url('/telemetrie/graph/' . $tour['id'] . '/inclinaison') ?>" class="btn <?= $type == 'inclinaison' ? 'btn-primary' : 'btn-outline' ?>">
                    Inclinaison
                </a>
                <a href="<?= \App\Utils\View::url('/telemetrie/graph/' . $tour['id'] . '/regime_moteur') ?>" class="btn <?= $type == 'regime_moteur' ? 'btn-primary' : 'btn-outline' ?>">
                    Régime moteur
                </a>
                <a href="<?= \App\Utils\View::url('/telemetrie/graph/' . $tour['id'] . '/freinage') ?>" class="btn <?= $type == 'freinage' ? 'btn-primary' : 'btn-outline' ?>">
                    Freinage
                </a>
            </div>
        </div>
    </div>

    <?php \App\Utils\View::showNotifications(); ?>

    <!-- Informations du tour -->
    <div class="card">
        <div class="card-header">
            <h3>Informations du tour</h3>
        </div>
        <div class="card-body">
            <div class="session-info-grid">
                <div class="info-group">
                    <h4>Tour</h4>
                    <div class="info-item">
                        <span class="info-label">Numéro</span>
                        <span class="info-value"><?= $tour['numero_tour'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Temps</span>
                        <span class="info-value"><?= gmdate('i:s.v', $tour['temps']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Statut</span>
                        <span class="info-value">
                            <?php if ($tour['meilleur_tour']): ?>
                                <span class="badge badge-success">Meilleur tour</span>
                            <?php elseif ($tour['valide']): ?>
                                <span class="badge badge-info">Valide</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Invalide</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-group">
                    <h4>Performance</h4>
                    <div class="info-item">
                        <span class="info-label">Vitesse max</span>
                        <span class="info-value"><?= $tour['vitesse_max'] ? $tour['vitesse_max'] . ' km/h' : 'N/A' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Vitesse moyenne</span>
                        <span class="info-value"><?= $tour['vitesse_moyenne'] ? round($tour['vitesse_moyenne'], 1) . ' km/h' : 'N/A' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Accélération max</span>
                        <span class="info-value"><?= $tour['acceleration_max'] ? round($tour['acceleration_max'], 2) . ' g' : 'N/A' ?></span>
                    </div>
                </div>
                
                <div class="info-group">
                    <h4>Timing</h4>
                    <div class="info-item">
                        <span class="info-label">Heure de début</span>
                        <span class="info-value"><?= date('H:i:s', strtotime($tour['heure_debut'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Heure de fin</span>
                        <span class="info-value"><?= date('H:i:s', strtotime($tour['heure_fin'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Durée</span>
                        <span class="info-value"><?= gmdate('i:s.v', $tour['temps']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique principal -->
    <div class="card">
        <div class="card-header">
            <h3>Graphique <?= ucfirst($type) ?></h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="telemetryChart"></canvas>
            </div>
            
            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #0066cc;"></div>
                    <span><?= ucfirst($type) ?></span>
                </div>
                <?php if (isset($graphData['datasets'][1])): ?>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #e30613;"></div>
                    <span>Tendance</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Carte du circuit avec données -->
    <div class="card">
        <div class="card-header">
            <h3>Carte du circuit</h3>
        </div>
        <div class="card-body">
            <div class="track-telemetry">
                <div id="circuitMap" class="circuit-map"></div>
                <div class="track-overlay" id="trackOverlay"></div>
                <div class="track-controls">
                    <div class="btn-group">
                        <button id="playButton" class="btn btn-sm btn-primary">
                            <i class="fas fa-play"></i>
                        </button>
                        <button id="pauseButton" class="btn btn-sm btn-outline">
                            <i class="fas fa-pause"></i>
                        </button>
                        <button id="resetButton" class="btn btn-sm btn-outline">
                            <i class="fas fa-redo"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="data-filters">
                <div class="filter-group">
                    <span class="filter-label">Afficher:</span>
                    <div class="filter-options">
                        <span class="filter-option active" data-filter="all">Tout</span>
                        <span class="filter-option" data-filter="braking">Freinage</span>
                        <span class="filter-option" data-filter="acceleration">Accélération</span>
                        <span class="filter-option" data-filter="cornering">Virages</span>
                    </div>
                </div>
                <div class="filter-group">
                    <span class="filter-label">Couleur:</span>
                    <div class="filter-options">
                        <span class="filter-option active" data-color="speed">Vitesse</span>
                        <span class="filter-option" data-color="acceleration">Accélération</span>
                        <span class="filter-option" data-color="lean">Inclinaison</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparaison avec d'autres tours -->
    <div class="card">
        <div class="card-header">
            <h3>Comparer avec un autre tour</h3>
        </div>
        <div class="card-body">
            <form action="<?= \App\Utils\View::url('/telemetrie/compare/' . $tour['id']) ?>" method="get" class="form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tour2_id">Sélectionner un tour à comparer</label>
                        <select id="tour2_id" name="tour2_id" class="form-control" required>
                            <option value="">Sélectionner un tour</option>
                            <?php foreach ($autres_tours as $autre_tour): ?>
                                <option value="<?= $autre_tour['id'] ?>">
                                    Tour <?= $autre_tour['numero_tour'] ?> (<?= gmdate('i:s.v', $autre_tour['temps']) ?>)
                                    <?= $autre_tour['meilleur_tour'] ? ' - Meilleur tour' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="compare_type">Type de données</label>
                        <select id="compare_type" name="type" class="form-control">
                            <option value="vitesse" <?= $type == 'vitesse' ? 'selected' : '' ?>>Vitesse</option>
                            <option value="acceleration" <?= $type == 'acceleration' ? 'selected' : '' ?>>Accélération</option>
                            <option value="inclinaison" <?= $type == 'inclinaison' ? 'selected' : '' ?>>Inclinaison</option>
                            <option value="regime_moteur" <?= $type == 'regime_moteur' ? 'selected' : '' ?>>Régime moteur</option>
                            <option value="freinage" <?= $type == 'freinage' ? 'selected' : '' ?>>Freinage</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Comparer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.css" />

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données du graphique
    const graphData = <?= json_encode($graphData) ?>;
    
    // Configuration du graphique
    const ctx = document.getElementById('telemetryChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: graphData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: '<?= ucfirst($type) ?> - Tour <?= $tour['numero_tour'] ?>',
                    font: {
                        size: 16
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    display: false
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
                    beginAtZero: <?= $type == 'vitesse' || $type == 'regime_moteur' ? 'true' : 'false' ?>
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
    
    // Simulation de la carte du circuit
    // Dans une implémentation réelle, cela utiliserait les coordonnées GPS réelles
    const circuitMap = L.map('circuitMap').setView([48.8566, 2.3522], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(circuitMap);
    
    // Simuler un tracé de circuit
    const circuitCoordinates = [
        [48.8566, 2.3522],
        [48.8576, 2.3532],
        [48.8586, 2.3542],
        [48.8596, 2.3532],
        [48.8606, 2.3522],
        [48.8596, 2.3512],
        [48.8586, 2.3502],
        [48.8576, 2.3512],
        [48.8566, 2.3522]
    ];
    
    const circuitPath = L.polyline(circuitCoordinates, {
        color: '#333',
        weight: 5
    }).addTo(circuitMap);
    
    // Ajuster la vue pour montrer tout le circuit
    circuitMap.fitBounds(circuitPath.getBounds());
    
    // Simuler des points de données sur le circuit
    const dataPoints = [];
    for (let i = 0; i < circuitCoordinates.length; i++) {
        const point = {
            position: circuitCoordinates[i],
            speed: Math.floor(Math.random() * 100) + 100, // 100-200 km/h
            acceleration: (Math.random() * 2).toFixed(1), // 0-2 g
            lean: Math.floor(Math.random() * 30) + 20, // 20-50 degrés
            type: i % 3 === 0 ? 'braking' : (i % 3 === 1 ? 'acceleration' : 'cornering')
        };
        dataPoints.push(point);
        
        // Ajouter un marqueur pour chaque point
        const marker = L.circleMarker(point.position, {
            radius: 5,
            fillColor: getColorBySpeed(point.speed),
            color: '#fff',
            weight: 1,
            opacity: 1,
            fillOpacity: 0.8
        }).addTo(circuitMap);
        
        marker.bindTooltip(`Vitesse: ${point.speed} km/h<br>Accélération: ${point.acceleration} g<br>Inclinaison: ${point.lean}°`);
    }
    
    // Fonction pour obtenir la couleur en fonction de la vitesse
    function getColorBySpeed(speed) {
        if (speed < 120) return '#00cc66'; // Vert
        if (speed < 160) return '#ffcc00'; // Jaune
        return '#e30613'; // Rouge
    }
    
    // Gestion des filtres
    document.querySelectorAll('.filter-option[data-filter]').forEach(option => {
        option.addEventListener('click', function() {
            // Mettre à jour l'état actif
            document.querySelectorAll('.filter-option[data-filter]').forEach(opt => {
                opt.classList.remove('active');
            });
            this.classList.add('active');
            
            // Appliquer le filtre
            const filter = this.getAttribute('data-filter');
            // Dans une implémentation réelle, cela filtrerait les points sur la carte
            console.log('Filtre appliqué:', filter);
        });
    });
    
    document.querySelectorAll('.filter-option[data-color]').forEach(option => {
        option.addEventListener('click', function() {
            // Mettre à jour l'état actif
            document.querySelectorAll('.filter-option[data-color]').forEach(opt => {
                opt.classList.remove('active');
            });
            this.classList.add('active');
            
            // Appliquer le code couleur
            const colorBy = this.getAttribute('data-color');
            // Dans une implémentation réelle, cela changerait les couleurs des points
            console.log('Coloration par:', colorBy);
        });
    });
    
    // Gestion des boutons de contrôle
    document.getElementById('playButton').addEventListener('click', function() {
        console.log('Lecture');
        // Dans une implémentation réelle, cela animerait le parcours sur la carte
    });
    
    document.getElementById('pauseButton').addEventListener('click', function() {
        console.log('Pause');
    });
    
    document.getElementById('resetButton').addEventListener('click', function() {
        console.log('Réinitialisation');
    });
});
</script>
