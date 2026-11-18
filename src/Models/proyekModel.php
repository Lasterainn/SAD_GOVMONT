<?php
namespace App\Models;

use App\Database;
use PDO;
use Throwable;

class ProyekModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public static function isValidUuid(?string $id): bool {
        return is_string($id) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id) === 1;
    }

    public static function statusLabels(): array {
        return [
            'dalam_pengerjaan' => 'Dalam pengerjaan',
            'selesai'          => 'Selesai',
            'direncanakan'     => 'Direncanakan',
        ];
    }

    public static function konfirmasiLabels(): array {
        return [
            'draft_admin'         => 'Draft Admin',
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
            'terkonfirmasi'       => 'Terkonfirmasi',
        ];
    }

    private function enrichRow(array $row, int $index = 0): array {
        $kota = $row['lokasi_kota'] ?? 'Jakarta Pusat';
        $cityCoords = [
            'Jakarta Pusat'   => ['x' => 48, 'y' => 44, 'lat' => -6.1862, 'lng' => 106.8340],
            'Jakarta Selatan' => ['x' => 46, 'y' => 64, 'lat' => -6.2615, 'lng' => 106.8106],
            'Jakarta Barat'   => ['x' => 31, 'y' => 36, 'lat' => -6.1674, 'lng' => 106.7637],
            'Jakarta Timur'   => ['x' => 72, 'y' => 45, 'lat' => -6.2250, 'lng' => 106.9004],
            'Jakarta Utara'   => ['x' => 54, 'y' => 24, 'lat' => -6.1384, 'lng' => 106.8639],
        ];
        $base = $cityCoords[$kota] ?? $cityCoords['Jakarta Pusat'];
        $hashByte = crc32((string)$row['id']);
        $offsetX = (($hashByte % 11) - 5);
        $offsetY = ((($hashByte >> 4) % 9) - 4);

        $tahap = [];
        if (!empty($row['tahap_pengerjaan'])) {
            $decoded = is_array($row['tahap_pengerjaan'])
                ? $row['tahap_pengerjaan']
                : json_decode((string)$row['tahap_pengerjaan'], true);
            if (is_array($decoded)) {
                $tahap = $decoded;
            }
        }

        $biaya = $row['biaya_pengadaan'];
        $budgetFormatted = $biaya !== null
            ? 'Rp ' . number_format((float)$biaya, 0, ',', '.')
            : 'Belum ditetapkan';

        $kelurahan = trim((string)($row['lokasi_kelurahan'] ?? ''));
        $locationFull = $kelurahan !== '' ? "{$kelurahan}, {$kota}" : $kota;

        $gambarUrl = null;
        if (!empty($row['gambar_uuid']) && !empty($row['gambar_ekstensi'])) {
            $rel = '/uploads/proyek/' . $row['gambar_uuid'] . '.' . $row['gambar_ekstensi'];
            if (is_file(__DIR__ . '/../../public' . $rel)) {
                $gambarUrl = $rel;
            }
        }

        $tones = ['Infrastruktur' => 1, 'Lingkungan' => 2, 'Fasilitas Publik' => 3, 'Transportasi' => 4];

        return array_merge($row, [
            'tone'              => $tones[$row['kategori'] ?? ''] ?? (($index % 4) + 1),
            'location'          => $locationFull,
            'agency'            => $row['instansi_pelaksana'] ?: ($row['pemerintah_instansi'] ?? 'Pemerintah Provinsi DKI Jakarta'),
            'progress'          => (int)$row['progres_persen'],
            'budget_formatted'  => $budgetFormatted,
            'target_formatted'  => !empty($row['tanggal_target_selesai'])
                ? 'Target ' . date('d M Y', strtotime($row['tanggal_target_selesai']))
                : 'Jadwal menyusul',
            'start_formatted'   => !empty($row['tanggal_mulai'])
                ? date('d M Y', strtotime($row['tanggal_mulai']))
                : '-',
            'updated_formatted' => !empty($row['updated_at'])
                ? date('d M Y H:i', strtotime($row['updated_at']))
                : '-',
            'tahap_list'        => $tahap,
            'gambar_url'        => $gambarUrl,
            'map_x'             => max(16, min(84, $base['x'] + $offsetX)),
            'map_y'             => max(18, min(82, $base['y'] + $offsetY)),
            'coords'            => number_format($base['lat'] + ($offsetY * 0.004), 4) . ', ' . number_format($base['lng'] + ($offsetX * 0.004), 4),
        ]);
    }

    public function all(bool $onlyConfirmed = false): array {
        $where = $onlyConfirmed ? "WHERE p.status_konfirmasi = 'terkonfirmasi'" : "";
        $sql = "
            SELECT p.*,
                   pem.nama_lengkap AS pemerintah_nama,
                   pem.instansi     AS pemerintah_instansi,
                   adm.nama_lengkap AS admin_nama,
                   (SELECT COUNT(*) FROM jakarta_connect.tbl_forum f WHERE f.proyek_id = p.id) AS jumlah_diskusi
            FROM jakarta_connect.tbl_proyek p
            LEFT JOIN jakarta_connect.tbl_pemerintah pem ON pem.id = p.pemilik_pemerintah_id
            LEFT JOIN jakarta_connect.tbl_admin adm ON adm.id = p.dibuat_oleh_admin_id
            {$where}
            ORDER BY p.created_at DESC
        ";
        $rows = $this->db->query($sql)->fetchAll();
        return array_map(fn($r, $i) => $this->enrichRow($r, $i), $rows, array_keys($rows));
    }

    public function findById(string $id): ?array {
        if (!self::isValidUuid($id)) {
            return null;
        }
        $stmt = $this->db->prepare("
            SELECT p.*,
                   pem.nama_lengkap AS pemerintah_nama,
                   pem.instansi     AS pemerintah_instansi,
                   adm.nama_lengkap AS admin_nama
            FROM jakarta_connect.tbl_proyek p
            LEFT JOIN jakarta_connect.tbl_pemerintah pem ON pem.id = p.pemilik_pemerintah_id
            LEFT JOIN jakarta_connect.tbl_admin adm ON adm.id = p.dibuat_oleh_admin_id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->enrichRow($row) : null;
    }

    public function getStats(): array {
        $proyekStats = $this->db->query("SELECT * FROM jakarta_connect.v_dashboard_proyek")->fetch() ?: [
            'total_proyek'     => 0,
            'dalam_pengerjaan' => 0,
            'selesai'          => 0,
            'belum_selesai'    => 0,
        ];
        $wargaCount = (int)$this->db->query("SELECT COUNT(*) FROM jakarta_connect.tbl_warga")->fetchColumn();
        $forumCount = (int)$this->db->query("SELECT COUNT(*) FROM jakarta_connect.tbl_forum")->fetchColumn();

        return array_merge($proyekStats, [
            'total_warga' => $wargaCount,
            'total_forum' => $forumCount,
        ]);
    }

    public function getPemerintahList(): array {
        return $this->db->query("
            SELECT id, nama_lengkap, instansi, email
            FROM jakarta_connect.tbl_pemerintah
            WHERE status = 'aktif'
            ORDER BY instansi ASC
        ")->fetchAll();
    }

    public function createByAdmin(array $data, string $adminId, ?array $img, string $ip): string {
        $this->db->beginTransaction();
        try {
            $statusProyek = $data['status_proyek'];
            $progres      = (int)$data['progres_persen'];
            if ($statusProyek === 'selesai') {
                $progres = 100;
            }

            $pemId = !empty($data['pemilik_pemerintah_id']) && self::isValidUuid($data['pemilik_pemerintah_id'])
                ? $data['pemilik_pemerintah_id']
                : null;

            $stmt = $this->db->prepare("
                INSERT INTO jakarta_connect.tbl_proyek (
                    judul, deskripsi, kategori, lokasi_kelurahan, lokasi_kota, instansi_pelaksana,
                    tanggal_mulai, tanggal_target_selesai, status_proyek, progres_persen,
                    gambar_uuid, gambar_ekstensi, gambar_mime, gambar_ukuran_byte,
                    status_konfirmasi, dibuat_oleh_admin_id, pemilik_pemerintah_id
                ) VALUES (
                    :judul, :deskripsi, :kategori, :kelurahan, :kota, :instansi,
                    :mulai, :selesai, :status::jakarta_connect.status_proyek, :progres,
                    :g_uuid, :g_ext, :g_mime, :g_size,
                    :konfirmasi::jakarta_connect.status_konfirmasi, :admin_id, :pem_id
                ) RETURNING id
            ");

            $konfirmasi = $pemId ? 'menunggu_konfirmasi' : 'draft_admin';
            $stmt->execute([
                'judul'      => $data['judul'],
                'deskripsi'  => $data['deskripsi'] ?: null,
                'kategori'   => $data['kategori'] ?: 'Infrastruktur',
                'kelurahan'  => $data['lokasi_kelurahan'] ?: null,
                'kota'       => $data['lokasi_kota'] ?: 'Jakarta Pusat',
                'instansi'   => $data['instansi_pelaksana'] ?: null,
                'mulai'      => $data['tanggal_mulai'] ?: null,
                'selesai'    => $data['tanggal_target_selesai'] ?: null,
                'status'     => $statusProyek,
                'progres'    => $progres,
                'g_uuid'     => $img['uuid'] ?? null,
                'g_ext'      => $img['ext'] ?? null,
                'g_mime'     => $img['mime'] ?? null,
                'g_size'     => $img['size'] ?? null,
                'konfirmasi' => $konfirmasi,
                'admin_id'   => $adminId,
                'pem_id'     => $pemId,
            ]);
            $newId = (string)$stmt->fetchColumn();

            $this->logProyek($newId, $adminId, 'admin', 'buat', null, $data, $ip);
            $this->db->commit();
            return $newId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateByAdmin(string $id, array $data, string $adminId, ?array $img, string $ip): bool {
        $old = $this->findById($id);
        if (!$old) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $statusProyek = $data['status_proyek'];
            $progres      = (int)$data['progres_persen'];
            if ($statusProyek === 'selesai') {
                $progres = 100;
            } elseif ($progres === 100 && $statusProyek !== 'selesai') {
                $progres = 99;
            }

            $pemId = !empty($data['pemilik_pemerintah_id']) && self::isValidUuid($data['pemilik_pemerintah_id'])
                ? $data['pemilik_pemerintah_id']
                : $old['pemilik_pemerintah_id'];

            $gUuid = $img['uuid'] ?? $old['gambar_uuid'];
            $gExt  = $img['ext']  ?? $old['gambar_ekstensi'];
            $gMime = $img['mime'] ?? $old['gambar_mime'];
            $gSize = $img['size'] ?? $old['gambar_ukuran_byte'];

            $stmt = $this->db->prepare("
                UPDATE jakarta_connect.tbl_proyek
                SET judul = :judul,
                    deskripsi = :deskripsi,
                    kategori = :kategori,
                    lokasi_kelurahan = :kelurahan,
                    lokasi_kota = :kota,
                    instansi_pelaksana = :instansi,
                    tanggal_mulai = :mulai,
                    tanggal_target_selesai = :selesai,
                    status_proyek = :status::jakarta_connect.status_proyek,
                    progres_persen = :progres,
                    pemilik_pemerintah_id = :pem_id,
                    gambar_uuid = :g_uuid,
                    gambar_ekstensi = :g_ext,
                    gambar_mime = :g_mime,
                    gambar_ukuran_byte = :g_size
                WHERE id = :id
            ");
            $stmt->execute([
                'judul'     => $data['judul'],
                'deskripsi' => $data['deskripsi'] ?: null,
                'kategori'  => $data['kategori'],
                'kelurahan' => $data['lokasi_kelurahan'] ?: null,
                'kota'      => $data['lokasi_kota'],
                'instansi'  => $data['instansi_pelaksana'] ?: null,
                'mulai'     => $data['tanggal_mulai'] ?: null,
                'selesai'   => $data['tanggal_target_selesai'] ?: null,
                'status'    => $statusProyek,
                'progres'   => $progres,
                'pem_id'    => $pemId,
                'g_uuid'    => $gUuid,
                'g_ext'     => $gExt,
                'g_mime'    => $gMime,
                'g_size'    => $gSize,
                'id'        => $id,
            ]);

            $this->logProyek($id, $adminId, 'admin', 'ubah', $old, $data, $ip);
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateAndConfirmByPemerintah(string $id, array $data, string $pemerintahId, ?array $img, string $ip): bool {
        $old = $this->findById($id);
        if (!$old) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $statusProyek = $data['status_proyek'];
            $progres      = (int)$data['progres_persen'];
            if ($statusProyek === 'selesai') {
                $progres = 100;
            } elseif ($progres === 100 && $statusProyek !== 'selesai') {
                $progres = 99;
            }

            $tahapJson = !empty($data['tahap_pengerjaan'])
                ? json_encode($data['tahap_pengerjaan'], JSON_UNESCAPED_UNICODE)
                : ($old['tahap_pengerjaan'] ?: json_encode([['tahap' => 'Pelaksanaan Utama', 'status' => 'berjalan', 'persen' => $progres]]));

            $biaya = $data['biaya_pengadaan'] !== '' && $data['biaya_pengadaan'] !== null
                ? (float)$data['biaya_pengadaan']
                : ($old['biaya_pengadaan'] !== null ? (float)$old['biaya_pengadaan'] : 0.0);

            $doConfirm = !empty($data['konfirmasi']) || $old['status_konfirmasi'] === 'terkonfirmasi';
            $konfirmasiStatus = $doConfirm ? 'terkonfirmasi' : 'menunggu_konfirmasi';

            $gUuid = $img['uuid'] ?? $old['gambar_uuid'];
            $gExt  = $img['ext']  ?? $old['gambar_ekstensi'];
            $gMime = $img['mime'] ?? $old['gambar_mime'];
            $gSize = $img['size'] ?? $old['gambar_ukuran_byte'];

            $stmt = $this->db->prepare("
                UPDATE jakarta_connect.tbl_proyek
                SET biaya_pengadaan = :biaya,
                    tanggal_mulai = :mulai,
                    tanggal_target_selesai = :selesai,
                    tahap_pengerjaan = :tahap::jsonb,
                    status_proyek = :status::jakarta_connect.status_proyek,
                    progres_persen = :progres,
                    pemilik_pemerintah_id = :pem_id,
                    status_konfirmasi = :konfirmasi::jakarta_connect.status_konfirmasi,
                    dikonfirmasi_at = CASE WHEN :do_confirm = '1' THEN COALESCE(dikonfirmasi_at, NOW()) ELSE dikonfirmasi_at END,
                    gambar_uuid = :g_uuid,
                    gambar_ekstensi = :g_ext,
                    gambar_mime = :g_mime,
                    gambar_ukuran_byte = :g_size
                WHERE id = :id
            ");
            $stmt->execute([
                'biaya'      => $biaya,
                'mulai'      => $data['tanggal_mulai'] ?: $old['tanggal_mulai'],
                'selesai'    => $data['tanggal_target_selesai'] ?: $old['tanggal_target_selesai'],
                'tahap'      => $tahapJson,
                'status'     => $statusProyek,
                'progres'    => $progres,
                'pem_id'     => $pemerintahId,
                'konfirmasi' => $konfirmasiStatus,
                'do_confirm' => $doConfirm ? '1' : '0',
                'g_uuid'     => $gUuid,
                'g_ext'      => $gExt,
                'g_mime'     => $gMime,
                'g_size'     => $gSize,
                'id'         => $id,
            ]);

            $aksi = ($doConfirm && $old['status_konfirmasi'] !== 'terkonfirmasi') ? 'konfirmasi' : 'ubah';
            $this->logProyek($id, $pemerintahId, 'pemerintah', $aksi, $old, $data, $ip);
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteByAdmin(string $id, string $adminId, string $ip): bool {
        $old = $this->findById($id);
        if (!$old) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM jakarta_connect.tbl_proyek WHERE id = :id");
            $stmt->execute(['id' => $id]);

            $this->logProyek($id, $adminId, 'admin', 'hapus', $old, null, $ip);
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function recentAuditLogs(int $limit = 6): array {
        $stmt = $this->db->prepare("
            SELECT 'PROYEK' AS sumber, aksi, proyek_id::text AS ref_id, aktor_role::text AS role, created_at
            FROM jakarta_connect.tbl_log_proyek
            UNION ALL
            SELECT 'FORUM' AS sumber, aksi, forum_id::text AS ref_id, aktor_role::text AS role, created_at
            FROM jakarta_connect.tbl_log_forum
            ORDER BY created_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function logProyek(string $proyekId, string $aktorId, string $role, string $aksi, ?array $lama, ?array $baru, string $ip): void {
        $stmt = $this->db->prepare("
            INSERT INTO jakarta_connect.tbl_log_proyek
                (proyek_id, aktor_id, aktor_role, aksi, data_lama, data_baru, ip_address)
            VALUES
                (:proyek_id, :aktor_id, :role::jakarta_connect.role_user, :aksi, :lama::jsonb, :baru::jsonb, :ip::inet)
        ");
        $stmt->execute([
            'proyek_id' => $proyekId,
            'aktor_id'  => $aktorId,
            'role'      => $role,
            'aksi'      => $aksi,
            'lama'      => $lama ? json_encode($lama, JSON_UNESCAPED_UNICODE) : null,
            'baru'      => $baru ? json_encode($baru, JSON_UNESCAPED_UNICODE) : null,
            'ip'        => $ip,
        ]);
    }
}