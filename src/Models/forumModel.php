<?php
namespace App\Models;

use App\Database;
use PDO;
use RuntimeException;
use Throwable;

class ForumModel {
    private PDO $db;

    public const CATEGORY_MAP = [
        'infrastruktur'    => 'Infrastruktur',
        'transportasi'     => 'Transportasi',
        'lingkungan'       => 'Lingkungan',
        'fasilitas_publik' => 'Fasilitas Publik',
        'acara_jakarta'    => 'Acara Jakarta',
    ];

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public static function categoryLabel(string $enumVal): string {
        return self::CATEGORY_MAP[$enumVal] ?? ucfirst(str_replace('_', ' ', $enumVal));
    }

    public static function isValidCategory(string $enumVal): bool {
        return array_key_exists($enumVal, self::CATEGORY_MAP);
    }

    public function allForums(?string $projectId = null): array {
        $where = '';
        $params = [];
        if ($projectId !== null && ProyekModel::isValidUuid($projectId)) {
            $where = 'WHERE f.proyek_id = :proyek_id';
            $params['proyek_id'] = $projectId;
        }

        $sql = "
            SELECT f.*,
                   w.nama_lengkap AS author_name,
                   p.judul        AS proyek_judul,
                   (SELECT COUNT(*) FROM jakarta_connect.tbl_chat c WHERE c.forum_id = f.id) AS comments_count
            FROM jakarta_connect.tbl_forum f
            JOIN jakarta_connect.tbl_warga w ON w.id = f.warga_id
            LEFT JOIN jakarta_connect.tbl_proyek p ON p.id = f.proyek_id
            {$where}
            ORDER BY f.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map(function ($r, $idx) {
            return array_merge($r, [
                'color'          => ($idx % 5) + 1,
                'category_label' => self::categoryLabel($r['kategori']),
                'time_formatted' => date('d M Y, H:i', strtotime($r['created_at'])),
                'excerpt'        => mb_substr(trim((string)($r['deskripsi'] ?? '')), 0, 180),
            ]);
        }, $rows, array_keys($rows));
    }

    public function categoryCounts(): array {
        $rows = $this->db->query("
            SELECT kategori::text AS kategori, COUNT(*)::int AS total
            FROM jakarta_connect.tbl_forum
            GROUP BY kategori
        ")->fetchAll();

        $byEnum = [];
        $totalAll = 0;
        foreach ($rows as $r) {
            $byEnum[$r['kategori']] = (int)$r['total'];
            $totalAll += (int)$r['total'];
        }

        $list = [['enum' => '', 'name' => 'Semua diskusi', 'count' => $totalAll]];
        foreach (self::CATEGORY_MAP as $enum => $label) {
            $list[] = [
                'enum'  => $enum,
                'name'  => $label,
                'count' => $byEnum[$enum] ?? 0,
            ];
        }
        return $list;
    }

    public function findForumById(string $id): ?array {
        if (!ProyekModel::isValidUuid($id)) {
            return null;
        }
        $stmt = $this->db->prepare("
            SELECT f.*,
                   w.nama_lengkap AS author_name,
                   p.judul        AS proyek_judul
            FROM jakarta_connect.tbl_forum f
            JOIN jakarta_connect.tbl_warga w ON w.id = f.warga_id
            LEFT JOIN jakarta_connect.tbl_proyek p ON p.id = f.proyek_id
            WHERE f.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return array_merge($row, [
            'color'          => (crc32((string)$row['warga_id']) % 5) + 1,
            'category_label' => self::categoryLabel($row['kategori']),
            'time_formatted' => date('d M Y, H:i', strtotime($row['created_at'])),
        ]);
    }

    public function getChatsByForumId(string $forumId): array {
        if (!ProyekModel::isValidUuid($forumId)) {
            return [];
        }
        $stmt = $this->db->prepare("
            SELECT c.id, c.forum_id, c.warga_id, c.balasan_ke_chat_id,
                   CASE WHEN c.dihapus THEN 'Chat ini dihapus karena ' || c.alasan_hapus ELSE c.isi END AS isi_tampil,
                   c.isi AS isi_asli,
                   c.dihapus, c.alasan_hapus, c.created_at, c.updated_at,
                   w.nama_lengkap AS author_name
            FROM jakarta_connect.tbl_chat c
            JOIN jakarta_connect.tbl_warga w ON w.id = c.warga_id
            WHERE c.forum_id = :forum_id
            ORDER BY c.created_at ASC
        ");
        $stmt->execute(['forum_id' => $forumId]);
        $rows = $stmt->fetchAll();

        $byId = [];
        foreach ($rows as $r) {
            $r['color']          = (crc32((string)$r['warga_id']) % 5) + 1;
            $r['time_formatted'] = date('d M Y, H:i', strtotime($r['created_at']));
            $r['children']       = [];
            $byId[$r['id']]      = $r;
        }

        $tree = [];
        foreach ($byId as $id => &$node) {
            $parentId = $node['balasan_ke_chat_id'];
            if ($parentId && isset($byId[$parentId])) {
                $byId[$parentId]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }
        unset($node);

        return $tree;
    }

    public function createForum(string $wargaId, string $kategori, string $judul, string $deskripsi, ?string $proyekId, string $ip): string {
        $this->db->beginTransaction();
        try {
            $validProjectId = ($proyekId && ProyekModel::isValidUuid($proyekId)) ? $proyekId : null;

            $stmt = $this->db->prepare("
                INSERT INTO jakarta_connect.tbl_forum (warga_id, proyek_id, kategori, judul, deskripsi)
                VALUES (:warga_id, :proyek_id, :kategori::jakarta_connect.kategori_forum, :judul, :deskripsi)
                RETURNING id
            ");
            $stmt->execute([
                'warga_id'  => $wargaId,
                'proyek_id' => $validProjectId,
                'kategori'  => $kategori,
                'judul'     => $judul,
                'deskripsi' => $deskripsi !== '' ? $deskripsi : null,
            ]);
            $forumId = (string)$stmt->fetchColumn();

            $this->logForum($forumId, null, $wargaId, 'warga', 'buat_forum', null, null, $ip);
            $this->db->commit();
            return $forumId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateForum(string $forumId, string $wargaId, string $kategori, string $judul, string $deskripsi, ?string $proyekId): bool {
        if (!ProyekModel::isValidUuid($forumId)) {
            return false;
        }
        $validProjectId = ($proyekId && ProyekModel::isValidUuid($proyekId)) ? $proyekId : null;

        $stmt = $this->db->prepare("
            UPDATE jakarta_connect.tbl_forum
            SET kategori = :kategori::jakarta_connect.kategori_forum,
                judul = :judul,
                deskripsi = :deskripsi,
                proyek_id = :proyek_id
            WHERE id = :id AND warga_id = :warga_id
        ");
        $stmt->execute([
            'kategori'  => $kategori,
            'judul'     => $judul,
            'deskripsi' => $deskripsi !== '' ? $deskripsi : null,
            'proyek_id' => $validProjectId,
            'id'        => $forumId,
            'warga_id'  => $wargaId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function createChat(string $forumId, string $wargaId, string $isi, ?string $replyToId, string $ip): string {
        // Rate limiting anti-spam menggunakan idx_chat_warga (maks 5 pesan / 60 detik)
        $rateStmt = $this->db->prepare("
            SELECT COUNT(*) FROM jakarta_connect.tbl_chat
            WHERE warga_id = :warga_id AND created_at >= now() - interval '60 seconds'
        ");
        $rateStmt->execute(['warga_id' => $wargaId]);
        if ((int)$rateStmt->fetchColumn() >= 5) {
            throw new RuntimeException('Terlalu banyak tanggapan dalam 1 menit. Tunggu sebentar sebelum mengirim lagi.');
        }

        $cooldownStmt = $this->db->prepare("
            SELECT 1 FROM jakarta_connect.tbl_chat
            WHERE warga_id = :warga_id AND created_at >= NOW() - INTERVAL '5 seconds'
            LIMIT 1
        ");
        $cooldownStmt->execute(['warga_id' => $wargaId]);
        if ($cooldownStmt->fetchColumn()) {
            throw new RuntimeException('Tunggu minimal 5 detik sebelum mengirim tanggapan berikutnya.');
        }

        $parentId = null;
        if ($replyToId && ProyekModel::isValidUuid($replyToId)) {
            $chk = $this->db->prepare("SELECT id FROM jakarta_connect.tbl_chat WHERE id = :cid AND forum_id = :fid");
            $chk->execute(['cid' => $replyToId, 'fid' => $forumId]);
            if ($chk->fetchColumn()) {
                $parentId = $replyToId;
            }
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO jakarta_connect.tbl_chat (forum_id, warga_id, balasan_ke_chat_id, isi)
                VALUES (:forum_id, :warga_id, :parent_id, :isi)
                RETURNING id
            ");
            $stmt->execute([
                'forum_id'  => $forumId,
                'warga_id'  => $wargaId,
                'parent_id' => $parentId,
                'isi'       => $isi,
            ]);
            $chatId = (string)$stmt->fetchColumn();

            $this->logForum($forumId, $chatId, $wargaId, 'warga', 'buat_chat', null, null, $ip);
            $this->db->commit();
            return $chatId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateChat(string $chatId, string $wargaId, string $isi): bool {
        if (!ProyekModel::isValidUuid($chatId)) {
            return false;
        }
        $stmt = $this->db->prepare("
            UPDATE jakarta_connect.tbl_chat
            SET isi = :isi
            WHERE id = :id AND warga_id = :warga_id AND dihapus = false
        ");
        $stmt->execute([
            'isi'      => $isi,
            'id'       => $chatId,
            'warga_id' => $wargaId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function softDeleteChatByAdmin(string $chatId, string $adminId, string $alasan, string $ip): bool {
        if (!ProyekModel::isValidUuid($chatId)) {
            return false;
        }
        $stmtFind = $this->db->prepare("
            SELECT id, forum_id, isi, dihapus
            FROM jakarta_connect.tbl_chat
            WHERE id = :id LIMIT 1
        ");
        $stmtFind->execute(['id' => $chatId]);
        $chat = $stmtFind->fetch();
        if (!$chat || $chat['dihapus']) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE jakarta_connect.tbl_chat
                SET dihapus = true,
                    alasan_hapus = :alasan,
                    dihapus_oleh_admin = :admin_id,
                    dihapus_at = NOW()
                WHERE id = :id AND dihapus = false
            ");
            $stmt->execute([
                'alasan'   => $alasan,
                'admin_id' => $adminId,
                'id'       => $chatId,
            ]);

            $this->logForum(
                (string)$chat['forum_id'],
                $chatId,
                $adminId,
                'admin',
                'hapus_chat',
                $alasan,
                (string)$chat['isi'],
                $ip
            );
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function logForum(
        string $forumId,
        ?string $chatId,
        string $aktorId,
        string $role,
        string $aksi,
        ?string $alasan,
        ?string $snapshot,
        string $ip
    ): void {
        $stmt = $this->db->prepare("
            INSERT INTO jakarta_connect.tbl_log_forum
                (forum_id, chat_id, aktor_id, aktor_role, aksi, alasan, isi_snapshot, ip_address)
            VALUES
                (:forum_id, :chat_id, :aktor_id, :role::jakarta_connect.role_user, :aksi, :alasan, :snapshot, :ip::inet)
        ");
        $stmt->execute([
            'forum_id' => $forumId,
            'chat_id'  => $chatId,
            'aktor_id' => $aktorId,
            'role'     => $role,
            'aksi'     => $aksi,
            'alasan'   => $alasan,
            'snapshot' => $snapshot,
            'ip'       => $ip,
        ]);
    }

    public function cekBatasBuatDiskusi(string $wargaId, string $judul, string $deskripsi): ?string
    {
        // 1. Cek jeda waktu (cooldown 60 detik) & kuota per jam (maks 5 utas)
        $stmtRate = $this->db->prepare("
            SELECT
                COUNT(*) FILTER (WHERE created_at > NOW() - INTERVAL '60 seconds') AS dalam_60_detik,
                COUNT(*) FILTER (WHERE created_at > NOW() - INTERVAL '1 hour')     AS dalam_1_jam
            FROM jakarta_connect.tbl_forum
            WHERE warga_id = :warga_id
        ");
        $stmtRate->execute(['warga_id' => $wargaId]);
        $rate = $stmtRate->fetch();

        if ((int)($rate['dalam_60_detik'] ?? 0) > 0) {
            return 'Tunggu minimal 60 detik sebelum membuat topik diskusi baru.';
        }
        if ((int)($rate['dalam_1_jam'] ?? 0) >= 5) {
            return 'Anda telah mencapai batas maksimal 5 topik diskusi baru per jam.';
        }

        // 2. Cek duplikasi judul atau deskripsi dalam 10 menit terakhir
        // Pastikan :warga_id, :judul, dan :deskripsi masing-masing hanya muncul 1 kali
        $stmtDup = $this->db->prepare("
            SELECT 1
            FROM jakarta_connect.tbl_forum
            WHERE warga_id = :warga_id
              AND created_at > NOW() - INTERVAL '10 minutes'
              AND (
                  LOWER(BTRIM(judul)) = LOWER(BTRIM(:judul))
                  OR (
                      COALESCE(BTRIM(deskripsi), '') <> ''
                      AND LOWER(BTRIM(deskripsi)) = LOWER(BTRIM(:deskripsi))
                  )
              )
            LIMIT 1
        ");
        $stmtDup->execute([
            'warga_id'  => $wargaId,
            'judul'     => $judul,
            'deskripsi' => $deskripsi,
        ]);

        if ($stmtDup->fetchColumn()) {
            return 'Diskusi dengan judul atau isi yang sama baru saja Anda kirimkan.';
        }

        return null;
    }
}