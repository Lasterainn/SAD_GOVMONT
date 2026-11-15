<?php
/** @var array $p one project row  @var array $statusLabels */
$detailUrl = '/proyek/' . urlencode($p['id']);
$pillTone  = $p['status'] === 'done' ? 'green' : ($p['status'] === 'planned' ? 'amber' : 'blue');
?>
<article class="card project-card">
  <div class="project-img tone-<?= (int)$p['tone'] ?>">
    <span class="pill pill-<?= $pillTone ?>"><?= e($statusLabels[$p['status']] ?? '') ?></span>
    <button class="save-btn bookmark-toggle" type="button" data-bookmark-id="<?= e($p['id']) ?>" aria-label="Simpan proyek" title="Simpan proyek">
      <?= icon('bookmark') ?>
    </button>
  </div>
  <div class="project-body">
    <div class="loc"><?= icon('pin', 15) ?> <?= e($p['location']) ?></div>
    <h3><a href="<?= e($detailUrl) ?>"><?= e($p['title']) ?></a></h3>
    <div class="muted small agency"><?= e($p['agency']) ?></div>
    <div class="progress-row small"><span class="muted">Progres pembangunan</span><strong><?= (int)$p['progress'] ?>%</strong></div>
    <progress max="100" value="<?= (int)$p['progress'] ?>"><?= (int)$p['progress'] ?>%</progress>
    <div class="project-foot tiny muted">
      <span><?= icon('calendar', 14) ?> <?= e($p['target']) ?></span>
      <a href="<?= e($detailUrl) ?>" class="link-more tiny">Detail &rarr;</a>
    </div>
  </div>
</article>
