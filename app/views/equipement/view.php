<?php require_once 'app/views/templates/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Détails de l'équipement</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row">
                    <!-- Informations de l'équipement -->
                    <div class="col-md-4">
                        <div class="profile_img">
                            <div id="crop-avatar">
                                <!-- Image spécifique selon le type d'équipement -->
                                <?php
                                $imageFile = 'images/equipements/' . strtolower($equipement['type']) . '.png';
                                if (file_exists($imageFile)) {
                                    $imageSrc = $imageFile;
                                } else {
                                    $imageSrc = 'images/equipement_default.png';
                                }
                                ?>
                                <img class="img-responsive avatar-view" src="<?php echo $imageSrc; ?>" alt="Équipement" style="width:100%">
                            </div>
                        </div>
                        <h3><?php echo htmlspecialchars($equipement['nom']); ?></h3>
                        
                        <ul class="list-unstyled user_data">
                            <li>
                                <i class="fa fa-tag"></i> Type: <?php echo htmlspecialchars($equipement['type']); ?>
                            </li>
                            <li>
                                <i class="fa fa-industry"></i> Fabricant: <?php echo htmlspecialchars($equipement['fabricant']); ?>
                            </li>
                            <li>
                                <i class="fa fa-calendar"></i> Ajouté le: 
                                <?php 
                                $date = new DateTime($equipement['date_creation']);
                                echo $date->format('d/m/Y');
                                ?>
                            </li>
                        </ul>

                        <a href="index.php?route=equipement/edit&id=<?php echo $equipement['id']; ?>" class="btn btn-warning btn-block">
                            <i class="fa fa-edit m-right-xs"></i> Modifier l'équipement
                        </a>
                    </div>

                    <!-- Description et statistiques -->
                    <div class="col-md-8">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Description</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <p><?php echo nl2br(htmlspecialchars($equipement['description'])); ?></p>
                            </div>
                        </div>

                        <!-- Statistiques -->
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Statistiques d'utilisation</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="row tile_count">
                                    <div class="col-md-4 tile_stats_count">
                                        <span class="count_top"><i class="fa fa-motorcycle"></i> Motos équipées</span>
                                        <div class="count"><?php echo htmlspecialchars($equipement['total_motos']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Liste des motos équipées -->
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Motos équipées</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <?php if (empty($motos)): ?>
                                    <p>Aucune moto n'est actuellement équipée de cet équipement.</p>
                                <?php else: ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Marque</th>
                                                <th>Modèle</th>
                                                <th>Année</th>
                                                <th>Sessions</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($motos as $moto): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($moto['marque']); ?></td>
                                                    <td><?php echo htmlspecialchars($moto['modele']); ?></td>
                                                    <td><?php echo htmlspecialchars($moto['annee']); ?></td>
                                                    <td>
                                                        <span class="badge badge-info">
                                                            <?php echo htmlspecialchars($moto['total_sessions']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="index.php?route=moto/view&id=<?php echo $moto['id']; ?>" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="fa fa-eye"></i> Voir
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/templates/footer.php'; ?> 