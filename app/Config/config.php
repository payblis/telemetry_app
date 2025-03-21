<?php

return [
    'app' => [
        'name' => 'Telemetry App',
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => $_ENV['APP_DEBUG'] ?? false,
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'timezone' => 'Europe/Paris',
    ],

    'database' => [
        'driver' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_DATABASE'] ?? 'telemetry_db',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],

    'openai' => [
        'api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
        'model' => 'gpt-4-turbo-preview',
        'max_tokens' => 2000,
    ],

    'security' => [
        'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'your-secret-key',
        'jwt_expiration' => 3600, // 1 hour
        'password_hash_algo' => PASSWORD_BCRYPT,
    ],

    'session' => [
        'lifetime' => 120,
        'expire_on_close' => false,
        'encrypt' => false,
        'cookie' => 'telemetry_session',
        'path' => '/',
        'domain' => null,
        'secure' => false,
    ],
]; 