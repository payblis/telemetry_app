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
    $recentSessions = $session->getAllByUserId($_SESSION['user_id'], 5);
    
    // Récupérer les feedbacks récents
    $recentFeedbacks = $aiFeedback->getAllByUserId($_SESSION['user_id'], 5);
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
    <div class="dashboard-container">
        <div class="dashboard-welcome">
            <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            <p>Votre télémétriste virtuel <?php echo htmlspecialchars($_SESSION['telemetrician_name'] ?? $_SESSION['username']); ?> est prêt à vous aider.</p>
        </div>
        
        <div class="dashboard-stats">
            <div class="row">
                <div class="col-25">
                    <div class="card">
                        <div class="card-header">
                            Pilotes
                        </div>
                        <div class="card-body">
                            <div class="stat-number"><?php echo $pilotsCount; ?></div>
                            <div class="stat-label">Pilotes enregistrés</div>
                        </div>
                        <div class="card-footer">
                            <a href="index.php?page=pilots" class="btn btn-secondary">Gérer les pilotes</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-25">
                    <div class="card">
                        <div class="card-header">
                            Motos
                        </div>
                        <div class="card-body">
                            <div class="stat-number"><?php echo $motosCount; ?></div>
                            <div class="stat-label">Motos enregistrées</div>
                        </div>
                        <div class="card-footer">
                            <a href="index.php?page=motos" class="btn btn-secondary">Gérer les motos</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-25">
                    <div class="card">
                        <div class="card-header">
                            Sessions
                        </div>
                        <div class="card-body">
                            <div class="stat-number"><?php echo $sessionsCount; ?></div>
                            <div class="stat-label">Sessions enregistrées</div>
                        </div>
                        <div class="card-footer">
                            <a href="index.php?page=sessions" class="btn btn-secondary">Gérer les sessions</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-25">
                    <div class="card">
                        <div class="card-header">
                            Recommandations
                        </div>
                        <div class="card-body">
                            <div class="stat-number"><?php echo $feedbacksCount; ?></div>
                            <div class="stat-label">Recommandations IA</div>
                        </div>
                        <div class="card-footer">
                            <a href="index.php?page=ai_chat" class="btn btn-primary">Assistant IA</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="dashboard-recent">
            <div class="row">
                <div class="col-50">
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
                                                <a href="index.php?page=session_details&id=<?php echo $session['id']; ?>" class="btn btn-secondary">Détails</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="index.php?page=session_add" class="btn btn-primary">Nouvelle session</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-50">
                    <div class="card">
                        <div class="card-header">
                            Dernières recommandations IA
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentFeedbacks)): ?>
                                <p>Aucune recommandation récente.</p>
                            <?php else: ?>
                                <div class="feedbacks-list">
                                    <?php foreach ($recentFeedbacks as $feedback): ?>
                                    <div class="feedback-item">
                                        <div class="feedback-header">
                                            <span class="feedback-problem"><?php echo htmlspecialchars($feedback['problem_type']); ?></span>
                                            <span class="feedback-date"><?php echo date('d/m/Y H:i', strtotime($feedback['created_at'])); ?></span>
                                        </div>
                                        <div class="feedback-body">
                                            <p><strong>Problème :</strong> <?php echo htmlspecialchars(truncateText($feedback['problem_description'], 100)); ?></p>
                                            <p><strong>Solution :</strong> <?php echo htmlspecialchars(truncateText($feedback['solution_description'], 150)); ?></p>
                                        </div>
                                        <div class="feedback-footer">
                                            <a href="index.php?page=feedback_details&id=<?php echo $feedback['id']; ?>" class="btn btn-secondary">Détails</a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="index.php?page=ai_chat" class="btn btn-primary">Consulter l'assistant IA</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
