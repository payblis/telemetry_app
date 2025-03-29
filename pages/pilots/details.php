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
$pilot = null;
$sessions = [];

try {
    // Charger les classes nécessaires
    require_once 'classes/Pilot.php';
    require_once 'classes/Session.php';
    
    $pilotObj = new Pilot();
    $sessionObj = new Session();
    
    // Vérifier si le pilote appartient à l'utilisateur
    if (!$pilotObj->belongsToUser($pilotId, $_SESSION['user_id'])) {
        throw new Exception("Vous n'avez pas accès à ce pilote.");
    }
    
    // Récupérer les données du pilote
    $pilot = $pilotObj->getById($pilotId);
    if (!$pilot) {
        throw new Exception("Pilote non trouvé.");
    }
    
    // Récupérer les sessions du pilote
    $sessions = $sessionObj->getByPilotId($pilotId);
    
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur lors de la récupération des détails du pilote : " . $e->getMessage());
}
?>

<div class="container">
    <div class="page-header">
        <h1>Détails du Pilote</h1>
        <a href="index.php?page=pilots" class="btn btn-secondary">Retour à la liste</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($pilot): ?>
    <div class="row">
        <!-- Informations du pilote -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Informations personnelles</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Nom :</strong> <?php echo htmlspecialchars($pilot['name']); ?>
                        </li>
                        <li class="mb-2">
                            <strong>Taille :</strong> <?php echo htmlspecialchars($pilot['height']); ?> cm
                        </li>
                        <li class="mb-2">
                            <strong>Poids :</strong> <?php echo htmlspecialchars($pilot['weight']); ?> kg
                        </li>
                        <li class="mb-2">
                            <strong>Expérience :</strong> <?php echo htmlspecialchars($pilot['experience']); ?>
                        </li>
                    </ul>
                    <div class="mt-3">
                        <a href="index.php?page=pilots/edit&id=<?php echo $pilotId; ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sessions du pilote -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Sessions</h5>
                        <a href="index.php?page=sessions/add&pilot_id=<?php echo $pilotId; ?>" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Nouvelle session
                        </a>
                    </div>

                    <?php if (empty($sessions)): ?>
                        <p class="text-muted">Aucune session enregistrée pour ce pilote.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Circuit</th>
                                        <th>Moto</th>
                                        <th>Conditions</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sessions as $session): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($session['date'])); ?></td>
                                            <td><?php echo htmlspecialchars($session['session_type']); ?></td>
                                            <td><?php echo htmlspecialchars($session['circuit_name']); ?></td>
                                            <td><?php echo htmlspecialchars($session['moto_brand'] . ' ' . $session['moto_model']); ?></td>
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