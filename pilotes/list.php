<?php
// list.php

$page_title = 'Pilots Management';
require_once '../includes/header.php';

// Récupération des pilotes de l'utilisateur connecté
try {
    $stmt = $pdo->prepare("SELECT * FROM pilotes WHERE user_id = ? ORDER BY nom, prenom");
    $stmt->execute([$_SESSION['user_id']]);
    $pilotes = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error retrieving pilots: " . $e->getMessage();
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Pilots List</h5>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add Pilot
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif (empty($pilotes)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5>No Pilots Found</h5>
                <p class="text-muted">Start by adding your first pilot</p>
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Pilot
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pilot</th>
                            <th>Level</th>
                            <th>Experience</th>
                            <th>License</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pilotes as $pilote): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary-light mr-3">
                                            <span class="avatar-initials">
                                                <?php echo strtoupper(substr($pilote['prenom'], 0, 1) . substr($pilote['nom'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold"><?php echo htmlspecialchars($pilote['nom'] . ' ' . $pilote['prenom']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($pilote['pseudo']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
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
                                </td>
                                <td><?php echo htmlspecialchars($pilote['experience_annees']); ?> years</td>
                                <td>
                                    <?php if ($pilote['licence']): ?>
                                        <span class="badge badge-pill badge-success">
                                            <i class="fas fa-check-circle"></i>
                                            Valid
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-pill badge-secondary">
                                            <i class="fas fa-times-circle"></i>
                                            None
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="view.php?id=<?php echo $pilote['id']; ?>" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?php echo $pilote['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $pilote['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this pilot?')">
                                            <i class="fas fa-trash"></i>
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
        font-size: 1rem;
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

    .btn-group {
        display: flex;
        gap: .25rem;
    }

    .text-center {
        text-align: center;
    }

    .py-5 {
        padding-top: 3rem;
        padding-bottom: 3rem;
    }

    .ml-3 {
        margin-left: 1rem;
    }

    .font-weight-bold {
        font-weight: 600;
    }

    .text-success {
        color: var(--success);
    }

    .text-muted {
        color: #6c757d;
    }

    .fa-3x {
        font-size: 3rem;
    }

    .mb-3 {
        margin-bottom: 1rem;
    }
</style>

<?php require_once '../includes/footer.php'; ?>