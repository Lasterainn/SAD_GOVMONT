<?php
/** @var array $p one project row from ProyekModel  @var array $statusLabels */
$detailUrl = '/proyek/' . urlencode($p['id']);
$statusKey = $p['status_proyek'] ?? 'direncanakan';
$pillTone  = $statusKey === 'selesai' ? 'green' : ($statusKey === 'direncanakan' ? 'amber' : 'blue');
$bgStyle   = !empty($p['gambar_url'])
    ? ' style="background-image: linear-gradient(180deg, rgba(15,23,42,.2), rgba(15,23,42,.55)), url(\'' . e($p['gambar_url']) . '\'); background-size: cover; background-position: center;"'
    : '';
?>
<article class="card project-card">
  <div class="project-img tone-<?= (int)$p['tone'] ?>"<?= $bgStyle ?>>
    <span class="pill pill-<?= $pillTone ?>"><?= e($statusLabels[$statusKey] ?? $statusKey) ?></span>
    <button class="save-btn bookmark-toggle" type="button" data-bookmark-id="<?= e($p['id']) ?>" aria-label="Simpan proyek" title="Simpan proyek">
      <?= icon('bookmark') ?>
    </button>
  </div>
  <div class="project-body">
    <div class="loc"><?= icon('pin', 15) ?> <?= e($p['location']) ?></div>
    <h3><a href="<?= e($detailUrl) ?>"><?= e($p['judul']) ?></a></h3>
    <div class="muted small agency"><?= e($p['agency']) ?></div>
    <div class="progress-row small"><span class="muted">Progres pembangunan</span><strong><?= (int)$p['progress'] ?>%</strong></div>
    <progress max="100" value="<?= (int)$p['progress'] ?>"><?= (int)$p['progress'] ?>%</progress>
    <div class="project-foot tiny muted">
      <span><?= icon('calendar', 14) ?> <?= e($p['target_formatted']) ?></span>
      <a href="<?= e($detailUrl) ?>" class="link-more tiny">Detail &rarr;</a>
    </div>
  </div>
</article>