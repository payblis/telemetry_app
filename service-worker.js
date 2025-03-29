const CACHE_NAME = 'telemetry-app-v1';
const ASSETS_TO_CACHE = [
    '/',
    '/index.php',
    '/manifest.json',
    '/assets/css/style.css',
    '/assets/css/telemetry.css',
    '/assets/js/telemetry.js',
    '/assets/icons/icon-72x72.png',
    '/assets/icons/icon-96x96.png',
    '/assets/icons/icon-128x128.png',
    '/assets/icons/icon-144x144.png',
    '/assets/icons/icon-152x152.png',
    '/assets/icons/icon-192x192.png',
    '/assets/icons/icon-384x384.png',
    '/assets/icons/icon-512x512.png',
    '/assets/icons/session-96x96.png',
    '/assets/icons/dashboard-96x96.png',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css'
];

// Installation du service worker
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Cache ouvert');
                return cache.addAll(ASSETS_TO_CACHE);
            })
    );
});

// Activation du service worker
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Suppression de l\'ancien cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Interception des requêtes
self.addEventListener('fetch', (event) => {
    // Stratégie "Network First" pour les API
    if (event.request.url.includes('/api/')) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    return response;
                })
                .catch(() => {
                    return caches.match(event.request);
                })
        );
        return;
    }

    // Stratégie "Cache First" pour les assets statiques
    if (event.request.url.match(/\.(css|js|png|jpg|jpeg|gif|ico)$/)) {
        event.respondWith(
            caches.match(event.request)
                .then((response) => {
                    return response || fetch(event.request)
                        .then((fetchResponse) => {
                            return caches.open(CACHE_NAME)
                                .then((cache) => {
                                    cache.put(event.request, fetchResponse.clone());
                                    return fetchResponse;
                                });
                        });
                })
        );
        return;
    }

    // Stratégie "Network First" pour les autres requêtes
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                return caches.open(CACHE_NAME)
                    .then((cache) => {
                        cache.put(event.request, response.clone());
                        return response;
                    });
            })
            .catch(() => {
                return caches.match(event.request);
            })
    );
});

// Gestion des notifications push
self.addEventListener('push', function(event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    const data = event.data ? event.data.json() : {};
    const title = data.title || 'Nouvelle notification';
    const options = {
        body: data.message || 'Vous avez une nouvelle notification',
        icon: data.icon || '/assets/images/notification-icon.png',
        badge: data.badge || '/assets/images/notification-badge.png',
        tag: data.tag || 'default',
        data: data.data || {},
        actions: data.actions || [],
        vibrate: data.vibrate || [200, 100, 200],
        requireInteraction: data.requireInteraction || false,
        renotify: data.renotify || false,
        silent: data.silent || false,
        timestamp: data.timestamp || Date.now()
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Gestion des clics sur les notifications
self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    const notificationData = event.notification.data;
    let urlToOpen = notificationData.url || '/';

    if (event.action) {
        switch (event.action) {
            case 'view':
                urlToOpen = notificationData.viewUrl || urlToOpen;
                break;
            case 'settings':
                urlToOpen = '/notifications/settings.php';
                break;
            // Ajoutez d'autres actions si nécessaire
        }
    }

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        })
        .then(function(clientList) {
            // Vérification si une fenêtre est déjà ouverte
            for (let client of clientList) {
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            // Si aucune fenêtre n'est ouverte, en ouvrir une nouvelle
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

// Synchronisation en arrière-plan
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-telemetry') {
        event.waitUntil(
            // Synchroniser les données de télémétrie
            fetch('/api/telemetry.php?action=sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    timestamp: Date.now()
                })
            })
        );
    }
});

self.addEventListener('notificationclose', function(event) {
    // Enregistrement de la fermeture de la notification
    const notificationData = event.notification.data;
    if (notificationData.id) {
        fetch('/api/notifications/track.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'close',
                notificationId: notificationData.id
            })
        }).catch(function(error) {
            console.error('Erreur lors du suivi de la notification:', error);
        });
    }
});

self.addEventListener('install', function(event) {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', function(event) {
    event.waitUntil(self.clients.claim());
}); 