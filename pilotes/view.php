<?php
$page_title = 'Pilot Profile';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM pilotes WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $pilote = $stmt->fetch();

    if (!$pilote) {
        header('Location: list.php');
        exit;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Pilot Profile</h5>
        <div>
            <a href="edit.php?id=<?php echo $pilote['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="list.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-6">
                    <h6>Personal Information</h6>
                    <table class="table">
                        <tr>
                            <th>Name:</th>
                            <td><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></td>
                        </tr>
                        <tr>
                            <th>Nickname:</th>
                            <td><?php echo htmlspecialchars($pilote['pseudo']); ?></td>
                        </tr>
                        <tr>
                            <th>Level:</th>
                            <td><?php echo htmlspecialchars($pilote['niveau']); ?></td>
                        </tr>
                        <tr>
                            <th>Riding Style:</th>
                            <td><?php echo htmlspecialchars($pilote['style_pilotage']); ?></td>
                        </tr>
                        <tr>
                            <th>Experience:</th>
                            <td><?php echo htmlspecialchars($pilote['experience_annees']); ?> years</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Physical Information</h6>
                    <table class="table">
                        <tr>
                            <th>Height:</th>
                            <td><?php echo htmlspecialchars($pilote['taille']); ?> cm</td>
                        </tr>
                        <tr>
                            <th>Weight:</th>
                            <td><?php echo htmlspecialchars($pilote['poids']); ?> kg</td>
                        </tr>
                        <tr>
                            <th>Racing License:</th>
                            <td>
                                <?php if ($pilote['licence']): ?>
                                    <span class="badge bg-success">Yes</span>
                                    <div class="mt-2">
                                        <small>Number: <?php echo htmlspecialchars($pilote['licence_numero']); ?></small><br>
                                        <small>Expires: <?php echo date('d/m/Y', strtotime($pilote['licence_date_expiration'])); ?></small>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php if (!empty($pilote['commentaires'])): ?>
                <div class="mt-4">
                    <h6>Comments</h6>
                    <p><?php echo nl2br(htmlspecialchars($pilote['commentaires'])); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 