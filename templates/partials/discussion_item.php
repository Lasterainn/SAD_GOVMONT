<?php
/** @var array $d one forum row from ForumModel  @var bool $full */
$full      = $full ?? false;
$detailUrl = '/diskusi/' . urlencode($d['id']);
$author    = $d['author_name'] ?? 'Warga';
?>
<article class="card discussion">
  <span class="avatar av-<?= (int)$d['color'] ?>"><?= e(initials($author)) ?></span>
  <div class="discussion-main">
    <div class="meta small">
      <strong><?= e($author) ?></strong>
      <span class="muted">&middot; <?= e($d['time_formatted']) ?></span>
      <?php if (!empty($d['proyek_judul'])): ?>
        <span class="pill pill-blue pill-xs">Terkait Proyek</span>
      <?php endif; ?>
    </div>
    <h3><a href="<?= e($detailUrl) ?>"><?= e($d['judul']) ?></a></h3>
    <?php if ($d['excerpt'] !== ''): ?>
      <p class="muted small"><?= e($d['excerpt']) ?></p>
    <?php endif; ?>
    <div class="actions tiny muted<?= $full ? ' actions-ruled' : '' ?>">
      <a href="<?= e($detailUrl) ?>" class="action-inline"><?= icon('chat', 16) ?> <?= (int)$d['comments_count'] ?> tanggapan</a>
      <?php if ($full): ?>
        <button type="button" class="action-inline bookmark-toggle" data-bookmark-id="<?= e($d['id']) ?>">
          <?= icon('bookmark', 16) ?> <span class="bookmark-label">Simpan</span>
        </button>
      <?php endif; ?>
    </div>
  </div>
  <span class="tag"><?= e($d['category_label']) ?></span>
  <?php if (!$full): ?><a href="<?= e($detailUrl) ?>" class="muted row-chevron" aria-label="Buka diskusi"><?= icon('chevron') ?></a><?php endif; ?>
</article>