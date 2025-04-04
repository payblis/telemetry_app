<?php
/**
 * Vue pour la création d'une session
 */
?>

<div class="telemetrie-container">
    <div class="page-header">
        <h1>Créer une Session</h1>
        <div class="page-actions">
            <a href="<?= \App\Utils\View::url('/telemetrie') ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <?php \App\Utils\View::showNotifications(); ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= \App\Utils\View::url('/telemetrie/store') ?>" method="post" class="form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-section">
                    <h3>Informations générales</h3>
                    
                    <div class="form-row">
                        <div class="form-group <?= isset($_SESSION['form_errors']['date_session']) ? 'has-error' : '' ?>">
                            <label for="date_session">Date de la session</label>
                            <input type="date" id="date_session" name="date_session" value="<?= isset($_SESSION['form_data']['date_session']) ? \App\Utils\View::escape($_SESSION['form_data']['date_session']) : date('Y-m-d') ?>" required>
                            <?php if (isset($_SESSION['form_errors']['date_session'])): ?>
                                <div class="form-error"><?= \App\Utils\View::escape($_SESSION['form_errors']['date_session'][0]) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="heure_debut">Heure de début</label>
                            <input type="time" id="heure_debut" name="heure_debut" value="<?= isset($_SESSION['form_data']['heure_debut']) ? \App\Utils\View::escape($_SESSION['form_data']['heure_debut']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="heure_fin">Heure de fin</label>
                            <input type="time" id="heure_fin" name="heure_fin" value="<?= isset($_SESSION['form_data']['heure_fin']) ? \App\Utils\View::escape($_SESSION['form_data']['heure_fin']) : '' ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Pilote et Moto</h3>
                    
                    <div class="form-row">
                        <div class="form-group <?= isset($_SESSION['form_errors']['pilote_id']) ? 'has-error' : '' ?>">
                            <label for="pilote_id">Pilote</label>
                            <select id="pilote_id" name="pilote_id" required>
                                <option value="">Sélectionner un pilote</option>
                                <?php foreach ($pilotes as $pilote): ?>
                                    <option value="<?= $pilote['id'] ?>" <?= isset($_SESSION['form_data']['pilote_id']) && $_SESSION['form_data']['pilote_id'] == $pilote['id'] ? 'selected' : '' ?>>
                                        <?= \App\Utils\View::escape($pilote['prenom'] . ' ' . $pilote['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($_SESSION['form_errors']['pilote_id'])): ?>
                                <div class="form-error"><?= \App\Utils\View::escape($_SESSION['form_errors']['pilote_id'][0]) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group <?= isset($_SESSION['form_errors']['moto_id']) ? 'has-error' : '' ?>">
                            <label for="moto_id">Moto</label>
                            <select id="moto_id" name="moto_id" required>
                                <option value="">Sélectionner une moto</option>
                                <?php foreach ($motos as $moto): ?>
                                    <option value="<?= $moto['id'] ?>" <?= isset($_SESSION['form_data']['moto_id']) && $_SESSION['form_data']['moto_id'] == $moto['id'] ? 'selected' : '' ?>>
                                        <?= \App\Utils\View::escape($moto['marque'] . ' ' . $moto['modele']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($_SESSION['form_errors']['moto_id'])): ?>
                                <div class="form-error"><?= \App\Utils\View::escape($_SESSION['form_errors']['moto_id'][0]) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Circuit</h3>
                    
                    <div class="form-group <?= isset($_SESSION['form_errors']['circuit_id']) ? 'has-error' : '' ?>">
                        <label for="circuit_id">Circuit</label>
                        <select id="circuit_id" name="circuit_id" required>
                            <option value="">Sélectionner un circuit</option>
                            <?php foreach ($circuits as $circuit): ?>
                                <option value="<?= $circuit['id'] ?>" <?= isset($_SESSION['form_data']['circuit_id']) && $_SESSION['form_data']['circuit_id'] == $circuit['id'] ? 'selected' : '' ?>>
                                    <?= \App\Utils\View::escape($circuit['nom'] . ' (' . $circuit['pays'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($_SESSION['form_errors']['circuit_id'])): ?>
                            <div class="form-error"><?= \App\Utils\View::escape($_SESSION['form_errors']['circuit_id'][0]) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Conditions météorologiques</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="conditions_meteo">Conditions météo</label>
                            <select id="conditions_meteo" name="conditions_meteo">
                                <option value="Ensoleillé" <?= isset($_SESSION['form_data']['conditions_meteo']) && $_SESSION['form_data']['conditions_meteo'] == 'Ensoleillé' ? 'selected' : '' ?>>Ensoleillé</option>
                                <option value="Nuageux" <?= isset($_SESSION['form_data']['conditions_meteo']) && $_SESSION['form_data']['conditions_meteo'] == 'Nuageux' ? 'selected' : '' ?>>Nuageux</option>
                                <option value="Pluie légère" <?= isset($_SESSION['form_data']['conditions_meteo']) && $_SESSION['form_data']['conditions_meteo'] == 'Pluie légère' ? 'selected' : '' ?>>Pluie légère</option>
                                <option value="Pluie forte" <?= isset($_SESSION['form_data']['conditions_meteo']) && $_SESSION['form_data']['conditions_meteo'] == 'Pluie forte' ? 'selected' : '' ?>>Pluie forte</option>
                                <option value="Brouillard" <?= isset($_SESSION['form_data']['conditions_meteo']) && $_SESSION['form_data']['conditions_meteo'] == 'Brouillard' ? 'selected' : '' ?>>Brouillard</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="temperature_air">Température air (°C)</label>
                            <input type="number" id="temperature_air" name="temperature_air" step="0.1" value="<?= isset($_SESSION['form_data']['temperature_air']) ? \App\Utils\View::escape($_SESSION['form_data']['temperature_air']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="temperature_piste">Température piste (°C)</label>
                            <input type="number" id="temperature_piste" name="temperature_piste" step="0.1" value="<?= isset($_SESSION['form_data']['temperature_piste']) ? \App\Utils\View::escape($_SESSION['form_data']['temperature_piste']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="humidite">Humidité (%)</label>
                            <input type="number" id="humidite" name="humidite" min="0" max="100" value="<?= isset($_SESSION['form_data']['humidite']) ? \App\Utils\View::escape($_SESSION['form_data']['humidite']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="vent">Vent (km/h)</label>
                            <input type="number" id="vent" name="vent" min="0" value="<?= isset($_SESSION['form_data']['vent']) ? \App\Utils\View::escape($_SESSION['form_data']['vent']) : '' ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Notes</h3>
                    
                    <div class="form-group">
                        <label for="notes">Notes sur la session</label>
                        <textarea id="notes" name="notes" rows="4"><?= isset($_SESSION['form_data']['notes']) ? \App\Utils\View::escape($_SESSION['form_data']['notes']) : '' ?></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Créer la session</button>
                    <a href="<?= \App\Utils\View::url('/telemetrie') ?>" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Nettoyer les données de formulaire et les erreurs en session
unset($_SESSION['form_data']);
unset($_SESSION['form_errors']);
?>
