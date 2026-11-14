<?php
/** @var array $p one project row  @var array $statusLabels */
?>
<article class="card project-card">
  <div class="project-img tone-<?= (int)$p['tone'] ?>"><?php /* swap for <img src="/images/..."> when you have photos */ ?>
    <span class="pill pill-<?= $p['status'] === 'done' ? 'green' : 'blue' ?>"><?= e($statusLabels[$p['status']] ?? '') ?></span>
    <button class="save-btn" type="button" aria-label="Simpan proyek"><?= icon('bookmark') ?></button>
  </div>
  <div class="project-body">
    <div class="loc"><?= icon('pin', 15) ?> <?= e($p['location']) ?></div>
    <h3><a href="#"><?= e($p['title']) ?></a></h3>
    <div class="muted small agency"><?= e($p['agency']) ?></div>
    <div class="progress-row small"><span class="muted">Progres pembangunan</span><strong><?= (int)$p['progress'] ?>%</strong></div>
    <progress max="100" value="<?= (int)$p['progress'] ?>"><?= (int)$p['progress'] ?>%</progress>
    <div class="project-foot tiny muted">
      <span><?= icon('calendar', 14) ?> <?= e($p['target']) ?></span>
      <span><?= e($p['updated']) ?></span>
    </div>
  </div>
</article>
