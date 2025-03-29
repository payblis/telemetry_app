<?php
// Initialiser les variables
$pilotsCount = 0;
$motosCount = 0;
$sessionsCount = 0;
$feedbacksCount = 0;
$recentSessions = [];
$recentFeedbacks = [];

try {
    // Charger les classes nécessaires
    require_once 'classes/Pilot.php';
    require_once 'classes/Moto.php';
    require_once 'classes/Session.php';
    require_once 'classes/AIFeedback.php';
    
    // Créer les instances
    $pilot = new Pilot();
    $moto = new Moto();
    $session = new Session();
    $aiFeedback = new AIFeedback();
    
    // Récupérer les compteurs
    $pilotsCount = count($pilot->getAllByUserId($_SESSION['user_id']));
    $motosCount = count($moto->getAllByUserId($_SESSION['user_id']));
    $sessionsCount = count($session->getAllByUserId($_SESSION['user_id']));
    $feedbacksCount = count($aiFeedback->getAllByUserId($_SESSION['user_id']));
    
    // Récupérer les sessions récentes
    $recentSessions = $session->getAllByUserId($_SESSION['user_id']);
    $recentSessions = array_slice($recentSessions, 0, 5);
    
    // Récupérer les feedbacks récents
    $recentFeedbacks = $aiFeedback->getRecentByUserId($_SESSION['user_id'], 5);
} catch (Exception $e) {
    // En cas d'erreur, on garde les valeurs par défaut
    error_log("Erreur dans le dashboard : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - TeleMoto</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Tableau de bord</h1>
            <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pilotes</h5>
                        <p class="card-text"><?php echo $pilotsCount; ?></p>
                        <a href="index.php?page=pilots" class="btn btn-primary">Gérer les pilotes</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Motos</h5>
                        <p class="card-text"><?php echo $motosCount; ?></p>
                        <a href="index.php?page=motos" class="btn btn-primary">Gérer les motos</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sessions</h5>
                        <p class="card-text"><?php echo $sessionsCount; ?></p>
                        <a href="index.php?page=sessions" class="btn btn-primary">Gérer les sessions</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recommandations IA</h5>
                        <p class="card-text"><?php echo $feedbacksCount; ?></p>
                        <a href="index.php?page=ai_chat" class="btn btn-primary">Voir les recommandations</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Sessions récentes
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentSessions)): ?>
                            <p>Aucune session récente.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Circuit</th>
                                        <th>Pilote</th>
                                        <th>Moto</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentSessions as $session): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($session['date'])); ?></td>
                                        <td><?php echo htmlspecialchars($session['circuit_name']); ?></td>
                                        <td><?php echo htmlspecialchars($session['pilot_name']); ?></td>
                                        <td><?php echo htmlspecialchars($session['moto_brand'] . ' ' . $session['moto_model']); ?></td>
                                        <td>
                                            <a href="index.php?page=session_details&id=<?php echo $session['id']; ?>" class="btn btn-secondary btn-sm">Détails</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Recommandations IA récentes
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentFeedbacks)): ?>
                            <p>Aucune recommandation récente.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentFeedbacks as $feedback): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($feedback['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($feedback['problem_type']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($feedback['problem_description'], 0, 50)) . '...'; ?></td>
                                        <td>
                                            <a href="index.php?page=ai_feedback_details&id=<?php echo $feedback['id']; ?>" class="btn btn-secondary btn-sm">Détails</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
