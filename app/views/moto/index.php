<?php require_once 'app/views/templates/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Liste des motos</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row mb-3">
                    <div class="col-md-12 text-right">
                        <a href="index.php?route=moto/new" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Ajouter une moto
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">
                                <th class="column-title">Marque</th>
                                <th class="column-title">Modèle</th>
                                <th class="column-title">Année</th>
                                <th class="column-title">Cylindrée</th>
                                <th class="column-title">Équipements</th>
                                <th class="column-title">Sessions</th>
                                <th class="column-title no-link last"><span class="nobr">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($motos)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Aucune moto enregistrée</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($motos as $moto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($moto['marque']); ?></td>
                                    <td><?php echo htmlspecialchars($moto['modele']); ?></td>
                                    <td><?php echo htmlspecialchars($moto['annee']); ?></td>
                                    <td><?php echo htmlspecialchars($moto['cylindree']); ?> cc</td>
                                    <td>
                                        <?php 
                                        if (!empty($moto['equipements'])) {
                                            $equipements = explode(', ', $moto['equipements']);
                                            foreach ($equipements as $equipement) {
                                                echo '<span class="badge badge-info">' . htmlspecialchars($equipement) . '</span> ';
                                            }
                                        } else {
                                            echo '<span class="text-muted">Aucun équipement</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($moto['total_sessions']); ?></td>
                                    <td class="last">
                                        <a href="index.php?route=moto/view&id=<?php echo $moto['id']; ?>" 
                                           class="btn btn-info btn-sm" 
                                           data-toggle="tooltip" 
                                           title="Voir le profil">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="index.php?route=moto/edit&id=<?php echo $moto['id']; ?>" 
                                           class="btn btn-warning btn-sm"
                                           data-toggle="tooltip" 
                                           title="Modifier">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="index.php?route=moto/delete&id=<?php echo $moto['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette moto ?');"
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