<?php
/** @var array $d one discussion row  @var bool $full show excerpt divider + save link (forum page) */
$full = $full ?? false;
?>
<article class="card discussion">
  <span class="avatar av-<?= (int)$d['color'] ?>"><?= e(initials($d['author'])) ?></span>
  <div class="discussion-main">
    <div class="meta small">
      <strong><?= e($d['author']) ?></strong>
      <?php if ($d['official']): ?>
        <span class="pill pill-blue pill-xs"><?= icon('shield', 13) ?> <?= $full ? 'Resmi' : 'Terverifikasi' ?></span>
      <?php endif; ?>
      <span class="muted">&middot; <?= e($d['time']) ?></span>
    </div>
    <h3><a href="#"><?= e($d['title']) ?></a></h3>
    <p class="muted small"><?= e($d['excerpt']) ?></p>
    <div class="actions tiny muted<?= $full ? ' actions-ruled' : '' ?>">
      <span><?= icon('chat', 16) ?> <?= (int)$d['comments'] ?> tanggapan</span>
      <span><?= icon('heart', 16) ?> <?= (int)$d['likes'] ?></span>
      <?php if ($full): ?><span><?= icon('bookmark', 16) ?> Simpan</span><?php endif; ?>
    </div>
  </div>
  <span class="tag"><?= e($d['category']) ?></span>
  <?php if (!$full): ?><span class="muted row-chevron"><?= icon('chevron') ?></span><?php endif; ?>
</article>
