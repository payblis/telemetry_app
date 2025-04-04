<?php
$page_title = 'Delete Pilot';
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("DELETE FROM pilotes WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
        
        header('Location: list.php?success=2');
        exit;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Delete Pilot</h5>
        <a href="list.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php else: ?>
            <div class="alert alert-warning">
                Are you sure you want to delete this pilot? This action cannot be undone.
            </div>
            <form method="POST">
                <div class="text-end">
                    <a href="list.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 