<?php

// Configuration des notifications en temps réel
define('NOTIFICATION_REALTIME_MODE', 'websocket'); // 'websocket' ou 'sse'

// Configuration WebSocket
define('WEBSOCKET_HOST', 'localhost');
define('WEBSOCKET_PORT', 8080);
define('WEBSOCKET_SECURE', false); // true pour WSS, false pour WS

// Configuration des notifications par email
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'notifications@example.com');
define('SMTP_PASSWORD', 'your_smtp_password');
define('SMTP_FROM_EMAIL', 'notifications@example.com');
define('SMTP_FROM_NAME', 'Telemetry App');

// Configuration des notifications push
define('VAPID_PUBLIC_KEY', 'your_vapid_public_key');
define('VAPID_PRIVATE_KEY', 'your_vapid_private_key');
define('VAPID_SUBJECT', 'mailto:admin@example.com');

// Configuration des notifications par défaut
define('DEFAULT_NOTIFICATION_PREFERENCES', json_encode([
    'email' => [
        'enabled' => true,
        'session_analysis' => true,
        'performance_alerts' => true,
        'maintenance' => true,
        'weather' => true,
        'events' => true,
        'daily_summary' => true,
        'weekly_report' => true
    ],
    'push' => [
        'enabled' => true,
        'session_analysis' => true,
        'performance_alerts' => true,
        'maintenance' => true,
        'weather' => true,
        'events' => true
    ],
    'quiet_hours' => [
        'enabled' => false,
        'start' => '22:00',
        'end' => '07:00',
        'timezone' => 'Europe/Paris'
    ]
]));

// Configuration des types de notifications
define('NOTIFICATION_TYPES', json_encode([
    'session_analysis' => [
        'name' => 'Analyse de session',
        'description' => 'Notifications concernant l\'analyse de vos sessions',
        'default_enabled' => true
    ],
    'performance_alert' => [
        'name' => 'Alertes de performance',
        'description' => 'Alertes sur les changements significatifs de performance',
        'default_enabled' => true
    ],
    'maintenance' => [
        'name' => 'Rappels de maintenance',
        'description' => 'Rappels pour l\'entretien de votre moto',
        'default_enabled' => true
    ],
    'weather' => [
        'name' => 'Alertes météo',
        'description' => 'Alertes sur les conditions météorologiques',
        'default_enabled' => true
    ],
    'event' => [
        'name' => 'Événements',
        'description' => 'Notifications concernant les événements à venir',
        'default_enabled' => true
    ],
    'daily_summary' => [
        'name' => 'Résumé quotidien',
        'description' => 'Résumé quotidien de vos activités',
        'default_enabled' => true
    ],
    'weekly_report' => [
        'name' => 'Rapport hebdomadaire',
        'description' => 'Rapport détaillé hebdomadaire',
        'default_enabled' => true
    ]
]));

// Configuration des seuils d'alerte de performance
define('PERFORMANCE_ALERT_THRESHOLDS', json_encode([
    'speed' => [
        'increase' => 5, // %
        'decrease' => -5 // %
    ],
    'lap_time' => [
        'increase' => 5, // %
        'decrease' => -5 // %
    ],
    'rpm' => [
        'increase' => 10, // %
        'decrease' => -10 // %
    ],
    'lean_angle' => [
        'increase' => 5, // degrés
        'decrease' => -5 // degrés
    ]
]));

// Configuration des seuils d'alerte météo
define('WEATHER_ALERT_THRESHOLDS', json_encode([
    'rain' => [
        'probability' => 70, // %
        'intensity' => 5 // mm/h
    ],
    'wind' => [
        'speed' => 30, // km/h
        'gusts' => 50 // km/h
    ],
    'temperature' => [
        'min' => 5, // °C
        'max' => 35 // °C
    ]
]));

// Configuration des rappels de maintenance
define('MAINTENANCE_REMINDERS', json_encode([
    'oil_change' => [
        'interval' => 5000, // km
        'priority' => 'high'
    ],
    'chain' => [
        'interval' => 1000, // km
        'priority' => 'medium'
    ],
    'tires' => [
        'interval' => 8000, // km
        'priority' => 'high'
    ],
    'brakes' => [
        'interval' => 5000, // km
        'priority' => 'high'
    ]
]));

// Configuration des rapports
define('REPORT_CONFIG', json_encode([
    'daily_summary' => [
        'time' => '20:00',
        'timezone' => 'Europe/Paris'
    ],
    'weekly_report' => [
        'day' => 1, // Lundi
        'time' => '09:00',
        'timezone' => 'Europe/Paris'
    ]
]));

// Configuration des URLs
define('NOTIFICATION_SETTINGS_URL', '/notifications/settings.php');
define('NOTIFICATION_LIST_URL', '/notifications/all.php');
define('NOTIFICATION_API_URL', '/api/notifications/');
define('NOTIFICATION_URLS', [
    'preferences' => '/notifications/settings',
    'session_analysis' => '/sessions/analysis',
    'performance_alerts' => '/stats/alerts',
    'maintenance' => '/maintenance',
    'weather' => '/weather',
    'events' => '/events',
    'daily_summary' => '/stats/daily',
    'weekly_report' => '/stats/weekly'
]); 