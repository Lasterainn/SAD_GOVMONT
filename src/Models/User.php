<?php
namespace App\Models;

use App\Database;
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

        $sql = "UPDATE jakarta_connect.{$table}
                SET jumlah_gagal_login = :attempts, terkunci_sampai = :locked_until, status = :status::jakarta_connect.status_akun
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'attempts' => $newAttempts,
            'locked_until' => $lockedUntil,
            'status' => $status,
            'id' => $id
        ]);
    }

    public function resetFailedLogin(string $table, string $id): void {
        $sql = "UPDATE jakarta_connect.{$table}
                SET jumlah_gagal_login = 0, terkunci_sampai = NULL, status = 'aktif'::jakarta_connect.status_akun, login_terakhir_at = NOW()
                WHERE id = :id";
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
}