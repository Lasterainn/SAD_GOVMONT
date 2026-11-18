<?php
namespace App\Controllers;

use App\Csrf;
use App\Models\ProyekModel;
use Throwable;

class ProyekController {
    private ProyekModel $model;

    public function __construct() {
        $this->model = new ProyekModel();
    }

    private function handleImageUpload(?array $file): ?array {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Gagal mengunggah file gambar.');
        }
        if ($file['size'] <= 0 || $file['size'] > 10485760) {
            throw new \RuntimeException('Ukuran gambar maksimal 10 MB.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($allowed[$mime])) {
            throw new \RuntimeException('Format gambar wajib JPG, PNG, atau WEBP.');
        }

        $ext  = $allowed[$mime];
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );

        $uploadDir = __DIR__ . '/../../public/uploads/proyek';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $dest = $uploadDir . '/' . $uuid . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new \RuntimeException('Gagal menyimpan gambar proyek ke server.');
        }

        return [
            'uuid' => $uuid,
            'ext'  => $ext,
            'mime' => $mime,
            'size' => (int)$file['size'],
        ];
    }

    public function store(): void {
        if (($_SESSION['user_role'] ?? '') !== 'admin' || empty($_SESSION['logged_in'])) {
            flash('error', 'Hanya Admin yang berwenang menambahkan kerangka proyek.');
            header('Location: /proyek');
            exit;
        }
        if (!Csrf::verifyToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Token CSRF tidak valid.');
            header('Location: /proyek');
            exit;
        }

        $judul = trim((string)($_POST['judul'] ?? ''));
        if (mb_strlen($judul) < 4) {
            flash('error', 'Judul proyek minimal 4 karakter.');
            header('Location: /proyek');
            exit;
        }

        try {
            $img = $this->handleImageUpload($_FILES['gambar'] ?? null);
            $newId = $this->model->createByAdmin([
                'judul'                  => $judul,
                'deskripsi'              => trim((string)($_POST['deskripsi'] ?? '')),
                'kategori'               => trim((string)($_POST['kategori'] ?? 'Infrastruktur')),
                'lokasi_kelurahan'       => trim((string)($_POST['lokasi_kelurahan'] ?? '')),
                'lokasi_kota'            => trim((string)($_POST['lokasi_kota'] ?? 'Jakarta Pusat')),
                'instansi_pelaksana'     => trim((string)($_POST['instansi_pelaksana'] ?? '')),
                'pemilik_pemerintah_id'  => trim((string)($_POST['pemilik_pemerintah_id'] ?? '')),
                'tanggal_mulai'          => trim((string)($_POST['tanggal_mulai'] ?? '')),
                'tanggal_target_selesai' => trim((string)($_POST['tanggal_target_selesai'] ?? '')),
                'status_proyek'          => trim((string)($_POST['status_proyek'] ?? 'direncanakan')),
                'progres_persen'         => max(0, min(100, (int)($_POST['progres_persen'] ?? 0))),
            ], (string)$_SESSION['user_id'], $img, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

            flash('success', 'Proyek berhasil disimpan ke PostgreSQL.');
            header('Location: /proyek/' . $newId);
            exit;
        } catch (Throwable $e) {
            error_log('Create proyek error: ' . $e->getMessage());
            flash('error', 'Gagal menyimpan proyek: ' . $e->getMessage());
            header('Location: /proyek');
            exit;
        }
    }

    public function update(string $id): void {
        $role = $_SESSION['user_role'] ?? '';
        if (!in_array($role, ['admin', 'pemerintah'], true) || empty($_SESSION['logged_in'])) {
            flash('error', 'Anda tidak memiliki izin mengubah proyek.');
            header('Location: /proyek/' . urlencode($id));
            exit;
        }
        if (!Csrf::verifyToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Token CSRF tidak valid.');
            header('Location: /proyek/' . urlencode($id));
            exit;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        try {
            $img = $this->handleImageUpload($_FILES['gambar'] ?? null);

            if ($role === 'admin') {
                $this->model->updateByAdmin($id, [
                    'judul'                  => trim((string)($_POST['judul'] ?? '')),
                    'deskripsi'              => trim((string)($_POST['deskripsi'] ?? '')),
                    'kategori'               => trim((string)($_POST['kategori'] ?? 'Infrastruktur')),
                    'lokasi_kelurahan'       => trim((string)($_POST['lokasi_kelurahan'] ?? '')),
                    'lokasi_kota'            => trim((string)($_POST['lokasi_kota'] ?? 'Jakarta Pusat')),
                    'instansi_pelaksana'     => trim((string)($_POST['instansi_pelaksana'] ?? '')),
                    'pemilik_pemerintah_id'  => trim((string)($_POST['pemilik_pemerintah_id'] ?? '')),
                    'tanggal_mulai'          => trim((string)($_POST['tanggal_mulai'] ?? '')),
                    'tanggal_target_selesai' => trim((string)($_POST['tanggal_target_selesai'] ?? '')),
                    'status_proyek'          => trim((string)($_POST['status_proyek'] ?? 'direncanakan')),
                    'progres_persen'         => max(0, min(100, (int)($_POST['progres_persen'] ?? 0))),
                ], (string)$_SESSION['user_id'], $img, $ip);

                flash('success', 'Data proyek berhasil diperbarui.');
            } else {
                // Pemerintah melengkapi biaya, jadwal, progres, tahapan, dan konfirmasi publikasi
                $tahapNames  = $_POST['tahap_nama'] ?? [];
                $tahapStatus = $_POST['tahap_status'] ?? [];
                $tahapPersen = $_POST['tahap_persen'] ?? [];
                $tahapMulai  = $_POST['tahap_mulai'] ?? [];
                $tahapSelesai= $_POST['tahap_selesai'] ?? [];

                $stages = [];
                foreach ($tahapNames as $idx => $namaTahap) {
                    $namaTahap = trim((string)$namaTahap);
                    if ($namaTahap === '') continue;
                    $stages[] = [
                        'tahap'   => $namaTahap,
                        'status'  => $tahapStatus[$idx] ?? 'rencana',
                        'persen'  => max(0, min(100, (int)($tahapPersen[$idx] ?? 0))),
                        'mulai'   => $tahapMulai[$idx] ?? '',
                        'selesai' => $tahapSelesai[$idx] ?? '',
                    ];
                }

                $this->model->updateAndConfirmByPemerintah($id, [
                    'biaya_pengadaan'        => $_POST['biaya_pengadaan'] ?? 0,
                    'tanggal_mulai'          => trim((string)($_POST['tanggal_mulai'] ?? '')),
                    'tanggal_target_selesai' => trim((string)($_POST['tanggal_target_selesai'] ?? '')),
                    'status_proyek'          => trim((string)($_POST['status_proyek'] ?? 'dalam_pengerjaan')),
                    'progres_persen'         => max(0, min(100, (int)($_POST['progres_persen'] ?? 0))),
                    'tahap_pengerjaan'       => $stages,
                    'konfirmasi'             => !empty($_POST['konfirmasi_publikasi']),
                ], (string)$_SESSION['user_id'], $img, $ip);

                flash('success', 'Detail proyek & progres berhasil diperbarui dan dikonfirmasi.');
            }
        } catch (Throwable $e) {
            error_log('Update proyek error: ' . $e->getMessage());
            flash('error', 'Gagal memperbarui proyek: ' . $e->getMessage());
        }

        header('Location: /proyek/' . urlencode($id));
        exit;
    }

    public function destroy(string $id): void {
        if (($_SESSION['user_role'] ?? '') !== 'admin' || empty($_SESSION['logged_in'])) {
            flash('error', 'Hanya Admin yang dapat menghapus proyek.');
            header('Location: /proyek');
            exit;
        }
        if (!Csrf::verifyToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Token CSRF tidak valid.');
            header('Location: /proyek/' . urlencode($id));
            exit;
        }

        try {
            $this->model->deleteByAdmin($id, (string)$_SESSION['user_id'], $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
            flash('success', 'Proyek berhasil dihapus dari database.');
        } catch (Throwable $e) {
            error_log('Delete proyek error: ' . $e->getMessage());
            flash('error', 'Gagal menghapus proyek: ' . $e->getMessage());
        }

        header('Location: /proyek');
        exit;
    }
}