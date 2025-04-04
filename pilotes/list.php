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

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Pilots List</h5>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Pilot
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (empty($pilotes)): ?>
            <p>No pilots registered yet.</p>
        <?php else: ?>
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
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>