<?php
$page_title = 'Gestion des Pilotes';
require_once '../includes/header.php';

// Récupération des pilotes de l'utilisateur connecté
try {
    $stmt = $pdo->prepare("SELECT * FROM pilotes WHERE user_id = ? ORDER BY nom, prenom");
    $stmt->execute([$_SESSION['user_id']]);
    $pilotes = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des pilotes : " . $e->getMessage();
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Liste des Pilotes</h5>
        <a href="ajouter.php" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Ajouter un pilote
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif (empty($pilotes)): ?>
            <div class="text-center py-5">
                <i class="fas fa-user fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Aucun pilote enregistré</h4>
                <p class="text-muted">Commencez par ajouter votre premier pilote</p>
                <a href="ajouter.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Ajouter un pilote
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Pseudo</th>
                            <th>Niveau</th>
                            <th>Expérience</th>
                            <th>Style</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pilotes as $pilote): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary-light">
                                            <span class="avatar-initials">
                                                <?php echo strtoupper(substr($pilote['prenom'], 0, 1) . substr($pilote['nom'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <div class="font-weight-bold"><?php echo htmlspecialchars($pilote['nom'] . ' ' . $pilote['prenom']); ?></div>
                                            <?php if ($pilote['licence']): ?>
                                                <small class="text-success">
                                                    <i class="fas fa-check-circle"></i>
                                                    Licence
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($pilote['pseudo']); ?></td>
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
                                <td><?php echo htmlspecialchars($pilote['experience_annees']); ?> ans</td>
                                <td><?php echo htmlspecialchars($pilote['style_pilotage']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="modifier.php?id=<?php echo $pilote['id']; ?>" class="btn btn-sm btn-info" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="supprimer.php?id=<?php echo $pilote['id']; ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce pilote ?');">
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