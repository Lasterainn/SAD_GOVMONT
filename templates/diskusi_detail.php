<section class="page-header-band">
  <div class="container">
    <nav class="breadcrumb small muted" aria-label="Breadcrumb">
      <a href="/">Beranda</a>
      <span>/</span>
      <a href="/diskusi">Diskusi</a>
      <span>/</span>
      <span><?= e($discussion['category']) ?></span>
    </nav>
  </div>
</section>

<section class="page-content-band">
  <div class="container detail-layout-grid">
    <div class="detail-main-col">
      <article class="card thread-post-card">
        <div class="thread-post-head">
          <div class="thread-author-row">
            <span class="avatar av-<?= (int)$discussion['color'] ?>"><?= e(initials($discussion['author'])) ?></span>
            <div>
              <div class="meta small">
                <strong><?= e($discussion['author']) ?></strong>
                <?php if ($discussion['official']): ?>
                  <span class="pill pill-blue pill-xs"><?= icon('shield', 13) ?> Resmi</span>
                <?php else: ?>
                  <span class="tiny muted">&middot; <?= e($discussion['role_badge'] ?? 'Warga Jakarta') ?></span>
                <?php endif; ?>
              </div>
              <span class="tiny muted">Dipublikasikan <?= e($discussion['time']) ?></span>
            </div>
          </div>
          <span class="tag"><?= e($discussion['category']) ?></span>
        </div>

        <h1 class="thread-title"><?= e($discussion['title']) ?></h1>

        <div class="thread-body">
          <?php foreach (explode("\n\n", $discussion['body']) as $para): ?>
            <p><?= e($para) ?></p>
          <?php endforeach; ?>
        </div>

        <div class="thread-action-bar">
          <button type="button" class="btn-outline btn-sm like-toggle" data-like-count="<?= (int)$discussion['likes'] ?>">
            <?= icon('heart', 15) ?> Suka (<span class="like-num"><?= (int)$discussion['likes'] ?></span>)
          </button>
          <button type="button" class="btn-outline btn-sm bookmark-toggle" data-bookmark-id="<?= e($discussion['id']) ?>">
            <?= icon('bookmark', 15) ?> <span class="bookmark-label">Simpan Utas</span>
          </button>
          <button type="button" class="btn-outline btn-sm copy-link-btn">
            Bagikan
          </button>
          <span class="tiny muted" style="margin-left: auto;">
            <?= icon('chat', 15) ?> <strong id="replyTotalCount"><?= (int)$discussion['comments'] ?></strong> tanggapan
          </span>
        </div>
      </article>

      <section class="card panel-card">
        <div class="panel-head">
          <h3>Tanggapan Warga &amp; Instansi</h3>
          <div class="sort-inline small">
            <span class="muted">Filter:</span>
            <select id="replyFilterSelect" aria-label="Filter tanggapan">
              <option value="all">Semua Tanggapan</option>
              <option value="official">Tanggapan Resmi Saja</option>
            </select>
          </div>
        </div>

        <form id="threadReplyForm" class="reply-composer">
          <div id="replyingToBanner" class="replying-banner hidden">
            <span>Membalas komentar <strong id="replyingToName"></strong></span>
            <button type="button" class="btn-link" id="cancelReplyTarget">Batal balas</button>
          </div>
          <div class="form-group" style="margin-bottom: 10px;">
            <textarea id="replyInputText" rows="3" required placeholder="Tulis tanggapan, pertanyaan lanjutan, atau masukan yang konstruktif..."></textarea>
          </div>
          <div class="reply-composer-foot">
            <span class="tiny muted">Posting sebagai <strong><?= e($_SESSION['user_name'] ?? 'Rani Prameswari (Warga)') ?></strong></span>
            <button type="submit" class="btn-solid btn-sm"><?= icon('send', 14) ?> Kirim Tanggapan</button>
          </div>
        </form>

        <div class="comment-thread-list" id="commentThreadList">
          <?php foreach ($discussion['replies'] as $rep): ?>
            <div class="comment-card<?= !empty($rep['official']) ? ' comment-official' : '' ?>"
                 data-official="<?= !empty($rep['official']) ? '1' : '0' ?>">
              <div class="comment-head">
                <span class="avatar avatar-sm av-<?= (int)$rep['color'] ?>"><?= e(initials($rep['author'])) ?></span>
                <div class="meta small">
                  <strong><?= e($rep['author']) ?></strong>
                  <?php if (!empty($rep['official'])): ?>
                    <span class="pill pill-blue pill-xs"><?= icon('shield', 12) ?> Resmi</span>
                  <?php endif; ?>
                  <span class="muted">&middot; <?= e($rep['time']) ?></span>
                </div>
              </div>

              <p class="comment-text"><?= e($rep['body']) ?></p>

              <div class="comment-actions tiny muted">
                <button type="button" class="action-inline like-toggle" data-like-count="<?= (int)$rep['likes'] ?>">
                  <?= icon('heart', 14) ?> <span class="like-num"><?= (int)$rep['likes'] ?></span>
                </button>
                <button type="button" class="action-inline reply-to-btn" data-reply-author="<?= e($rep['author']) ?>">
                  <?= icon('chat', 14) ?> Balas
                </button>
              </div>

              <?php if (!empty($rep['children'])): ?>
                <div class="nested-replies">
                  <?php foreach ($rep['children'] as $child): ?>
                    <div class="nested-reply-item">
                      <div class="comment-head">
                        <span class="avatar avatar-sm av-<?= (int)$child['color'] ?>"><?= e(initials($child['author'])) ?></span>
                        <div class="meta small">
                          <strong><?= e($child['author']) ?></strong>
                          <?php if (!empty($child['official'])): ?>
                            <span class="pill pill-blue pill-xs"><?= icon('shield', 12) ?> Resmi</span>
                          <?php endif; ?>
                          <span class="muted">&middot; <?= e($child['time']) ?></span>
                        </div>
                      </div>
                      <p class="comment-text"><?= e($child['body']) ?></p>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </div>

    <aside class="detail-side-col">
      <?php if ($linkedProject): ?>
        <?php $pTone = $linkedProject['status'] === 'done' ? 'green' : ($linkedProject['status'] === 'planned' ? 'amber' : 'blue'); ?>
        <div class="card panel-card">
          <span class="eyebrow">PROYEK TERKAIT</span>
          <div class="inspector-banner tone-<?= (int)$linkedProject['tone'] ?>" style="margin-top: 8px;">
            <span class="pill pill-<?= $pTone ?>"><?= e($statusLabels[$linkedProject['status']] ?? '') ?></span>
          </div>
          <h4 style="margin-top: 10px; font-size: 0.98rem;"><?= e($linkedProject['title']) ?></h4>
          <p class="tiny muted" style="margin: 4px 0 12px;"><?= e($linkedProject['location']) ?></p>
          <div class="progress-row tiny">
            <span class="muted">Progres</span>
            <strong><?= (int)$linkedProject['progress'] ?>%</strong>
          </div>
          <progress max="100" value="<?= (int)$linkedProject['progress'] ?>"><?= (int)$linkedProject['progress'] ?>%</progress>
          <a href="/proyek/<?= e($linkedProject['id']) ?>" class="btn-outline btn-block btn-sm" style="margin-top: 14px;">
            Lihat Detail Proyek <?= icon('arrow-right', 14) ?>
          </a>
        </div>
      <?php endif; ?>

      <div class="card panel-card">
        <span class="eyebrow">DISKUSI TERKAIT</span>
        <div class="side-project-list">
          <?php foreach ($relatedDiscussions as $rd): ?>
            <a href="/diskusi/<?= e($rd['id']) ?>" class="side-project-link">
              <strong><?= e($rd['title']) ?></strong>
              <span class="tiny muted"><?= e($rd['author']) ?> &middot; <?= (int)$rd['comments'] ?> tanggapan</span>
            </a>
          <?php endforeach; ?>
        </div>
        <a href="/diskusi" class="btn-outline btn-block btn-sm" style="margin-top: 14px;">&larr; Kembali ke Ruang Diskusi</a>
      </div>
    </aside>
  </div>
</section>
