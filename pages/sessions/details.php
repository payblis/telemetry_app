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
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur lors de la récupération des détails de la session : " . $e->getMessage());
}
?>

<div class="container">
    <div class="page-header">
        <h1>Détails de la Session</h1>
        <a href="index.php?page=sessions" class="btn btn-secondary">Retour à la liste</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($session): ?>
    <div class="row">
        <!-- Informations principales -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Informations générales</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Date :</strong> <?php echo date('d/m/Y', strtotime($session['date'])); ?>
                        </li>
                        <li class="mb-2">
                            <strong>Type :</strong> <?php echo htmlspecialchars($session['session_type']); ?>
                        </li>
                        <li class="mb-2">
                            <strong>Pilote :</strong> <?php echo htmlspecialchars($session['pilot_name']); ?>
                        </li>
                        <li class="mb-2">
                            <strong>Moto :</strong> <?php echo htmlspecialchars($session['moto_brand'] . ' ' . $session['moto_model']); ?>
                        </li>
                        <li class="mb-2">
                            <strong>Circuit :</strong> <?php echo htmlspecialchars($session['circuit_name']); ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Conditions de la session -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Conditions</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Météo :</strong> <?php echo htmlspecialchars($session['weather']); ?>
                        </li>
                        <li class="mb-2">
                            <strong>Température piste :</strong> <?php echo htmlspecialchars($session['track_temp']); ?> °C
                        </li>
                        <li class="mb-2">
                            <strong>Température air :</strong> <?php echo htmlspecialchars($session['air_temp']); ?> °C
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes -->
    <?php if (!empty($session['notes'])): ?>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Notes</h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($session['notes'])); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex gap-2">
                <a href="index.php?page=sessions/edit&id=<?php echo $sessionId; ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
                <a href="index.php?page=sessions/delete&id=<?php echo $sessionId; ?>" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Supprimer
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div> 