<?php include 'includes/header.php'; ?>

<div class="content-container">
    <div class="page-header">
        <h2>Gestion des circuits</h2>
        <a href="index.php?page=circuit_add" class="btn btn-primary">Ajouter un circuit</a>
    </div>
    
    <div class="search-container">
        <form action="index.php" method="get" class="search-form">
            <input type="hidden" name="page" value="circuits">
            <div class="form-group">
                <input type="text" name="search" placeholder="Rechercher un circuit..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                <button type="submit" class="btn btn-secondary">Rechercher</button>
            </div>
        </form>
    </div>
    
    <div class="card">
        <div class="card-header">
            Liste des circuits
        </div>
        <div class="card-body">
            <?php if (empty($circuits)): ?>
                <p>Aucun circuit trouvé.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Pays</th>
                            <th>Longueur</th>
                            <th>Virages</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($circuits as $circuit): ?>
                        <tr>
                            <td><?php echo $circuit['name']; ?></td>
                            <td><?php echo $circuit['country']; ?></td>
                            <td><?php echo $circuit['length']; ?> m</td>
                            <td><?php echo $circuit['corners_count'] ? $circuit['corners_count'] : 'N/A'; ?></td>
                            <td>
                                <a href="index.php?page=circuit_details&id=<?php echo $circuit['id']; ?>" class="btn btn-secondary">Détails</a>
                                <a href="index.php?page=circuit_edit&id=<?php echo $circuit['id']; ?>" class="btn btn-secondary">Modifier</a>
                                <a href="index.php?page=circuit_delete&id=<?php echo $circuit['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce circuit ?');">Supprimer</a>
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
