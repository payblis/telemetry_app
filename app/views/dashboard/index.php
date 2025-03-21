<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Tableau de bord</h1>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Sessions totales</h5>
                    <h2 class="card-text"><?php echo $totalSessions; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Pilotes</h5>
                    <h2 class="card-text"><?php echo $totalPilots; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Dernières sessions -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Dernières sessions</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($lastSessions)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Pilote</th>
                                        <th>Circuit</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lastSessions as $session): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($session['date_session'])); ?></td>
                                            <td><?php echo htmlspecialchars($session['pilot_name']); ?></td>
                                            <td><?php echo htmlspecialchars($session['circuit_name']); ?></td>
                                            <td>
                                                <a href="index.php?route=session&id=<?php echo $session['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> Voir
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Aucune session récente</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="index.php?route=session/new" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nouvelle session
                        </a>
                        <a href="index.php?route=pilote/new" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus"></i> Ajouter un pilote
                        </a>
                        <a href="index.php?route=moto/new" class="btn btn-outline-primary">
                            <i class="fas fa-motorcycle"></i> Ajouter une moto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    margin-bottom: 1rem;
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: white;
    border-bottom: 1px solid rgba(0,0,0,.125);
}

.table th {
    border-top: none;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style> 