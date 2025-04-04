<?php
// list.php

$page_title = 'Pilots Management';
require_once '../includes/header.php';

// Récupération des pilotes de l'utilisateur connecté
try {
    $stmt = $pdo->prepare("SELECT * FROM pilotes WHERE user_id = ? ORDER BY nom, prenom");
    $stmt->execute([$_SESSION['user_id']]);
    $pilotes = $stmt->fetchAll();
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
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php
            switch ($_GET['success']) {
                case 1:
                    echo "Pilot added successfully!";
                    break;
                case 2:
                    echo "Pilot deleted successfully!";
                    break;
                case 3:
                    echo "Pilot updated successfully!";
                    break;
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Pilots List</h5>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Add Pilot
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($pilotes)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h4>No Pilots Registered</h4>
                    <p class="text-muted">Start by adding your first pilot profile</p>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Pilot
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                            <div class="avatar me-3">
                                                <?php echo strtoupper(substr($pilote['prenom'], 0, 1) . substr($pilote['nom'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($pilote['pseudo']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-muted me-2"></i>
                                            <?php echo htmlspecialchars($pilote['experience_annees']); ?> years
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($pilote['licence']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i>
                                                Licensed
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-times"></i>
                                                No License
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="view.php?id=<?php echo $pilote['id']; ?>" class="btn btn-sm btn-secondary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $pilote['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $pilote['id']; ?>" class="btn btn-sm btn-danger" title="Delete">
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