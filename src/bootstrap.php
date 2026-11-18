<?php
error_reporting(E_ALL & ~E_DEPRECATED);

// 1. Autoloader PSR-4 (dengan fallback case-insensitive untuk Linux)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
        return;
    }

    $dir = dirname($file);
    $target = strtolower(basename($file));
    if (is_dir($dir)) {
        foreach (scandir($dir) as $entry) {
            if (strtolower($entry) === $target) {
                require $dir . '/' . $entry;
                return;
            }
        }
    }
});

// 2. Helper functions global untuk template & partials
require_once __DIR__ . '/helpers.php';

// 3. Muat .env
$envPath = __DIR__ . '/../.env';
if (!is_readable($envPath) && is_readable(__DIR__ . '/../.env.example')) {
    $envPath = __DIR__ . '/../.env.example';
}
\App\Env::load($envPath);
ini_set('display_errors', getenv('APP_DEBUG') === '1' ? '1' : '0');

// 4. HTTP Security Headers
$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 0");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Type: text/html; charset=UTF-8");
header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/; style-src 'self' 'unsafe-inline'; img-src 'self' data:; form-action 'self'; frame-ancestors 'self'; frame-src 'self' https://www.google.com/recaptcha/ https://recaptcha.google.com/recaptcha/; connect-src 'self' https://www.google.com/recaptcha/;");
if ($isHttps) {
    $_SERVER['HTTPS'] = 'on';
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}

// 5. Security Cookie & Strict Mode Sesi (Poin 5)
$timeout = max(60, (int)(getenv('SESSION_LIFETIME') ?: 1800));

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', (string)$timeout);
if ($isHttps) {
    ini_set('session.cookie_secure', '1');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 6. Pemeriksaan Idle Timeout Sesi
$now = time();
if (isset($_SESSION['last_activity']) && ($now - (int)$_SESSION['last_activity'] > $timeout)) {
    $hadAuthSession = !empty($_SESSION['user_id']) || !empty($_SESSION['mfa_pending']);

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => $now - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();

    session_start();
    session_regenerate_id(true);
    $_SESSION['last_activity'] = $now;

    if ($hadAuthSession) {
        $_SESSION['flash_auth_expired'] = 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.';
        header('Location: /login');
        exit;
    }
}
$_SESSION['last_activity'] = $now;