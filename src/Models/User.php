<?php
namespace App\Models;

use App\Database;
use InvalidArgumentException;
use PDO;

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findWargaByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM jakarta_connect.tbl_warga WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => strtolower($email)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findWargaByNikHash(string $nikHashBin): ?array {
        $stmt = $this->db->prepare("SELECT id FROM jakarta_connect.tbl_warga WHERE nik_hash = decode(:nik_hash, 'hex') LIMIT 1");
        $stmt->execute(['nik_hash' => bin2hex($nikHashBin)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findPemerintahByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM jakarta_connect.tbl_pemerintah WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => strtolower($email)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findAdminByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM jakarta_connect.tbl_admin WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => strtolower($email)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function createWarga(string $namaLengkap, string $email, string $nikEncryptedBin, string $nikHashBin, string $passwordHash): bool {
        $stmt = $this->db->prepare("
            INSERT INTO jakarta_connect.tbl_warga (nama_lengkap, email, nik_encrypted, nik_hash, password_hash)
            VALUES (:nama_lengkap, :email, decode(:nik_encrypted, 'hex'), decode(:nik_hash, 'hex'), :password_hash)
        ");
        return $stmt->execute([
            'nama_lengkap' => $namaLengkap,
            'email' => strtolower($email),
            'nik_encrypted' => bin2hex($nikEncryptedBin),
            'nik_hash' => bin2hex($nikHashBin),
            'password_hash' => $passwordHash
        ]);
    }

    public function incrementFailedLogin(string $table, string $id, int $currentAttempts): void {
        $newAttempts = min(3, $currentAttempts + 1);
        $lockedUntil = null;
        $status = 'aktif';

        if ($newAttempts >= 3) {
            $lockedUntil = date('Y-m-d H:i:sP', strtotime('+15 minutes'));
            $status = 'terkunci';
        }

        $sql = match ($table) {
            'tbl_warga' => "UPDATE jakarta_connect.tbl_warga
                            SET jumlah_gagal_login = :attempts, terkunci_sampai = :locked_until, status = :status::jakarta_connect.status_akun
                            WHERE id = :id",
            'tbl_pemerintah' => "UPDATE jakarta_connect.tbl_pemerintah
                                 SET jumlah_gagal_login = :attempts, terkunci_sampai = :locked_until, status = :status::jakarta_connect.status_akun
                                 WHERE id = :id",
            'tbl_admin' => "UPDATE jakarta_connect.tbl_admin
                            SET jumlah_gagal_login = :attempts, terkunci_sampai = :locked_until, status = :status::jakarta_connect.status_akun
                            WHERE id = :id",
            default => throw new InvalidArgumentException('Tabel akun tidak valid.'),
        };

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'attempts' => $newAttempts,
            'locked_until' => $lockedUntil,
            'status' => $status,
            'id' => $id
        ]);
    }

    public function resetFailedLogin(string $table, string $id): void {
        $sql = match ($table) {
            'tbl_warga' => "UPDATE jakarta_connect.tbl_warga
                            SET jumlah_gagal_login = 0, terkunci_sampai = NULL, status = 'aktif'::jakarta_connect.status_akun, login_terakhir_at = NOW()
                            WHERE id = :id",
            'tbl_pemerintah' => "UPDATE jakarta_connect.tbl_pemerintah
                                 SET jumlah_gagal_login = 0, terkunci_sampai = NULL, status = 'aktif'::jakarta_connect.status_akun, login_terakhir_at = NOW()
                                 WHERE id = :id",
            'tbl_admin' => "UPDATE jakarta_connect.tbl_admin
                            SET jumlah_gagal_login = 0, terkunci_sampai = NULL, status = 'aktif'::jakarta_connect.status_akun, login_terakhir_at = NOW()
                            WHERE id = :id",
            default => throw new InvalidArgumentException('Tabel akun tidak valid.'),
        };

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    public function logLoginAttempt(?string $userId, ?string $role, string $email, string $tahap, bool $berhasil, ?string $alasanGagal, string $ipAddress, string $userAgent): void {
        $stmt = $this->db->prepare("
            INSERT INTO jakarta_connect.tbl_log_login
            (user_id, role, email_percobaan, tahap, berhasil, alasan_gagal, ip_address, user_agent)
            VALUES (:user_id, :role::jakarta_connect.role_user, :email_percobaan, :tahap, :berhasil, :alasan_gagal, :ip_address::inet, :user_agent)
        ");
        $stmt->execute([
            'user_id' => $userId,
            'role' => $role,
            'email_percobaan' => strtolower($email),
            'tahap' => $tahap,
            'berhasil' => $berhasil ? 'true' : 'false',
            'alasan_gagal' => $alasanGagal,
            'ip_address' => $ipAddress,
            'user_agent' => substr($userAgent, 0, 500)
        ]);
    }

    // =================================================================
    // MFA (OTP) - hanya tbl_admin & tbl_pemerintah
    // Semua perhitungan waktu dilakukan di PostgreSQL (now()) agar tidak
    // ada selisih zona waktu antara PHP dan DB.
    // =================================================================

    /** Simpan hash OTP baru, reset percobaan, set kedaluwarsa = now() + ttl detik. */
    public function saveOtp(string $table, string $id, string $otpHash, int $ttlDetik): void {
        $sql = match ($table) {
            'tbl_pemerintah' => "UPDATE jakarta_connect.tbl_pemerintah
                                 SET otp_hash = :hash,
                                     otp_kedaluwarsa_at = now() + (CAST(:ttl AS integer) * interval '1 second'),
                                     otp_percobaan = 0
                                 WHERE id = :id",
            'tbl_admin' => "UPDATE jakarta_connect.tbl_admin
                            SET otp_hash = :hash,
                                otp_kedaluwarsa_at = now() + (CAST(:ttl AS integer) * interval '1 second'),
                                otp_percobaan = 0
                            WHERE id = :id",
            default => throw new InvalidArgumentException('Tabel MFA tidak valid.'),
        };

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['hash' => $otpHash, 'ttl' => $ttlDetik, 'id' => $id]);
    }

    /** Hapus OTP (sekali pakai / dibatalkan / gagal terlalu banyak). */
    public function clearOtp(string $table, string $id): void {
        $sql = match ($table) {
            'tbl_pemerintah' => "UPDATE jakarta_connect.tbl_pemerintah
                                 SET otp_hash = NULL, otp_kedaluwarsa_at = NULL, otp_percobaan = 0
                                 WHERE id = :id",
            'tbl_admin' => "UPDATE jakarta_connect.tbl_admin
                            SET otp_hash = NULL, otp_kedaluwarsa_at = NULL, otp_percobaan = 0
                            WHERE id = :id",
            default => throw new InvalidArgumentException('Tabel MFA tidak valid.'),
        };

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    /** Status MFA terkini user (dihitung di sisi DB). */
    public function findMfaState(string $table, string $id): ?array {
        $sql = match ($table) {
            'tbl_pemerintah' => "SELECT id, nama_lengkap, email, status, otp_hash, otp_percobaan,
                                        (otp_hash IS NOT NULL AND otp_kedaluwarsa_at > now())                    AS otp_aktif,
                                        COALESCE(EXTRACT(EPOCH FROM (otp_kedaluwarsa_at - now())), 0)::int       AS otp_sisa_detik,
                                        (terkunci_sampai IS NOT NULL AND terkunci_sampai > now())                AS sedang_terkunci
                                 FROM jakarta_connect.tbl_pemerintah
                                 WHERE id = :id LIMIT 1",
            'tbl_admin' => "SELECT id, nama_lengkap, email, status, otp_hash, otp_percobaan,
                                   (otp_hash IS NOT NULL AND otp_kedaluwarsa_at > now())                    AS otp_aktif,
                                   COALESCE(EXTRACT(EPOCH FROM (otp_kedaluwarsa_at - now())), 0)::int       AS otp_sisa_detik,
                                   (terkunci_sampai IS NOT NULL AND terkunci_sampai > now())                AS sedang_terkunci
                            FROM jakarta_connect.tbl_admin
                            WHERE id = :id LIMIT 1",
            default => throw new InvalidArgumentException('Tabel MFA tidak valid.'),
        };

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Pakai 1 jatah percobaan secara ATOMIK (sebelum membandingkan OTP),
     * sehingga request paralel tidak bisa menembus batas 3 percobaan.
     * Return jumlah percobaan terpakai (1..3), atau null jika jatah sudah habis / OTP tidak ada.
     */
    public function consumeOtpAttempt(string $table, string $id): ?int {
        $sql = match ($table) {
            'tbl_pemerintah' => "UPDATE jakarta_connect.tbl_pemerintah
                                 SET otp_percobaan = otp_percobaan + 1
                                 WHERE id = :id AND otp_hash IS NOT NULL AND otp_percobaan < 3
                                 RETURNING otp_percobaan",
            'tbl_admin' => "UPDATE jakarta_connect.tbl_admin
                            SET otp_percobaan = otp_percobaan + 1
                            WHERE id = :id AND otp_hash IS NOT NULL AND otp_percobaan < 3
                            RETURNING otp_percobaan",
            default => throw new InvalidArgumentException('Tabel MFA tidak valid.'),
        };

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? (int)$row['otp_percobaan'] : null;
    }

    /** Kunci akun 15 menit (dipakai saat OTP salah 3x). */
    public function lockAccount(string $table, string $id): void {
        $sql = match ($table) {
            'tbl_pemerintah' => "UPDATE jakarta_connect.tbl_pemerintah
                                 SET status = 'terkunci'::jakarta_connect.status_akun,
                                     jumlah_gagal_login = 3,
                                     terkunci_sampai = now() + interval '15 minutes'
                                 WHERE id = :id",
            'tbl_admin' => "UPDATE jakarta_connect.tbl_admin
                            SET status = 'terkunci'::jakarta_connect.status_akun,
                                jumlah_gagal_login = 3,
                                terkunci_sampai = now() + interval '15 minutes'
                            WHERE id = :id",
            default => throw new InvalidArgumentException('Tabel MFA tidak valid.'),
        };

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    public function getAdminDashboardStats(): array {
        $row = $this->db->query("SELECT * FROM jakarta_connect.v_dashboard_admin")->fetch() ?: [];
        $validLogin  = (bool)$this->db->query("SELECT COALESCE(bool_and(rantai_valid), true) FROM jakarta_connect.v_verifikasi_log_login")->fetchColumn();
        $validForum  = (bool)$this->db->query("SELECT COALESCE(bool_and(rantai_valid), true) FROM jakarta_connect.v_verifikasi_log_forum")->fetchColumn();
        $validProyek = (bool)$this->db->query("SELECT COALESCE(bool_and(rantai_valid), true) FROM jakarta_connect.v_verifikasi_log_proyek")->fetchColumn();

        return array_merge([
            'jumlah_warga'      => 0,
            'jumlah_pemerintah' => 0,
            'jumlah_admin'      => 0,
            'login_sukses_24jam'=> 0,
            'login_gagal_24jam' => 0,
            'chat_24jam'        => 0,
        ], $row, [
            'hash_chain_valid' => ($validLogin && $validForum && $validProyek),
        ]);
    }

    public function getWargaStats(string $wargaId): array {
        $stmtF = $this->db->prepare("SELECT COUNT(*) FROM jakarta_connect.tbl_forum WHERE warga_id = :id");
        $stmtF->execute(['id' => $wargaId]);

        $stmtC = $this->db->prepare("SELECT COUNT(*) FROM jakarta_connect.tbl_chat WHERE warga_id = :id AND dihapus = false");
        $stmtC->execute(['id' => $wargaId]);

        return [
            'forum_dibuat' => (int)$stmtF->fetchColumn(),
            'chat_dikirim' => (int)$stmtC->fetchColumn(),
        ];
    }
}