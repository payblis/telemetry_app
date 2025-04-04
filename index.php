<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/auth.php';

checkAuth();

$page_title = 'Dashboard';
require_once 'includes/header.php';

// Récupérer les statistiques
try {
    // Nombre de pilotes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pilotes WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $nb_pilotes = $stmt->fetchColumn();

    // Nombre de motos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM motos m JOIN pilotes p ON m.pilote_id = p.id WHERE p.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $nb_motos = $stmt->fetchColumn();

    // Nombre de sessions
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sessions s JOIN pilotes p ON s.pilote_id = p.id WHERE p.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $nb_sessions = $stmt->fetchColumn();

    // Dernières sessions
    $stmt = $pdo->prepare("
        SELECT s.*, p.nom, p.prenom, c.nom as circuit_nom 
        FROM sessions s 
        JOIN pilotes p ON s.pilote_id = p.id 
        JOIN circuits c ON s.circuit_id = c.id 
        WHERE p.user_id = ? 
        ORDER BY s.date_session DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $dernieres_sessions = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des données : " . $e->getMessage();
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Pilotes</h6>
                        <h3 class="mb-0"><?php echo $nb_pilotes; ?></h3>
                    </div>
                    <div class="icon-box bg-primary-light">
                        <i class="fas fa-user text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Motos</h6>
                        <h3 class="mb-0"><?php echo $nb_motos; ?></h3>
                    </div>
                    <div class="icon-box bg-success-light">
                        <i class="fas fa-motorcycle text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Sessions</h6>
                        <h3 class="mb-0"><?php echo $nb_sessions; ?></h3>
                    </div>
                    <div class="icon-box bg-info-light">
                        <i class="fas fa-clock text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Dernières sessions</h5>
        <a href="sessions/" class="btn btn-primary btn-sm">Voir toutes les sessions</a>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif (empty($dernieres_sessions)): ?>
            <p class="text-muted">Aucune session enregistrée</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Pilote</th>
                            <th>Circuit</th>
                            <th>Type</th>
                            <th>Météo</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dernieres_sessions as $session): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($session['date_session'])); ?></td>
                                <td><?php echo htmlspecialchars($session['nom'] . ' ' . $session['prenom']); ?></td>
                                <td><?php echo htmlspecialchars($session['circuit_nom']); ?></td>
                                <td><?php echo htmlspecialchars($session['type_session']); ?></td>
                                <td><?php echo htmlspecialchars($session['meteo']); ?></td>
                                <td>
                                    <a href="sessions/detail.php?id=<?php echo $session['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
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

<style>
    .row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-primary-light {
        background-color: rgba(63,106,216,.1);
    }

    .bg-success-light {
        background-color: rgba(58,196,125,.1);
    }

    .bg-info-light {
        background-color: rgba(22,170,255,.1);
    }

    .text-primary {
        color: var(--primary);
    }

    .text-success {
        color: var(--success);
    }

    .text-info {
        color: var(--info);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        padding: .75rem;
        border-bottom: 1px solid #dee2e6;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        padding: .375rem .75rem;
        border-radius: .25rem;
        text-decoration: none;
        color: #fff;
        font-size: .875rem;
        line-height: 1.5;
    }

    .btn-sm {
        padding: .25rem .5rem;
        font-size: .75rem;
    }

    .btn-primary {
        background-color: var(--primary);
    }

    .btn-info {
        background-color: var(--info);
    }

    .btn i {
        margin-right: .25rem;
    }
</style>

<?php require_once 'includes/footer.php'; ?>