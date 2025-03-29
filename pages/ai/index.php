<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Initialiser les variables
$feedbacks = [];
$error = null;
$success = null;

try {
    // Charger la classe AIFeedback
    require_once 'classes/AIFeedback.php';
    $aiFeedback = new AIFeedback();
    
    // Récupérer tous les retours d'IA de l'utilisateur
    $feedbacks = $aiFeedback->getAllByUserId($_SESSION['user_id']);
} catch (Exception $e) {
    $error = "Erreur lors du chargement des retours d'IA : " . $e->getMessage();
    error_log($error);
}
?>

<div class="container">
    <div class="page-header">
        <h1>Retours d'Intelligence Artificielle</h1>
        <a href="index.php?page=ai_chat" class="btn btn-primary">Nouvelle analyse</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (empty($feedbacks)): ?>
        <div class="alert alert-info">
            Aucun retour d'IA enregistré. <a href="index.php?page=ai_chat">Lancer une nouvelle analyse</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($feedbacks as $feedback): ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            Session : <?php echo htmlspecialchars($feedback['session_name']); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            <?php echo nl2br(htmlspecialchars($feedback['feedback'])); ?>
                        </p>
                        <p class="text-muted">
                            Date : <?php echo date('d/m/Y H:i', strtotime($feedback['created_at'])); ?>
                        </p>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group">
                            <a href="index.php?page=ai_details&id=<?php echo $feedback['id']; ?>" 
                               class="btn btn-info btn-sm">Détails</a>
                            <a href="index.php?page=ai_delete&id=<?php echo $feedback['id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirmAction('Êtes-vous sûr de vouloir supprimer ce retour d\'IA ?')">Supprimer</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div> 