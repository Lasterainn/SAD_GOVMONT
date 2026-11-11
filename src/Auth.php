<?php
namespace App;

use App\Models\User;

class Auth {
    private User $userModel;
    private string $nikPepper;  // AES-256-GCM (enkripsi NIK)
    private string $appKey;     // HMAC-SHA256 (nik_hash)

    public function __construct() {
        $this->userModel = new User();
        $config = require __DIR__ . '/../config/config.php';
        $this->appKey = $config['app']['key'];
        $this->nikPepper = $config['app']['nik_pepper'];
    }

    public function encryptNIK(string $nik): string {
        $cipher = "aes-256-gcm";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $tag = "";
        $encrypted = openssl_encrypt($nik, $cipher, $this->appKey, OPENSSL_RAW_DATA, $iv, $tag);
        return $iv . $tag . $encrypted;
    }

    public function hashNIK(string $nik): string {
        return hash_hmac('sha256', $nik, $this->nikPepper, true);
    }

    public function registerWarga(string $nama, string $email, string $nik, string $password): array {
        if ($this->userModel->findWargaByEmail($email)) {
            return ['success' => false, 'message' => 'Email sudah terdaftar.'];
        }

        $nikHashBin = $this->hashNIK($nik);
        if ($this->userModel->findWargaByNikHash($nikHashBin)) {
            return ['success' => false, 'message' => 'NIK sudah terdaftar dalam sistem.'];
        }

        $passwordHash = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost'   => 4,
            'threads'     => 1
        ]);

        $nikEncryptedBin = $this->encryptNIK($nik);
        $success = $this->userModel->createWarga($nama, $email, $nikEncryptedBin, $nikHashBin, $passwordHash);

        if ($success) {
            return ['success' => true, 'message' => 'Registrasi warga berhasil! Silakan login.'];
        }

        return ['success' => false, 'message' => 'Gagal mendaftar. Terjadi kesalahan sistem.'];
    }

    public function login(string $email, string $password): array {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        // Cari user di tbl_warga, tbl_pemerintah, atau tbl_admin
        $role = 'warga';
        $table = 'tbl_warga';
        $user = $this->userModel->findWargaByEmail($email);

        if (!$user) {
            $user = $this->userModel->findPemerintahByEmail($email);
            if ($user) {
                $role = 'pemerintah';
                $table = 'tbl_pemerintah';
            }
        }

        if (!$user) {
            $user = $this->userModel->findAdminByEmail($email);
            if ($user) {
                $role = 'admin';
                $table = 'tbl_admin';
            }
        }

        if (!$user) {
            $this->userModel->logLoginAttempt(null, null, $email, 'password', false, 'User tidak ditemukan', $ip, $ua);
            return ['success' => false, 'message' => 'Email atau password salah.'];
        }

        // Cek Status Akun & Penguncian 3x Gagal
        if ($user['status'] === 'terkunci' || ($user['terkunci_sampai'] && strtotime($user['terkunci_sampai']) > time())) {
            $this->userModel->logLoginAttempt($user['id'], $role, $email, 'password', false, 'Akun terkunci', $ip, $ua);
            return ['success' => false, 'message' => 'Akun Anda terkunci sementara karena 3 kali gagal login. Coba lagi dalam 15 menit.'];
        }

        // Verifikasi Password Argon2id
        if (!password_verify($password, $user['password_hash'])) {
            $this->userModel->incrementFailedLogin($table, $user['id'], (int)$user['jumlah_gagal_login']);
            $this->userModel->logLoginAttempt($user['id'], $role, $email, 'password', false, 'Password salah', $ip, $ua);

            $remaining = 2 - (int)$user['jumlah_gagal_login'];
            if ($remaining > 0) {
                return ['success' => false, 'message' => "Email atau password salah. Sisa percobaan: {$remaining}"];
            } else {
                return ['success' => false, 'message' => 'Akun terkunci selama 15 menit karena gagal 3 kali.'];
            }
        }

        // Password Benar -> Logging & Reset percobaan
        $this->userModel->resetFailedLogin($table, $user['id']);
        $this->userModel->logLoginAttempt($user['id'], $role, $email, 'password', true, null, $ip, $ua);

        // Regenerasi ID Sesi
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nama_lengkap'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $role;
        $_SESSION['logged_in'] = true;

        return ['success' => true, 'message' => 'Login berhasil.', 'role' => $role];
    }

    public function logout(): void {
        if (!empty($_SESSION['user_id'])) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $this->userModel->logLoginAttempt($_SESSION['user_id'], $_SESSION['user_role'] ?? 'warga', $_SESSION['user_email'] ?? '', 'logout', true, null, $ip, $ua);
        }

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public function logCaptchaFailure(string $email): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $this->userModel->logLoginAttempt(null, null, $email, 'password', false, 'captcha_gagal', $ip, $ua);
    }
}