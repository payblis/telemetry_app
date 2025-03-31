<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/config/config.php';

// Vérifier si le fichier manifest.json existe déjà
$manifest_file = __DIR__ . '/manifest.json';
if (!file_exists($manifest_file)) {
    // Créer le fichier manifest.json
    $manifest = [
        'name' => 'TeleMoto',
        'short_name' => 'TeleMoto',
        'description' => 'Application de télémétrie pour les pilotes moto',
        'start_url' => '/',
        'display' => 'standalone',
        'background_color' => '#1e2430',
        'theme_color' => '#00a8ff',
        'icons' => [
            [
                'src' => 'images/icons/icon-72x72.png',
                'sizes' => '72x72',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/icons/icon-96x96.png',
                'sizes' => '96x96',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/icons/icon-128x128.png',
                'sizes' => '128x128',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/icons/icon-144x144.png',
                'sizes' => '144x144',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/icons/icon-152x152.png',
                'sizes' => '152x152',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/icons/icon-192x192.png',
                'sizes' => '192x192',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/icons/icon-384x384.png',
                'sizes' => '384x384',
                'type' => 'image/png'
            ],
            [
                'src' => 'images/icons/icon-512x512.png',
                'sizes' => '512x512',
                'type' => 'image/png'
            ]
        ]
    ];
    
    file_put_contents($manifest_file, json_encode($manifest, JSON_PRETTY_PRINT));
}

// Vérifier si le fichier service-worker.js existe déjà
$sw_file = __DIR__ . '/service-worker.js';
if (!file_exists($sw_file)) {
    // Créer le fichier service-worker.js
    $service_worker = <<<'EOT'
// Nom du cache
const CACHE_NAME = 'telemoto-cache-v1';

// Fichiers à mettre en cache
const urlsToCache = [
  '/',
  '/index.php',
  '/css/style.css',
  '/js/main.js',
  '/js/pwa.js',
  '/manifest.json',
  '/images/logo.png',
  '/images/icons/icon-192x192.png',
  '/images/icons/icon-512x512.png',
  '/offline.html'
];

// Installation du Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Cache ouvert');
        return cache.addAll(urlsToCache);
      })
  );
});

// Activation du Service Worker
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Interception des requêtes
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Cache hit - retourner la réponse du cache
        if (response) {
          return response;
        }
        
        // Cloner la requête
        const fetchRequest = event.request.clone();
        
        return fetch(fetchRequest)
          .then(response => {
            // Vérifier si la réponse est valide
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }
            
            // Cloner la réponse
            const responseToCache = response.clone();
            
            caches.open(CACHE_NAME)
              .then(cache => {
                cache.put(event.request, responseToCache);
              });
              
            return response;
          })
          .catch(() => {
            // Si la requête échoue (pas de connexion), afficher la page hors ligne
            if (event.request.mode === 'navigate') {
              return caches.match('/offline.html');
            }
          });
      })
  );
});

// Gestion des notifications push
self.addEventListener('push', event => {
  const data = event.data.json();
  const options = {
    body: data.body,
    icon: '/images/icons/icon-192x192.png',
    badge: '/images/icons/icon-72x72.png',
    data: {
      url: data.url
    }
  };
  
  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

// Gestion du clic sur les notifications
self.addEventListener('notificationclick', event => {
  event.notification.close();
  
  event.waitUntil(
    clients.openWindow(event.notification.data.url)
  );
});
EOT;
    
    file_put_contents($sw_file, $service_worker);
}

// Vérifier si le fichier offline.html existe déjà
$offline_file = __DIR__ . '/offline.html';
if (!file_exists($offline_file)) {
    // Créer le fichier offline.html
    $offline_html = <<<'EOT'
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeleMoto - Hors ligne</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1e2430;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }
        
        .container {
            max-width: 600px;
            padding: 2rem;
            background-color: #2a3142;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #00a8ff;
            margin-bottom: 1rem;
        }
        
        p {
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 2rem;
        }
        
        .btn {
            display: inline-block;
            background-color: #00a8ff;
            color: #000;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #0095e0;
        }
        
        .offline-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="offline-icon">📶</div>
        <h1>Vous êtes hors ligne</h1>
        <p>Il semble que vous n'ayez pas de connexion Internet. Certaines fonctionnalités de TeleMoto ne sont pas disponibles en mode hors ligne.</p>
        <p>Vous pouvez toujours accéder aux données mises en cache et consulter vos sessions précédentes.</p>
        <a href="/" class="btn">Retour à l'accueil</a>
    </div>
</body>
</html>
EOT;
    
    file_put_contents($offline_file, $offline_html);
}

// Vérifier si le dossier images/icons existe
$icons_dir = __DIR__ . '/images/icons';
if (!is_dir($icons_dir)) {
    // Créer le dossier
    mkdir($icons_dir, 0755, true);
}

// Vérifier si le fichier js/pwa.js existe déjà
$js_dir = __DIR__ . '/js';
if (!is_dir($js_dir)) {
    // Créer le dossier
    mkdir($js_dir, 0755, true);
}

$pwa_file = $js_dir . '/pwa.js';
if (!file_exists($pwa_file)) {
    // Créer le fichier js/pwa.js
    $pwa_js = <<<'EOT'
// Enregistrement du Service Worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js')
      .then(registration => {
        console.log('Service Worker enregistré avec succès:', registration.scope);
      })
      .catch(error => {
        console.log('Échec de l\'enregistrement du Service Worker:', error);
      });
  });
}

// Gestion de l'installation de l'application
let deferredPrompt;
const addBtn = document.querySelector('.add-to-home');

window.addEventListener('beforeinstallprompt', (e) => {
  // Empêcher Chrome d'afficher automatiquement la bannière d'installation
  e.preventDefault();
  // Stocker l'événement pour pouvoir le déclencher plus tard
  deferredPrompt = e;
  // Afficher le bouton d'installation
  if (addBtn) {
    addBtn.style.display = 'block';
  }
});

// Gérer le clic sur le bouton d'installation
if (addBtn) {
  addBtn.addEventListener('click', (e) => {
    // Masquer le bouton d'installation
    addBtn.style.display = 'none';
    // Afficher la bannière d'installation
    deferredPrompt.prompt();
    // Attendre que l'utilisateur réponde à la bannière
    deferredPrompt.userChoice.then((choiceResult) => {
      if (choiceResult.outcome === 'accepted') {
        console.log('Utilisateur a accepté l\'installation');
      } else {
        console.log('Utilisateur a refusé l\'installation');
      }
      deferredPrompt = null;
    });
  });
}

// Détecter si l'application est lancée depuis l'écran d'accueil
window.addEventListener('appinstalled', (evt) => {
  console.log('Application installée');
});

// Vérifier si l'application est en mode standalone
if (window.matchMedia('(display-mode: standalone)').matches) {
  console.log('Application lancée depuis l\'écran d\'accueil');
}

// Gestion des notifications
function requestNotificationPermission() {
  if ('Notification' in window) {
    Notification.requestPermission().then(permission => {
      if (permission === 'granted') {
        console.log('Permission de notification accordée');
        
        // Souscrire aux notifications push
        subscribeToPushNotifications();
      }
    });
  }
}

// Souscrire aux notifications push
function subscribeToPushNotifications() {
  if ('serviceWorker' in navigator && 'PushManager' in window) {
    navigator.serviceWorker.ready.then(registration => {
      registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array('YOUR_PUBLIC_VAPID_KEY')
      })
      .then(subscription => {
        console.log('Souscription réussie:', subscription);
        // Envoyer la souscription au serveur
        updateSubscriptionOnServer(subscription);
      })
      .catch(error => {
        console.error('Échec de la souscription:', error);
      });
    });
  }
}

// Convertir la clé VAPID en format approprié
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

// Envoyer la souscription au serveur
function updateSubscriptionOnServer(subscription) {
  const subscriptionJson = JSON.stringify(subscription);
  
  fetch('/api/save-subscription.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: subscriptionJson
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Erreur lors de l\'envoi de la souscription');
    }
    return response.json();
  })
  .then(data => {
    console.log('Souscription enregistrée sur le serveur:', data);
  })
  .catch(error => {
    console.error('Erreur:', error);
  });
}

// Gestion du mode hors ligne
window.addEventListener('online', () => {
  document.body.classList.remove('offline');
  document.querySelector('.offline-indicator')?.classList.remove('visible');
});

window.addEventListener('offline', () => {
  document.body.classList.add('offline');
  document.querySelector('.offline-indicator')?.classList.add('visible');
});

// Vérifier l'état de la connexion au chargement
if (!navigator.onLine) {
  document.body.classList.add('offline');
  document.querySelector('.offline-indicator')?.classList.add('visible');
}

// Synchronisation en arrière-plan
if ('serviceWorker' in navigator && 'SyncManager' in window) {
  navigator.serviceWorker.ready.then(registration => {
    // Enregistrer une tâche de synchronisation
    document.querySelector('.sync-form')?.addEventListener('submit', event => {
      event.preventDefault();
      
      // Stocker les données dans IndexedDB
      storeDataForSync().then(() => {
        // Enregistrer la synchronisation
        registration.sync.register('sync-form-data').then(() => {
          console.log('Synchronisation enregistrée');
        }).catch(error => {
          console.error('Échec de l\'enregistrement de la synchronisation:', error);
        });
      });
    });
  });
}

// Stocker les données dans IndexedDB pour la synchronisation
function storeDataForSync() {
  // Implémentation de stockage dans IndexedDB
  return new Promise((resolve, reject) => {
    // Code de stockage dans IndexedDB
    resolve();
  });
}
EOT;
    
    file_put_contents($pwa_file, $pwa_js);
}

// Vérifier si le fichier api/save-subscription.php existe déjà
$api_dir = __DIR__ . '/api';
if (!is_dir($api_dir)) {
    // Créer le dossier
    mkdir($api_dir, 0755, true);
}

$subscription_file = $api_dir . '/save-subscription.php';
if (!file_exists($subscription_file)) {
    // Créer le fichier api/save-subscription.php
    $subscription_php = <<<'EOT'
<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/auth_functions.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que l'utilisateur est connecté
if (!isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Utilisateur non connecté']);
    exit;
}

// Récupérer l'ID de l'utilisateur
$user_id = $_SESSION['user_id'];

// Récupérer les données de souscription
$subscription = json_decode(file_get_contents('php://input'), true);

if (!$subscription) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Données de souscription invalides']);
    exit;
}

// Connexion à la base de données
$conn = getDBConnection();

// Vérifier si la souscription existe déjà
$stmt = $conn->prepare("SELECT id FROM push_subscriptions WHERE utilisateur_id = ? AND endpoint = ?");
$stmt->bind_param("is", $user_id, $subscription['endpoint']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Mettre à jour la souscription existante
    $row = $result->fetch_assoc();
    $subscription_id = $row['id'];
    
    $stmt = $conn->prepare("UPDATE push_subscriptions SET auth = ?, p256dh = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $subscription['keys']['auth'], $subscription['keys']['p256dh'], $subscription_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Souscription mise à jour']);
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Erreur lors de la mise à jour de la souscription']);
    }
} else {
    // Insérer une nouvelle souscription
    $stmt = $conn->prepare("INSERT INTO push_subscriptions (utilisateur_id, endpoint, auth, p256dh, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("isss", $user_id, $subscription['endpoint'], $subscription['keys']['auth'], $subscription['keys']['p256dh']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Souscription enregistrée']);
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Erreur lors de l\'enregistrement de la souscription']);
    }
}
EOT;
    
    file_put_contents($subscription_file, $subscription_php);
}

// Modifier le fichier includes/header.php pour ajouter les balises meta et les liens vers les fichiers PWA
$header_file = __DIR__ . '/includes/header.php';
if (file_exists($header_file)) {
    $header_content = file_get_contents($header_file);
    
    // Vérifier si les balises meta pour PWA sont déjà présentes
    if (strpos($header_content, 'manifest.json') === false) {
        // Ajouter les balises meta pour PWA
        $pwa_meta = <<<'EOT'
    <!-- Meta tags pour PWA -->
    <meta name="theme-color" content="#00a8ff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TeleMoto">
    <link rel="manifest" href="<?php echo url('manifest.json'); ?>">
    <link rel="apple-touch-icon" href="<?php echo url('images/icons/icon-192x192.png'); ?>">
    <!-- Fin des meta tags pour PWA -->
EOT;
        
        // Insérer les balises meta après la balise <title>
        $header_content = preg_replace('/<\/title>/', '</title>' . PHP_EOL . $pwa_meta, $header_content);
        
        // Ajouter le script PWA avant la fermeture de la balise </head>
        $pwa_script = <<<'EOT'
    <!-- Script PWA -->
    <script src="<?php echo url('js/pwa.js'); ?>" defer></script>
    <!-- Fin du script PWA -->
EOT;
        
        $header_content = preg_replace('/<\/head>/', $pwa_script . PHP_EOL . '</head>', $header_content);
        
        // Ajouter l'indicateur hors ligne avant la fermeture de la balise <body>
        $offline_indicator = <<<'EOT'
    <!-- Indicateur hors ligne -->
    <div class="offline-indicator">
        <div class="offline-message">
            <i class="fas fa-wifi-slash"></i> Vous êtes hors ligne
        </div>
    </div>
    <!-- Fin de l'indicateur hors ligne -->
EOT;
        
        $header_content = preg_replace('/<\/body>/', $offline_indicator . PHP_EOL . '</body>', $header_content);
        
        // Enregistrer les modifications
        file_put_contents($header_file, $header_content);
    }
}

// Ajouter des styles CSS pour l'indicateur hors ligne
$css_file = __DIR__ . '/css/style.css';
if (file_exists($css_file)) {
    $css_content = file_get_contents($css_file);
    
    // Vérifier si les styles pour l'indicateur hors ligne sont déjà présents
    if (strpos($css_content, 'offline-indicator') === false) {
        // Ajouter les styles pour l'indicateur hors ligne
        $offline_styles = <<<'EOT'

/* Styles pour PWA */
.offline-indicator {
    display: none;
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: rgba(255, 62, 62, 0.9);
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    z-index: 9999;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.offline-indicator.visible {
    display: block;
    animation: fadeIn 0.3s ease-in-out;
}

.offline-message {
    display: flex;
    align-items: center;
    gap: 10px;
}

.offline-message i {
    font-size: 1.2rem;
}

body.offline .online-only {
    opacity: 0.5;
    pointer-events: none;
}

.add-to-home {
    display: none;
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: var(--primary-color);
    color: #000;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    z-index: 9998;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    font-weight: bold;
}

.add-to-home i {
    margin-right: 5px;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translate(-50%, 20px); }
    to { opacity: 1; transform: translate(-50%, 0); }
}
/* Fin des styles pour PWA */
EOT;
        
        // Ajouter les styles à la fin du fichier CSS
        file_put_contents($css_file, $css_content . $offline_styles);
    }
}

// Ajouter le bouton d'installation de l'application dans le fichier includes/footer.php
$footer_file = __DIR__ . '/includes/footer.php';
if (file_exists($footer_file)) {
    $footer_content = file_get_contents($footer_file);
    
    // Vérifier si le bouton d'installation est déjà présent
    if (strpos($footer_content, 'add-to-home') === false) {
        // Ajouter le bouton d'installation avant la fermeture de la balise <body>
        $install_button = <<<'EOT'
    <!-- Bouton d'installation PWA -->
    <div class="add-to-home">
        <i class="fas fa-download"></i> Installer l'application
    </div>
    <!-- Fin du bouton d'installation PWA -->
EOT;
        
        $footer_content = preg_replace('/<\/body>/', $install_button . PHP_EOL . '</body>', $footer_content);
        
        // Enregistrer les modifications
        file_put_contents($footer_file, $footer_content);
    }
}

// Rediriger vers la page d'accueil
header('Location: ' . url('index.php'));
exit;
?>
