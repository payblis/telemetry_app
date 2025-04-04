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

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Pilots List</h5>
    <a href="add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Pilot
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (empty($pilotes)): ?>
    <div class="card">
        <div class="card-body">
            <p>No pilots registered yet.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Level</th>
                            <th>Experience</th>
                            <th>License</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pilotes as $pilote): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></td>
                                <td><?php echo htmlspecialchars($pilote['niveau']); ?></td>
                                <td><?php echo htmlspecialchars($pilote['experience_annees']); ?> years</td>
                                <td>
                                    <?php if ($pilote['licence']): ?>
                                        <span class="badge bg-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="view.php?id=<?php echo $pilote['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?php echo $pilote['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $pilote['id']; ?>" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>