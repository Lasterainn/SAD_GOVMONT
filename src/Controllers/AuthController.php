<?php
namespace App\Controllers;

use App\Recaptcha;
use App\Auth;
use App\Csrf;
use App\Validator;

class AuthController {
    private Auth $auth;

    public function __construct() {
        $this->auth = new Auth();
    }

    public function showLogin(?string $error = null, ?string $success = null): void {
        $csrfToken = Csrf::generateToken();
        $recaptchaSiteKey = Recaptcha::siteKey();
        $title = "Login - JakartaConnect";
        $viewFile = __DIR__ . '/../../templates/login.php';
        require __DIR__ . '/../../templates/layout.php';
    }

    public function showRegister(?array $errors = null, ?string $success = null, array $old = []): void {
        $csrfToken = Csrf::generateToken();
        $recaptchaSiteKey = Recaptcha::siteKey();

        $title = "Register Warga - JakartaConnect";
        $viewFile = __DIR__ . '/../../templates/register.php';
        require __DIR__ . '/../../templates/layout.php';
    }

    public function register(): void {
        if (!Csrf::verifyToken($_POST['csrf_token'] ?? '')) {
            $this->showRegister(['Token CSRF tidak valid.']);
            return;
        }

        $nama = trim($_POST['nama_lengkap'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nik = trim($_POST['nik'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        if (!Recaptcha::verify($recaptchaResponse, $_SERVER['REMOTE_ADDR'] ?? null)) {
            $errors[] = "Verifikasi reCAPTCHA gagal. Silakan centang \"I'm not a robot\".";
        }

        $errors = [];

        if (empty($nama)) $errors[] = "Nama lengkap wajib diisi.";
        if (!Validator::validateEmail($email)) $errors[] = "Format email tidak valid.";
        if (!Validator::validateNIK($nik)) $errors[] = "NIK harus 16 digit angka.";

        // Validasi Kebijakan Password
        $pwdErrors = Validator::validatePassword($password);
        $errors = array_merge($errors, $pwdErrors);

        // Validasi Konfirmasi Password
        if ($password !== $confirmPassword) {
            $errors[] = "Konfirmasi password tidak cocok dengan password yang dimasukkan.";
        }

        if (empty($_SESSION['captcha_answer']) || (int)$captcha !== (int)$_SESSION['captcha_answer']) {
            $errors[] = "Jawaban CAPTCHA salah.";
        }

        if (!empty($errors)) {
            $this->showRegister($errors, null, ['nama_lengkap' => $nama, 'email' => $email, 'nik' => $nik]);
            return;
        }

        $result = $this->auth->registerWarga($nama, $email, $nik, $password);

        if ($result['success']) {
            $this->showLogin(null, $result['message']);
        } else {
            $this->showRegister([$result['message']], null, ['nama_lengkap' => $nama, 'email' => $email, 'nik' => $nik]);
        }
    }

    public function login(): void {
        if (!Csrf::verifyToken($_POST['csrf_token'] ?? '')) {
            $this->showLogin('Token CSRF tidak valid.');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->showLogin('Email dan password wajib diisi.');
            return;
        }

        // reCAPTCHA
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        if (!Recaptcha::verify($recaptchaResponse, $_SERVER['REMOTE_ADDR'] ?? null)) {
            $this->auth->logCaptchaFailure($email);
            $this->showLogin('Verifikasi reCAPTCHA gagal. Silakan centang "I\'m not a robot".');
            return;
        }

        $result = $this->auth->login($email, $password);

        if ($result['success']) {
            header('Location: /dashboard');
            exit;
        } else {
            $this->showLogin($result['message']);
        }
    }

    public function dashboard(): void {
        if (empty($_SESSION['logged_in'])) {
            header('Location: /login');
            exit;
        }

        $title = "Dashboard - JakartaConnect";
        $viewFile = __DIR__ . '/../../templates/dashboard.php';
        require __DIR__ . '/../../templates/layout.php';
    }

    public function logout(): void {
        $this->auth->logout();
        header('Location: /login');
        exit;
    }
}