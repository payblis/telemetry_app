<?php require_once 'app/views/templates/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Liste des pilotes</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row mb-3">
                    <div class="col-md-12 text-right">
                        <a href="index.php?route=pilote/new" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Ajouter un pilote
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">
                                <th class="column-title">Nom</th>
                                <th class="column-title">Prénom</th>
                                <th class="column-title">Âge</th>
                                <th class="column-title">Expérience</th>
                                <th class="column-title">Sessions</th>
                                <th class="column-title no-link last"><span class="nobr">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pilotes)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Aucun pilote enregistré</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($pilotes as $pilote): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pilote['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($pilote['prenom']); ?></td>
                                    <td><?php echo htmlspecialchars($pilote['age']); ?> ans</td>
                                    <td><?php echo htmlspecialchars($pilote['experience']); ?></td>
                                    <td><?php echo htmlspecialchars($pilote['total_sessions']); ?></td>
                                    <td class="last">
                                        <a href="index.php?route=pilote/view&id=<?php echo $pilote['id']; ?>" 
                                           class="btn btn-info btn-sm" 
                                           data-toggle="tooltip" 
                                           title="Voir le profil">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="index.php?route=pilote/edit&id=<?php echo $pilote['id']; ?>" 
                                           class="btn btn-warning btn-sm"
                                           data-toggle="tooltip" 
                                           title="Modifier">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="index.php?route=pilote/delete&id=<?php echo $pilote['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce pilote ?');"
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