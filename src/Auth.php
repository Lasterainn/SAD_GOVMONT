<?php
namespace App;

use App\Models\User;

class Auth {
    // Hash Argon2id valid agar waktu komputasi saat email tidak ditemukan sama dengan akun asli
    private const DUMMY_PASSWORD_HASH = '$argon2id$v=19$m=65536,t=4,p=1$c2FsdHNhbHRzYWx0c2FsdA$aGFzaGhhc2hoYXNoaGFzaGhhc2hoYXNoaGFzaGhhc2g';
    // Pesan gagal tunggal agar penyerang tidak tahu apakah email terdaftar / terkunci
    private const GENERIC_LOGIN_ERROR = 'Email atau password salah.';
    private const MFA_ROLES            = ['admin', 'pemerintah'];
    private const OTP_TTL_DETIK        = 300;  // OTP berlaku 5 menit
    private const OTP_RESEND_COOLDOWN  = 60;   // jeda minimal kirim ulang (detik)
    private const OTP_MAX_RESEND       = 3;    // maks kirim ulang per sesi MFA
    private const MFA_SESSION_TTL      = 600;  // batas total tahap OTP sejak password benar (10 menit)
    private const MAX_REGISTRASI_PER_IP_JAM = 3;
    // Hash bcrypt cost-12 valid untuk menyamakan waktu komputasi saat email tidak ditemukan

    private User $userModel;
    private string $nikPepper;  // HMAC-SHA256 (nik_hash)
    private string $appKey;     // AES-256-GCM (enkripsi NIK)
    private string $otpPepper;  // HMAC-SHA256 (otp_hash)

    public function __construct() {
        $this->userModel = new User();
        $config = require __DIR__ . '/../config/config.php';
        $this->appKey    = $config['app']['key'];
        $this->nikPepper = $config['app']['nik_pepper'];
        $this->otpPepper = $config['app']['otp_pepper'];
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
        $ip = class_exists(LoginThrottle::class)
            ? LoginThrottle::clientIp()
            : ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        if ($this->userModel->countRecentRegistrationsByIp($ip) >= self::MAX_REGISTRASI_PER_IP_JAM) {
            return [
                'success'      => false,
                'rate_limited' => true,
                'message'      => 'Batas registrasi dari alamat IP Anda telah tercapai. Silakan coba lagi dalam 1 jam.',
            ];
        }

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
            'threads'     => 1,
        ]);

        $nikEncryptedBin = $this->encryptNIK($nik);
        $success = $this->userModel->createWarga($nama, $email, $nikEncryptedBin, $nikHashBin, $passwordHash, $ip);

        if ($success) {
            return ['success' => true, 'message' => 'Registrasi warga berhasil! Silakan login.'];
        }

        return ['success' => false, 'message' => 'Gagal mendaftar. Terjadi kesalahan sistem.'];
    }

    // =================================================================
    // LOGIN TAHAP 1: email + password (+ reCAPTCHA di controller)
    // =================================================================
    public function login(string $email, string $password): array {
        $ip = class_exists(LoginThrottle::class)
            ? LoginThrottle::clientIp()
            : ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        // 1. Cari user di tbl_warga, tbl_pemerintah, atau tbl_admin
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

        // 2. SELALU jalankan password_verify() lebih dulu (pakai dummy hash jika user tidak ada)
        //    agar waktu respons identik dan tidak bisa dibedakan lewat timing attack.
        $hashToVerify = $user['password_hash'] ?? self::DUMMY_PASSWORD_HASH;
        $isPasswordValid = password_verify($password, $hashToVerify);

        // 3. Jika user tidak ditemukan -> catat log, balas pesan seragam
        if (!$user) {
            $this->userModel->logLoginAttempt(null, null, $email, 'password', false, 'User tidak ditemukan', $ip, $ua);
            return ['success' => false, 'message' => self::GENERIC_LOGIN_ERROR];
        }

        // 4. Cek penguncian & status nonaktif -> catat alasan asli di log Admin, tapi balas pesan seragam ke user
        $lockedUntil = !empty($user['terkunci_sampai']) ? strtotime($user['terkunci_sampai']) : null;
        $lockMasihAktif = $lockedUntil !== null && $lockedUntil > time();

        if ($user['status'] === 'nonaktif' || $lockMasihAktif) {
            $alasanLog = ($user['status'] === 'nonaktif') ? 'Akun nonaktif' : 'Akun terkunci';
            $this->userModel->logLoginAttempt($user['id'], $role, $email, 'password', false, $alasanLog, $ip, $ua);
            return ['success' => false, 'message' => self::GENERIC_LOGIN_ERROR];
        }

        // Jika masa kunci sudah lewat, hitung percobaan dari 0 lagi
        $attempts = ($lockedUntil !== null) ? 0 : (int)$user['jumlah_gagal_login'];

        // 5. Cek hasil verifikasi password -> tetap jalankan incrementFailedLogin di DB,
        //    tapi JANGAN tampilkan sisa percobaan atau info penguncian ke layar user.
        if (!$isPasswordValid) {
            $this->userModel->incrementFailedLogin($table, $user['id'], $attempts);
            $this->userModel->logLoginAttempt($user['id'], $role, $email, 'password', false, 'Password salah', $ip, $ua);

            return ['success' => false, 'message' => self::GENERIC_LOGIN_ERROR];
        }

        // ---- Password benar ----
        session_regenerate_id(true); // Cegah session fixation setelah kredensial valid
        $this->userModel->logLoginAttempt($user['id'], $role, $email, 'password', true, null, $ip, $ua);

        // Admin & Pemerintah: belum login penuh -> lanjut tahap OTP
        if (in_array($role, self::MFA_ROLES, true)) {
            return $this->startMfa($user, $role, $table, $ip, $ua);
        }

        // Warga: login penuh
        $this->userModel->resetFailedLogin($table, $user['id']);
        $this->completeLogin($user, $role);

        return ['success' => true, 'message' => 'Login berhasil.', 'role' => $role];
    }

    /** Buat sesi baru dan tandai user sebagai sudah login penuh. */
    private function completeLogin(array $user, string $role): void {
        session_regenerate_id(true);
        unset($_SESSION['csrf_token'], $_SESSION['mfa_pending'], $_SESSION['mfa_resend']);

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['nama_lengkap'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $role;
        $_SESSION['logged_in']  = true;
    }

    // =================================================================
    // LOGIN TAHAP 2: MFA / OTP via email (admin & pemerintah)
    // =================================================================

    private function generateOtp(): string {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /** Hash OTP = HMAC-SHA256(user_id|otp, OTP_PEPPER). OTP asli tidak pernah disimpan. */
    private function hashOtp(string $userId, string $otp): string {
        return hash_hmac('sha256', $userId . '|' . $otp, $this->otpPepper);
    }

    public function maskEmail(string $email): string {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visible = substr($local, 0, 2);
        return $visible . str_repeat('*', max(1, strlen($local) - 2)) . '@' . $domain;
    }

    /** Buat + kirim OTP baru. Return true jika email terkirim. */
    private function issueOtp(string $table, string $userId, string $email, string $name): bool {
        $otp = $this->generateOtp();
        $this->userModel->saveOtp($table, $userId, $this->hashOtp($userId, $otp), self::OTP_TTL_DETIK);

        if (!Mailer::sendOtp($email, $name, $otp, intdiv(self::OTP_TTL_DETIK, 60))) {
            $this->userModel->clearOtp($table, $userId);
            return false;
        }
        return true;
    }

    private function startMfa(array $user, string $role, string $table, string $ip, string $ua): array {
        if (!$this->issueOtp($table, $user['id'], $user['email'], $user['nama_lengkap'])) {
            $this->userModel->logLoginAttempt($user['id'], $role, $user['email'], 'otp', false, 'gagal_kirim_email', $ip, $ua);
            return ['success' => false, 'message' => 'Gagal mengirim kode OTP. Silakan coba lagi beberapa saat.'];
        }

        // Sesi baru, TANPA logged_in. User baru dianggap login setelah OTP benar.
        session_regenerate_id(true);
        unset($_SESSION['logged_in']);
        $_SESSION['mfa_pending'] = [
            'user_id'    => $user['id'],
            'role'       => $role,
            'table'      => $table,
            'name'       => $user['nama_lengkap'],
            'email'      => $user['email'],
            'started_at' => time(),
        ];
        $_SESSION['mfa_resend'] = 0;

        return ['success' => true, 'mfa_required' => true, 'role' => $role];
    }

    /** Batalkan proses MFA (OTP di DB dihapus, sesi pending dibuang). */
    public function cancelMfa(): void {
        $p = $_SESSION['mfa_pending'] ?? null;
        if ($p) {
            $this->userModel->clearOtp($p['table'], $p['user_id']);
        }
        unset($_SESSION['mfa_pending'], $_SESSION['mfa_resend']);
    }

    public function verifyMfa(string $otpInput): array {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $p = $_SESSION['mfa_pending'] ?? null;
        if (!$p) {
            return ['success' => false, 'restart' => true, 'message' => 'Sesi verifikasi tidak ditemukan. Silakan login ulang.'];
        }

        if (time() - (int)$p['started_at'] > self::MFA_SESSION_TTL) {
            $this->cancelMfa();
            return ['success' => false, 'restart' => true, 'message' => 'Sesi verifikasi habis. Silakan login ulang.'];
        }

        $state = $this->userModel->findMfaState($p['table'], $p['user_id']);
        if (!$state || $state['status'] === 'nonaktif' || $state['sedang_terkunci']) {
            $this->cancelMfa();
            return ['success' => false, 'restart' => true, 'message' => 'Akun tidak dapat diverifikasi saat ini.'];
        }

        $otpInput = preg_replace('/\s+/', '', $otpInput);
        if (!preg_match('/^[0-9]{6}$/', $otpInput)) {
            return ['success' => false, 'message' => 'Kode OTP harus 6 digit angka.']; // tidak memakai jatah percobaan
        }

        if (!$state['otp_aktif']) {
            $this->userModel->logLoginAttempt($p['user_id'], $p['role'], $p['email'], 'otp', false, 'otp_kedaluwarsa', $ip, $ua);
            return ['success' => false, 'message' => 'Kode OTP sudah kedaluwarsa. Klik "Kirim ulang kode".'];
        }

        // Pakai jatah percobaan SEBELUM membandingkan (atomik di DB)
        $attempt = $this->userModel->consumeOtpAttempt($p['table'], $p['user_id']);
        if ($attempt === null) {
            return $this->failTooManyOtp($p, $ip, $ua);
        }

        $expected = $this->hashOtp($p['user_id'], $otpInput);
        if (!hash_equals((string)$state['otp_hash'], $expected)) {
            $this->userModel->logLoginAttempt($p['user_id'], $p['role'], $p['email'], 'otp', false, 'otp_salah', $ip, $ua);

            if ($attempt >= 3) {
                return $this->failTooManyOtp($p, $ip, $ua);
            }
            $sisa = 3 - $attempt;
            return ['success' => false, 'message' => "Kode OTP salah. Sisa percobaan: {$sisa}"];
        }

        // ---- OTP benar: sekali pakai ----
        $this->userModel->clearOtp($p['table'], $p['user_id']);
        $this->userModel->resetFailedLogin($p['table'], $p['user_id']);
        $this->userModel->logLoginAttempt($p['user_id'], $p['role'], $p['email'], 'otp', true, null, $ip, $ua);

        $this->completeLogin(
            ['id' => $p['user_id'], 'nama_lengkap' => $p['name'], 'email' => $p['email']],
            $p['role']
        );

        return ['success' => true, 'role' => $p['role']];
    }

    /** OTP salah 3x: hapus OTP, kunci akun 15 menit, paksa login ulang. */
    private function failTooManyOtp(array $p, string $ip, string $ua): array {
        $this->userModel->clearOtp($p['table'], $p['user_id']);
        $this->userModel->lockAccount($p['table'], $p['user_id']);
        $this->userModel->logLoginAttempt($p['user_id'], $p['role'], $p['email'], 'otp', false, 'otp_gagal_3x_akun_terkunci', $ip, $ua);
        unset($_SESSION['mfa_pending'], $_SESSION['mfa_resend']);

        return [
            'success' => false,
            'restart' => true,
            'message' => 'OTP salah 3 kali. Akun terkunci selama 15 menit.',
        ];
    }

    public function resendMfa(): array {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $p = $_SESSION['mfa_pending'] ?? null;
        if (!$p || time() - (int)$p['started_at'] > self::MFA_SESSION_TTL) {
            $this->cancelMfa();
            return ['success' => false, 'restart' => true, 'message' => 'Sesi verifikasi habis. Silakan login ulang.'];
        }

        if ((int)($_SESSION['mfa_resend'] ?? 0) >= self::OTP_MAX_RESEND) {
            return ['success' => false, 'message' => 'Batas kirim ulang tercapai. Silakan batalkan dan login ulang.'];
        }

        $state = $this->userModel->findMfaState($p['table'], $p['user_id']);
        if (!$state || $state['status'] === 'nonaktif' || $state['sedang_terkunci']) {
            $this->cancelMfa();
            return ['success' => false, 'restart' => true, 'message' => 'Akun tidak dapat diverifikasi saat ini.'];
        }

        // Cooldown: umur OTP aktif = TTL - sisa waktu
        if ($state['otp_aktif']) {
            $umur = self::OTP_TTL_DETIK - (int)$state['otp_sisa_detik'];
            if ($umur < self::OTP_RESEND_COOLDOWN) {
                $tunggu = self::OTP_RESEND_COOLDOWN - $umur;
                return ['success' => false, 'message' => "Tunggu {$tunggu} detik sebelum meminta kode baru."];
            }
        }

        if (!$this->issueOtp($p['table'], $p['user_id'], $p['email'], $p['name'])) {
            $this->userModel->logLoginAttempt($p['user_id'], $p['role'], $p['email'], 'otp', false, 'gagal_kirim_email', $ip, $ua);
            return ['success' => false, 'message' => 'Gagal mengirim kode OTP. Coba lagi beberapa saat.'];
        }

        $_SESSION['mfa_resend'] = (int)($_SESSION['mfa_resend'] ?? 0) + 1;
        return ['success' => true, 'message' => 'Kode OTP baru telah dikirim ke email Anda.'];
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