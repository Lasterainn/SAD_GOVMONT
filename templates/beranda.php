<section class="hero-section">
  <div class="container hero-grid">
    <div class="hero-copy">
      <span class="pill pill-blue"><?= icon('pin', 14) ?> Ruang kolaborasi warga Jakarta</span>
      <h1>Jakarta maju,<br><span class="text-primary">warga tahu.</span></h1>
      <p class="hero-subtitle">Pantau pembangunan, ikuti perkembangan proyek, dan sampaikan aspirasi untuk Jakarta yang lebih baik.</p>

      <div class="hero-cta-group">
        <a href="/proyek" class="btn-solid">Jelajahi Proyek <?= icon('arrow-right', 16) ?></a>
        <a href="/diskusi" class="btn-outline"><?= icon('chat', 16) ?> Gabung Diskusi</a>
      </div>

      <div class="hero-social-proof">
        <div class="avatar-stack">
          <span class="avatar av-1">RP</span>
          <span class="avatar av-2">BW</span>
          <span class="avatar av-3">DA</span>
        </div>
        <div class="social-proof-text">
          <strong>12.400+ warga</strong>
          <span>sudah bergabung</span>
        </div>
      </div>
    </div>

    <div class="hero-visual">
      <div class="hero-photo-card">
        <span class="hero-live-badge"><span class="live-dot"></span> 4 proyek aktif</span>
        <div class="hero-skyline-art" aria-hidden="true"></div>
        <a href="/proyek/prj-01" class="hero-featured-overlay">
          <div>
            <span class="tiny muted"><?= icon('pin', 13) ?> Proyek unggulan</span>
            <strong>Revitalisasi Trotoar Rasuna Said</strong>
          </div>
          <span class="featured-arrow"><?= icon('chevron', 18) ?></span>
        </a>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="stats-strip card">
      <div class="stat-item">
        <span class="stat-icon stat-blue"><?= icon('building', 20) ?></span>
        <div>
          <strong>248</strong>
          <span>Total proyek</span>
        </div>
      </div>
      <div class="stat-item">
        <span class="stat-icon stat-amber"><?= icon('clock', 20) ?></span>
        <div>
          <strong>86</strong>
          <span>Dalam pengerjaan</span>
        </div>
      </div>
      <div class="stat-item">
        <span class="stat-icon stat-green"><?= icon('check-circle', 20) ?></span>
        <div>
          <strong>142</strong>
          <span>Proyek selesai</span>
        </div>
      </div>
      <div class="stat-item">
        <span class="stat-icon stat-indigo"><?= icon('chat', 20) ?></span>
        <div>
          <strong>3.842</strong>
          <span>Diskusi warga</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="page-section">
  <div class="container">
    <div class="section-head">
      <div>
        <span class="eyebrow">PROYEK PILIHAN</span>
        <h2>Pembangunan yang sedang bergerak</h2>
        <p class="muted">Ikuti perkembangan proyek prioritas di berbagai sudut Jakarta.</p>
      </div>
      <a href="/proyek" class="link-more">Lihat semua proyek <?= icon('arrow-right', 15) ?></a>
    </div>

    <div class="grid-3">
      <?php foreach ($projects as $p): ?>
        <?php include __DIR__ . '/partials/project_card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="page-section section-tinted">
  <div class="container">
    <div class="section-head">
      <div>
        <span class="eyebrow">SUARA WARGA</span>
        <h2>Percakapan yang sedang ramai</h2>
        <p class="muted">Bertanya, berbagi informasi, dan berdiskusi bersama warga.</p>
      </div>
      <a href="/diskusi" class="link-more">Masuk ke forum <?= icon('arrow-right', 15) ?></a>
    </div>

    <div class="voice-grid">
      <div class="discussion-stack">
        <?php $full = false; ?>
        <?php foreach ($discussions as $d): ?>
          <?php include __DIR__ . '/partials/discussion_item.php'; ?>
        <?php endforeach; ?>
      </div>

      <aside class="cta-dark-card">
        <div class="cta-icon-ring"><?= icon('users', 26) ?></div>
        <span class="cta-pill">PARTISIPASI PUBLIK</span>
        <h3>Aspirasi Anda berarti untuk Jakarta.</h3>
        <p>Sampaikan pertanyaan, usulan, atau masukan langsung ke sesama warga dan instansi pemerintah.</p>
        <a href="/diskusi" class="btn-solid btn-block">Mulai berdiskusi <?= icon('arrow-right', 16) ?></a>
      </aside>
    </div>
  </div>
</section>

<section class="page-section" id="agenda">
  <div class="container">
    <div class="section-head">
      <div>
        <span class="eyebrow">AGENDA KOTA</span>
        <h2>Yang akan datang di Jakarta</h2>
      </div>
      <a href="/diskusi" class="link-more">Lihat semua agenda <?= icon('arrow-right', 15) ?></a>
    </div>

    <div class="grid-3">
      <?php foreach ($events as $ev): ?>
        <article class="card event-card">
          <div class="event-date">
            <strong><?= e($ev['day']) ?></strong>
            <span><?= e($ev['month']) ?></span>
          </div>
          <div class="event-info">
            <span class="pill pill-blue pill-xs"><?= e($ev['badge']) ?></span>
            <h3><?= e($ev['title']) ?></h3>
            <span class="tiny muted"><?= icon('pin', 13) ?> <?= e($ev['location']) ?></span>
          </div>
          <span class="muted row-chevron"><?= icon('chevron', 16) ?></span>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
