<?php
return [
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '5432',
        'dbname' => $_ENV['DB_NAME'] ?? 'jakarta_connect',
        'schema' => $_ENV['DB_SCHEMA'] ?? 'jakarta_connect',
        'user' => $_ENV['DB_USER'] ?? 'postgres',
        'pass' => $_ENV['DB_PASS'] ?? 'admin', //password postgre
    ],
    'app' => [
        'key' => $_ENV['APP_KEY'] ?? '32byteslongsecretkeyforaes256gcm!!',
        'session_lifetime' => 1800, // 30 menit
        'max_login_attempts' => 3,
    ],
    'recaptcha' => [
        'site_key'   => $_ENV['RECAPTCHA_SITE_KEY']   ?? '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
        'secret_key' => $_ENV['RECAPTCHA_SECRET_KEY'] ?? '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
    ]
];