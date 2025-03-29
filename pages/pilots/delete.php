<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Vérifier si l'ID du pilote est fourni
if (!isset($_GET['id'])) {
    header('Location: index.php?page=pilots');
    exit();
}

$pilotId = intval($_GET['id']);

// Initialiser les variables
$error = null;
$success = null;
$pilot = null;

try {
    // Charger la classe Pilot
    require_once 'classes/Pilot.php';
    $pilotObj = new Pilot();
    
    // Vérifier si le pilote appartient à l'utilisateur
    if (!$pilotObj->belongsToUser($pilotId, $_SESSION['user_id'])) {
        throw new Exception("Vous n'avez pas accès à ce pilote.");
    }
    
    // Récupérer les données du pilote
    $pilot = $pilotObj->getById($pilotId);
    if (!$pilot) {
        throw new Exception("Pilote non trouvé.");
    }
    
    // Traitement de la suppression
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
        if ($pilotObj->delete($pilotId)) {
            $success = "Pilote supprimé avec succès !";
            // Rediriger après 2 secondes
            header("refresh:2;url=index.php?page=pilots");
        } else {
            throw new Exception("Erreur lors de la suppression du pilote.");
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur lors de la suppression du pilote : " . $e->getMessage());
}
?>

<div class="container">
    <div class="page-header">
        <h1>Supprimer un Pilote</h1>
        <a href="index.php?page=pilots" class="btn btn-secondary">Retour à la liste</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($pilot && !$success): ?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Confirmation de suppression</h5>
                    <p class="card-text">
                        Êtes-vous sûr de vouloir supprimer le pilote <strong><?php echo htmlspecialchars($pilot['name']); ?></strong> ?
                        Cette action est irréversible et supprimera également toutes les sessions associées à ce pilote.
                    </p>
                    
                    <form method="POST" class="mt-4">
                        <div class="d-flex gap-2">
                            <button type="submit" name="confirm" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Confirmer la suppression
                            </button>
                            <a href="index.php?page=pilots" class="btn btn-secondary">
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