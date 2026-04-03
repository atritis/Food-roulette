<?php

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_DATABASE') ?: 'food_roulette',
        'username' => getenv('DB_USERNAME') ?: 'foodapp',
        'password' => getenv('DB_PASSWORD') ?: 'foodapp_secret',
        'charset' => 'utf8mb4',
    ],
    'bearer_token' => getenv('API_BEARER_TOKEN') ?: 'change-me-to-a-secure-token',
    'upload_path' => __DIR__ . '/../uploads',
    'max_image_width' => 1200,
];
