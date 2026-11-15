<?php
/** @var array $d one discussion row  @var bool $full show excerpt divider + save link (forum page) */
$full = $full ?? false;
$detailUrl = '/diskusi/' . urlencode($d['id']);
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
    <h3><a href="<?= e($detailUrl) ?>"><?= e($d['title']) ?></a></h3>
    <p class="muted small"><?= e($d['excerpt']) ?></p>
    <div class="actions tiny muted<?= $full ? ' actions-ruled' : '' ?>">
      <a href="<?= e($detailUrl) ?>" class="action-inline"><?= icon('chat', 16) ?> <?= (int)$d['comments'] ?> tanggapan</a>
      <button type="button" class="action-inline like-toggle" data-like-count="<?= (int)$d['likes'] ?>">
        <?= icon('heart', 16) ?> <span class="like-num"><?= (int)$d['likes'] ?></span>
      </button>
      <?php if ($full): ?>
        <button type="button" class="action-inline bookmark-toggle" data-bookmark-id="<?= e($d['id']) ?>">
          <?= icon('bookmark', 16) ?> <span class="bookmark-label">Simpan</span>
        </button>
      <?php endif; ?>
    </div>
  </div>
  <span class="tag"><?= e($d['category']) ?></span>
  <?php if (!$full): ?><a href="<?= e($detailUrl) ?>" class="muted row-chevron" aria-label="Buka diskusi"><?= icon('chevron') ?></a><?php endif; ?>
</article>
