<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Vérifier si l'ID de la session est fourni
if (!isset($_GET['id'])) {
    header('Location: index.php?page=sessions');
    exit();
}

$sessionId = intval($_GET['id']);

// Initialiser les variables
$error = null;
$success = null;
$session = null;

try {
    // Charger la classe Session
    require_once 'classes/Session.php';
    $sessionObj = new Session();
    
    // Vérifier si la session appartient à l'utilisateur
    if (!$sessionObj->belongsToUser($sessionId, $_SESSION['user_id'])) {
        throw new Exception("Vous n'avez pas accès à cette session.");
    }
    
    // Récupérer les données de la session
    $session = $sessionObj->getById($sessionId);
    if (!$session) {
        throw new Exception("Session non trouvée.");
    }
    
    // Traitement de la suppression
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
        if ($sessionObj->delete($sessionId)) {
            $success = "Session supprimée avec succès !";
            // Rediriger après 2 secondes
            header("refresh:2;url=index.php?page=sessions");
        } else {
            throw new Exception("Erreur lors de la suppression de la session.");
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur lors de la suppression de la session : " . $e->getMessage());
}
?>

<div class="container">
    <div class="page-header">
        <h1>Supprimer une Session</h1>
        <a href="index.php?page=sessions" class="btn btn-secondary">Retour à la liste</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($session && !$success): ?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Confirmation de suppression</h5>
                    <p class="card-text">
                        Êtes-vous sûr de vouloir supprimer la session du <strong><?php echo date('d/m/Y', strtotime($session['date'])); ?></strong> ?
                        <br>
                        <strong>Pilote :</strong> <?php echo htmlspecialchars($session['pilot_name']); ?>
                        <br>
                        <strong>Moto :</strong> <?php echo htmlspecialchars($session['moto_brand'] . ' ' . $session['moto_model']); ?>
                        <br>
                        <strong>Circuit :</strong> <?php echo htmlspecialchars($session['circuit_name']); ?>
                    </p>
                    
                    <form method="POST" class="mt-4">
                        <div class="d-flex gap-2">
                            <button type="submit" name="confirm" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Confirmer la suppression
                            </button>
                            <a href="index.php?page=sessions" class="btn btn-secondary">
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