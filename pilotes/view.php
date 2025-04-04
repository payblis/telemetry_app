<?php
$page_title = 'Pilot Profile';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

try {
    // Get pilot information
    $stmt = $pdo->prepare("SELECT * FROM pilotes WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $pilote = $stmt->fetch();

    if (!$pilote) {
        header('Location: list.php');
        exit;
    }

    // Get statistics
    $stats = [
        'sessions' => 0,
        'circuits' => 0,
        'motos' => 0,
        'temps_moyen' => 0,
        'ressenti_moyen' => 0
    ];

    // Number of sessions
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sessions WHERE pilote_id = ?");
    $stmt->execute([$pilote['id']]);
    $stats['sessions'] = $stmt->fetchColumn();

    // Number of unique circuits
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT circuit_id) FROM sessions WHERE pilote_id = ?");
    $stmt->execute([$pilote['id']]);
    $stats['circuits'] = $stmt->fetchColumn();

    // Number of bikes used
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT moto_id) FROM sessions WHERE pilote_id = ?");
    $stmt->execute([$pilote['id']]);
    $stats['motos'] = $stmt->fetchColumn();

    // Average time and feeling
    if ($stats['sessions'] > 0) {
        $stmt = $pdo->prepare("SELECT AVG(temps_tour) as temps_moyen, AVG(ressenti) as ressenti_moyen FROM sessions WHERE pilote_id = ?");
        $stmt->execute([$pilote['id']]);
        $moyennes = $stmt->fetch();
        $stats['temps_moyen'] = round($moyennes['temps_moyen'], 2);
        $stats['ressenti_moyen'] = round($moyennes['ressenti_moyen'], 1);
    }

    // Latest sessions
    $stmt = $pdo->prepare("
        SELECT s.*, c.nom as circuit_nom, m.marque, m.modele 
        FROM sessions s
        LEFT JOIN circuits c ON s.circuit_id = c.id
        LEFT JOIN motos m ON s.moto_id = m.id
        WHERE s.pilote_id = ?
        ORDER BY s.date DESC
        LIMIT 5
    ");
    $stmt->execute([$pilote['id']]);
    $dernieres_sessions = $stmt->fetchAll();

    // Bikes used
    $stmt = $pdo->prepare("
        SELECT m.*, COUNT(s.id) as nb_sessions
        FROM motos m
        JOIN sessions s ON m.id = s.moto_id
        WHERE s.pilote_id = ?
        GROUP BY m.id
        ORDER BY nb_sessions DESC
    ");
    $stmt->execute([$pilote['id']]);
    $motos_utilisees = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Error retrieving data: " . $e->getMessage();
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Pilot Profile</h5>
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
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <div class="avatar-circle bg-primary-light mx-auto mb-3" style="width: 100px; height: 100px;">
                                <span class="avatar-initials" style="font-size: 2.5rem;">
                                    <?php echo strtoupper(substr($pilote['prenom'], 0, 1) . substr($pilote['nom'], 0, 1)); ?>
                                </span>
                            </div>
                            <h4 class="mb-1"><?php echo htmlspecialchars($pilote['nom'] . ' ' . $pilote['prenom']); ?></h4>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($pilote['pseudo']); ?></p>
                            <div class="d-flex justify-content-center gap-2 mb-3">
                                <span class="badge badge-pill badge-<?php 
                                    echo match($pilote['niveau']) {
                                        'Professionnel' => 'danger',
                                        'Expert' => 'warning',
                                        'Avancé' => 'info',
                                        'Intermédiaire' => 'success',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo htmlspecialchars($pilote['niveau']); ?>
                                </span>
                                <?php if ($pilote['licence']): ?>
                                    <span class="badge badge-pill badge-success">
                                        <i class="fas fa-check-circle"></i>
                                        License
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted">Riding Style</label>
                                <p class="mb-0"><?php echo htmlspecialchars($pilote['style_pilotage']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted">Experience</label>
                                <p class="mb-0"><?php echo htmlspecialchars($pilote['experience_annees']); ?> years</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted">Morphology</label>
                                <p class="mb-0"><?php echo htmlspecialchars($pilote['taille']); ?> cm / <?php echo htmlspecialchars($pilote['poids']); ?> kg</p>
                            </div>
                            <?php if ($pilote['licence']): ?>
                                <div class="mb-3">
                                    <label class="text-muted">License</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($pilote['licence_numero']); ?></p>
                                    <?php if ($pilote['licence_date_expiration']): ?>
                                        <small class="text-muted">Expires on <?php echo date('d/m/Y', strtotime($pilote['licence_date_expiration'])); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($pilote['commentaires']): ?>
                                <div>
                                    <label class="text-muted">Comments</label>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($pilote['commentaires'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">Statistics</h6>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <div class="text-center">
                                                <h3 class="mb-1"><?php echo $stats['sessions']; ?></h3>
                                                <small class="text-muted">Sessions</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="text-center">
                                                <h3 class="mb-1"><?php echo $stats['circuits']; ?></h3>
                                                <small class="text-muted">Circuits</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center">
                                                <h3 class="mb-1"><?php echo $stats['motos']; ?></h3>
                                                <small class="text-muted">Bikes</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center">
                                                <h3 class="mb-1"><?php echo $stats['temps_moyen']; ?>s</h3>
                                                <small class="text-muted">Avg. Time</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">Latest Sessions</h6>
                                    <?php if (empty($dernieres_sessions)): ?>
                                        <p class="text-muted mb-0">No sessions recorded</p>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($dernieres_sessions as $session): ?>
                                                <div class="list-group-item px-0">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($session['circuit_nom']); ?></h6>
                                                            <small class="text-muted">
                                                                <?php echo htmlspecialchars($session['marque'] . ' ' . $session['modele']); ?> •
                                                                <?php echo date('d/m/Y', strtotime($session['date'])); ?>
                                                            </small>
                                                        </div>
                                                        <span class="badge badge-pill badge-primary">
                                                            <?php echo $session['temps_tour']; ?>s
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Bikes Used</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($motos_utilisees)): ?>
                                <p class="text-muted mb-0">No bikes recorded</p>
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
                                            <?php foreach ($motos_utilisees as $moto): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-circle bg-primary-light mr-3">
                                                                <i class="fas fa-motorcycle"></i>
                                                            </div>
                                                            <div>
                                                                <div class="font-weight-bold"><?php echo htmlspecialchars($moto['marque'] . ' ' . $moto['modele']); ?></div>
                                                                <small class="text-muted"><?php echo htmlspecialchars($moto['annee']); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo $moto['nb_sessions']; ?></td>
                                                    <td>
                                                        <?php
                                                        $stmt = $pdo->prepare("SELECT MAX(date) as derniere_date FROM sessions WHERE moto_id = ? AND pilote_id = ?");
                                                        $stmt->execute([$moto['id'], $pilote['id']]);
                                                        $derniere_date = $stmt->fetchColumn();
                                                        echo $derniere_date ? date('d/m/Y', strtotime($derniere_date)) : '-';
                                                        ?>
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
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .avatar-initials {
        color: var(--primary);
        font-weight: 600;
    }

    .badge {
        padding: .5em .75em;
        font-size: .75rem;
        font-weight: 600;
    }

    .badge-pill {
        border-radius: 50rem;
    }

    .badge-danger {
        background-color: var(--danger);
        color: white;
    }

    .badge-warning {
        background-color: var(--warning);
        color: white;
    }

    .badge-info {
        background-color: var(--info);
        color: white;
    }

    .badge-success {
        background-color: var(--success);
        color: white;
    }

    .badge-secondary {
        background-color: var(--secondary);
        color: white;
    }

    .badge-primary {
        background-color: var(--primary);
        color: white;
    }

    .btn-group {
        display: flex;
        gap: .5rem;
    }

    .text-muted {
        color: #6c757d;
    }

    .mb-0 {
        margin-bottom: 0;
    }

    .mb-1 {
        margin-bottom: 0.25rem;
    }

    .mb-3 {
        margin-bottom: 1rem;
    }

    .mb-4 {
        margin-bottom: 1.5rem;
    }

    .mx-auto {
        margin-left: auto;
        margin-right: auto;
    }

    .mr-3 {
        margin-right: 1rem;
    }

    .gap-2 {
        gap: 0.5rem;
    }

    .list-group-item {
        border: none;
        padding: 0.75rem 0;
    }

    .list-group-item:first-child {
        padding-top: 0;
    }

    .list-group-item:last-child {
        padding-bottom: 0;
    }
</style>

<?php require_once '../includes/footer.php'; ?> 