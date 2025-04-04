<?php
/**
 * Vue pour l'affichage graphique des données télémétriques d'un tour
 */
?>

<div class="telemetrie-container">
    <div class="page-header">
        <h1>Graphique <?= ucfirst($type) ?> - Tour <?= $tour['numero_tour'] ?></h1>
        <div class="page-actions">
            <a href="<?= \App\Utils\View::url('/telemetrie/view/' . $tour['session_id']) ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour à la session
            </a>
            <a href="<?= \App\Utils\View::url('/telemetrie/view-tour/' . $tour['id']) ?>" class="btn btn-outline">
                <i class="fas fa-eye"></i> Détails du tour
            </a>
        </div>
    </div>

    <?php \App\Utils\View::showNotifications(); ?>

    <div class="card">
        <div class="card-header">
            <h3>Informations du tour</h3>
        </div>
        <div class="card-body">
            <div class="tour-info-grid">
                <div class="info-item">
                    <span class="info-label">Tour</span>
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
                <div class="info-item">
                    <span class="info-label">Heure de début</span>
                    <span class="info-value"><?= date('H:i:s', strtotime($tour['heure_debut'])) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Heure de fin</span>
                    <span class="info-value"><?= date('H:i:s', strtotime($tour['heure_fin'])) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Graphique <?= ucfirst($type) ?></h3>
            <div class="card-actions">
                <div class="btn-group">
                    <a href="<?= \App\Utils\View::url('/telemetrie/graph/' . $tour['id'] . '/vitesse') ?>" class="btn btn-sm <?= $type == 'vitesse' ? 'btn-primary' : 'btn-outline' ?>">
                        Vitesse
                    </a>
                    <a href="<?= \App\Utils\View::url('/telemetrie/graph/' . $tour['id'] . '/acceleration') ?>" class="btn btn-sm <?= $type == 'acceleration' ? 'btn-primary' : 'btn-outline' ?>">
                        Accélération
                    </a>
                    <a href="<?= \App\Utils\View::url('/telemetrie/graph/' . $tour['id'] . '/inclinaison') ?>" class="btn btn-sm <?= $type == 'inclinaison' ? 'btn-primary' : 'btn-outline' ?>">
                        Inclinaison
                    </a>
                    <a href="<?= \App\Utils\View::url('/telemetrie/graph/' . $tour['id'] . '/regime_moteur') ?>" class="btn btn-sm <?= $type == 'regime_moteur' ? 'btn-primary' : 'btn-outline' ?>">
                        Régime moteur
                    </a>
                    <a href="<?= \App\Utils\View::url('/telemetrie/graph/' . $tour['id'] . '/freinage') ?>" class="btn btn-sm <?= $type == 'freinage' ? 'btn-primary' : 'btn-outline' ?>">
                        Freinage
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="graph-container">
                <canvas id="telemetryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Comparer avec un autre tour</h3>
        </div>
        <div class="card-body">
            <form action="<?= \App\Utils\View::url('/telemetrie/compare/' . $tour['id']) ?>" method="get" class="form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tour2_id">Sélectionner un tour à comparer</label>
                        <select id="tour2_id" name="tour2_id" required>
                            <option value="">Sélectionner un tour</option>
                            <!-- Les options seront chargées dynamiquement via JavaScript -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="compare_type">Type de données</label>
                        <select id="compare_type" name="type">
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
    
    // Charger les tours disponibles pour la comparaison
    loadAvailableTours();
    
    function loadAvailableTours() {
        // Dans une implémentation réelle, cela serait fait via une requête AJAX
        // Pour l'instant, nous simulons avec des données statiques
        const tourSelect = document.getElementById('tour2_id');
        
        // Exemple de tours disponibles (à remplacer par une requête AJAX)
        const availableTours = [
            { id: 1, numero: 1, temps: '1:45.123' },
            { id: 2, numero: 2, temps: '1:43.567' },
            { id: 3, numero: 3, temps: '1:42.890' },
            { id: 4, numero: 4, temps: '1:42.345' }
        ];
        
        // Filtrer le tour actuel
        const filteredTours = availableTours.filter(t => t.id != <?= $tour['id'] ?>);
        
        // Ajouter les options
        filteredTours.forEach(tour => {
            const option = document.createElement('option');
            option.value = tour.id;
            option.textContent = `Tour ${tour.numero} (${tour.temps})`;
            tourSelect.appendChild(option);
        });
    }
});
</script>
