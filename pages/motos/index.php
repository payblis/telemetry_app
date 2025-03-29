<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Initialiser les variables
$motos = [];
$error = null;
$success = null;

try {
    // Charger la classe Moto
    require_once 'classes/Moto.php';
    $moto = new Moto();
    
    // Récupérer toutes les motos de l'utilisateur
    $motos = $moto->getAllByUserId($_SESSION['user_id']);
} catch (Exception $e) {
    $error = "Erreur lors du chargement des motos : " . $e->getMessage();
    error_log($error);
}
?>

<div class="container">
    <div class="page-header">
        <h1>Gestion des Motos</h1>
        <a href="index.php?page=moto_add" class="btn btn-primary">Ajouter une moto</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (empty($motos)): ?>
        <div class="alert alert-info">
            Aucune moto enregistrée. <a href="index.php?page=moto_add">Ajouter une moto</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Marque</th>
                        <th>Modèle</th>
                        <th>Cylindrée</th>
                        <th>Année</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($motos as $moto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($moto['brand']); ?></td>
                        <td><?php echo htmlspecialchars($moto['model']); ?></td>
                        <td><?php echo htmlspecialchars($moto['engine_capacity']); ?> cc</td>
                        <td><?php echo htmlspecialchars($moto['year']); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="index.php?page=moto_edit&id=<?php echo $moto['id']; ?>" 
                                   class="btn btn-secondary btn-sm">Modifier</a>
                                <a href="index.php?page=moto_details&id=<?php echo $moto['id']; ?>" 
                                   class="btn btn-info btn-sm">Détails</a>
                                <a href="index.php?page=moto_delete&id=<?php echo $moto['id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirmAction('Êtes-vous sûr de vouloir supprimer cette moto ?')">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div> 