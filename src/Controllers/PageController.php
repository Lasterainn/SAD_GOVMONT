<?php
namespace App\Controllers;

use App\Data\MockData;

class PageController {
    public function beranda(): void {
        $title        = 'JakartaConnect — Ruang Kolaborasi Warga Jakarta';
        $activeNav    = 'beranda';
        $layoutMode   = 'portal';
        $projects     = array_slice(MockData::projects(), 0, 3);
        $statusLabels = MockData::statusLabels();
        $discussions  = array_slice(MockData::discussions(), 0, 3);
        $events       = MockData::events();

        $viewFile = __DIR__ . '/../../templates/beranda.php';
        require __DIR__ . '/../../templates/layout.php';
    }

    public function proyek(): void {
        $title        = 'Jelajahi Proyek Jakarta — JakartaConnect';
        $activeNav    = 'proyek';
        $layoutMode   = 'portal';
        $projects     = MockData::projects();
        $statusLabels = MockData::statusLabels();

        $viewFile = __DIR__ . '/../../templates/proyek.php';
        require __DIR__ . '/../../templates/layout.php';
    }

    public function diskusi(): void {
        $title       = 'Ruang Diskusi Jakarta — JakartaConnect';
        $activeNav   = 'diskusi';
        $layoutMode  = 'portal';
        $discussions = MockData::discussions();
        $categories  = MockData::forumCategories();

        $viewFile = __DIR__ . '/../../templates/diskusi.php';
        require __DIR__ . '/../../templates/layout.php';
    }

    public function dashboard(): void {
        $title        = 'Dashboard — JakartaConnect';
        $activeNav    = 'dashboard';
        $layoutMode   = 'portal';

        // Gunakan data session bila sudah login, atau data flat demo bila belum login
        $userName     = $_SESSION['user_name'] ?? 'Rani Prameswari';
        $sessionRole  = $_SESSION['user_role'] ?? null;
        $requestedRole = $_GET['role'] ?? null;

        $allowedRoles = ['masyarakat', 'pemerintah', 'admin'];
        if (in_array($requestedRole, $allowedRoles, true)) {
            $activeRole = $requestedRole;
        } elseif ($sessionRole === 'admin') {
            $activeRole = 'admin';
        } elseif ($sessionRole === 'pemerintah') {
            $activeRole = 'pemerintah';
        } else {
            // Default tampilan sesuai Gambar 3 (Rani Prameswari) dengan switcher ke Admin & Pemerintah
            $activeRole = 'masyarakat';
        }

        $followedProjects = array_slice(MockData::projects(), 0, 3);
        $adminProjects    = MockData::adminProjectsQueue();
        $adminModeration  = MockData::adminModerationFeed();

        $viewFile = __DIR__ . '/../../templates/dashboard.php';
        require __DIR__ . '/../../templates/layout.php';
    }
}
