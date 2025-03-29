<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Initialiser les variables
$sessions = [];
$error = null;
$success = null;

try {
    // Charger la classe Session
    require_once 'classes/Session.php';
    $session = new Session();
    
    // Récupérer toutes les sessions de l'utilisateur
    $sessions = $session->getAllByUserId($_SESSION['user_id']);
} catch (Exception $e) {
    $error = "Erreur lors du chargement des sessions : " . $e->getMessage();
    error_log($error);
}
?>

<div class="container">
    <div class="page-header">
        <h1>Gestion des Sessions</h1>
        <a href="index.php?page=session_add" class="btn btn-primary">Ajouter une session</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (empty($sessions)): ?>
        <div class="alert alert-info">
            Aucune session enregistrée. <a href="index.php?page=session_add">Ajouter une session</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Circuit</th>
                        <th>Pilote</th>
                        <th>Moto</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($session['date'])); ?></td>
                        <td><?php echo htmlspecialchars($session['circuit_name']); ?></td>
                        <td><?php echo htmlspecialchars($session['pilot_name']); ?></td>
                        <td><?php echo htmlspecialchars($session['moto_name']); ?></td>
                        <td><?php echo htmlspecialchars($session['type']); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="index.php?page=session_edit&id=<?php echo $session['id']; ?>" 
                                   class="btn btn-secondary btn-sm">Modifier</a>
                                <a href="index.php?page=session_details&id=<?php echo $session['id']; ?>" 
                                   class="btn btn-info btn-sm">Détails</a>
                                <a href="index.php?page=session_delete&id=<?php echo $session['id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirmAction('Êtes-vous sûr de vouloir supprimer cette session ?')">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div> 