<?php include 'includes/header.php'; ?>

<div class="content-container">
    <div class="page-header">
        <h2>Gestion des motos</h2>
        <a href="index.php?page=moto_add" class="btn btn-primary">Ajouter une moto</a>
    </div>
    
    <div class="search-container">
        <form action="index.php" method="get" class="search-form">
            <input type="hidden" name="page" value="motos">
            <div class="form-group">
                <input type="text" name="search" placeholder="Rechercher une moto..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                <button type="submit" class="btn btn-secondary">Rechercher</button>
            </div>
        </form>
    </div>
    
    <div class="card">
        <div class="card-header">
            Liste des motos
        </div>
        <div class="card-body">
            <?php if (empty($motos)): ?>
                <p>Aucune moto trouvée.</p>
            <?php else: ?>
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
                            <td><?php echo $moto['brand']; ?></td>
                            <td><?php echo $moto['model']; ?></td>
                            <td><?php echo $moto['engine_capacity']; ?> cc</td>
                            <td><?php echo $moto['year']; ?></td>
                            <td>
                                <a href="index.php?page=moto_details&id=<?php echo $moto['id']; ?>" class="btn btn-secondary">Détails</a>
                                <a href="index.php?page=moto_edit&id=<?php echo $moto['id']; ?>" class="btn btn-secondary">Modifier</a>
                                <a href="index.php?page=moto_delete&id=<?php echo $moto['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette moto ?');">Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
