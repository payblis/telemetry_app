<?php require_once APP_PATH . 'views/templates/header.php'; ?>

<div class="container">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <?php echo htmlspecialchars($moto['marque'] . ' ' . $moto['modele'] . ' ' . $moto['annee']); ?>
            </div>
        </div>
        <div class="panel-content">
            <!-- Informations Générales -->
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Informations Générales</h2>
                </div>
                <div class="panel-content">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Marque:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($moto['marque']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Modèle:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($moto['modele']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Année:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($moto['annee']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Cylindrée:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($moto['cylindree']); ?> cc</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Type:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($moto['type_moto']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Puissance:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($moto['puissance_moteur']); ?> ch</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Couple:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($moto['couple_moteur']); ?> Nm</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Poids à sec:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($moto['poids_sec']); ?> kg</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suspensions -->
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Suspensions</h2>
                </div>
                <div class="panel-content">
                    <h3 class="section-header">Fourche</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Marque:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($suspensions['fourche_marque']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Modèle:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($suspensions['fourche_modele']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Précharge:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($suspensions['fourche_precharge']); ?> tours</span>
                        </div>
                    </div>

                    <h3 class="section-header">Amortisseur</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Marque:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($suspensions['amortisseur_marque']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Modèle:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($suspensions['amortisseur_modele']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Freinage -->
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Freinage</h2>
                </div>
                <div class="panel-content">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Étrier avant:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($freins['etrier_avant_marque'] . ' ' . $freins['etrier_avant_modele']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Étrier arrière:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($freins['etrier_arriere_marque'] . ' ' . $freins['etrier_arriere_modele']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transmission -->
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Transmission</h2>
                </div>
                <div class="panel-content">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Rapport de transmission:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($transmission['couronne_dents']); ?>/<?php echo htmlspecialchars($transmission['pignon_dents']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Chaîne:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($transmission['chaine_type'] . ' ' . $transmission['chaine_marque'] . ' ' . $transmission['chaine_modele']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Échappement -->
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Échappement</h2>
                </div>
                <div class="panel-content">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Marque:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($echappement['marque']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Modèle:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($echappement['modele']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Type:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($echappement['type']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Électronique -->
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Électronique</h2>
                </div>
                <div class="panel-content">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">ECU:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($electronique['ecu_marque'] . ' ' . $electronique['ecu_modele']); ?></span>
                        </div>
                    </div>
                    <h3 class="section-header">Capteurs installés</h3>
                    <div class="detail-grid">
                        <?php foreach ($capteurs as $capteur => $value): ?>
                            <?php if ($value): ?>
                                <div class="detail-item">
                                    <span class="detail-value"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $capteur))); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Pneumatiques -->
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Pneumatiques</h2>
                </div>
                <div class="panel-content">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Marque:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($pneumatiques['marque']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Modèle:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($pneumatiques['modele']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Type de gomme:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($pneumatiques['type_gomme']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group" style="text-align: center; margin-top: 20px;">
                <a href="index.php?route=moto/edit&id=<?php echo $moto['id']; ?>" class="btn btn-primary">Modifier</a>
                <a href="index.php?route=motos" class="btn btn-primary">Retour à la liste</a>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . 'views/templates/footer.php'; ?> 