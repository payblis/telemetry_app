<?php
// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    return;
}

// Récupération des préférences de notification
$notificationPreferences = $notification->getPreferences($_SESSION['user_id']);
?>

<!-- Bouton de notification -->
<button id="notification-toggle" title="Notifications">
    <i class="fas fa-bell"></i>
    <span id="unread-count" style="display: none;">0</span>
</button>

<!-- Conteneur des notifications -->
<div id="notification-container">
    <div class="notification-header">
        <h3>Notifications</h3>
        <div class="notification-actions">
            <button id="mark-all-read" title="Tout marquer comme lu">
                <i class="fas fa-check-double"></i>
            </button>
            <button id="clear-all" title="Tout effacer">
                <i class="fas fa-trash"></i>
            </button>
            <a href="/notifications/settings.php" class="settings-link" title="Paramètres">
                <i class="fas fa-cog"></i>
            </a>
        </div>
    </div>
    <div id="notification-list">
        <!-- Les notifications seront chargées dynamiquement ici -->
    </div>
    <div class="notification-footer">
        <a href="/notifications/all.php" class="view-all">Voir toutes les notifications</a>
    </div>
</div>

<!-- Scripts -->
<script src="/assets/js/websocket.js"></script>
<script src="/assets/js/sse.js"></script>
<script src="/assets/js/notification-ui.js"></script>

<script>
// Configuration du mode de notification en temps réel
const REALTIME_MODE = '<?php echo NOTIFICATION_REALTIME_MODE; ?>'; // 'websocket' ou 'sse'

document.addEventListener('DOMContentLoaded', () => {
    // Initialisation du mode de notification en temps réel approprié
    if (REALTIME_MODE === 'websocket') {
        wsManager.connect();
    } else if (REALTIME_MODE === 'sse') {
        sseManager.connect();
    }
});

// Gestion de la déconnexion
window.addEventListener('beforeunload', () => {
    if (REALTIME_MODE === 'websocket') {
        wsManager.disconnect();
    } else if (REALTIME_MODE === 'sse') {
        sseManager.disconnect();
    }
});
</script> 