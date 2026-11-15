<?php
$pillTone = $project['status'] === 'done' ? 'green' : ($project['status'] === 'planned' ? 'amber' : 'blue');
?>
<section class="page-header-band">
  <div class="container">
    <nav class="breadcrumb small muted" aria-label="Breadcrumb">
      <a href="/">Beranda</a>
      <span>/</span>
      <a href="/proyek">Proyek</a>
      <span>/</span>
      <span><?= e($project['wilayah']) ?></span>
    </nav>

    <div class="detail-hero-card card">
      <div class="detail-hero-banner tone-<?= (int)$project['tone'] ?>">
        <div class="detail-badges">
          <span class="pill pill-<?= $pillTone ?>"><?= e($statusLabels[$project['status']] ?? '') ?></span>
          <span class="pill pill-blue"><?= icon('shield', 13) ?> <?= e($project['konfirmasi']) ?></span>
          <span class="tag"><?= e($project['category']) ?></span>
        </div>

        <div class="detail-hero-actions">
          <button type="button" class="btn-outline btn-sm bookmark-toggle" data-bookmark-id="<?= e($project['id']) ?>">
            <?= icon('bookmark', 15) ?> <span class="bookmark-label">Ikuti Proyek</span>
          </button>
          <button type="button" class="btn-outline btn-sm copy-link-btn">
            Bagikan Tautan
          </button>
        </div>
      </div>

      <div class="detail-hero-body">
        <div class="loc"><?= icon('pin', 15) ?> <?= e($project['location']) ?> &middot; Kelurahan <?= e($project['kelurahan']) ?></div>
        <h1><?= e($project['title']) ?></h1>
        <p class="muted"><?= e($project['agency']) ?> &middot; <?= e($project['updated']) ?></p>

        <div class="detail-metrics-grid">
          <div class="metric-box">
            <span class="tiny muted">Progres Fisik</span>
            <div class="metric-progress-head">
              <strong><?= (int)$project['progress'] ?>%</strong>
              <span class="tiny text-primary"><?= e($statusLabels[$project['status']] ?? '') ?></span>
            </div>
            <progress max="100" value="<?= (int)$project['progress'] ?>"><?= (int)$project['progress'] ?>%</progress>
          </div>

          <div class="metric-box">
            <span class="tiny muted">Biaya Pengadaan</span>
            <strong><?= e($project['budget']) ?></strong>
            <span class="tiny muted">APBD DKI Jakarta</span>
          </div>

          <div class="metric-box">
            <span class="tiny muted">Periode Pelaksanaan</span>
            <strong><?= e($project['target']) ?></strong>
            <span class="tiny muted">Mulai <?= e($project['start_date']) ?></span>
          </div>

          <div class="metric-box">
            <span class="tiny muted">Titik Koordinat</span>
            <strong><?= e($project['coords']) ?></strong>
            <a href="/proyek?view=map" class="tiny text-primary">Lihat di Peta Jakarta &rarr;</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="page-content-band">
  <div class="container detail-layout-grid">
    <div class="detail-main-col">
      <article class="card panel-card">
        <h3>Deskripsi &amp; Ruang Lingkup Pekerjaan</h3>
        <p class="detail-paragraph"><?= e($project['description']) ?></p>

        <h4 class="sub-heading">Cakupan Utama Proyek</h4>
        <ul class="highlight-list">
          <?php foreach ($project['highlights'] as $item): ?>
            <li><?= icon('check-circle', 16) ?> <span><?= e($item) ?></span></li>
          <?php endforeach; ?>
        </ul>
      </article>

      <article class="card panel-card">
        <div class="panel-head">
          <h3>Tahapan Pengerjaan</h3>
          <span class="tiny muted"><?= count($project['stages']) ?> tahapan terjadwal</span>
        </div>

        <div class="timeline-list">
          <?php foreach ($project['stages'] as $i => $st): ?>
            <?php
              $stBadge = $st['status'] === 'selesai' ? 'pill-green' : ($st['status'] === 'berjalan' ? 'pill-blue' : 'pill-amber');
              $stLabel = $st['status'] === 'selesai' ? 'Selesai' : ($st['status'] === 'berjalan' ? 'Sedang Berjalan' : 'Direncanakan');
            ?>
            <div class="timeline-item">
              <div class="timeline-step <?= e($st['status']) ?>"><?= $i + 1 ?></div>
              <div class="timeline-content">
                <div class="timeline-top">
                  <strong><?= e($st['tahap']) ?></strong>
                  <span class="pill <?= $stBadge ?> pill-xs"><?= e($stLabel) ?> (<?= (int)$st['persen'] ?>%)</span>
                </div>
                <span class="tiny muted"><?= icon('calendar', 13) ?> <?= e($st['periode']) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </article>

      <article class="card panel-card">
        <div class="panel-head">
          <div>
            <h3>Diskusi Warga Terkait Proyek</h3>
            <p class="tiny muted">Sampaikan pertanyaan atau masukan terkait pelaksanaan proyek ini.</p>
          </div>
          <a href="/diskusi" class="btn-solid btn-sm"><?= icon('plus', 15) ?> Buat Topik</a>
        </div>

        <div class="discussion-stack">
          <?php $full = false; ?>
          <?php foreach ($relatedDiscussions as $d): ?>
            <?php include __DIR__ . '/partials/discussion_item.php'; ?>
          <?php endforeach; ?>
        </div>
      </article>
    </div>

    <aside class="detail-side-col">
      <div class="card panel-card">
        <span class="eyebrow">INSTANSI PELAKSANA</span>
        <div class="agency-box">
          <span class="avatar av-2"><?= e(initials($project['agency'])) ?></span>
          <div>
            <strong><?= e($project['agency']) ?></strong>
            <span class="pill pill-blue pill-xs" style="margin-top: 4px;"><?= icon('shield', 12) ?> Akun Resmi Terverifikasi</span>
          </div>
        </div>
        <p class="tiny muted" style="margin-top: 12px;">
          Data anggaran, jadwal, dan progres proyek ini telah dikonfirmasi oleh instansi terkait melalui sistem JakartaConnect.
        </p>
      </div>

      <div class="card panel-card">
        <span class="eyebrow">LOKASI PROYEK</span>
        <div class="mini-map-box">
          <div class="mini-map-pin" style="left: <?= (int)$project['map_x'] ?>%; top: <?= (int)$project['map_y'] ?>%;">
            <span><?= icon('pin', 16) ?></span>
          </div>
        </div>
        <strong class="small" style="display:block; margin-top: 10px;"><?= e($project['location']) ?></strong>
        <span class="tiny muted">Kelurahan <?= e($project['kelurahan']) ?> &middot; <?= e($project['coords']) ?></span>
        <a href="/proyek?view=map" class="btn-outline btn-block btn-sm" style="margin-top: 12px;">Buka Peta Interaktif Jakarta</a>
      </div>

      <div class="card panel-card">
        <span class="eyebrow">PROYEK LAINNYA</span>
        <div class="side-project-list">
          <?php foreach ($otherProjects as $op): ?>
            <a href="/proyek/<?= e($op['id']) ?>" class="side-project-link">
              <strong><?= e($op['title']) ?></strong>
              <span class="tiny muted"><?= e($op['location']) ?> &middot; <?= (int)$op['progress'] ?>%</span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </aside>
  </div>
</section>
