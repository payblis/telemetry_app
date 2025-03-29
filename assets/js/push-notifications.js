class PushNotificationManager {
    constructor() {
        this.pushSupported = 'serviceWorker' in navigator && 'PushManager' in window;
        this.subscribed = false;
        this.registration = null;
    }

    async initialize() {
        if (!this.pushSupported) {
            console.log('Les notifications push ne sont pas supportées');
            return;
        }

        try {
            // Enregistrement du service worker
            this.registration = await navigator.serviceWorker.register('/service-worker.js');
            console.log('Service Worker enregistré');

            // Vérification de l'état de la souscription
            const subscription = await this.registration.pushManager.getSubscription();
            this.subscribed = !!subscription;

            // Mise à jour de l'interface utilisateur
            this.updateSubscriptionUI();
        } catch (error) {
            console.error('Erreur lors de l\'initialisation des notifications push:', error);
        }
    }

    async subscribe() {
        if (!this.pushSupported || !this.registration) {
            return;
        }

        try {
            // Demande de permission
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                throw new Error('Permission refusée');
            }

            // Conversion de la clé VAPID
            const vapidPublicKey = this.urlBase64ToUint8Array(VAPID_PUBLIC_KEY);

            // Souscription aux notifications push
            const subscription = await this.registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: vapidPublicKey
            });

            // Envoi de la souscription au serveur
            await this.sendSubscriptionToServer(subscription);

            this.subscribed = true;
            this.updateSubscriptionUI();
            
            console.log('Souscription aux notifications push réussie');
        } catch (error) {
            console.error('Erreur lors de la souscription aux notifications push:', error);
        }
    }

    async unsubscribe() {
        if (!this.pushSupported || !this.registration) {
            return;
        }

        try {
            const subscription = await this.registration.pushManager.getSubscription();
            if (subscription) {
                // Suppression de la souscription
                await subscription.unsubscribe();

                // Notification au serveur
                await this.deleteSubscriptionFromServer(subscription);

                this.subscribed = false;
                this.updateSubscriptionUI();
                
                console.log('Désinscription des notifications push réussie');
            }
        } catch (error) {
            console.error('Erreur lors de la désinscription des notifications push:', error);
        }
    }

    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch('/api/notifications/subscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    subscription: subscription.toJSON()
                })
            });

            if (!response.ok) {
                throw new Error('Erreur lors de l\'envoi de la souscription au serveur');
            }
        } catch (error) {
            console.error('Erreur lors de l\'envoi de la souscription:', error);
            throw error;
        }
    }

    async deleteSubscriptionFromServer(subscription) {
        try {
            const response = await fetch('/api/notifications/unsubscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    subscription: subscription.toJSON()
                })
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la suppression de la souscription du serveur');
            }
        } catch (error) {
            console.error('Erreur lors de la suppression de la souscription:', error);
            throw error;
        }
    }

    updateSubscriptionUI() {
        const pushToggle = document.getElementById('push-toggle');
        if (pushToggle) {
            pushToggle.checked = this.subscribed;
            pushToggle.disabled = !this.pushSupported;
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
}

// Création d'une instance globale du gestionnaire de notifications push
const pushManager = new PushNotificationManager();

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    pushManager.initialize();

    // Gestion du toggle des notifications push
    const pushToggle = document.getElementById('push-toggle');
    if (pushToggle) {
        pushToggle.addEventListener('change', async (e) => {
            if (e.target.checked) {
                await pushManager.subscribe();
            } else {
                await pushManager.unsubscribe();
            }
        });
    }
}); 