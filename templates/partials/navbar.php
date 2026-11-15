<?php
$isLoggedIn  = !empty($_SESSION['logged_in']);
$displayName = $_SESSION['user_name'] ?? 'Rani Prameswari';
$nameParts   = preg_split('/\s+/', trim($displayName)) ?: ['Warga'];
$shortName   = $nameParts[0] . (isset($nameParts[1]) && $nameParts[1] !== '' ? ' ' . mb_substr($nameParts[1], 0, 1) . '.' : '');
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
        <a href="/dashboard" class="icon-btn notif-btn" title="Notifikasi" aria-label="Notifikasi">
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
      <?php foreach (\App\Data\MockData::projects() as $gp): ?>
        <a href="/proyek/<?= e($gp['id']) ?>" class="gs-item" data-gs-text="<?= e(mb_strtolower($gp['title'] . ' ' . $gp['location'] . ' ' . $gp['agency'] . ' ' . $gp['category'])) ?>">
          <div>
            <strong><?= e($gp['title']) ?></strong>
            <span class="tiny muted"><?= e($gp['location']) ?> &middot; <?= e($gp['agency']) ?></span>
          </div>
          <span class="pill pill-blue pill-xs"><?= (int)$gp['progress'] ?>%</span>
        </a>
      <?php endforeach; ?>

      <div class="gs-section-label" style="margin-top: 12px;">Ruang Diskusi Warga</div>
      <?php foreach (\App\Data\MockData::discussions() as $gd): ?>
        <a href="/diskusi/<?= e($gd['id']) ?>" class="gs-item" data-gs-text="<?= e(mb_strtolower($gd['title'] . ' ' . $gd['excerpt'] . ' ' . $gd['author'] . ' ' . $gd['category'])) ?>">
          <div>
            <strong><?= e($gd['title']) ?></strong>
            <span class="tiny muted">Oleh <?= e($gd['author']) ?> &middot; <?= (int)$gd['comments'] ?> tanggapan</span>
          </div>
          <span class="tag"><?= e($gd['category']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
