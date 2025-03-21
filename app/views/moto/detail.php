<?php require_once APP_PATH . 'views/templates/header.php'; ?>

<?php
// Helper function to safely handle null values
function safe_html($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function format_value($value, $default = '-') {
    return empty($value) ? $default : safe_html($value);
}

function safe_array_get($array, $key, $default = '-') {
    if (!is_array($array)) {
        return $default;
    }
    return format_value($array[$key] ?? null, $default);
}

function format_concat_values($array, $keys, $separator = ' ', $default = '-') {
    if (!is_array($array)) {
        return $default;
    }
    $values = array_map(function($key) use ($array) {
        return $array[$key] ?? '';
    }, $keys);
    $values = array_filter($values);
    return empty($values) ? $default : safe_html(implode($separator, $values));
}

// Validate required data
if (!isset($moto) || !is_array($moto)) {
    echo '<div class="alert alert-danger">Erreur: Données de la moto non trouvées.</div>';
    return;
}
?>

<div class="container">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <?php echo format_concat_values($moto, ['marque', 'modele', 'annee']); ?>
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
                            <span class="detail-value"><?php echo safe_array_get($moto, 'marque'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Modèle:</span>
                            <span class="detail-value"><?php echo safe_array_get($moto, 'modele'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Année:</span>
                            <span class="detail-value"><?php echo safe_array_get($moto, 'annee'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Cylindrée:</span>
                            <span class="detail-value"><?php echo safe_array_get($moto, 'cylindree'); ?> cc</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Type:</span>
                            <span class="detail-value"><?php echo safe_array_get($moto, 'type_moto'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Puissance:</span>
                            <span class="detail-value"><?php echo safe_array_get($moto, 'puissance_moteur'); ?> ch</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Couple:</span>
                            <span class="detail-value"><?php echo safe_array_get($moto, 'couple_moteur'); ?> Nm</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Poids à sec:</span>
                            <span class="detail-value"><?php echo safe_array_get($moto, 'poids_sec'); ?> kg</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suspensions -->
            <?php if (isset($suspensions) && is_array($suspensions)): ?>
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Suspensions</h2>
                </div>
                <div class="panel-content">
                    <h3 class="section-header">Fourche</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Marque:</span>
                            <span class="detail-value"><?php echo safe_array_get($suspensions, 'fourche_marque'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Modèle:</span>
                            <span class="detail-value"><?php echo safe_array_get($suspensions, 'fourche_modele'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Précharge:</span>
                            <span class="detail-value"><?php echo safe_array_get($suspensions, 'fourche_precharge'); ?> tours</span>
                        </div>
                    </div>

                    <h3 class="section-header">Amortisseur</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Marque:</span>
                            <span class="detail-value"><?php echo safe_array_get($suspensions, 'amortisseur_marque'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Modèle:</span>
                            <span class="detail-value"><?php echo safe_array_get($suspensions, 'amortisseur_modele'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Freinage -->
            <?php if (isset($freins) && is_array($freins)): ?>
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Freinage</h2>
                </div>
                <div class="panel-content">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Étrier avant:</span>
                            <span class="detail-value"><?php echo format_concat_values($freins, ['etrier_avant_marque', 'etrier_avant_modele']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Étrier arrière:</span>
                            <span class="detail-value"><?php echo format_concat_values($freins, ['etrier_arriere_marque', 'etrier_arriere_modele']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Transmission -->
            <?php if (isset($transmission) && is_array($transmission)): ?>
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Transmission</h2>
                </div>
                <div class="panel-content">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Rapport de transmission:</span>
                            <span class="detail-value"><?php echo safe_array_get($transmission, 'couronne_dents'); ?>/<?php echo safe_array_get($transmission, 'pignon_dents'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Chaîne:</span>
                            <span class="detail-value"><?php echo format_concat_values($transmission, ['chaine_type', 'chaine_marque', 'chaine_modele']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Échappement -->
            <?php if (isset($echappement) && is_array($echappement)): ?>
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Échappement</h2>
                </div>
                <div class="panel-content">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Marque:</span>
                            <span class="detail-value"><?php echo safe_array_get($echappement, 'marque'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Modèle:</span>
                            <span class="detail-value"><?php echo safe_array_get($echappement, 'modele'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Type:</span>
                            <span class="detail-value"><?php echo safe_array_get($echappement, 'type'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Électronique -->
            <?php if (isset($electronique) && is_array($electronique)): ?>
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Électronique</h2>
                </div>
                <div class="panel-content">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">ECU:</span>
                            <span class="detail-value"><?php echo format_concat_values($electronique, ['ecu_marque', 'ecu_modele']); ?></span>
                        </div>
                    </div>
                    <?php if (isset($capteurs) && is_array($capteurs) && array_filter($capteurs)): ?>
                    <h3 class="section-header">Capteurs installés</h3>
                    <div class="detail-grid">
                        <?php foreach ($capteurs as $capteur => $value): ?>
                            <?php if ($value): ?>
                                <div class="detail-item">
                                    <span class="detail-value"><?php echo format_value(ucfirst(str_replace('_', ' ', $capteur))); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Pneumatiques -->
            <?php if (isset($pneumatiques) && is_array($pneumatiques)): ?>
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Pneumatiques</h2>
                </div>
                <div class="panel-content">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Marque:</span>
                            <span class="detail-value"><?php echo safe_array_get($pneumatiques, 'marque'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Modèle:</span>
                            <span class="detail-value"><?php echo safe_array_get($pneumatiques, 'modele'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Type de gomme:</span>
                            <span class="detail-value"><?php echo safe_array_get($pneumatiques, 'type_gomme'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group" style="text-align: center; margin-top: 20px;">
                <a href="index.php?route=moto/edit&id=<?php echo safe_array_get($moto, 'id'); ?>" class="btn btn-primary">Modifier</a>
                <a href="index.php?route=motos" class="btn btn-primary">Retour à la liste</a>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . 'views/templates/footer.php'; ?> 