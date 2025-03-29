<?php include 'includes/header.php'; ?>

<div class="content-container">
    <div class="page-header">
        <h2>Gestion des pilotes</h2>
        <a href="index.php?page=pilot_add" class="btn btn-primary">Ajouter un pilote</a>
    </div>
    
    <div class="search-container">
        <form action="index.php" method="get" class="search-form">
            <input type="hidden" name="page" value="pilots">
            <div class="form-group">
                <input type="text" name="search" placeholder="Rechercher un pilote..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                <button type="submit" class="btn btn-secondary">Rechercher</button>
            </div>
        </form>
    </div>
    
    <div class="card">
        <div class="card-header">
            Liste des pilotes
        </div>
        <div class="card-body">
            <?php if (empty($pilots)): ?>
                <p>Aucun pilote trouvé.</p>
            <?php else: ?>
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
                            <td><?php echo $pilot['name']; ?></td>
                            <td><?php echo $pilot['height']; ?> cm</td>
                            <td><?php echo $pilot['weight']; ?> kg</td>
                            <td><?php echo truncateText($pilot['experience'], 50); ?></td>
                            <td>
                                <a href="index.php?page=pilot_details&id=<?php echo $pilot['id']; ?>" class="btn btn-secondary">Détails</a>
                                <a href="index.php?page=pilot_edit&id=<?php echo $pilot['id']; ?>" class="btn btn-secondary">Modifier</a>
                                <a href="index.php?page=pilot_delete&id=<?php echo $pilot['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce pilote ?');">Supprimer</a>
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
