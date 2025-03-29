class WebSocketManager {
    constructor() {
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000;
        this.pingInterval = null;
        this.isAuthenticated = false;
        this.userId = null;
    }

    connect() {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            return;
        }

        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsUrl = `${protocol}//${window.location.hostname}:8080`;
        
        this.ws = new WebSocket(wsUrl);
        
        this.ws.onopen = () => {
            console.log('WebSocket connecté');
            this.reconnectAttempts = 0;
            this.startPingInterval();
            this.authenticate();
        };

        this.ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleMessage(data);
        };

        this.ws.onclose = () => {
            console.log('WebSocket déconnecté');
            this.stopPingInterval();
            this.handleDisconnect();
        };

        this.ws.onerror = (error) => {
            console.error('Erreur WebSocket:', error);
        };
    }

    authenticate() {
        const token = this.getAuthToken();
        if (token) {
            this.send({
                type: 'authenticate',
                token: token
            });
        }
    }

    handleMessage(data) {
        switch (data.type) {
            case 'authenticated':
                this.handleAuthentication(data);
                break;
            case 'notifications':
                this.handleNotifications(data.notifications);
                break;
            case 'unread_count':
                this.handleUnreadCount(data.count);
                break;
            case 'pong':
                // Gestion du pong
                break;
            case 'error':
                this.handleError(data.error);
                break;
        }
    }

    handleAuthentication(data) {
        this.isAuthenticated = true;
        this.userId = data.userId;
        console.log('Authentification WebSocket réussie');
    }

    handleNotifications(notifications) {
        // Mise à jour de l'interface utilisateur avec les nouvelles notifications
        if (typeof updateNotificationUI === 'function') {
            updateNotificationUI(notifications);
        }
    }

    handleUnreadCount(count) {
        // Mise à jour du compteur de notifications non lues
        if (typeof updateUnreadCount === 'function') {
            updateUnreadCount(count);
        }
    }

    handleError(error) {
        console.error('Erreur WebSocket:', error);
        // Afficher l'erreur à l'utilisateur si nécessaire
    }

    handleDisconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            setTimeout(() => {
                console.log(`Tentative de reconnexion ${this.reconnectAttempts}/${this.maxReconnectAttempts}`);
                this.connect();
            }, this.reconnectDelay * this.reconnectAttempts);
        } else {
            console.error('Impossible de se reconnecter au serveur WebSocket');
            // Afficher un message à l'utilisateur
        }
    }

    startPingInterval() {
        this.pingInterval = setInterval(() => {
            if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                this.send({ type: 'ping' });
            }
        }, 30000); // Ping toutes les 30 secondes
    }

    stopPingInterval() {
        if (this.pingInterval) {
            clearInterval(this.pingInterval);
            this.pingInterval = null;
        }
    }

    send(data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
        } else {
            console.error('WebSocket non connecté');
        }
    }

    getAuthToken() {
        // Récupérer le token d'authentification depuis le localStorage ou les cookies
        return localStorage.getItem('ws_token');
    }

    disconnect() {
        if (this.ws) {
            this.ws.close();
            this.ws = null;
        }
        this.stopPingInterval();
        this.isAuthenticated = false;
        this.userId = null;
    }
}

// Création d'une instance globale du gestionnaire WebSocket
const wsManager = new WebSocketManager();

// Connexion automatique au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    wsManager.connect();
});

// Gestion de la déconnexion lors de la fermeture de la page
window.addEventListener('beforeunload', () => {
    wsManager.disconnect();
}); 