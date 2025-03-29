class NotificationManager {
    constructor() {
        this.supported = 'Notification' in window && 'serviceWorker' in navigator;
        this.permission = Notification.permission;
        this.subscription = null;
        this.registration = null;
        this.vapidPublicKey = VAPID_PUBLIC_KEY;
    }

    async init() {
        if (!this.supported) {
            console.log('Les notifications ne sont pas supportées par votre navigateur');
            return false;
        }

        try {
            // Enregistrement du service worker
            this.registration = await navigator.serviceWorker.register('/service-worker.js');
            console.log('Service Worker enregistré');

            // Récupération de l'abonnement existant
            this.subscription = await this.registration.pushManager.getSubscription();
            if (this.subscription) {
                console.log('Abonnement existant trouvé');
                await this.updateSubscription();
            }

            // Demande de permission si nécessaire
            if (this.permission === 'default') {
                this.permission = await Notification.requestPermission();
                if (this.permission === 'granted') {
                    await this.subscribe();
                }
            }

            return true;
        } catch (error) {
            console.error('Erreur lors de l\'initialisation des notifications:', error);
            return false;
        }
    }

    async subscribe() {
        try {
            // Génération des clés pour l'abonnement
            const applicationServerKey = this.urlBase64ToUint8Array(this.vapidPublicKey);
            
            // Création de l'abonnement
            this.subscription = await this.registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            });

            // Envoi de l'abonnement au serveur
            await this.updateSubscription();
            
            console.log('Abonnement aux notifications créé');
            return true;
        } catch (error) {
            console.error('Erreur lors de l\'abonnement aux notifications:', error);
            return false;
        }
    }

    async unsubscribe() {
        try {
            if (this.subscription) {
                await this.subscription.unsubscribe();
                this.subscription = null;
                
                // Suppression de l'abonnement sur le serveur
                await fetch('/api/notifications/unsubscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                console.log('Désabonnement des notifications effectué');
                return true;
            }
            return false;
        } catch (error) {
            console.error('Erreur lors du désabonnement des notifications:', error);
            return false;
        }
    }

    async updateSubscription() {
        try {
            const response = await fetch('/api/notifications/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    subscription: this.subscription.toJSON()
                })
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la mise à jour de l\'abonnement');
            }

            console.log('Abonnement mis à jour sur le serveur');
            return true;
        } catch (error) {
            console.error('Erreur lors de la mise à jour de l\'abonnement:', error);
            return false;
        }
    }

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Gestion des préférences de notification
    async updatePreferences(preferences) {
        try {
            const response = await fetch('/api/notifications/preferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(preferences)
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la mise à jour des préférences');
            }

            console.log('Préférences de notification mises à jour');
            return true;
        } catch (error) {
            console.error('Erreur lors de la mise à jour des préférences:', error);
            return false;
        }
    }

    async getPreferences() {
        try {
            const response = await fetch('/api/notifications/preferences');
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des préférences');
            }

            return await response.json();
        } catch (error) {
            console.error('Erreur lors de la récupération des préférences:', error);
            return null;
        }
    }

    // Gestion des notifications non lues
    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/api/notifications/${notificationId}/read`, {
                method: 'POST'
            });

            if (!response.ok) {
                throw new Error('Erreur lors du marquage de la notification comme lue');
            }

            console.log('Notification marquée comme lue');
            return true;
        } catch (error) {
            console.error('Erreur lors du marquage de la notification comme lue:', error);
            return false;
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/api/notifications/mark-all-read', {
                method: 'POST'
            });

            if (!response.ok) {
                throw new Error('Erreur lors du marquage de toutes les notifications comme lues');
            }

            console.log('Toutes les notifications marquées comme lues');
            return true;
        } catch (error) {
            console.error('Erreur lors du marquage de toutes les notifications comme lues:', error);
            return false;
        }
    }

    // Gestion des notifications en temps réel
    async getUnreadCount() {
        try {
            const response = await fetch('/api/notifications/unread-count');
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération du nombre de notifications non lues');
            }

            return await response.json();
        } catch (error) {
            console.error('Erreur lors de la récupération du nombre de notifications non lues:', error);
            return 0;
        }
    }

    // Mise à jour de l'interface utilisateur
    updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    // Gestion des clics sur les notifications
    handleNotificationClick(notification) {
        notification.close();

        const data = notification.data;
        if (data && data.url) {
            window.open(data.url, '_blank');
        }
    }
}

// Initialisation du gestionnaire de notifications
document.addEventListener('DOMContentLoaded', async () => {
    const notificationManager = new NotificationManager();
    await notificationManager.init();

    // Mise à jour du badge de notifications
    const unreadCount = await notificationManager.getUnreadCount();
    notificationManager.updateNotificationBadge(unreadCount);

    // Gestion des clics sur les notifications
    navigator.serviceWorker.addEventListener('message', (event) => {
        if (event.data && event.data.type === 'NOTIFICATION_CLICKED') {
            notificationManager.handleNotificationClick(event.data.notification);
        }
    });
}); 