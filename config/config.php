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
        'key'              => Env::key('APP_KEY'),
        'nik_pepper'       => Env::key('NIK_PEPPER'),
        'otp_pepper'       => Env::key('OTP_PEPPER'),
        'session_lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 1800),
        'max_login_attempts' => 3,
    ],
    'recaptcha' => [
        'site_key'   => Env::get('RECAPTCHA_SITE_KEY'),
        'secret_key' => Env::get('RECAPTCHA_SECRET_KEY'),
    ],
    'mail' => [
        'driver'     => getenv('MAIL_DRIVER') ?: 'smtp',   // smtp | log (log hanya dev)
        'host'       => getenv('MAIL_HOST') ?: '',
        'port'       => (int)(getenv('MAIL_PORT') ?: 587),
        'user'       => getenv('MAIL_USER') ?: '',
        'pass'       => getenv('MAIL_PASS') ?: '',
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls', // tls (587) | ssl (465)
        'from'       => getenv('MAIL_FROM') ?: 'no-reply@jakartaconnect.id',
        'from_name'  => getenv('MAIL_FROM_NAME') ?: 'JakartaConnect',
    ],
];