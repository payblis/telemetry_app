<?php
require_once '../../includes/init.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Récupération des préférences actuelles
$preferences = $notification->getPreferences($_SESSION['user_id']);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $newPreferences = [
            'email_enabled' => isset($_POST['email_enabled']),
            'push_enabled' => isset($_POST['push_enabled']),
            'session_analysis' => isset($_POST['session_analysis']),
            'performance_alerts' => isset($_POST['performance_alerts']),
            'maintenance' => isset($_POST['maintenance']),
            'weather' => isset($_POST['weather']),
            'events' => isset($_POST['events']),
            'daily_summary' => isset($_POST['daily_summary']),
            'weekly_report' => isset($_POST['weekly_report']),
            'quiet_hours_start' => $_POST['quiet_hours_start'],
            'quiet_hours_end' => $_POST['quiet_hours_end']
        ];

        // Validation des heures calmes
        if ($newPreferences['quiet_hours_start'] >= $newPreferences['quiet_hours_end']) {
            throw new Exception('L\'heure de fin doit être postérieure à l\'heure de début');
        }

        // Mise à jour des préférences
        $notification->updatePreferences($_SESSION['user_id'], $newPreferences);
        $success = 'Préférences mises à jour avec succès';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres des notifications - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <main class="container">
        <div class="notification-settings">
            <h1>Paramètres des notifications</h1>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="settings-form">
                <!-- Canaux de notification -->
                <section class="settings-section">
                    <h2>Canaux de notification</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <div class="setting-info">
                                <h3>Notifications par email</h3>
                                <p>Recevoir des notifications par email</p>
                            </div>
                            <label class="setting-toggle">
                                <input type="checkbox" name="email_enabled" <?php echo $preferences['email_enabled'] ? 'checked' : ''; ?>>
                                <span class="setting-toggle-slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h3>Notifications push</h3>
                                <p>Recevoir des notifications dans le navigateur</p>
                            </div>
                            <label class="setting-toggle">
                                <input type="checkbox" name="push_enabled" <?php echo $preferences['push_enabled'] ? 'checked' : ''; ?>>
                                <span class="setting-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </section>

                <!-- Types de notifications -->
                <section class="settings-section">
                    <h2>Types de notifications</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <div class="setting-info">
                                <h3>Analyse de session</h3>
                                <p>Recevoir une analyse détaillée après chaque session</p>
                            </div>
                            <label class="setting-toggle">
                                <input type="checkbox" name="session_analysis" <?php echo $preferences['session_analysis'] ? 'checked' : ''; ?>>
                                <span class="setting-toggle-slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h3>Alertes de performance</h3>
                                <p>Être notifié des améliorations ou baisses de performance</p>
                            </div>
                            <label class="setting-toggle">
                                <input type="checkbox" name="performance_alerts" <?php echo $preferences['performance_alerts'] ? 'checked' : ''; ?>>
                                <span class="setting-toggle-slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h3>Maintenance</h3>
                                <p>Rappels de maintenance et entretien</p>
                            </div>
                            <label class="setting-toggle">
                                <input type="checkbox" name="maintenance" <?php echo $preferences['maintenance'] ? 'checked' : ''; ?>>
                                <span class="setting-toggle-slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h3>Météo</h3>
                                <p>Alertes météo pour vos sessions</p>
                            </div>
                            <label class="setting-toggle">
                                <input type="checkbox" name="weather" <?php echo $preferences['weather'] ? 'checked' : ''; ?>>
                                <span class="setting-toggle-slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h3>Événements</h3>
                                <p>Informations sur les événements à venir</p>
                            </div>
                            <label class="setting-toggle">
                                <input type="checkbox" name="events" <?php echo $preferences['events'] ? 'checked' : ''; ?>>
                                <span class="setting-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </section>

                <!-- Rapports périodiques -->
                <section class="settings-section">
                    <h2>Rapports périodiques</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <div class="setting-info">
                                <h3>Résumé quotidien</h3>
                                <p>Recevoir un résumé de vos activités quotidiennes</p>
                            </div>
                            <label class="setting-toggle">
                                <input type="checkbox" name="daily_summary" <?php echo $preferences['daily_summary'] ? 'checked' : ''; ?>>
                                <span class="setting-toggle-slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h3>Rapport hebdomadaire</h3>
                                <p>Recevoir un rapport détaillé de votre progression hebdomadaire</p>
                            </div>
                            <label class="setting-toggle">
                                <input type="checkbox" name="weekly_report" <?php echo $preferences['weekly_report'] ? 'checked' : ''; ?>>
                                <span class="setting-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </section>

                <!-- Heures calmes -->
                <section class="settings-section">
                    <h2>Heures calmes</h2>
                    <p class="section-description">Période pendant laquelle vous ne souhaitez pas recevoir de notifications</p>
                    <div class="quiet-hours">
                        <div class="time-input">
                            <label for="quiet_hours_start">Début</label>
                            <input type="time" id="quiet_hours_start" name="quiet_hours_start" value="<?php echo $preferences['quiet_hours_start']; ?>" required>
                        </div>
                        <div class="time-input">
                            <label for="quiet_hours_end">Fin</label>
                            <input type="time" id="quiet_hours_end" name="quiet_hours_end" value="<?php echo $preferences['quiet_hours_end']; ?>" required>
                        </div>
                    </div>
                </section>

                <!-- Actions du formulaire -->
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>

    <script>
        // Validation des heures calmes
        document.querySelector('.settings-form').addEventListener('submit', function(e) {
            const start = document.getElementById('quiet_hours_start').value;
            const end = document.getElementById('quiet_hours_end').value;

            if (start >= end) {
                e.preventDefault();
                alert('L\'heure de fin doit être postérieure à l\'heure de début');
            }
        });

        // Gestion des dépendances entre les toggles
        document.querySelectorAll('.setting-toggle input').forEach(toggle => {
            toggle.addEventListener('change', function() {
                if (this.name === 'push_enabled' && !this.checked) {
                    // Désactiver les notifications push
                    document.querySelectorAll('[name^="push_"]').forEach(input => {
                        if (input !== this) {
                            input.checked = false;
                            input.disabled = true;
                        }
                    });
                }
            });
        });
    </script>
</body>
</html> 