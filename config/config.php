<?php
// Configuration de l'application
define('APP_NAME', 'TéléMoto AI');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost'); // À modifier en production

// Configuration des chemins
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Configuration des sessions
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600); // 1 heure

// Configuration de la sécurité
define('HASH_COST', 12); // Coût du hachage bcrypt
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Configuration des fichiers
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10 MB
define('ALLOWED_EXTENSIONS', ['csv', 'xml', 'json']);

// Configuration des graphiques
define('CHART_COLORS', [
    '#2563eb', // Bleu
    '#dc2626', // Rouge
    '#059669', // Vert
    '#d97706', // Orange
    '#7c3aed', // Violet
    '#db2777'  // Rose
]);

// Configuration des messages d'erreur
define('ERROR_MESSAGES', [
    'login_failed' => 'Identifiants incorrects',
    'session_expired' => 'Votre session a expiré',
    'invalid_csrf' => 'Token de sécurité invalide',
    'upload_failed' => 'Échec du téléchargement du fichier',
    'invalid_file_type' => 'Type de fichier non autorisé',
    'file_too_large' => 'Fichier trop volumineux'
]);

// Configuration des formats de date
define('DATE_FORMAT', 'd/m/Y');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');

// Configuration des limites
define('ITEMS_PER_PAGE', 20);
define('MAX_SEARCH_RESULTS', 100);
define('CACHE_DURATION', 3600);

// Configuration des types de données télémétriques
define('TELEMETRY_TYPES', [
    'vitesse' => [
        'unit' => 'km/h',
        'min' => 0,
        'max' => 350
    ],
    'regime_moteur' => [
        'unit' => 'tr/min',
        'min' => 0,
        'max' => 16000
    ],
    'angle_inclinaison' => [
        'unit' => '°',
        'min' => -70,
        'max' => 70
    ],
    'temperature_pneu' => [
        'unit' => '°C',
        'min' => 0,
        'max' => 120
    ],
    'suspension' => [
        'unit' => 'mm',
        'min' => 0,
        'max' => 150
    ]
]); 