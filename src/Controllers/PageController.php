<?php
namespace App\Controllers;

use App\Csrf;
use App\Models\ForumModel;
use App\Models\ProyekModel;
use App\Models\User;

class PageController {
    private ProyekModel $proyekModel;
    private ForumModel $forumModel;

    public function __construct() {
        $this->proyekModel = new ProyekModel();
        $this->forumModel  = new ForumModel();
    }

    public function beranda(): void {
        $title        = 'JakartaConnect — Ruang Kolaborasi Warga Jakarta';
        $activeNav    = 'beranda';
        $layoutMode   = 'portal';
        $allProjects  = $this->proyekModel->all();
        $projects     = array_slice($allProjects, 0, 3);
        $featured     = $projects[0] ?? null;
        $stats        = $this->proyekModel->getStats();
        $statusLabels = ProyekModel::statusLabels();
        $discussions  = array_slice($this->forumModel->allForums(), 0, 3);

        $viewFile = __DIR__ . '/../../templates/beranda.php';
        require __DIR__ . '/../../templates/layout.php';
    }

    public function proyek(): void {
        $title          = 'Jelajahi Proyek Jakarta — JakartaConnect';
        $activeNav      = 'proyek';
        $layoutMode     = 'portal';
        $projects       = $this->proyekModel->all();
        $statusLabels   = ProyekModel::statusLabels();
        $initialView    = ($_GET['view'] ?? 'grid') === 'map' ? 'map' : 'grid';
        $pemerintahList = (($_SESSION['user_role'] ?? '') === 'admin')
            ? $this->proyekModel->getPemerintahList()
            : [];
        $csrfToken      = Csrf::generateToken();

        $viewFile = __DIR__ . '/../../templates/proyek.php';
        require __DIR__ . '/../../templates/layout.php';
    }

    public function proyekDetail(?string $id = null): void {
        $project = $id ? $this->proyekModel->findById($id) : null;
        if (!$project) {
            http_response_code(404);
            echo "Proyek tidak ditemukan.";
            return;
        }

        $statusLabels       = ProyekModel::statusLabels();
        $konfirmasiLabels   = ProyekModel::konfirmasiLabels();
        $relatedDiscussions = $this->forumModel->allForums($project['id']);
        $otherProjects      = array_slice(
            array_values(array_filter($this->proyekModel->all(), fn($p) => $p['id'] !== $project['id'])),
            0,
            2
        );
        $pemerintahList = (($_SESSION['user_role'] ?? '') === 'admin')
            ? $this->proyekModel->getPemerintahList()
            : [];
        $csrfToken  = Csrf::generateToken();
        $title      = $project['judul'] . ' — Detail Proyek JakartaConnect';
        $activeNav  = 'proyek';
        $layoutMode = 'portal';

        $viewFile = __DIR__ . '/../../templates/proyek_detail.php';
        require __DIR__ . '/../../templates/layout.php';
    }

    public function diskusi(): void {
        $title       = 'Ruang Diskusi Jakarta — JakartaConnect';
        $activeNav   = 'diskusi';
        $layoutMode  = 'portal';
        $discussions = $this->forumModel->allForums();
        $categories  = $this->forumModel->categoryCounts();
        $projects    = $this->proyekModel->all();
        $csrfToken   = Csrf::generateToken();

        $viewFile = __DIR__ . '/../../templates/diskusi.php';
        require __DIR__ . '/../../templates/layout.php';
    }

    public function diskusiDetail(?string $id = null): void {
        $discussion = $id ? $this->forumModel->findForumById($id) : null;
        if (!$discussion) {
            http_response_code(404);
            echo "Topik diskusi tidak ditemukan.";
            return;
        }

        $chats         = $this->forumModel->getChatsByForumId($discussion['id']);
        $linkedProject = !empty($discussion['proyek_id'])
            ? $this->proyekModel->findById($discussion['proyek_id'])
            : null;
        $statusLabels  = ProyekModel::statusLabels();
        $allProjects   = $this->proyekModel->all();
        $relatedDiscussions = array_slice(
            array_values(array_filter($this->forumModel->allForums(), fn($d) => $d['id'] !== $discussion['id'])),
            0,
            3
        );
        $csrfToken  = Csrf::generateToken();
        $title      = $discussion['judul'] . ' — Ruang Diskusi JakartaConnect';
        $activeNav  = 'diskusi';
        $layoutMode = 'portal';

        $viewFile = __DIR__ . '/../../templates/diskusi_detail.php';
        require __DIR__ . '/../../templates/layout.php';
    }

    public function dashboard(): void {
        if (empty($_SESSION['logged_in'])) {
            header('Location: /login');
            exit;
        }

        $title        = 'Dashboard — JakartaConnect';
        $activeNav    = 'dashboard';
        $layoutMode   = 'portal';
        $userName     = $_SESSION['user_name'] ?? 'Pengguna';
        $userId       = (string)$_SESSION['user_id'];
        $activeRole   = $_SESSION['user_role'] ?? 'warga';

        $userModel    = new User();
        $projects     = $this->proyekModel->all();
        $proyekStats  = $this->proyekModel->getStats();
        $adminStats   = $activeRole === 'admin' ? $userModel->getAdminDashboardStats() : [];
        $wargaStats   = $activeRole === 'warga' ? $userModel->getWargaStats($userId) : [];
        $auditLogs    = $activeRole === 'admin' ? $this->proyekModel->recentAuditLogs(6) : [];
        $pemerintahList = $activeRole === 'admin' ? $this->proyekModel->getPemerintahList() : [];
        $csrfToken    = Csrf::generateToken();

        $viewFile = __DIR__ . '/../../templates/dashboard.php';
        require __DIR__ . '/../../templates/layout.php';
    }
}