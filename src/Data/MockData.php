<?php
namespace App\Data;

class MockData {
    public static function statusLabels(): array {
        return [
            'ongoing' => 'Dalam pengerjaan',
            'done'    => 'Selesai',
            'planned' => 'Direncanakan',
        ];
    }

    public static function projects(): array {
        return [
            [
                'id'       => 'prj-01',
                'tone'     => 1,
                'status'   => 'ongoing',
                'location' => 'Setiabudi, Jakarta Selatan',
                'wilayah'  => 'Jakarta Selatan',
                'category' => 'Infrastruktur',
                'title'    => 'Revitalisasi Trotoar Jl. HR Rasuna Said',
                'agency'   => 'Dinas Bina Marga DKI Jakarta',
                'progress' => 72,
                'target'   => 'Target Desember 2025',
                'updated'  => 'Diperbarui 3 jam lalu',
                'budget'   => 'Rp 48,5 Miliar',
                'konfirmasi' => 'Terkonfirmasi',
            ],
            [
                'id'       => 'prj-02',
                'tone'     => 2,
                'status'   => 'ongoing',
                'location' => 'Mampang Prapatan, Jakarta Selatan',
                'wilayah'  => 'Jakarta Selatan',
                'category' => 'Infrastruktur',
                'title'    => 'Peningkatan Drainase Kawasan Kemang',
                'agency'   => 'Dinas Sumber Daya Air DKI Jakarta',
                'progress' => 48,
                'target'   => 'Target Februari 2026',
                'updated'  => 'Diperbarui kemarin',
                'budget'   => 'Rp 31,2 Miliar',
                'konfirmasi' => 'Terkonfirmasi',
            ],
            [
                'id'       => 'prj-03',
                'tone'     => 3,
                'status'   => 'done',
                'location' => 'Kebayoran Baru, Jakarta Selatan',
                'wilayah'  => 'Jakarta Selatan',
                'category' => 'Fasilitas Publik',
                'title'    => 'Penataan Taman Literasi Blok M',
                'agency'   => 'Dinas Pertamanan dan Hutan Kota',
                'progress' => 100,
                'target'   => 'Target September 2025',
                'updated'  => 'Diperbarui 4 hari lalu',
                'budget'   => 'Rp 19,8 Miliar',
                'konfirmasi' => 'Terkonfirmasi',
            ],
            [
                'id'       => 'prj-04',
                'tone'     => 4,
                'status'   => 'ongoing',
                'location' => 'Tanah Abang, Jakarta Pusat',
                'wilayah'  => 'Jakarta Pusat',
                'category' => 'Transportasi',
                'title'    => 'Integrasi Halte Transjakarta Dukuh Atas',
                'agency'   => 'Dinas Perhubungan DKI Jakarta',
                'progress' => 31,
                'target'   => 'Target April 2026',
                'updated'  => 'Diperbarui 5 hari lalu',
                'budget'   => 'Rp 64,0 Miliar',
                'konfirmasi' => 'Terkonfirmasi',
            ],
        ];
    }

    public static function discussions(): array {
        return [
            [
                'id'       => 'dsc-01',
                'color'    => 1,
                'author'   => 'Rani Prameswari',
                'official' => false,
                'time'     => '20 menit lalu',
                'title'    => 'Akses pejalan kaki di sekitar Stasiun Manggarai',
                'excerpt'  => 'Apakah ada rencana penambahan pelican crossing pada sisi timur stasiun?',
                'comments' => 24,
                'likes'    => 81,
                'category' => 'Transportasi',
            ],
            [
                'id'       => 'dsc-02',
                'color'    => 2,
                'author'   => 'Dinas Bina Marga',
                'official' => true,
                'time'     => '2 jam lalu',
                'title'    => 'Penjelasan tahapan perbaikan Jl. Daan Mogot',
                'excerpt'  => 'Pekerjaan malam hari akan dimulai pada 18 November untuk meminimalkan gangguan lalu lintas.',
                'comments' => 43,
                'likes'    => 156,
                'category' => 'Infrastruktur',
            ],
            [
                'id'       => 'dsc-03',
                'color'    => 3,
                'author'   => 'Bima Wardana',
                'official' => false,
                'time'     => 'Kemarin',
                'title'    => 'Usulan titik baru bank sampah di Cempaka Putih',
                'excerpt'  => 'Warga RW 04 mengusulkan lahan dekat balai warga untuk mendukung pemilahan sampah.',
                'comments' => 17,
                'likes'    => 54,
                'category' => 'Lingkungan',
            ],
            [
                'id'       => 'dsc-04',
                'color'    => 4,
                'author'   => 'Dewi Anggraeni',
                'official' => false,
                'time'     => '2 hari lalu',
                'title'    => 'Lampu penerangan di jalur pedestrian Matraman',
                'excerpt'  => 'Beberapa titik masih gelap pada malam hari. Mohon informasi jadwal pemeliharaan berikutnya.',
                'comments' => 11,
                'likes'    => 38,
                'category' => 'Fasilitas Publik',
            ],
        ];
    }

    public static function forumCategories(): array {
        return [
            ['name' => 'Semua diskusi',    'count' => 128, 'active' => true],
            ['name' => 'Infrastruktur',    'count' => 44,  'active' => false],
            ['name' => 'Transportasi',     'count' => 32,  'active' => false],
            ['name' => 'Lingkungan',       'count' => 21,  'active' => false],
            ['name' => 'Fasilitas Publik', 'count' => 19,  'active' => false],
            ['name' => 'Acara Jakarta',    'count' => 12,  'active' => false],
        ];
    }

    public static function events(): array {
        return [
            [
                'day'      => '19',
                'month'    => 'NOV',
                'badge'    => 'Konsultasi publik',
                'title'    => 'Forum Warga: Penataan Kawasan Kota Tua',
                'location' => 'Museum Sejarah Jakarta',
            ],
            [
                'day'      => '23',
                'month'    => 'NOV',
                'badge'    => 'Pengumuman',
                'title'    => 'Uji coba rekayasa lalu lintas Dukuh Atas',
                'location' => 'Sudirman - Thamrin',
            ],
            [
                'day'      => '30',
                'month'    => 'NOV',
                'badge'    => 'Kegiatan warga',
                'title'    => 'Aksi tanam 1.000 pohon bersama warga',
                'location' => 'Taman Tebet Eco Park',
            ],
        ];
    }

    public static function adminProjectsQueue(): array {
        return [
            [
                'judul'     => 'Revitalisasi Trotoar Jl. HR Rasuna Said',
                'instansi'  => 'Dinas Bina Marga DKI Jakarta',
                'progres'   => 72,
                'status'    => 'Terkonfirmasi',
                'badge'     => 'pill-green',
                'updated'   => '3 jam lalu',
            ],
            [
                'judul'     => 'Normalisasi Saluran Penghubung Sunter',
                'instansi'  => 'Dinas Sumber Daya Air DKI Jakarta',
                'progres'   => 15,
                'status'    => 'Menunggu Konfirmasi',
                'badge'     => 'pill-amber',
                'updated'   => 'Hari ini, 09:40',
            ],
            [
                'judul'     => 'Revitalisasi JPO Pasar Minggu',
                'instansi'  => 'Dinas Bina Marga DKI Jakarta',
                'progres'   => 0,
                'status'    => 'Draft Admin',
                'badge'     => 'pill-blue',
                'updated'   => 'Kemarin, 16:15',
            ],
            [
                'judul'     => 'Integrasi Halte Transjakarta Dukuh Atas',
                'instansi'  => 'Dinas Perhubungan DKI Jakarta',
                'progres'   => 31,
                'status'    => 'Terkonfirmasi',
                'badge'     => 'pill-green',
                'updated'   => '5 hari lalu',
            ],
        ];
    }

    public static function adminModerationFeed(): array {
        return [
            [
                'tag'    => 'MODERASI CHAT',
                'dot'    => 'dot-red',
                'title'  => 'Komentar mengandung tautan promosi di "Akses Stasiun Manggarai"',
                'author' => 'Oleh: Akun #WRG-8841 · 18 menit lalu',
                'status' => 'Perlu Tinjauan',
            ],
            [
                'tag'    => 'KONFIRMASI PROYEK',
                'dot'    => 'dot-blue',
                'title'  => 'Dinas SDA melengkapi RAB & tahap pengerjaan Drainase Sunter',
                'author' => 'Menunggu verifikasi final · 2 jam lalu',
                'status' => 'Siap Publikasi',
            ],
            [
                'tag'    => 'KEAMANAN & LOG',
                'dot'    => 'dot-green',
                'title'  => 'Hash chain log login & forum terverifikasi valid (1.420 entri)',
                'author' => 'Audit otomatis sistem · 5 jam lalu',
                'status' => 'Valid',
            ],
        ];
    }
}
