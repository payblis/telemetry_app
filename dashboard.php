<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$page_title = 'Tableau de bord';

// Récupération des statistiques
try {
    // Nombre total de sessions
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sessions WHERE pilote_id IN (SELECT id FROM pilotes WHERE user_id = ?)");
    $stmt->execute([$_SESSION['user_id']]);
    $total_sessions = $stmt->fetchColumn();

    // Nombre total de tours
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM laps l 
        JOIN sessions s ON l.session_id = s.id 
        WHERE s.pilote_id IN (SELECT id FROM pilotes WHERE user_id = ?)
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $total_laps = $stmt->fetchColumn();

    // Meilleur temps
    $stmt = $pdo->prepare("
        SELECT MIN(temps_tour) 
        FROM laps l 
        JOIN sessions s ON l.session_id = s.id 
        WHERE s.pilote_id IN (SELECT id FROM pilotes WHERE user_id = ?)
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $best_time = $stmt->fetchColumn();

    // Dernières sessions
    $stmt = $pdo->prepare("
        SELECT s.*, c.nom as circuit_nom, m.marque, m.modele 
        FROM sessions s 
        JOIN circuits c ON s.circuit_id = c.id 
        JOIN motos m ON s.moto_id = m.id 
        WHERE s.pilote_id IN (SELECT id FROM pilotes WHERE user_id = ?) 
        ORDER BY s.date_session DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_sessions = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des statistiques: " . $e->getMessage());
    $error = "Une erreur est survenue lors du chargement des données.";
}

require_once 'includes/header.php';
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-value"><?php echo $total_sessions; ?></div>
        <div class="stat-label">Sessions</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $total_laps; ?></div>
        <div class="stat-label">Tours</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $best_time ? formatTime($best_time) : 'N/A'; ?></div>
        <div class="stat-label">Meilleur temps</div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Dernières sessions</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Circuit</th>
                                    <th>Moto</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_sessions as $session): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($session['date_session'])); ?></td>
                                        <td><?php echo htmlspecialchars($session['circuit_nom']); ?></td>
                                        <td><?php echo htmlspecialchars($session['marque'] . ' ' . $session['modele']); ?></td>
                                        <td><?php echo htmlspecialchars($session['type_session']); ?></td>
                                        <td>
                                            <a href="sessions/view.php?id=<?php echo $session['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Actions rapides</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="sessions/create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouvelle session
                    </a>
                    <a href="pilotes/" class="btn btn-secondary">
                        <i class="fas fa-user"></i> Gérer les pilotes
                    </a>
                    <a href="motos/" class="btn btn-secondary">
                        <i class="fas fa-motorcycle"></i> Gérer les motos
                    </a>
                    <a href="circuits/" class="btn btn-secondary">
                        <i class="fas fa-route"></i> Gérer les circuits
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>