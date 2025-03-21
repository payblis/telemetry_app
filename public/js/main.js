// Fonction pour initialiser les composants au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    initializeCharts();
    setupAjaxHandlers();
});

// Initialisation des tooltips Bootstrap
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialisation des graphiques (à implémenter avec Chart.js)
function initializeCharts() {
    // Cette fonction sera implémentée plus tard avec Chart.js
    console.log('Charts initialization placeholder');
}

// Configuration des gestionnaires AJAX
function setupAjaxHandlers() {
    // Gestionnaire d'erreur AJAX global
    $(document).ajaxError(function(event, jqXHR, settings, error) {
        showNotification('Erreur', 'Une erreur est survenue lors de la communication avec le serveur.', 'error');
    });
}

// Fonction pour afficher les notifications
function showNotification(title, message, type = 'info') {
    // Création de l'élément de notification
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.innerHTML = `
        <strong>${title}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Ajout de la notification au conteneur
    const container = document.getElementById('notifications-container') || document.body;
    container.appendChild(notification);

    // Auto-suppression après 5 secondes
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Fonction pour formater les données télémétrique
function formatTelemetryData(data) {
    return {
        timestamp: new Date(data.timestamp).toLocaleString(),
        speed: data.speed.toFixed(1) + ' km/h',
        rpm: data.rpm.toFixed(0),
        gear: data.gear,
        throttle: (data.throttle * 100).toFixed(1) + '%',
        brake: (data.brake * 100).toFixed(1) + '%',
        lean_angle: data.lean_angle.toFixed(1) + '°'
    };
}

// Fonction pour mettre à jour l'interface en temps réel
function updateTelemetryUI(data) {
    const formattedData = formatTelemetryData(data);
    
    // Mise à jour des éléments de l'interface
    Object.keys(formattedData).forEach(key => {
        const element = document.getElementById(`telemetry-${key}`);
        if (element) {
            element.textContent = formattedData[key];
        }
    });
}

// Fonction pour gérer les connexions WebSocket
function initializeWebSocket(sessionId) {
    const ws = new WebSocket(`ws://${window.location.host}/ws/telemetry/${sessionId}`);
    
    ws.onmessage = function(event) {
        const data = JSON.parse(event.data);
        updateTelemetryUI(data);
    };
    
    ws.onerror = function(error) {
        showNotification('Erreur WebSocket', 'La connexion temps réel a été perdue.', 'error');
    };
    
    return ws;
}

// Export des fonctions pour utilisation externe
window.TelemetryApp = {
    showNotification,
    initializeWebSocket,
    updateTelemetryUI
}; 