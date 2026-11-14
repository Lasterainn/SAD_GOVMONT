<?php
$isLoggedIn = !empty($_SESSION['logged_in']);
$displayName = $_SESSION['user_name'] ?? 'Rani Prameswari';
$shortName = explode(' ', trim($displayName))[0] . (isset(explode(' ', trim($displayName))[1]) ? ' ' . mb_substr(explode(' ', trim($displayName))[1], 0, 1) . '.' : '');
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
      <a href="/proyek" class="icon-btn" title="Cari proyek atau diskusi" aria-label="Cari"><?= icon('search', 18) ?></a>

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
