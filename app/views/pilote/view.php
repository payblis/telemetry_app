<?php require_once 'app/views/templates/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Profil du pilote</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row">
                    <!-- Informations personnelles -->
                    <div class="col-md-4">
                        <div class="profile_img">
                            <div id="crop-avatar">
                                <img class="img-responsive avatar-view" src="images/user.png" alt="Avatar" style="width:100%">
                            </div>
                        </div>
                        <h3><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></h3>
                        
                        <ul class="list-unstyled user_data">
                            <li>
                                <i class="fa fa-info-circle"></i> <?php echo htmlspecialchars($pilote['age']); ?> ans
                            </li>
                            <li>
                                <i class="fa fa-balance-scale"></i> <?php echo htmlspecialchars($pilote['poids']); ?> kg
                            </li>
                            <li>
                                <i class="fa fa-arrows-v"></i> <?php echo htmlspecialchars($pilote['taille']); ?> cm
                            </li>
                            <li>
                                <i class="fa fa-trophy"></i> <?php echo htmlspecialchars($pilote['experience']); ?>
                            </li>
                        </ul>

                        <a href="index.php?route=pilote/edit&id=<?php echo $pilote['id']; ?>" class="btn btn-warning btn-block">
                            <i class="fa fa-edit m-right-xs"></i> Modifier le profil
                        </a>
                    </div>

                    <!-- Statistiques -->
                    <div class="col-md-8">
                        <div class="row tile_count">
                            <div class="col-md-4 tile_stats_count">
                                <span class="count_top"><i class="fa fa-clock-o"></i> Total Sessions</span>
                                <div class="count"><?php echo htmlspecialchars($pilote['total_sessions']); ?></div>
                            </div>
                            <div class="col-md-4 tile_stats_count">
                                <span class="count_top"><i class="fa fa-road"></i> Circuits différents</span>
                                <div class="count"><?php echo htmlspecialchars($pilote['total_circuits']); ?></div>
                            </div>
                            <div class="col-md-4 tile_stats_count">
                                <span class="count_top"><i class="fa fa-calendar"></i> Membre depuis</span>
                                <div class="count green">
                                    <?php 
                                    $date = new DateTime($pilote['date_creation']);
                                    echo $date->format('d/m/Y');
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Dernières sessions -->
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Dernières sessions</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <?php if (empty($sessions)): ?>
                                    <p>Aucune session enregistrée pour ce pilote.</p>
                                <?php else: ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Circuit</th>
                                                <th>Moto</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sessions as $session): ?>
                                                <tr>
                                                    <td>
                                                        <?php 
                                                        $date = new DateTime($session['date_session']);
                                                        echo $date->format('d/m/Y H:i');
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($session['circuit_nom']); ?></td>
                                                    <td><?php echo htmlspecialchars($session['moto_modele']); ?></td>
                                                    <td>
                                                        <a href="index.php?route=session/view&id=<?php echo $session['id']; ?>" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="fa fa-eye"></i> Voir
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                                
                                <div class="text-center mt-3">
                                    <a href="index.php?route=session/new&pilote_id=<?php echo $pilote['id']; ?>" 
                                       class="btn btn-success">
                                        <i class="fa fa-plus"></i> Nouvelle session
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/templates/footer.php'; ?> 