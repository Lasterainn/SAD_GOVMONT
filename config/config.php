<?php
use App\Env;

return [
    'db' => [
        'host'   => Env::get('DB_HOST'),
        'port'   => Env::get('DB_PORT'),
        'dbname' => Env::get('DB_NAME'),
        'schema' => Env::get('DB_SCHEMA'),
        'user'   => Env::get('DB_USER'),
        'pass'   => Env::get('DB_PASS'),
    ],
    'app' => [
        'key'              => Env::key('APP_KEY'),     // 32 byte biner
        'nik_pepper'       => Env::key('NIK_PEPPER'),
        'otp_pepper'       => Env::key('OTP_PEPPER'),
        'session_lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 1800),
        'max_login_attempts' => 3,
    ],
    'recaptcha' => [
        'site_key'   => Env::get('RECAPTCHA_SITE_KEY'),
        'secret_key' => Env::get('RECAPTCHA_SECRET_KEY'),
    ],
];