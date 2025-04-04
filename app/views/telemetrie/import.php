<?php
/**
 * Vue pour l'importation de données télémétriques
 */
?>

<div class="telemetrie-container">
    <div class="page-header">
        <h1>Importer des données télémétriques</h1>
        <div class="page-actions">
            <a href="<?= \App\Utils\View::url('/telemetrie/view/' . $session['id']) ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <?php \App\Utils\View::showNotifications(); ?>

    <div class="card">
        <div class="card-header">
            <h3>Session du <?= date('d/m/Y', strtotime($session['date_session'])) ?></h3>
        </div>
        <div class="card-body">
            <div class="session-info">
                <div class="info-item">
                    <span class="info-label">Circuit</span>
                    <span class="info-value"><?= \App\Utils\View::escape($session['circuit_nom']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Pilote</span>
                    <span class="info-value"><?= \App\Utils\View::escape($session['pilote_nom'] . ' ' . $session['pilote_prenom']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Moto</span>
                    <span class="info-value"><?= \App\Utils\View::escape($session['moto_marque'] . ' ' . $session['moto_modele']) ?></span>
                </div>
            </div>

            <div class="import-instructions">
                <h4>Instructions d'importation</h4>
                <p>Pour importer vos données télémétriques, suivez ces étapes :</p>
                <ol>
                    <li>Utilisez l'application <strong>Sensor Logger</strong> sur votre smartphone pendant votre session sur circuit</li>
                    <li>Enregistrez vos données en utilisant tous les capteurs disponibles (accéléromètre, gyroscope, GPS, etc.)</li>
                    <li>Exportez les données au format JSON depuis l'application Sensor Logger</li>
                    <li>Téléchargez le fichier JSON ci-dessous</li>
                </ol>
                
                <div class="sensor-logger-info">
                    <h5>À propos de Sensor Logger</h5>
                    <p>Sensor Logger est une application mobile disponible gratuitement sur iPhone et Android qui permet d'enregistrer les données des capteurs de votre smartphone.</p>
                    <div class="app-links">
                        <a href="https://apps.apple.com/app/sensor-logger/id1531582925" target="_blank" class="btn btn-outline btn-sm">
                            <i class="fab fa-apple"></i> App Store
                        </a>
                        <a href="https://play.google.com/store/apps/details?id=com.kelvin.sensorapp" target="_blank" class="btn btn-outline btn-sm">
                            <i class="fab fa-android"></i> Google Play
                        </a>
                    </div>
                </div>
            </div>

            <form action="<?= \App\Utils\View::url('/telemetrie/process-import/' . $session['id']) ?>" method="post" enctype="multipart/form-data" class="form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-group">
                    <label for="telemetrie_file">Fichier de données télémétriques (JSON)</label>
                    <input type="file" id="telemetrie_file" name="telemetrie_file" accept=".json,application/json" required>
                    <div class="form-help">Sélectionnez le fichier JSON exporté depuis l'application Sensor Logger</div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Importer les données</button>
                    <a href="<?= \App\Utils\View::url('/telemetrie/view/' . $session['id']) ?>" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
