<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Vérifier si l'ID de la moto est fourni
if (!isset($_GET['id'])) {
    header('Location: index.php?page=motos');
    exit();
}

$motoId = intval($_GET['id']);

// Initialiser les variables
$error = null;
$success = null;
$moto = null;

try {
    // Charger la classe Moto
    require_once 'classes/Moto.php';
    $motoObj = new Moto();
    
    // Vérifier si la moto appartient à l'utilisateur
    if (!$motoObj->belongsToUser($motoId, $_SESSION['user_id'])) {
        throw new Exception("Vous n'avez pas accès à cette moto.");
    }
    
    // Récupérer les données de la moto
    $moto = $motoObj->getById($motoId);
    if (!$moto) {
        throw new Exception("Moto non trouvée.");
    }
    
    // Traitement de la suppression
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
        if ($motoObj->delete($motoId)) {
            $success = "Moto supprimée avec succès !";
            // Rediriger après 2 secondes
            header("refresh:2;url=index.php?page=motos");
        } else {
            throw new Exception("Erreur lors de la suppression de la moto.");
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur lors de la suppression de la moto : " . $e->getMessage());
}
?>

<div class="container">
    <div class="page-header">
        <h1>Supprimer une Moto</h1>
        <a href="index.php?page=motos" class="btn btn-secondary">Retour à la liste</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($moto && !$success): ?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Confirmation de suppression</h5>
                    <p class="card-text">
                        Êtes-vous sûr de vouloir supprimer la moto <strong><?php echo htmlspecialchars($moto['brand'] . ' ' . $moto['model']); ?></strong> ?
                        Cette action est irréversible et supprimera également toutes les sessions associées à cette moto.
                    </p>
                    
                    <form method="POST" class="mt-4">
                        <div class="d-flex gap-2">
                            <button type="submit" name="confirm" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Confirmer la suppression
                            </button>
                            <a href="index.php?page=motos" class="btn btn-secondary">
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