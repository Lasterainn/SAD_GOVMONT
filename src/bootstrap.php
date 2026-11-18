<?php
error_reporting(E_ALL & ~E_DEPRECATED);

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// 1. Autoloader PSR-4
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

// 2. Helper functions global untuk template & partials
require_once __DIR__ . '/helpers.php';

// 3. Muat .env (fallback ke .env.example saat mode flat/dev bila .env belum dibuat)
$envPath = __DIR__ . '/../.env';
if (!is_readable($envPath) && is_readable(__DIR__ . '/../.env.example')) {
    $envPath = __DIR__ . '/../.env.example';
}
\App\Env::load($envPath);
ini_set('display_errors', getenv('APP_DEBUG') === '1' ? '1' : '0');

// 4. HTTP Security Headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 0");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Type: text/html; charset=UTF-8");
header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/; style-src 'self' 'unsafe-inline'; img-src 'self' data:; form-action 'self'; frame-ancestors 'self'; frame-src 'self' https://www.google.com/recaptcha/ https://recaptcha.google.com/recaptcha/; connect-src 'self' https://www.google.com/recaptcha/;");

// 5. Security Cookie Sesi
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_only_cookies', '1');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', '1');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 6. Session Timeout (30 Menit)
$timeout = (int)(getenv('SESSION_LIFETIME') ?: 1800);

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();
