<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = 'Détails de la session';

// Vérification de l'ID de la session
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['flash_message'] = "Session non trouvée.";
    $_SESSION['flash_type'] = "danger";
    header('Location: list.php');
    exit;
}

$session_id = (int)$_GET['id'];

try {
    // Récupération des informations de la session
    $stmt = $pdo->prepare("
        SELECT s.*, 
               p.nom AS pilote_nom, p.prenom AS pilote_prenom, p.pseudo AS pilote_pseudo,
               m.marque, m.modele, m.annee,
               c.nom AS circuit_nom, c.pays AS circuit_pays, c.longueur_km, c.nb_virages,
               r.precharge_avant, r.precharge_arriere, r.detente_avant, r.detente_arriere,
               r.compression_avant, r.compression_arriere, r.hauteur_avant, r.hauteur_arriere,
               r.rapport_final, r.pression_pneu_avant, r.pression_pneu_arriere, r.notes AS notes_reglages
        FROM sessions s
        JOIN pilotes p ON s.pilote_id = p.id
        JOIN motos m ON s.moto_id = m.id
        JOIN circuits c ON s.circuit_id = c.id
        LEFT JOIN reglages r ON s.id = r.session_id
        WHERE s.id = ? AND p.user_id = ?
    ");
    $stmt->execute([$session_id, $_SESSION['user_id']]);
    $session = $stmt->fetch();
    
    if (!$session) {
        $_SESSION['flash_message'] = "Session non trouvée ou accès non autorisé.";
        $_SESSION['flash_type'] = "danger";
        header('Location: list.php');
        exit;
    }
    
    // Récupération des tours
    $stmt = $pdo->prepare("
        SELECT * FROM laps 
        WHERE session_id = ? 
        ORDER BY numero_tour ASC
    ");
    $stmt->execute([$session_id]);
    $laps = $stmt->fetchAll();
    
    // Calcul des statistiques
    $total_laps = count($laps);
    $best_lap = null;
    $average_speed = 0;
    $total_speed = 0;
    
    foreach ($laps as $lap) {
        if (!$best_lap || $lap['temps_tour'] < $best_lap['temps_tour']) {
            $best_lap = $lap;
        }
        $total_speed += $lap['vitesse_moyenne'];
    }
    
    if ($total_laps > 0) {
        $average_speed = $total_speed / $total_laps;
    }
    
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des données de la session: " . $e->getMessage());
    $_SESSION['flash_message'] = "Une erreur est survenue lors du chargement des données.";
    $_SESSION['flash_type'] = "danger";
    header('Location: list.php');
    exit;
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Session du <?php echo date('d/m/Y', strtotime($session['date_session'])); ?></h1>
        <div class="btn-group">
            <a href="edit.php?id=<?php echo $session_id; ?>" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="delete.php?id=<?php echo $session_id; ?>" 
               class="btn btn-danger"
               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette session ?')">
                <i class="fas fa-trash"></i> Supprimer
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Informations générales -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Pilote</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($session['pilote_prenom'] . ' ' . $session['pilote_nom']); ?></dd>
                        
                        <dt class="col-sm-4">Moto</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($session['marque'] . ' ' . $session['modele'] . ' (' . $session['annee'] . ')'); ?></dd>
                        
                        <dt class="col-sm-4">Circuit</dt>
                        <dd class="col-sm-8">
                            <?php echo htmlspecialchars($session['circuit_nom']); ?>
                            <small class="text-muted">(<?php echo htmlspecialchars($session['circuit_pays']); ?>)</small>
                        </dd>
                        
                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?php 
                                echo match($session['type_session']) {
                                    'free practice' => 'info',
                                    'qualification' => 'warning',
                                    'course' => 'danger',
                                    'trackday' => 'success',
                                    default => 'secondary'
                                };
                            ?>">
                                <?php echo htmlspecialchars($session['type_session']); ?>
                            </span>
                        </dd>
                        
                        <dt class="col-sm-4">Météo</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($session['meteo'] ?? 'N/A'); ?></dd>
                        
                        <dt class="col-sm-4">Température</dt>
                        <dd class="col-sm-8">
                            Air: <?php echo $session['temperature_air'] ? $session['temperature_air'] . '°C' : 'N/A'; ?><br>
                            Piste: <?php echo $session['temperature_piste'] ? $session['temperature_piste'] . '°C' : 'N/A'; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- Statistiques -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistiques</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stat-value"><?php echo $total_laps; ?></div>
                            <div class="stat-label">Tours</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-value"><?php echo $best_lap ? formatTime($best_lap['temps_tour']) : 'N/A'; ?></div>
                            <div class="stat-label">Meilleur temps</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-value"><?php echo $best_lap ? formatSpeed($best_lap['vitesse_max']) : 'N/A'; ?></div>
                            <div class="stat-label">Vitesse max</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-value"><?php echo $average_speed ? formatSpeed($average_speed) : 'N/A'; ?></div>
                            <div class="stat-label">Vitesse moyenne</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Réglages -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Réglages</h5>
                </div>
                <div class="card-body">
                    <?php if ($session['precharge_avant']): ?>
                        <h6>Suspension avant</h6>
                        <dl class="row mb-2">
                            <dt class="col-sm-6">Précharge</dt>
                            <dd class="col-sm-6"><?php echo $session['precharge_avant']; ?> mm</dd>
                            
                            <dt class="col-sm-6">Détente</dt>
                            <dd class="col-sm-6"><?php echo $session['detente_avant']; ?> clics</dd>
                            
                            <dt class="col-sm-6">Compression</dt>
                            <dd class="col-sm-6"><?php echo $session['compression_avant']; ?> clics</dd>
                            
                            <dt class="col-sm-6">Hauteur</dt>
                            <dd class="col-sm-6"><?php echo $session['hauteur_avant']; ?> mm</dd>
                        </dl>
                        
                        <h6>Suspension arrière</h6>
                        <dl class="row mb-2">
                            <dt class="col-sm-6">Précharge</dt>
                            <dd class="col-sm-6"><?php echo $session['precharge_arriere']; ?> mm</dd>
                            
                            <dt class="col-sm-6">Détente</dt>
                            <dd class="col-sm-6"><?php echo $session['detente_arriere']; ?> clics</dd>
                            
                            <dt class="col-sm-6">Compression</dt>
                            <dd class="col-sm-6"><?php echo $session['compression_arriere']; ?> clics</dd>
                            
                            <dt class="col-sm-6">Hauteur</dt>
                            <dd class="col-sm-6"><?php echo $session['hauteur_arriere']; ?> mm</dd>
                        </dl>
                        
                        <h6>Autres</h6>
                        <dl class="row mb-0">
                            <dt class="col-sm-6">Rapport final</dt>
                            <dd class="col-sm-6"><?php echo $session['rapport_final']; ?></dd>
                            
                            <dt class="col-sm-6">Pression avant</dt>
                            <dd class="col-sm-6"><?php echo $session['pression_pneu_avant']; ?> bar</dd>
                            
                            <dt class="col-sm-6">Pression arrière</dt>
                            <dd class="col-sm-6"><?php echo $session['pression_pneu_arriere']; ?> bar</dd>
                        </dl>
                    <?php else: ?>
                        <p class="text-muted mb-0">Aucun réglage enregistré pour cette session.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Liste des tours -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Tours</h5>
        </div>
        <div class="card-body">
            <?php if (empty($laps)): ?>
                <p class="text-muted mb-0">Aucun tour enregistré pour cette session.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tour</th>
                                <th>Temps</th>
                                <th>Vitesse max</th>
                                <th>Vitesse moyenne</th>
                                <th>Angle max</th>
                                <th>Accélération moyenne</th>
                                <th>Freinage moyen</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laps as $lap): ?>
                                <tr class="<?php echo $lap['id'] === $best_lap['id'] ? 'table-success' : ''; ?>">
                                    <td><?php echo $lap['numero_tour']; ?></td>
                                    <td><?php echo formatTime($lap['temps_tour']); ?></td>
                                    <td><?php echo formatSpeed($lap['vitesse_max']); ?></td>
                                    <td><?php echo formatSpeed($lap['vitesse_moyenne']); ?></td>
                                    <td><?php echo formatAngle($lap['angle_max']); ?></td>
                                    <td><?php echo $lap['acceleration_moyenne']; ?> g</td>
                                    <td><?php echo $lap['freinage_moyen']; ?> g</td>
                                    <td>
                                        <?php if ($lap['video_url']): ?>
                                            <a href="<?php echo htmlspecialchars($lap['video_url']); ?>" 
                                               class="btn btn-sm btn-primary" 
                                               target="_blank"
                                               title="Voir la vidéo">
                                                <i class="fas fa-video"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Notes -->
    <?php if ($session['notes']): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Notes</h5>
            </div>
            <div class="card-body">
                <?php echo nl2br(htmlspecialchars($session['notes'])); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?> 