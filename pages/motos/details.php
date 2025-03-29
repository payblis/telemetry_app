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
$moto = null;
$sessions = [];

try {
    // Charger les classes nécessaires
    require_once 'classes/Moto.php';
    require_once 'classes/Session.php';
    
    $motoObj = new Moto();
    $sessionObj = new Session();
    
    // Vérifier si la moto appartient à l'utilisateur
    if (!$motoObj->belongsToUser($motoId, $_SESSION['user_id'])) {
        throw new Exception("Vous n'avez pas accès à cette moto.");
    }
    
    // Récupérer les données de la moto
    $moto = $motoObj->getById($motoId);
    if (!$moto) {
        throw new Exception("Moto non trouvée.");
    }
    
    // Récupérer les sessions de la moto
    $sessions = $sessionObj->getByMotoId($motoId);
    
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur lors de la récupération des détails de la moto : " . $e->getMessage());
}
?>

<div class="container">
    <div class="page-header">
        <h1>Détails de la Moto</h1>
        <a href="index.php?page=motos" class="btn btn-secondary">Retour à la liste</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($moto): ?>
    <div class="row">
        <!-- Informations de la moto -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Informations techniques</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Marque :</strong> <?php echo htmlspecialchars($moto['brand']); ?>
                        </li>
                        <li class="mb-2">
                            <strong>Modèle :</strong> <?php echo htmlspecialchars($moto['model']); ?>
                        </li>
                        <li class="mb-2">
                            <strong>Cylindrée :</strong> <?php echo htmlspecialchars($moto['engine_capacity']); ?> cc
                        </li>
                        <li class="mb-2">
                            <strong>Année :</strong> <?php echo htmlspecialchars($moto['year']); ?>
                        </li>
                    </ul>
                    <div class="mt-3">
                        <a href="index.php?page=motos/edit&id=<?php echo $motoId; ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sessions de la moto -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Sessions</h5>
                        <a href="index.php?page=sessions/add&moto_id=<?php echo $motoId; ?>" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Nouvelle session
                        </a>
                    </div>

                    <?php if (empty($sessions)): ?>
                        <p class="text-muted">Aucune session enregistrée pour cette moto.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Pilote</th>
                                        <th>Circuit</th>
                                        <th>Conditions</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sessions as $session): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($session['date'])); ?></td>
                                            <td><?php echo htmlspecialchars($session['session_type']); ?></td>
                                            <td><?php echo htmlspecialchars($session['pilot_name']); ?></td>
                                            <td><?php echo htmlspecialchars($session['circuit_name']); ?></td>
                                            <td><?php echo htmlspecialchars($session['weather']); ?></td>
                                            <td>
                                                <a href="index.php?page=sessions/details&id=<?php echo $session['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
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
        </div>
    </div>
    <?php endif; ?>
</div> 