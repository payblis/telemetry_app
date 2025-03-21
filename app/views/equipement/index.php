<?php require_once 'app/views/templates/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Liste des équipements</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row mb-3">
                    <div class="col-md-12 text-right">
                        <a href="index.php?route=equipement/new" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Ajouter un équipement
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">
                                <th class="column-title">Nom</th>
                                <th class="column-title">Type</th>
                                <th class="column-title">Fabricant</th>
                                <th class="column-title">Description</th>
                                <th class="column-title">Motos équipées</th>
                                <th class="column-title no-link last"><span class="nobr">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($equipements)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Aucun équipement enregistré</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($equipements as $equipement): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($equipement['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($equipement['type']); ?></td>
                                    <td><?php echo htmlspecialchars($equipement['fabricant']); ?></td>
                                    <td>
                                        <?php 
                                        $description = htmlspecialchars($equipement['description']);
                                        echo strlen($description) > 50 ? substr($description, 0, 47) . '...' : $description;
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo htmlspecialchars($equipement['total_motos']); ?>
                                        </span>
                                    </td>
                                    <td class="last">
                                        <a href="index.php?route=equipement/view&id=<?php echo $equipement['id']; ?>" 
                                           class="btn btn-info btn-sm" 
                                           data-toggle="tooltip" 
                                           title="Voir les détails">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="index.php?route=equipement/edit&id=<?php echo $equipement['id']; ?>" 
                                           class="btn btn-warning btn-sm"
                                           data-toggle="tooltip" 
                                           title="Modifier">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="index.php?route=equipement/delete&id=<?php echo $equipement['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet équipement ?');"
                                           data-toggle="tooltip" 
                                           title="Supprimer">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/templates/footer.php'; ?> 