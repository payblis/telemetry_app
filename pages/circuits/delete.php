<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Vérifier si l'ID du circuit est fourni
if (!isset($_GET['id'])) {
    header('Location: index.php?page=circuits');
    exit();
}

$circuitId = intval($_GET['id']);

// Initialiser les variables
$error = null;
$success = null;
$circuit = null;

try {
    // Charger la classe Circuit
    require_once 'classes/Circuit.php';
    $circuitObj = new Circuit();
    
    // Vérifier si le circuit appartient à l'utilisateur
    if (!$circuitObj->belongsToUser($circuitId, $_SESSION['user_id'])) {
        throw new Exception("Vous n'avez pas accès à ce circuit.");
    }
    
    // Récupérer les données du circuit
    $circuit = $circuitObj->getById($circuitId);
    if (!$circuit) {
        throw new Exception("Circuit non trouvé.");
    }
    
    // Traitement de la suppression
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
        if ($circuitObj->delete($circuitId)) {
            $success = "Circuit supprimé avec succès !";
            // Rediriger après 2 secondes
            header("refresh:2;url=index.php?page=circuits");
        } else {
            throw new Exception("Erreur lors de la suppression du circuit.");
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur lors de la suppression du circuit : " . $e->getMessage());
}
?>

<div class="container">
    <div class="page-header">
        <h1>Supprimer un Circuit</h1>
        <a href="index.php?page=circuits" class="btn btn-secondary">Retour à la liste</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($circuit && !$success): ?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Confirmation de suppression</h5>
                    <p class="card-text">
                        Êtes-vous sûr de vouloir supprimer le circuit <strong><?php echo htmlspecialchars($circuit['name']); ?></strong> ?
                        Cette action est irréversible et supprimera également toutes les sessions associées à ce circuit.
                    </p>
                    
                    <form method="POST" class="mt-4">
                        <div class="d-flex gap-2">
                            <button type="submit" name="confirm" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Confirmer la suppression
                            </button>
                            <a href="index.php?page=circuits" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div> 