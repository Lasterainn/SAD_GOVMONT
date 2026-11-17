<?php
$isLoggedIn  = !empty($_SESSION['logged_in']);
$displayName = $_SESSION['user_name'] ?? 'Warga Jakarta';
$nameParts   = preg_split('/\s+/', trim($displayName)) ?: ['Warga'];
$shortName   = $nameParts[0] . (isset($nameParts[1]) && $nameParts[1] !== '' ? ' ' . mb_substr($nameParts[1], 0, 1) . '.' : '');

$gsProjects = [];
$gsForums   = [];
try {
    $gsProjects = array_slice((new \App\Models\ProyekModel())->all(), 0, 8);
    $gsForums   = array_slice((new \App\Models\ForumModel())->allForums(), 0, 8);
} catch (\Throwable $e) {
    // Tetap aman jika database belum dimigrasi
}
?>
<header class="topbar">
  <div class="container topbar-inner">
    <a href="/" class="brand-logo">
      <span class="brand-mark"><?= icon('building', 18) ?></span>
      <span class="brand-word"><strong>Jakarta</strong><span>Connect</span></span>
    </a>

    <nav class="main-nav" aria-label="Navigasi utama">
      <a href="/" class="nav-link<?= $activeNav === 'beranda' ? ' active' : '' ?>">Beranda</a>
      <a href="/proyek" class="nav-link<?= $activeNav === 'proyek' ? ' active' : '' ?>">Proyek</a>
      <a href="/diskusi" class="nav-link<?= $activeNav === 'diskusi' ? ' active' : '' ?>">Diskusi</a>
      <a href="/dashboard" class="nav-link<?= $activeNav === 'dashboard' ? ' active' : '' ?>">Dashboard</a>
    </nav>

    <div class="topbar-actions">
      <button type="button" class="global-search-trigger" id="openGlobalSearch" aria-label="Cari proyek atau diskusi">
        <?= icon('search', 16) ?>
        <span class="search-trigger-text">Cari...</span>
        <kbd class="search-kbd">Ctrl K</kbd>
      </button>

      <?php if ($isLoggedIn): ?>
        <a href="/dashboard" class="icon-btn notif-btn" title="Dashboard" aria-label="Dashboard">
          <?= icon('bell', 18) ?>
          <span class="notif-dot"></span>
        </a>
        <a href="/dashboard" class="user-chip" title="<?= e($displayName) ?>">
          <span class="avatar av-1 avatar-sm"><?= e(initials($displayName)) ?></span>
          <span class="user-chip-name"><?= e($shortName) ?></span>
        </a>
        <form method="POST" action="/logout" class="inline-form">
          <button type="submit" class="icon-btn" title="Keluar" aria-label="Keluar"><?= icon('log-out', 17) ?></button>
        </form>
      <?php else: ?>
        <a href="/login" class="btn-nav-outline">Masuk</a>
        <a href="/register" class="btn-nav-primary">Daftar</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- Global Quick Search Modal -->
<div class="modal-backdrop hidden" id="globalSearchModal" role="dialog" aria-modal="true" aria-label="Pencarian cepat JakartaConnect">
  <div class="modal-card card global-search-card">
    <div class="global-search-input-row">
      <?= icon('search', 18) ?>
      <input type="text" id="globalSearchInput" placeholder="Ketik nama proyek, lokasi, instansi, atau topik diskusi..." autocomplete="off">
      <button type="button" class="icon-btn" id="closeGlobalSearch" aria-label="Tutup">&times;</button>
    </div>
    <div class="global-search-results" id="globalSearchResults">
      <div class="gs-section-label">Proyek Pembangunan</div>
      <?php if (empty($gsProjects)): ?>
        <p class="tiny muted" style="padding: 8px 12px;">Belum ada data proyek di database.</p>
      <?php else: ?>
        <?php foreach ($gsProjects as $gp): ?>
          <a href="/proyek/<?= e($gp['id']) ?>" class="gs-item" data-gs-text="<?= e(mb_strtolower($gp['judul'] . ' ' . $gp['location'] . ' ' . $gp['agency'] . ' ' . ($gp['kategori'] ?? ''))) ?>">
            <div>
              <strong><?= e($gp['judul']) ?></strong>
              <span class="tiny muted"><?= e($gp['location']) ?> &middot; <?= e($gp['agency']) ?></span>
            </div>
            <span class="pill pill-blue pill-xs"><?= (int)$gp['progress'] ?>%</span>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>

      <div class="gs-section-label" style="margin-top: 12px;">Ruang Diskusi Warga</div>
      <?php if (empty($gsForums)): ?>
        <p class="tiny muted" style="padding: 8px 12px;">Belum ada topik diskusi di database.</p>
      <?php else: ?>
        <?php foreach ($gsForums as $gd): ?>
          <a href="/diskusi/<?= e($gd['id']) ?>" class="gs-item" data-gs-text="<?= e(mb_strtolower($gd['judul'] . ' ' . $gd['excerpt'] . ' ' . $gd['author_name'] . ' ' . $gd['category_label'])) ?>">
            <div>
              <strong><?= e($gd['judul']) ?></strong>
              <span class="tiny muted">Oleh <?= e($gd['author_name']) ?> &middot; <?= (int)$gd['comments_count'] ?> tanggapan</span>
            </div>
            <span class="tag"><?= e($gd['category_label']) ?></span>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>