<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Initialiser les variables
$pilots = [];
$error = null;
$success = null;

try {
    // Charger la classe Pilot
    require_once 'classes/Pilot.php';
    $pilot = new Pilot();
    
    // Récupérer tous les pilotes de l'utilisateur
    $pilots = $pilot->getAllByUserId($_SESSION['user_id']);
} catch (Exception $e) {
    $error = "Erreur lors du chargement des pilotes : " . $e->getMessage();
    error_log($error);
}
?>

<div class="container">
    <div class="page-header">
        <h1>Gestion des Pilotes</h1>
        <a href="index.php?page=pilot_add" class="btn btn-primary">Ajouter un pilote</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (empty($pilots)): ?>
        <div class="alert alert-info">
            Aucun pilote enregistré. <a href="index.php?page=pilot_add">Ajouter un pilote</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Taille</th>
                        <th>Poids</th>
                        <th>Expérience</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pilots as $pilot): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pilot['name']); ?></td>
                        <td><?php echo htmlspecialchars($pilot['height']); ?> cm</td>
                        <td><?php echo htmlspecialchars($pilot['weight']); ?> kg</td>
                        <td><?php echo htmlspecialchars($pilot['experience']); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="index.php?page=pilot_edit&id=<?php echo $pilot['id']; ?>" 
                                   class="btn btn-secondary btn-sm">Modifier</a>
                                <a href="index.php?page=pilot_details&id=<?php echo $pilot['id']; ?>" 
                                   class="btn btn-info btn-sm">Détails</a>
                                <a href="index.php?page=pilot_delete&id=<?php echo $pilot['id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirmAction('Êtes-vous sûr de vouloir supprimer ce pilote ?')">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips Bootstrap si présents
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script> 