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
$circuit = null;
$sessions = [];

try {
    // Charger les classes nécessaires
    require_once 'classes/Circuit.php';
    require_once 'classes/Session.php';
    
    $circuitObj = new Circuit();
    $sessionObj = new Session();
    
    // Vérifier si le circuit appartient à l'utilisateur
    if (!$circuitObj->belongsToUser($circuitId, $_SESSION['user_id'])) {
        throw new Exception("Vous n'avez pas accès à ce circuit.");
    }
    
    // Récupérer les données du circuit
    $circuit = $circuitObj->getById($circuitId);
    if (!$circuit) {
        throw new Exception("Circuit non trouvé.");
    }
    
    // Récupérer les sessions du circuit
    $sessions = $sessionObj->getByCircuitId($circuitId);
    
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Erreur lors de la récupération des détails du circuit : " . $e->getMessage());
}
?>

<div class="container">
    <div class="page-header">
        <h1>Détails du Circuit</h1>
        <a href="index.php?page=circuits" class="btn btn-secondary">Retour à la liste</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($circuit): ?>
    <div class="row">
        <!-- Informations du circuit -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Informations techniques</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Nom :</strong> <?php echo htmlspecialchars($circuit['name']); ?>
                        </li>
                        <li class="mb-2">
                            <strong>Pays :</strong> <?php echo htmlspecialchars($circuit['country']); ?>
                        </li>
                        <li class="mb-2">
                            <strong>Longueur :</strong> <?php echo htmlspecialchars($circuit['length']); ?> m
                        </li>
                        <li class="mb-2">
                            <strong>Largeur :</strong> <?php echo htmlspecialchars($circuit['width']); ?> m
                        </li>
                        <li class="mb-2">
                            <strong>Nombre de virages :</strong> <?php echo htmlspecialchars($circuit['corners_count']); ?>
                        </li>
                    </ul>
                    <div class="mt-3">
                        <a href="index.php?page=circuits/edit&id=<?php echo $circuitId; ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sessions du circuit -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Sessions</h5>
                        <a href="index.php?page=sessions/add&circuit_id=<?php echo $circuitId; ?>" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Nouvelle session
                        </a>
                    </div>

                    <?php if (empty($sessions)): ?>
                        <p class="text-muted">Aucune session enregistrée sur ce circuit.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Pilote</th>
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
                                            <td><?php echo htmlspecialchars($session['pilot_name']); ?></td>
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