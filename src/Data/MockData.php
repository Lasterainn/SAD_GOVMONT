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
                'id'           => 'prj-01',
                'tone'         => 1,
                'status'       => 'ongoing',
                'location'     => 'Setiabudi, Jakarta Selatan',
                'kelurahan'    => 'Karet Kuningan',
                'wilayah'      => 'Jakarta Selatan',
                'category'     => 'Infrastruktur',
                'title'        => 'Revitalisasi Trotoar Jl. HR Rasuna Said',
                'agency'       => 'Dinas Bina Marga DKI Jakarta',
                'progress'     => 72,
                'start_date'   => '12 Maret 2025',
                'target'       => 'Target Desember 2025',
                'updated'      => 'Diperbarui 3 jam lalu',
                'budget'       => 'Rp 48,5 Miliar',
                'konfirmasi'   => 'Terkonfirmasi',
                'coords'       => '-6.2215, 106.8326',
                'map_x'        => 51,
                'map_y'        => 53,
                'description'  => 'Penataan ulang jalur pedestrian sepanjang 3,4 km di koridor Jl. HR Rasuna Said dengan lebar trotoar 4–5 meter, pemasangan ubin pemandu disabilitas (guiding block), ducting utilitas bawah tanah, bangku taman, dan lampu penerangan hemat energi.',
                'highlights'   => [
                    'Panjang koridor revitalisasi: 3,4 kilometer (sisi barat & timur)',
                    'Integrasi langsung dengan 6 titik halte Transjakarta & stasiun LRT',
                    'Penambahan 140 titik lampu pedestrian LED & 85 pohon peneduh',
                ],
                'stages'       => [
                    ['tahap' => 'Persiapan & Survei Utilitas Bawah Tanah', 'periode' => 'Mar – Apr 2025', 'status' => 'selesai', 'persen' => 100],
                    ['tahap' => 'Relokasi Kabel Udara ke Box Ducting',      'periode' => 'Mei – Jul 2025', 'status' => 'selesai', 'persen' => 100],
                    ['tahap' => 'Pemasangan Lantai Granit & Guiding Block',  'periode' => 'Agu – Nov 2025', 'status' => 'berjalan', 'persen' => 72],
                    ['tahap' => 'Penghijauan, Street Furniture & Uji Laik', 'periode' => 'Des 2025',       'status' => 'rencana', 'persen' => 10],
                ],
            ],
            [
                'id'           => 'prj-02',
                'tone'         => 2,
                'status'       => 'ongoing',
                'location'     => 'Mampang Prapatan, Jakarta Selatan',
                'kelurahan'    => 'Bangka',
                'wilayah'      => 'Jakarta Selatan',
                'category'     => 'Infrastruktur',
                'title'        => 'Peningkatan Drainase Kawasan Kemang',
                'agency'       => 'Dinas Sumber Daya Air DKI Jakarta',
                'progress'     => 48,
                'start_date'   => '04 Juni 2025',
                'target'       => 'Target Februari 2026',
                'updated'      => 'Diperbarui kemarin',
                'budget'       => 'Rp 31,2 Miliar',
                'konfirmasi'   => 'Terkonfirmasi',
                'coords'       => '-6.2607, 106.8135',
                'map_x'        => 46,
                'map_y'        => 66,
                'description'  => 'Pembangunan saluran U-ditch berkapasitas besar dan peningkatan kapasitas rumah pompa di kawasan Kemang Raya untuk mempercepat surutnya genangan air saat curah hujan ekstrem.',
                'highlights'   => [
                    'Pemasangan box culvert & U-ditch sepanjang 2,1 km menuju Kali Krukut',
                    'Peningkatan kapasitas 2 unit pompa submersibel menjadi 1.500 liter/detik',
                    'Pembangunan 32 sumur resapan vertikal di titik cekungan jalan',
                ],
                'stages'       => [
                    ['tahap' => 'Pembebasan Titik Utilitas & Rekayasa Lalin', 'periode' => 'Jun – Jul 2025', 'status' => 'selesai', 'persen' => 100],
                    ['tahap' => 'Galian & Pemasangan U-Ditch Segmen Utara',   'periode' => 'Agu – Okt 2025', 'status' => 'selesai', 'persen' => 100],
                    ['tahap' => 'Koneksi Saluran Utama ke Kali Krukut',       'periode' => 'Nov 2025 – Jan 2026', 'status' => 'berjalan', 'persen' => 45],
                    ['tahap' => 'Pengaspalan Ulang Badan Jalan & Uji Pompa',  'periode' => 'Feb 2026',       'status' => 'rencana', 'persen' => 0],
                ],
            ],
            [
                'id'           => 'prj-03',
                'tone'         => 3,
                'status'       => 'done',
                'location'     => 'Kebayoran Baru, Jakarta Selatan',
                'kelurahan'    => 'Melawai',
                'wilayah'      => 'Jakarta Selatan',
                'category'     => 'Fasilitas Publik',
                'title'        => 'Penataan Taman Literasi Blok M',
                'agency'       => 'Dinas Pertamanan dan Hutan Kota',
                'progress'     => 100,
                'start_date'   => '10 Januari 2025',
                'target'       => 'Target September 2025',
                'updated'      => 'Diperbarui 4 hari lalu',
                'budget'       => 'Rp 19,8 Miliar',
                'konfirmasi'   => 'Terkonfirmasi',
                'coords'       => '-6.2442, 106.7995',
                'map_x'        => 41,
                'map_y'        => 60,
                'description'  => 'Revitalisasi ruang terbuka hijau menjadi pusat literasi publik, amphitheater terbuka, ruang baca komunitas, serta area bermain ramah anak yang terintegrasi dengan kawasan TOD Blok M.',
                'highlights'   => [
                    'Ruang baca publik berkapasitas 120 pengunjung dengan akses Wi-Fi gratis',
                    'Amphitheater terbuka untuk diskusi komunitas & pertunjukan seni warga',
                    'Penambahan 65% tutupan kanopi hijau dan kolam retensi hujan',
                ],
                'stages'       => [
                    ['tahap' => 'Perencanaan Partisipatif Bersama Komunitas', 'periode' => 'Jan – Feb 2025', 'status' => 'selesai', 'persen' => 100],
                    ['tahap' => 'Konstruksi Paviliun Literasi & Amphitheater', 'periode' => 'Mar – Jun 2025', 'status' => 'selesai', 'persen' => 100],
                    ['tahap' => 'Penanaman Lanskap & Pencahayaan Taman',       'periode' => 'Jul – Agu 2025', 'status' => 'selesai', 'persen' => 100],
                    ['tahap' => 'Serah Terima & Pembukaan untuk Publik',       'periode' => 'Sep 2025',       'status' => 'selesai', 'persen' => 100],
                ],
            ],
            [
                'id'           => 'prj-04',
                'tone'         => 4,
                'status'       => 'ongoing',
                'location'     => 'Tanah Abang, Jakarta Pusat',
                'kelurahan'    => 'Kebon Melati',
                'wilayah'      => 'Jakarta Pusat',
                'category'     => 'Transportasi',
                'title'        => 'Integrasi Halte Transjakarta Dukuh Atas',
                'agency'       => 'Dinas Perhubungan DKI Jakarta',
                'progress'     => 31,
                'start_date'   => '15 Agustus 2025',
                'target'       => 'Target April 2026',
                'updated'      => 'Diperbarui 5 hari lalu',
                'budget'       => 'Rp 64,0 Miliar',
                'konfirmasi'   => 'Terkonfirmasi',
                'coords'       => '-6.2008, 106.8228',
                'map_x'        => 48,
                'map_y'        => 43,
                'description'  => 'Pembangunan jembatan penyeberangan terintegrasi dan perluasan peron halte Transjakarta Dukuh Atas guna menghubungkan KRL Commuter Line, MRT Jakarta, LRT Jabodebek, dan Kereta Bandara dalam satu koridor bebas hujan.',
                'highlights'   => [
                    'Skybridge tertutup sepanjang 180 meter dengan 4 unit lift prioritas',
                    'Peningkatan kapasitas tampung halte hingga 4.500 penumpang/jam',
                    'Sistem papan informasi kedatangan moda transportasi real-time',
                ],
                'stages'       => [
                    ['tahap' => 'Pengujian Tanah & Pondasi Bore Pile',       'periode' => 'Agu – Sep 2025', 'status' => 'selesai', 'persen' => 100],
                    ['tahap' => 'Pemasangan Struktur Baja Jembatan',         'periode' => 'Okt – Des 2025', 'status' => 'berjalan', 'persen' => 42],
                    ['tahap' => 'Instalasi Lift, Eskalator & Atap Kanopi',   'periode' => 'Jan – Mar 2026', 'status' => 'rencana', 'persen' => 0],
                    ['tahap' => 'Uji Operasi Terpadu Antar Moda',            'periode' => 'Apr 2026',       'status' => 'rencana', 'persen' => 0],
                ],
            ],
            [
                'id'           => 'prj-05',
                'tone'         => 2,
                'status'       => 'ongoing',
                'location'     => 'Cakung, Jakarta Timur',
                'kelurahan'    => 'Pulo Gebang',
                'wilayah'      => 'Jakarta Timur',
                'category'     => 'Lingkungan',
                'title'        => 'Pembangunan Taman & Embung Pengendali Banjir Cakung',
                'agency'       => 'Dinas Sumber Daya Air DKI Jakarta',
                'progress'     => 64,
                'start_date'   => '02 Mei 2025',
                'target'       => 'Target Januari 2026',
                'updated'      => 'Diperbarui 1 hari lalu',
                'budget'       => 'Rp 41,7 Miliar',
                'konfirmasi'   => 'Terkonfirmasi',
                'coords'       => '-6.1852, 106.9412',
                'map_x'        => 76,
                'map_y'        => 42,
                'description'  => 'Pembangunan embung retensi seluas 2,4 hektar yang dipadukan dengan lintasan lari warga, hutan kota mini, dan area edukasi ekologi untuk mereduksi debit banjir di kawasan Cakung dan Pulo Gebang.',
                'highlights'   => [
                    'Kapasitas tampung air hujan hingga 78.000 meter kubik',
                    'Jogging track sepanjang 1,2 km mengelilingi tepi embung',
                    'Penanaman 320 pohon produktif dan tanaman penyerap polutan',
                ],
                'stages'       => [
                    ['tahap' => 'Pengerukan Basin Embung & Tanggul',        'periode' => 'Mei – Jul 2025', 'status' => 'selesai', 'persen' => 100],
                    ['tahap' => 'Konstruksi Pintu Air & Saluran Inlet',      'periode' => 'Agu – Okt 2025', 'status' => 'selesai', 'persen' => 100],
                    ['tahap' => 'Pembangunan Jogging Track & Lanskap Taman', 'periode' => 'Nov – Des 2025', 'status' => 'berjalan', 'persen' => 55],
                    ['tahap' => 'Finishing & Penyerahan Pengelolaan',        'periode' => 'Jan 2026',       'status' => 'rencana', 'persen' => 0],
                ],
            ],
            [
                'id'           => 'prj-06',
                'tone'         => 1,
                'status'       => 'planned',
                'location'     => 'Grogol Petamburan, Jakarta Barat',
                'kelurahan'    => 'Tanjung Duren Utara',
                'wilayah'      => 'Jakarta Barat',
                'category'     => 'Transportasi',
                'title'        => 'Penataan Jalur Sepeda & Pedestrian Jl. Daan Mogot',
                'agency'       => 'Dinas Bina Marga DKI Jakarta',
                'progress'     => 12,
                'start_date'   => '10 November 2025',
                'target'       => 'Target Juli 2026',
                'updated'      => 'Diperbarui 6 jam lalu',
                'budget'       => 'Rp 27,4 Miliar',
                'konfirmasi'   => 'Terkonfirmasi',
                'coords'       => '-6.1664, 106.7865',
                'map_x'        => 32,
                'map_y'        => 34,
                'description'  => 'Pembuatan jalur sepeda terproteksi sepanjang 4,2 km beserta perbaikan permukaan trotoar dan lampu penerangan jalan umum di koridor Jl. Daan Mogot.',
                'highlights'   => [
                    'Pembatas fisik planter box antara jalur sepeda dan kendaraan bermotor',
                    'Perbaikan 18 titik ramp akses kursi roda di persimpangan',
                    'Pemasangan 95 titik PJU smart-dimming berbasis sensor cahaya',
                ],
                'stages'       => [
                    ['tahap' => 'Konsultasi Publik & Finalisasi Desain',     'periode' => 'Nov 2025',       'status' => 'berjalan', 'persen' => 65],
                    ['tahap' => 'Pekerjaan Perkerasan Trotoar Segmen 1',     'periode' => 'Des 2025 – Feb 2026', 'status' => 'rencana', 'persen' => 0],
                    ['tahap' => 'Pemasangan Separator Jalur Sepeda & Marka', 'periode' => 'Mar – Mei 2026', 'status' => 'rencana', 'persen' => 0],
                    ['tahap' => 'Uji Coba & Evaluasi Keselamatan',           'periode' => 'Jun – Jul 2026', 'status' => 'rencana', 'persen' => 0],
                ],
            ],
        ];
    }

    public static function findProjectById(?string $id): ?array {
        foreach (self::projects() as $project) {
            if ($project['id'] === $id) {
                return $project;
            }
        }
        return self::projects()[0] ?? null;
    }

    public static function discussions(): array {
        return [
            [
                'id'         => 'dsc-01',
                'color'      => 1,
                'author'     => 'Rani Prameswari',
                'role_badge' => 'Warga Setiabudi',
                'official'   => false,
                'time'       => '20 menit lalu',
                'title'      => 'Akses pejalan kaki di sekitar Stasiun Manggarai',
                'excerpt'    => 'Apakah ada rencana penambahan pelican crossing pada sisi timur stasiun?',
                'body'       => "Setiap pagi dan sore hari pada jam sibuk komuter, arus penyeberang jalan di sisi timur Stasiun Manggarai sangat padat dan sering berpotongan langsung dengan kendaraan yang keluar dari arah terminal.\n\nApakah Dinas Perhubungan dan Dinas Bina Marga memiliki rencana penambahan pelican crossing dilengkapi tombol penyeberang dan pita kejut (rumble strip) di titik tersebut dalam waktu dekat?",
                'comments'   => 24,
                'likes'      => 81,
                'category'   => 'Transportasi',
                'project_id' => 'prj-04',
                'replies'    => [
                    [
                        'id'       => 'rep-101',
                        'author'   => 'Dinas Perhubungan DKI Jakarta',
                        'color'    => 2,
                        'official' => true,
                        'time'     => '12 menit lalu',
                        'body'     => 'Terima kasih atas masukannya Ibu Rani. Tim Manajemen Rekayasa Lalu Lintas telah melakukan survei volume pejalan kaki di pintu timur Stasiun Manggarai pekan lalu. Pemasangan pelican crossing dan rambu zona selamat pejalan kaki dijadwalkan masuk paket pekerjaan triwulan ini.',
                        'likes'    => 42,
                        'children' => [
                            [
                                'id'       => 'rep-102',
                                'author'   => 'Rani Prameswari',
                                'color'    => 1,
                                'official' => false,
                                'time'     => '7 menit lalu',
                                'body'     => 'Terima kasih atas respons cepatnya. Semoga dilengkapi juga dengan ramp landai untuk pengguna kursi roda dan kereta dorong bayi.',
                                'likes'    => 15,
                            ],
                        ],
                    ],
                    [
                        'id'       => 'rep-103',
                        'author'   => 'Hendra Wijaya',
                        'color'    => 5,
                        'official' => false,
                        'time'     => '4 menit lalu',
                        'body'     => 'Setuju sekali, terutama saat hujan turun jarak pandang pengendara berkurang. Lampu peringatan kuning berkedip di atas zebra cross akan sangat membantu.',
                        'likes'    => 9,
                        'children' => [],
                    ],
                ],
            ],
            [
                'id'         => 'dsc-02',
                'color'      => 2,
                'author'     => 'Dinas Bina Marga',
                'role_badge' => 'Instansi Pemerintah',
                'official'   => true,
                'time'       => '2 jam lalu',
                'title'      => 'Penjelasan tahapan perbaikan Jl. Daan Mogot',
                'excerpt'    => 'Pekerjaan malam hari akan dimulai pada 18 November untuk meminimalkan gangguan lalu lintas.',
                'body'       => "Kepada seluruh warga Jakarta Barat dan pengguna koridor Jl. Daan Mogot, kami informasikan bahwa pekerjaan penataan trotoar dan jalur sepeda terproteksi akan memasuki tahap persiapan lapangan.\n\nSeluruh mobilisasi material berat dilaksanakan pada pukul 22.00 – 04.30 WIB. Selama pekerjaan berlangsung, dua lajur kendaraan tetap dibuka dan petugas lapangan disiagakan di setiap titik penyempitan.",
                'comments'   => 43,
                'likes'      => 156,
                'category'   => 'Infrastruktur',
                'project_id' => 'prj-06',
                'replies'    => [
                    [
                        'id'       => 'rep-201',
                        'author'   => 'Siska Maharani',
                        'color'    => 4,
                        'official' => false,
                        'time'     => '1 jam lalu',
                        'body'     => 'Mohon dipastikan akses keluar-masuk gang pemukiman warga di sekitar Tanjung Duren tetap diberi jembatan pelat baja sementara selama galian saluran berlangsung.',
                        'likes'    => 28,
                        'children' => [
                            [
                                'id'       => 'rep-202',
                                'author'   => 'Dinas Bina Marga',
                                'color'    => 2,
                                'official' => true,
                                'time'     => '45 menit lalu',
                                'body'     => 'Sudah menjadi standar operasional kami Ibu Siska. Setiap akses masuk rumah warga dan gang akan dipasang pelat baja anti-selip.',
                                'likes'    => 34,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id'         => 'dsc-03',
                'color'      => 3,
                'author'     => 'Bima Wardana',
                'role_badge' => 'Warga Cempaka Putih',
                'official'   => false,
                'time'       => 'Kemarin',
                'title'      => 'Usulan titik baru bank sampah di Cempaka Putih',
                'excerpt'    => 'Warga RW 04 mengusulkan lahan dekat balai warga untuk mendukung pemilahan sampah.',
                'body'       => "Pengurus karang taruna dan ibu-ibu PKK di RW 04 Cempaka Putih telah menjalankan pemilahan sampah anorganik secara mandiri selama 6 bulan terakhir dengan partisipasi lebih dari 140 kepala keluarga.\n\nKami mengusulkan pemanfaatan lahan aset kosong seluas 6x8 meter di samping balai warga sebagai titik resmi Bank Sampah Unit beserta timbangan digital dan rak pilah.",
                'comments'   => 17,
                'likes'      => 54,
                'category'   => 'Lingkungan',
                'project_id' => 'prj-05',
                'replies'    => [
                    [
                        'id'       => 'rep-301',
                        'author'   => 'Nadia Putri',
                        'color'    => 1,
                        'official' => false,
                        'time'     => '19 jam lalu',
                        'body'     => 'Keren sekali inisiatif warga RW 04! Di kelurahan sebelah juga sudah berjalan dan sangat mengurangi volume sampah yang diangkut ke TPS.',
                        'likes'    => 14,
                        'children' => [],
                    ],
                ],
            ],
            [
                'id'         => 'dsc-04',
                'color'      => 4,
                'author'     => 'Dewi Anggraeni',
                'role_badge' => 'Warga Matraman',
                'official'   => false,
                'time'       => '2 hari lalu',
                'title'      => 'Lampu penerangan di jalur pedestrian Matraman',
                'excerpt'    => 'Beberapa titik masih gelap pada malam hari. Mohon informasi jadwal pemeliharaan berikutnya.',
                'body'       => "Di sepanjang jalur pedestrian dekat perempatan Matraman arah Salemba terdapat sekitar 5 tiang lampu pedestrian yang padam sejak awal pekan.\n\nKarena banyak pekerja dan mahasiswa melintas di atas pukul 20.00 WIB, mohon bantuan Suku Dinas Bina Marga untuk melakukan pengecekan jaringan dan penggantian lampu.",
                'comments'   => 11,
                'likes'      => 38,
                'category'   => 'Fasilitas Publik',
                'project_id' => 'prj-01',
                'replies'    => [
                    [
                        'id'       => 'rep-401',
                        'author'   => 'Dinas Bina Marga',
                        'color'    => 2,
                        'official' => true,
                        'time'     => '1 hari lalu',
                        'body'     => 'Laporan telah diteruskan ke Satgas Penerangan Jalan Umum (PJU) Jakarta Timur untuk pengecekan panel dan penggantian lampu malam ini.',
                        'likes'    => 21,
                        'children' => [],
                    ],
                ],
            ],
        ];
    }

    public static function findDiscussionById(?string $id): ?array {
        foreach (self::discussions() as $discussion) {
            if ($discussion['id'] === $id) {
                return $discussion;
            }
        }
        return self::discussions()[0] ?? null;
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
                'id'        => 'prj-01',
                'judul'     => 'Revitalisasi Trotoar Jl. HR Rasuna Said',
                'instansi'  => 'Dinas Bina Marga DKI Jakarta',
                'progres'   => 72,
                'status'    => 'Terkonfirmasi',
                'badge'     => 'pill-green',
                'updated'   => '3 jam lalu',
            ],
            [
                'id'        => 'prj-02',
                'judul'     => 'Peningkatan Drainase Kawasan Kemang',
                'instansi'  => 'Dinas Sumber Daya Air DKI Jakarta',
                'progres'   => 48,
                'status'    => 'Menunggu Konfirmasi',
                'badge'     => 'pill-amber',
                'updated'   => 'Hari ini, 09:40',
            ],
            [
                'id'        => 'prj-06',
                'judul'     => 'Penataan Jalur Sepeda & Pedestrian Jl. Daan Mogot',
                'instansi'  => 'Dinas Bina Marga DKI Jakarta',
                'progres'   => 12,
                'status'    => 'Draft Admin',
                'badge'     => 'pill-blue',
                'updated'   => 'Kemarin, 16:15',
            ],
            [
                'id'        => 'prj-04',
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
                'title'  => 'Dinas SDA melengkapi RAB & tahap pengerjaan Drainase Kemang',
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
