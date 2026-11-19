<?php

return [
    'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
    'db_port' => (int) (getenv('DB_PORT') ?: 3306),
    'db_name' => getenv('DB_NAME') ?: 'FILM_DATABASE',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_password' => getenv('DB_PASSWORD') ?: 'tiger',
    'twig_autoload' => getenv('TWIG_AUTOLOAD') ?: __DIR__ . '/vendor/autoload.php',
    'frontend_path' => getenv('FRONTEND_PATH') ?: dirname(__DIR__) . '/frontend',
];
