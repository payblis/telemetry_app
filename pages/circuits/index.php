<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Initialiser les variables
$circuits = [];
$error = null;
$success = null;

try {
    // Charger la classe Circuit
    require_once 'classes/Circuit.php';
    $circuit = new Circuit();
    
    // Récupérer tous les circuits de l'utilisateur
    $circuits = $circuit->getAllByUserId($_SESSION['user_id']);
} catch (Exception $e) {
    $error = "Erreur lors du chargement des circuits : " . $e->getMessage();
    error_log($error);
}
?>

<div class="container">
    <div class="page-header">
        <h1>Gestion des Circuits</h1>
        <a href="index.php?page=circuit_add" class="btn btn-primary">Ajouter un circuit</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (empty($circuits)): ?>
        <div class="alert alert-info">
            Aucun circuit enregistré. <a href="index.php?page=circuit_add">Ajouter un circuit</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Pays</th>
                        <th>Longueur</th>
                        <th>Largeur</th>
                        <th>Nombre de virages</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($circuits as $circuit): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($circuit['name']); ?></td>
                        <td><?php echo htmlspecialchars($circuit['country']); ?></td>
                        <td><?php echo htmlspecialchars($circuit['length']); ?> m</td>
                        <td><?php echo htmlspecialchars($circuit['width']); ?> m</td>
                        <td><?php echo htmlspecialchars($circuit['corners_count']); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="index.php?page=circuit_edit&id=<?php echo $circuit['id']; ?>" 
                                   class="btn btn-secondary btn-sm">Modifier</a>
                                <a href="index.php?page=circuit_details&id=<?php echo $circuit['id']; ?>" 
                                   class="btn btn-info btn-sm">Détails</a>
                                <a href="index.php?page=circuit_delete&id=<?php echo $circuit['id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirmAction('Êtes-vous sûr de vouloir supprimer ce circuit ?')">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div> 