<?php
namespace App\Controllers;

use App\Csrf;
use App\Models\ForumModel;
use Throwable;

class ForumController {
    private ForumModel $model;

    public function __construct() {
        $this->model = new ForumModel();
    }

    public function storeForum(): void {
        if (($_SESSION['user_role'] ?? '') !== 'warga' || empty($_SESSION['logged_in'])) {
            flash('error', 'Silakan masuk menggunakan akun Warga untuk membuat topik diskusi.');
            header('Location: /login');
            exit;
        }
        if (!Csrf::verifyToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Token CSRF tidak valid.');
            header('Location: /diskusi');
            exit;
        }

        $judul     = trim((string)($_POST['judul'] ?? ''));
        $kategori  = trim((string)($_POST['kategori'] ?? 'infrastruktur'));
        $deskripsi = trim((string)($_POST['deskripsi'] ?? ''));
        $proyekId  = trim((string)($_POST['proyek_id'] ?? ''));

        if (mb_strlen($judul) < 3) {
            flash('error', 'Judul diskusi minimal 3 karakter.');
            header('Location: /diskusi');
            exit;
        }
        if (!ForumModel::isValidCategory($kategori)) {
            flash('error', 'Kategori diskusi tidak valid.');
            header('Location: /diskusi');
            exit;
        }

        try {
            $newId = $this->model->createForum(
                (string)$_SESSION['user_id'],
                $kategori,
                $judul,
                $deskripsi,
                $proyekId !== '' ? $proyekId : null,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            );
            flash('success', 'Topik diskusi berhasil dipublikasikan.');
            header('Location: /diskusi/' . $newId);
            exit;
        } catch (Throwable $e) {
            error_log('Create forum error: ' . $e->getMessage());
            flash('error', 'Terjadi kesalahan sistem saat membuat topik diskusi.');
            header('Location: /diskusi');
            exit;
        }
    }

    public function updateForum(string $id): void {
        if (($_SESSION['user_role'] ?? '') !== 'warga' || empty($_SESSION['logged_in'])) {
            flash('error', 'Akses ditolak.');
            header('Location: /diskusi/' . urlencode($id));
            exit;
        }
        if (!Csrf::verifyToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Token CSRF tidak valid.');
            header('Location: /diskusi/' . urlencode($id));
            exit;
        }

        $judul     = trim((string)($_POST['judul'] ?? ''));
        $kategori  = trim((string)($_POST['kategori'] ?? 'infrastruktur'));
        $deskripsi = trim((string)($_POST['deskripsi'] ?? ''));
        $proyekId  = trim((string)($_POST['proyek_id'] ?? ''));

        if (mb_strlen($judul) < 3 || !ForumModel::isValidCategory($kategori)) {
            flash('error', 'Judul minimal 3 karakter dan kategori wajib valid.');
            header('Location: /diskusi/' . urlencode($id));
            exit;
        }

        $updated = $this->model->updateForum(
            $id,
            (string)$_SESSION['user_id'],
            $kategori,
            $judul,
            $deskripsi,
            $proyekId !== '' ? $proyekId : null
        );

        flash($updated ? 'success' : 'error', $updated ? 'Topik diskusi diperbarui.' : 'Anda hanya dapat mengedit topik milik Anda sendiri.');
        header('Location: /diskusi/' . urlencode($id));
        exit;
    }

    public function storeChat(string $id): void {
        if (($_SESSION['user_role'] ?? '') !== 'warga' || empty($_SESSION['logged_in'])) {
            flash('error', 'Hanya Warga yang terdaftar dapat mengirim tanggapan diskusi.');
            header('Location: /diskusi/' . urlencode($id));
            exit;
        }
        if (!Csrf::verifyToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Token CSRF tidak valid.');
            header('Location: /diskusi/' . urlencode($id));
            exit;
        }

        $isi      = trim((string)($_POST['isi'] ?? ''));
        $parentId = trim((string)($_POST['balasan_ke_chat_id'] ?? ''));

        if ($isi === '' || mb_strlen($isi) > 2000) {
            flash('error', 'Isi tanggapan wajib 1 hingga 2000 karakter.');
            header('Location: /diskusi/' . urlencode($id));
            exit;
        }

        try {
            $this->model->createChat(
                $id,
                (string)$_SESSION['user_id'],
                $isi,
                $parentId !== '' ? $parentId : null,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            );
            flash('success', 'Tanggapan Anda berhasil dikirim.');
        } catch (Throwable $e) {
            error_log('Create chat error: ' . $e->getMessage());
            flash('error', 'Terjadi kesalahan sistem saat mengirim tanggapan.');
        }

        header('Location: /diskusi/' . urlencode($id));
        exit;
    }

    public function updateChat(string $forumId, string $chatId): void {
        if (($_SESSION['user_role'] ?? '') !== 'warga' || empty($_SESSION['logged_in'])) {
            flash('error', 'Akses ditolak.');
            header('Location: /diskusi/' . urlencode($forumId));
            exit;
        }
        if (!Csrf::verifyToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Token CSRF tidak valid.');
            header('Location: /diskusi/' . urlencode($forumId));
            exit;
        }

        $isi = trim((string)($_POST['isi'] ?? ''));
        if ($isi === '' || mb_strlen($isi) > 2000) {
            flash('error', 'Isi komentar wajib 1–2000 karakter.');
            header('Location: /diskusi/' . urlencode($forumId));
            exit;
        }

        $ok = $this->model->updateChat($chatId, (string)$_SESSION['user_id'], $isi);
        flash($ok ? 'success' : 'error', $ok ? 'Komentar berhasil diperbarui.' : 'Gagal mengubah komentar.');
        header('Location: /diskusi/' . urlencode($forumId));
        exit;
    }

    public function moderateChat(string $forumId, string $chatId): void {
        if (($_SESSION['user_role'] ?? '') !== 'admin' || empty($_SESSION['logged_in'])) {
            flash('error', 'Hanya Admin yang dapat melakukan moderasi pesan.');
            header('Location: /diskusi/' . urlencode($forumId));
            exit;
        }
        if (!Csrf::verifyToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Token CSRF tidak valid.');
            header('Location: /diskusi/' . urlencode($forumId));
            exit;
        }

        $alasan = trim((string)($_POST['alasan_hapus'] ?? ''));
        if ($alasan === '') {
            $alasan = 'Melanggar pedoman komunitas JakartaConnect.';
        }

        try {
            $ok = $this->model->softDeleteChatByAdmin(
                $chatId,
                (string)$_SESSION['user_id'],
                $alasan,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            );
            flash($ok ? 'success' : 'error', $ok ? 'Pesan dimoderasi (soft-delete) dan tercatat di audit log.' : 'Pesan sudah dihapus sebelumnya.');
        } catch (Throwable $e) {
            error_log('Moderate chat error: ' . $e->getMessage());
            flash('error', 'Terjadi kesalahan sistem saat memoderasi pesan.');
        }

        header('Location: /diskusi/' . urlencode($forumId));
        exit;
    }
}