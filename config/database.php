<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

return new PDO(
    sprintf(
        '%s:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $_ENV['DB_CONNECTION'],
        $_ENV['DB_HOST'],
        (int) $_ENV['DB_PORT'],
        $_ENV['DB_DATABASE']
    ),
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);