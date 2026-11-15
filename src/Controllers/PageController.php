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
        $initialView  = ($_GET['view'] ?? 'grid') === 'map' ? 'map' : 'grid';

        $viewFile = __DIR__ . '/../../templates/proyek.php';
        require __DIR__ . '/../../templates/layout.php';
    }

    public function proyekDetail(?string $id = null): void {
        $projectId    = $id ?: ($_GET['id'] ?? 'prj-01');
        $project      = MockData::findProjectById($projectId);
        $statusLabels = MockData::statusLabels();

        $relatedDiscussions = array_values(array_filter(
            MockData::discussions(),
            fn($d) => ($d['project_id'] ?? '') === $project['id']
        ));
        if (empty($relatedDiscussions)) {
            $relatedDiscussions = array_slice(MockData::discussions(), 0, 2);
        }

        $otherProjects = array_values(array_filter(
            MockData::projects(),
            fn($p) => $p['id'] !== $project['id']
        ));
        $otherProjects = array_slice($otherProjects, 0, 2);

        $title      = $project['title'] . ' — Detail Proyek JakartaConnect';
        $activeNav  = 'proyek';
        $layoutMode = 'portal';

        $viewFile = __DIR__ . '/../../templates/proyek_detail.php';
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

    public function diskusiDetail(?string $id = null): void {
        $discussionId  = $id ?: ($_GET['id'] ?? 'dsc-01');
        $discussion    = MockData::findDiscussionById($discussionId);
        $linkedProject = !empty($discussion['project_id'])
            ? MockData::findProjectById($discussion['project_id'])
            : null;
        $statusLabels  = MockData::statusLabels();

        $relatedDiscussions = array_values(array_filter(
            MockData::discussions(),
            fn($d) => $d['id'] !== $discussion['id']
        ));
        $relatedDiscussions = array_slice($relatedDiscussions, 0, 3);

        $title      = $discussion['title'] . ' — Ruang Diskusi JakartaConnect';
        $activeNav  = 'diskusi';
        $layoutMode = 'portal';

        $viewFile = __DIR__ . '/../../templates/diskusi_detail.php';
        require __DIR__ . '/../../templates/layout.php';
    }

    public function dashboard(): void {
        $title        = 'Dashboard — JakartaConnect';
        $activeNav    = 'dashboard';
        $layoutMode   = 'portal';

        $userName      = $_SESSION['user_name'] ?? 'Rani Prameswari';
        $sessionRole   = $_SESSION['user_role'] ?? null;
        $requestedRole = $_GET['role'] ?? null;

        $allowedRoles = ['masyarakat', 'pemerintah', 'admin'];
        if (in_array($requestedRole, $allowedRoles, true)) {
            $activeRole = $requestedRole;
        } elseif ($sessionRole === 'admin') {
            $activeRole = 'admin';
        } elseif ($sessionRole === 'pemerintah') {
            $activeRole = 'pemerintah';
        } else {
            $activeRole = 'masyarakat';
        }

        $followedProjects = array_slice(MockData::projects(), 0, 3);
        $adminProjects    = MockData::adminProjectsQueue();
        $adminModeration  = MockData::adminModerationFeed();

        $viewFile = __DIR__ . '/../../templates/dashboard.php';
        require __DIR__ . '/../../templates/layout.php';
    }
}
