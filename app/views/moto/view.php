<?php require_once 'app/views/templates/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Profil de la moto</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row">
                    <!-- Informations de la moto -->
                    <div class="col-md-4">
                        <div class="profile_img">
                            <div id="crop-avatar">
                                <img class="img-responsive avatar-view" src="images/moto.png" alt="Moto" style="width:100%">
                            </div>
                        </div>
                        <h3><?php echo htmlspecialchars($moto['marque'] . ' ' . $moto['modele']); ?></h3>
                        
                        <ul class="list-unstyled user_data">
                            <li>
                                <i class="fa fa-calendar"></i> <?php echo htmlspecialchars($moto['annee']); ?>
                            </li>
                            <li>
                                <i class="fa fa-tachometer"></i> <?php echo htmlspecialchars($moto['cylindree']); ?> cc
                            </li>
                            <li>
                                <i class="fa fa-balance-scale"></i> <?php echo htmlspecialchars($moto['poids']); ?> kg
                            </li>
                            <li>
                                <i class="fa fa-bolt"></i> <?php echo htmlspecialchars($moto['puissance']); ?> ch
                            </li>
                        </ul>

                        <a href="index.php?route=moto/edit&id=<?php echo $moto['id']; ?>" class="btn btn-warning btn-block">
                            <i class="fa fa-edit m-right-xs"></i> Modifier la moto
                        </a>
                    </div>

                    <!-- Statistiques et équipements -->
                    <div class="col-md-8">
                        <div class="row tile_count">
                            <div class="col-md-4 tile_stats_count">
                                <span class="count_top"><i class="fa fa-clock-o"></i> Total Sessions</span>
                                <div class="count"><?php echo htmlspecialchars($moto['total_sessions']); ?></div>
                            </div>
                            <div class="col-md-4 tile_stats_count">
                                <span class="count_top"><i class="fa fa-users"></i> Pilotes différents</span>
                                <div class="count"><?php echo htmlspecialchars($moto['total_pilotes']); ?></div>
                            </div>
                        </div>

                        <!-- Équipements -->
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Équipements installés</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <?php if (empty($moto['equipements'])): ?>
                                    <p>Aucun équipement installé sur cette moto.</p>
                                <?php else: ?>
                                    <div class="equipements-list">
                                        <?php 
                                        $equipements = explode(', ', $moto['equipements']);
                                        foreach ($equipements as $equipement): 
                                        ?>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($equipement); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
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
                                    <p>Aucune session enregistrée pour cette moto.</p>
                                <?php else: ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Pilote</th>
                                                <th>Circuit</th>
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
                                                    <td>
                                                        <?php echo htmlspecialchars($session['pilote_prenom'] . ' ' . $session['pilote_nom']); ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($session['circuit_nom']); ?></td>
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
                                    <a href="index.php?route=session/new&moto_id=<?php echo $moto['id']; ?>" 
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

<style>
.equipements-list {
    margin: 10px 0;
}
.equipements-list .badge {
    margin: 0 5px 5px 0;
    padding: 8px 12px;
    font-size: 14px;
}
</style>

<?php require_once 'app/views/templates/footer.php'; ?> 