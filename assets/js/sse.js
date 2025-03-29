class SSEManager {
    constructor() {
        this.eventSource = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000;
    }

    connect() {
        if (this.eventSource) {
            return;
        }

        this.eventSource = new EventSource('/api/notifications/stream.php');

        this.eventSource.addEventListener('connected', (event) => {
            console.log('SSE connecté:', JSON.parse(event.data));
            this.reconnectAttempts = 0;
        });

        this.eventSource.addEventListener('notifications', (event) => {
            const data = JSON.parse(event.data);
            if (typeof updateNotificationUI === 'function') {
                updateNotificationUI(data.notifications);
            }
        });

        this.eventSource.addEventListener('unread_count', (event) => {
            const data = JSON.parse(event.data);
            if (typeof updateUnreadCount === 'function') {
                updateUnreadCount(data.count);
            }
        });

        this.eventSource.addEventListener('error', (event) => {
            console.error('Erreur SSE:', event);
            this.handleError();
        });
    }

    handleError() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }

        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Tentative de reconnexion SSE ${this.reconnectAttempts}/${this.maxReconnectAttempts}`);
            
            setTimeout(() => {
                this.connect();
            }, this.reconnectDelay * this.reconnectAttempts);
        } else {
            console.error('Impossible de se reconnecter au serveur SSE');
        }
    }

    disconnect() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }
    }
}

// Création d'une instance globale du gestionnaire SSE
const sseManager = new SSEManager();

// Connexion automatique au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    sseManager.connect();
});

// Gestion de la déconnexion lors de la fermeture de la page
window.addEventListener('beforeunload', () => {
    sseManager.disconnect();
}); 