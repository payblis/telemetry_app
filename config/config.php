<?php
/**
 * Configuration principale de l'application SaaS de Télémétrie Moto
 */

// Mode de débogage (à désactiver en production)
define('DEBUG_MODE', true);

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'test2');
define('DB_USER', 'test2');
define('DB_PASS', '3S890zqy#');
define('DB_CHARSET', 'utf8mb4');

// Configuration des chemins
define('BASE_URL', 'https://test2.payblis.com');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_PATH', ROOT_PATH . '/public/uploads');
define('VIEWS_PATH', ROOT_PATH . '/resources/views');

// Configuration de l'application
define('APP_NAME', 'Télémétrie Moto SaaS');
define('APP_VERSION', '1.0.0');
define('DEFAULT_LANGUAGE', 'fr');
define('SESSION_LIFETIME', 86400); // 24 heures en secondes

// Configuration de sécurité
define('HASH_SALT', 'telemetrie_moto_salt_' . APP_VERSION); // À changer en production
define('TOKEN_LIFETIME', 3600); // 1 heure en secondes

// Configuration API
define('API_VERSION', 'v1');
define('API_KEY_REQUIRED', true);

// Configuration OpenAI (pour les recommandations IA)
define('OPENAI_API_KEY', ''); // À remplir avec votre clé API
define('OPENAI_MODEL', 'gpt-4');
define('OPENAI_TEMPERATURE', 0.7);
define('OPENAI_MAX_TOKENS', 500);

// Configuration des limites
define('MAX_UPLOAD_SIZE', 100 * 1024 * 1024); // 100 MB
define('MAX_VIDEO_SIZE', 500 * 1024 * 1024); // 500 MB
define('MAX_API_CALLS_PER_DAY', 100);

// Configuration des formats acceptés
define('ACCEPTED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ACCEPTED_VIDEO_TYPES', ['video/mp4', 'video/quicktime', 'video/x-msvideo']);
define('ACCEPTED_DATA_TYPES', ['application/json']);

// Fonctions utilitaires de configuration
function config($key, $default = null) {
    $config = [
        'app' => [
            'name' => APP_NAME,
            'version' => APP_VERSION,
            'debug' => DEBUG_MODE,
            'default_language' => DEFAULT_LANGUAGE
        ],
        'db' => [
            'host' => DB_HOST,
            'name' => DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASS,
            'charset' => DB_CHARSET
        ],
        'paths' => [
            'base' => BASE_URL,
            'assets' => ASSETS_URL,
            'uploads' => UPLOADS_PATH,
            'views' => VIEWS_PATH
        ],
        'security' => [
            'hash_salt' => HASH_SALT,
            'token_lifetime' => TOKEN_LIFETIME,
            'session_lifetime' => SESSION_LIFETIME
        ],
        'api' => [
            'version' => API_VERSION,
            'key_required' => API_KEY_REQUIRED,
            'max_calls_per_day' => MAX_API_CALLS_PER_DAY
        ],
        'openai' => [
            'api_key' => OPENAI_API_KEY,
            'model' => OPENAI_MODEL,
            'temperature' => OPENAI_TEMPERATURE,
            'max_tokens' => OPENAI_MAX_TOKENS
        ],
        'limits' => [
            'max_upload_size' => MAX_UPLOAD_SIZE,
            'max_video_size' => MAX_VIDEO_SIZE
        ],
        'formats' => [
            'accepted_image_types' => ACCEPTED_IMAGE_TYPES,
            'accepted_video_types' => ACCEPTED_VIDEO_TYPES,
            'accepted_data_types' => ACCEPTED_DATA_TYPES
        ]
    ];
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $default;
        }
    }
    
    return $value;
}
