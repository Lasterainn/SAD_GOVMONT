<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../src/bootstrap.php';

use App\Auth;
use App\Database;

$db   = Database::getConnection();
$auth = new Auth();

$hash = fn(string $pwd): string => password_hash($pwd, PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,
    'time_cost'   => 4,
    'threads'     => 1,
]);

$db->beginTransaction();

try {
    // 1. Admin & Pemerintah
    $stmtAdmin = $db->prepare("
        INSERT INTO jakarta_connect.tbl_admin (nama_lengkap, email, password_hash)
        VALUES (:nama, :email, :hash)
        ON CONFLICT (email) DO UPDATE SET nama_lengkap = EXCLUDED.nama_lengkap
        RETURNING id
    ");
    $stmtAdmin->execute([
        'nama'  => 'Administrator JakartaConnect',
        'email' => 'admin@jakartaconnect.id',
        'hash'  => $hash('Admin#Jakarta2026'),
    ]);
    $adminId = $stmtAdmin->fetchColumn();

    $pemerintahData = [
        ['Dinas Bina Marga DKI Jakarta', 'Ir. Hendra Pratama', 'binamarga@jakarta.go.id'],
        ['Dinas Perhubungan DKI Jakarta', 'Drs. Wahyu Santoso', 'dishub@jakarta.go.id'],
        ['Dinas Sumber Daya Air DKI Jakarta', 'Ir. Kartika Dewi', 'sda@jakarta.go.id'],
    ];

    $pemIds = [];
    $stmtPem = $db->prepare("
        INSERT INTO jakarta_connect.tbl_pemerintah (nama_lengkap, instansi, email, password_hash)
        VALUES (:nama, :instansi, :email, :hash)
        ON CONFLICT (email) DO UPDATE SET instansi = EXCLUDED.instansi
        RETURNING id
    ");
    foreach ($pemerintahData as [$instansi, $nama, $email]) {
        $stmtPem->execute([
            'nama'     => $nama,
            'instansi' => $instansi,
            'email'    => $email,
            'hash'     => $hash('Pemprov#Jakarta2026'),
        ]);
        $pemIds[$instansi] = $stmtPem->fetchColumn();
    }

    // 2. Warga
    $wargaData = [
        ['Rani Prameswari', 'rani.prameswari@warga.id', '3174015204950001'],
        ['Bima Wardana',    'bima.wardana@warga.id',    '3171031408920002'],
        ['Dewi Anggraeni',  'dewi.anggraeni@warga.id',  '3175016509960003'],
    ];

    $wargaIds = [];
    $stmtWarga = $db->prepare("
        INSERT INTO jakarta_connect.tbl_warga (nama_lengkap, email, nik_encrypted, nik_hash, password_hash)
        VALUES (:nama, :email, decode(:nik_enc, 'hex'), decode(:nik_hash, 'hex'), :hash)
        ON CONFLICT (email) DO UPDATE SET nama_lengkap = EXCLUDED.nama_lengkap
        RETURNING id
    ");
    foreach ($wargaData as [$nama, $email, $nik]) {
        $stmtWarga->execute([
            'nama'     => $nama,
            'email'    => $email,
            'nik_enc'  => bin2hex($auth->encryptNIK($nik)),
            'nik_hash' => bin2hex($auth->hashNIK($nik)),
            'hash'     => $hash('Warga#Jakarta2026'),
        ]);
        $wargaIds[] = $stmtWarga->fetchColumn();
    }

    // 3. Proyek Pembangunan
    $projects = [
        [
            'judul'       => 'Revitalisasi Trotoar Jl. HR Rasuna Said',
            'deskripsi'   => 'Penataan ulang jalur pedestrian sepanjang 3,4 km dengan lebar trotoar 4-5 meter, pemasangan guiding block disabilitas, dan ducting utilitas bawah tanah.',
            'kategori'    => 'Infrastruktur',
            'kelurahan'   => 'Karet Kuningan',
            'kota'        => 'Jakarta Selatan',
            'instansi'    => 'Dinas Bina Marga DKI Jakarta',
            'biaya'       => 48500000000,
            'mulai'       => '2026-03-12',
            'selesai'     => '2026-12-20',
            'status'      => 'dalam_pengerjaan',
            'progres'     => 72,
            'konfirmasi'  => 'terkonfirmasi',
            'tahap'       => [
                ['tahap' => 'Persiapan & Survei Utilitas', 'mulai' => '2026-03-12', 'selesai' => '2026-04-30', 'estimasi_hari' => 49, 'status' => 'selesai', 'persen' => 100],
                ['tahap' => 'Relokasi Kabel ke Box Ducting', 'mulai' => '2026-05-01', 'selesai' => '2026-07-31', 'estimasi_hari' => 91, 'status' => 'selesai', 'persen' => 100],
                ['tahap' => 'Pemasangan Granit & Guiding Block', 'mulai' => '2026-08-01', 'selesai' => '2026-11-30', 'estimasi_hari' => 121, 'status' => 'berjalan', 'persen' => 72],
                ['tahap' => 'Penghijauan & Uji Laik Fungsi', 'mulai' => '2026-12-01', 'selesai' => '2026-12-20', 'estimasi_hari' => 19, 'status' => 'rencana', 'persen' => 0],
            ],
        ],
        [
            'judul'       => 'Integrasi Halte Transjakarta Dukuh Atas',
            'deskripsi'   => 'Pembangunan skybridge tertutup sepanjang 180 meter dan perluasan peron untuk menghubungkan KRL, MRT, LRT, dan Kereta Bandara.',
            'kategori'    => 'Transportasi',
            'kelurahan'   => 'Kebon Melati',
            'kota'        => 'Jakarta Pusat',
            'instansi'    => 'Dinas Perhubungan DKI Jakarta',
            'biaya'       => 64000000000,
            'mulai'       => '2026-06-01',
            'selesai'     => '2027-02-28',
            'status'      => 'dalam_pengerjaan',
            'progres'     => 38,
            'konfirmasi'  => 'terkonfirmasi',
            'tahap'       => [
                ['tahap' => 'Pengujian Tanah & Bore Pile', 'mulai' => '2026-06-01', 'selesai' => '2026-07-30', 'estimasi_hari' => 59, 'status' => 'selesai', 'persen' => 100],
                ['tahap' => 'Struktur Baja Jembatan Skybridge', 'mulai' => '2026-08-01', 'selesai' => '2026-11-30', 'estimasi_hari' => 121, 'status' => 'berjalan', 'persen' => 45],
                ['tahap' => 'Instalasi Lift & Kanopi', 'mulai' => '2026-12-01', 'selesai' => '2027-02-28', 'estimasi_hari' => 89, 'status' => 'rencana', 'persen' => 0],
            ],
        ],
        [
            'judul'       => 'Pembangunan Taman & Embung Pengendali Banjir Cakung',
            'deskripsi'   => 'Pembangunan embung retensi seluas 2,4 hektar dengan kapasitas tampung 78.000 m3 yang dipadukan dengan jogging track warga.',
            'kategori'    => 'Lingkungan',
            'kelurahan'   => 'Pulo Gebang',
            'kota'        => 'Jakarta Timur',
            'instansi'    => 'Dinas Sumber Daya Air DKI Jakarta',
            'biaya'       => 41700000000,
            'mulai'       => '2026-01-15',
            'selesai'     => '2026-10-15',
            'status'      => 'selesai',
            'progres'     => 100,
            'konfirmasi'  => 'terkonfirmasi',
            'tahap'       => [
                ['tahap' => 'Pengerukan Basin Embung', 'mulai' => '2026-01-15', 'selesai' => '2026-04-30', 'estimasi_hari' => 105, 'status' => 'selesai', 'persen' => 100],
                ['tahap' => 'Konstruksi Pintu Air & Inlet', 'mulai' => '2026-05-01', 'selesai' => '2026-08-15', 'estimasi_hari' => 106, 'status' => 'selesai', 'persen' => 100],
                ['tahap' => 'Lanskap Taman & Serah Terima', 'mulai' => '2026-08-16', 'selesai' => '2026-10-15', 'estimasi_hari' => 60, 'status' => 'selesai', 'persen' => 100],
            ],
        ],
        [
            'judul'       => 'Penataan Jalur Sepeda & Pedestrian Jl. Daan Mogot',
            'deskripsi'   => 'Pembuatan jalur sepeda terproteksi sepanjang 4,2 km beserta perbaikan permukaan trotoar dan lampu penerangan jalan umum.',
            'kategori'    => 'Transportasi',
            'kelurahan'   => 'Tanjung Duren Utara',
            'kota'        => 'Jakarta Barat',
            'instansi'    => 'Dinas Bina Marga DKI Jakarta',
            'biaya'       => null,
            'mulai'       => '2026-12-01',
            'selesai'     => '2027-06-30',
            'status'      => 'direncanakan',
            'progres'     => 0,
            'konfirmasi'  => 'draft_admin',
            'tahap'       => null,
        ],
    ];

    $projectIds = [];
    $stmtPrj = $db->prepare("
        INSERT INTO jakarta_connect.tbl_proyek (
            judul, deskripsi, kategori, lokasi_kelurahan, lokasi_kota, instansi_pelaksana,
            biaya_pengadaan, tanggal_mulai, tanggal_target_selesai, tahap_pengerjaan,
            status_proyek, progres_persen, status_konfirmasi,
            dibuat_oleh_admin_id, pemilik_pemerintah_id, dikonfirmasi_at
        ) VALUES (
            :judul, :deskripsi, :kategori, :kelurahan, :kota, :instansi,
            :biaya, :mulai, :selesai, :tahap::jsonb,
            :status::jakarta_connect.status_proyek, :progres, :konfirmasi::jakarta_connect.status_konfirmasi,
            :admin_id, :pem_id, :konfirmasi_at
        ) RETURNING id
    ");

    foreach ($projects as $p) {
        $pemId = $pemIds[$p['instansi']] ?? null;
        $isConfirmed = $p['konfirmasi'] === 'terkonfirmasi';
        $stmtPrj->execute([
            'judul'         => $p['judul'],
            'deskripsi'     => $p['deskripsi'],
            'kategori'      => $p['kategori'],
            'kelurahan'     => $p['kelurahan'],
            'kota'          => $p['kota'],
            'instansi'      => $p['instansi'],
            'biaya'         => $p['biaya'],
            'mulai'         => $p['mulai'],
            'selesai'       => $p['selesai'],
            'tahap'         => $p['tahap'] ? json_encode($p['tahap'], JSON_UNESCAPED_UNICODE) : null,
            'status'        => $p['status'],
            'progres'       => $p['progres'],
            'konfirmasi'    => $p['konfirmasi'],
            'admin_id'      => $adminId,
            'pem_id'        => $pemId,
            'konfirmasi_at' => $isConfirmed ? date('Y-m-d H:i:sP') : null,
        ]);
        $projectIds[] = $stmtPrj->fetchColumn();
    }

    // 4. Forum Diskusi & Chat Warga
    $stmtForum = $db->prepare("
        INSERT INTO jakarta_connect.tbl_forum (warga_id, proyek_id, kategori, judul, deskripsi)
        VALUES (:warga_id, :proyek_id, :kategori::jakarta_connect.kategori_forum, :judul, :deskripsi)
        RETURNING id
    ");

    $stmtForum->execute([
        'warga_id'  => $wargaIds[0],
        'proyek_id' => $projectIds[1],
        'kategori'  => 'transportasi',
        'judul'     => 'Akses pejalan kaki di sekitar Stasiun Manggarai & Dukuh Atas',
        'deskripsi' => 'Setiap pagi dan sore pada jam sibuk komuter, arus penyeberang jalan sangat padat. Apakah ada penambahan pelican crossing dengan tombol penyeberang?',
    ]);
    $forum1Id = $stmtForum->fetchColumn();

    $stmtForum->execute([
        'warga_id'  => $wargaIds[1],
        'proyek_id' => $projectIds[2],
        'kategori'  => 'lingkungan',
        'judul'     => 'Usulan titik baru bank sampah & jadwal kegiatan di Embung Cakung',
        'deskripsi' => 'Warga RW 04 mengusulkan pemanfaatan area tepi taman embung untuk titik bank sampah unit dan edukasi pemilahan sampah warga.',
    ]);
    $forum2Id = $stmtForum->fetchColumn();

    $stmtChat = $db->prepare("
        INSERT INTO jakarta_connect.tbl_chat (forum_id, warga_id, balasan_ke_chat_id, isi)
        VALUES (:forum_id, :warga_id, :parent_id, :isi)
        RETURNING id
    ");
    $stmtChat->execute([
        'forum_id'  => $forum1Id,
        'warga_id'  => $wargaIds[1],
        'parent_id' => null,
        'isi'       => 'Setuju sekali, terutama saat hujan turun jarak pandang pengendara berkurang. Lampu peringatan kuning berkedip di atas zebra cross akan sangat membantu.',
    ]);
    $chat1Id = $stmtChat->fetchColumn();

    $stmtChat->execute([
        'forum_id'  => $forum1Id,
        'warga_id'  => $wargaIds[2],
        'parent_id' => $chat1Id,
        'isi'       => 'Betul Pak Bima, semoga sekaligus dilengkapi ramp landai untuk pengguna kursi roda dan kereta dorong bayi.',
    ]);

    $db->commit();
    echo "Seed database JakartaConnect berhasil dijalankan!\n";
    echo "Akun Demo:\n";
    echo "- Admin      : admin@jakartaconnect.id / Admin#Jakarta2026\n";
    echo "- Pemerintah : binamarga@jakarta.go.id / Pemprov#Jakarta2026\n";
    echo "- Warga      : rani.prameswari@warga.id / Warga#Jakarta2026\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, "Gagal seed database: " . $e->getMessage() . "\n");
    exit(1);
}