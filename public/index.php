<?php
require_once __DIR__ . '/../src/bootstrap.php';

use App\Router;
use App\Controllers\AuthController;

$router = new Router();

$router->add('GET', '/', [AuthController::class, 'showLogin']);
$router->add('GET', '/login', [AuthController::class, 'showLogin']);
$router->add('POST', '/login', [AuthController::class, 'login']);
$router->add('GET', '/register', [AuthController::class, 'showRegister']);
$router->add('POST', '/register', [AuthController::class, 'register']);

$router->add('GET', '/mfa', [AuthController::class, 'showMfa']);
$router->add('POST', '/mfa', [AuthController::class, 'verifyMfa']);
$router->add('POST', '/mfa/resend', [AuthController::class, 'resendMfa']);
$router->add('POST', '/mfa/cancel', [AuthController::class, 'cancelMfa']);

$router->add('GET', '/dashboard', [AuthController::class, 'dashboard']);
$router->add('POST', '/logout', [AuthController::class, 'logout']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);