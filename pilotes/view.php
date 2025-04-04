<?php
$page_title = 'Pilot Profile';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

try {
    // Récupérer les informations du pilote
    $stmt = $pdo->prepare("SELECT * FROM pilotes WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $pilote = $stmt->fetch();

    if (!$pilote) {
        header('Location: list.php');
        exit;
    }

    // Récupérer les statistiques
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT s.id) as total_sessions,
            COUNT(DISTINCT s.circuit_id) as unique_circuits,
            COUNT(DISTINCT s.moto_id) as bikes_used,
            AVG(s.temps_moyen) as avg_time
        FROM sessions s
        WHERE s.pilote_id = ?
    ");
    $stmt->execute([$pilote['id']]);
    $stats = $stmt->fetch();

    // Récupérer les dernières sessions
    $stmt = $pdo->prepare("
        SELECT s.*, c.nom as circuit_nom, m.marque, m.modele
        FROM sessions s
        JOIN circuits c ON s.circuit_id = c.id
        JOIN motos m ON s.moto_id = m.id
        WHERE s.pilote_id = ?
        ORDER BY s.date DESC
        LIMIT 5
    ");
    $stmt->execute([$pilote['id']]);
    $sessions = $stmt->fetchAll();

    // Récupérer les motos utilisées
    $stmt = $pdo->prepare("
        SELECT 
            m.marque,
            m.modele,
            COUNT(s.id) as sessions_count,
            MAX(s.date) as last_used
        FROM sessions s
        JOIN motos m ON s.moto_id = m.id
        WHERE s.pilote_id = ?
        GROUP BY m.id
        ORDER BY sessions_count DESC
    ");
    $stmt->execute([$pilote['id']]);
    $bikes = $stmt->fetchAll();

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="content-wrapper">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php else: ?>
        <!-- Profile Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <div class="avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 40px;">
                            <?php echo strtoupper(substr($pilote['prenom'], 0, 1) . substr($pilote['nom'], 0, 1)); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h2 class="mb-1"><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></h2>
                        <p class="text-muted mb-2"><?php echo htmlspecialchars($pilote['pseudo']); ?></p>
                        <div class="d-flex gap-2">
                            <?php
                            $badge_class = match($pilote['niveau']) {
                                'Débutant' => 'badge-beginner',
                                'Intermédiaire' => 'badge-intermediate',
                                'Avancé' => 'badge-advanced',
                                'Expert' => 'badge-expert',
                                'Professionnel' => 'badge-professional',
                                default => 'badge-secondary'
                            };
                            ?>
                            <span class="badge <?php echo $badge_class; ?>">
                                <?php echo htmlspecialchars($pilote['niveau']); ?>
                            </span>
                            <?php if ($pilote['licence']): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i>
                                    Licensed
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group">
                            <a href="edit.php?id=<?php echo $pilote['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i>
                                Edit
                            </a>
                            <a href="list.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Information -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Riding Style</label>
                            <p class="mb-0"><?php echo htmlspecialchars($pilote['style_pilotage']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Experience</label>
                            <p class="mb-0"><?php echo htmlspecialchars($pilote['experience_annees']); ?> years</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Morphology</label>
                            <p class="mb-0"><?php echo htmlspecialchars($pilote['taille']); ?> cm / <?php echo htmlspecialchars($pilote['poids']); ?> kg</p>
                        </div>
                        <?php if ($pilote['licence']): ?>
                            <div class="mb-3">
                                <label class="text-muted small">License Number</label>
                                <p class="mb-0"><?php echo htmlspecialchars($pilote['licence_numero']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Expiration Date</label>
                                <p class="mb-0"><?php echo date('d/m/Y', strtotime($pilote['licence_date_expiration'])); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($pilote['commentaires'])): ?>
                            <div class="mb-3">
                                <label class="text-muted small">Comments</label>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($pilote['commentaires'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3><?php echo $stats['total_sessions']; ?></h3>
                            <p>Number of sessions</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3><?php echo $stats['unique_circuits']; ?></h3>
                            <p>Number of unique circuits</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3><?php echo $stats['bikes_used']; ?></h3>
                            <p>Number of bikes used</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3><?php echo number_format($stats['avg_time'], 2); ?>s</h3>
                            <p>Average time</p>
                        </div>
                    </div>
                </div>

                <!-- Latest Sessions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Latest Sessions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($sessions)): ?>
                            <div class="text-center py-3">
                                <p class="text-muted mb-0">No sessions recorded yet</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Circuit</th>
                                            <th>Bike</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sessions as $session): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($session['circuit_nom']); ?></td>
                                                <td><?php echo htmlspecialchars($session['marque'] . ' ' . $session['modele']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($session['date'])); ?></td>
                                                <td><?php echo number_format($session['temps_moyen'], 2); ?>s</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bikes Used -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Bikes Used</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($bikes)): ?>
                            <div class="text-center py-3">
                                <p class="text-muted mb-0">No bikes used yet</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Bike</th>
                                            <th>Sessions</th>
                                            <th>Last Used</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bikes as $bike): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($bike['marque'] . ' ' . $bike['modele']); ?></td>
                                                <td><?php echo $bike['sessions_count']; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($bike['last_used'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?> 