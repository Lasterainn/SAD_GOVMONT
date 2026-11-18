<?php
require_once __DIR__ . '/../src/bootstrap.php';

use App\Router;
use App\Controllers\AuthController;
use App\Controllers\PageController;
use App\Controllers\ProyekController;
use App\Controllers\ForumController;

$router = new Router();

// Halaman Portal Publik & Detail
$router->add('GET', '/', [PageController::class, 'beranda']);
$router->add('GET', '/beranda', [PageController::class, 'beranda']);
$router->add('GET', '/proyek', [PageController::class, 'proyek']);
$router->add('GET', '/proyek/{id}', [PageController::class, 'proyekDetail']);
$router->add('GET', '/diskusi', [PageController::class, 'diskusi']);
$router->add('GET', '/diskusi/{id}', [PageController::class, 'diskusiDetail']);
$router->add('GET', '/dashboard', [PageController::class, 'dashboard']);

// CRUD Proyek (Admin & Pemerintah -> PostgreSQL)
$router->add('POST', '/proyek/create', [ProyekController::class, 'store']);
$router->add('POST', '/proyek/{id}/update', [ProyekController::class, 'update']);
$router->add('POST', '/proyek/{id}/delete', [ProyekController::class, 'destroy']);

// CRUD Forum Diskusi & Chat + Moderasi Admin (PostgreSQL)
$router->add('POST', '/diskusi/create', [ForumController::class, 'storeForum']);
$router->add('POST', '/diskusi/{id}/update', [ForumController::class, 'updateForum']);
$router->add('POST', '/diskusi/{id}/chat', [ForumController::class, 'storeChat']);
$router->add('POST', '/diskusi/{id}/chat/{chatId}/update', [ForumController::class, 'updateChat']);
$router->add('POST', '/diskusi/{id}/chat/{chatId}/hapus', [ForumController::class, 'moderateChat']);

// Autentikasi & MFA
$router->add('GET', '/login', [AuthController::class, 'showLogin']);
$router->add('POST', '/login', [AuthController::class, 'login']);
$router->add('GET', '/register', [AuthController::class, 'showRegister']);
$router->add('POST', '/register', [AuthController::class, 'register']);

$router->add('GET', '/mfa', [AuthController::class, 'showMfa']);
$router->add('POST', '/mfa', [AuthController::class, 'verifyMfa']);
$router->add('POST', '/mfa/resend', [AuthController::class, 'resendMfa']);
$router->add('POST', '/mfa/cancel', [AuthController::class, 'cancelMfa']);

$router->add('POST', '/logout', [AuthController::class, 'logout']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);