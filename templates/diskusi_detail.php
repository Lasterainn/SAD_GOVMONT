<?php
$isLoggedIn   = !empty($_SESSION['logged_in']);
$currentRole  = $_SESSION['user_role'] ?? '';
$currentUid   = (string)($_SESSION['user_id'] ?? '');
$isWarga      = $isLoggedIn && $currentRole === 'warga';
$isAdmin      = $isLoggedIn && $currentRole === 'admin';
$isTopicOwner = $isWarga && $currentUid === (string)$discussion['warga_id'];
$totalReplies = 0;
foreach ($chats as $cItem) {
    $totalReplies += 1 + count($cItem['children'] ?? []);
}
?>
<section class="page-header-band">
  <div class="container">
    <nav class="breadcrumb small muted" aria-label="Breadcrumb">
      <a href="/">Beranda</a>
      <span>/</span>
      <a href="/diskusi">Diskusi</a>
      <span>/</span>
      <span><?= e($discussion['category_label']) ?></span>
    </nav>
  </div>
</section>

<section class="page-content-band">
  <div class="container detail-layout-grid">
    <div class="detail-main-col">
      <article class="card thread-post-card">
        <div class="thread-post-head">
          <div class="thread-author-row">
            <span class="avatar av-<?= (int)$discussion['color'] ?>"><?= e(initials($discussion['author_name'])) ?></span>
            <div>
              <div class="meta small">
                <strong><?= e($discussion['author_name']) ?></strong>
                <span class="tiny muted">&middot; Warga Jakarta</span>
              </div>
              <span class="tiny muted">Dipublikasikan <?= e($discussion['time_formatted']) ?></span>
            </div>
          </div>
          <span class="tag"><?= e($discussion['category_label']) ?></span>
        </div>

        <h1 class="thread-title"><?= e($discussion['judul']) ?></h1>

        <div class="thread-body">
          <?php if (!empty($discussion['deskripsi'])): ?>
            <?php foreach (preg_split("/\r?\n\r?\n/", (string)$discussion['deskripsi']) as $para): ?>
              <p><?= e($para) ?></p>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="thread-action-bar">
          <button type="button" class="btn-outline btn-sm bookmark-toggle" data-bookmark-id="<?= e($discussion['id']) ?>">
            <?= icon('bookmark', 15) ?> <span class="bookmark-label">Simpan Utas</span>
          </button>
          <button type="button" class="btn-outline btn-sm copy-link-btn">
            Bagikan
          </button>
          <?php if ($isTopicOwner): ?>
            <button type="button" class="btn-outline btn-sm" id="toggleEditTopicBtn">Edit Topik</button>
          <?php endif; ?>
          <span class="tiny muted" style="margin-left: auto;">
            <?= icon('chat', 15) ?> <strong><?= $totalReplies ?></strong> tanggapan
          </span>
        </div>

        <?php if ($isTopicOwner): ?>
          <form method="POST" action="/diskusi/<?= e($discussion['id']) ?>/update" id="editTopicForm" class="reply-composer hidden" style="margin-top: 16px;">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <h4 style="margin-bottom: 10px; font-size: 0.95rem;">Edit Topik Diskusi</h4>
            <div class="form-group">
              <label for="editDiscJudul">Judul Topik</label>
              <input type="text" id="editDiscJudul" name="judul" required minlength="3" maxlength="200" value="<?= e($discussion['judul']) ?>">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
              <div class="form-group">
                <label for="editDiscKat">Kategori</label>
                <select id="editDiscKat" name="kategori" class="form-select">
                  <?php foreach (\App\Models\ForumModel::CATEGORY_MAP as $enumKey => $enumLabel): ?>
                    <option value="<?= e($enumKey) ?>"<?= $discussion['kategori'] === $enumKey ? ' selected' : '' ?>><?= e($enumLabel) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="editDiscPrj">Proyek Terkait</label>
                <select id="editDiscPrj" name="proyek_id" class="form-select">
                  <option value="">-- Tidak ada --</option>
                  <?php foreach ($allProjects as $ap): ?>
                    <option value="<?= e($ap['id']) ?>"<?= ($discussion['proyek_id'] ?? '') === $ap['id'] ? ' selected' : '' ?>><?= e($ap['judul']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label for="editDiscDesk">Deskripsi</label>
              <textarea id="editDiscDesk" name="deskripsi" rows="3" maxlength="1000"><?= e($discussion['deskripsi'] ?? '') ?></textarea>
            </div>
            <div style="display:flex; gap:8px; justify-content:flex-end;">
              <button type="button" class="btn-outline btn-sm" id="cancelEditTopicBtn">Batal</button>
              <button type="submit" class="btn-solid btn-sm">Simpan Perubahan</button>
            </div>
          </form>
        <?php endif; ?>
      </article>

      <section class="card panel-card">
        <div class="panel-head">
          <h3>Tanggapan Warga (<?= $totalReplies ?>)</h3>
        </div>

        <?php if ($isWarga): ?>
          <form method="POST" action="/diskusi/<?= e($discussion['id']) ?>/chat" id="threadReplyForm" class="reply-composer">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="balasan_ke_chat_id" id="replyParentIdInput" value="">

            <div id="replyingToBanner" class="replying-banner hidden">
              <span>Membalas komentar <strong id="replyingToName"></strong></span>
              <button type="button" class="btn-link" id="cancelReplyTarget">Batal balas</button>
            </div>
            <div class="form-group" style="margin-bottom: 10px;">
              <textarea id="replyInputText" name="isi" rows="3" maxlength="2000" required placeholder="Tulis tanggapan, pertanyaan lanjutan, atau masukan yang konstruktif..."></textarea>
            </div>
            <div class="reply-composer-foot">
              <span class="tiny muted">Posting sebagai <strong><?= e($_SESSION['user_name']) ?></strong></span>
              <button type="submit" class="btn-solid btn-sm"><?= icon('send', 14) ?> Kirim Tanggapan</button>
            </div>
          </form>
        <?php else: ?>
          <div class="reply-composer" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
            <span class="small muted">Masuk dengan akun <strong>Warga</strong> untuk ikut menanggapi diskusi ini.</span>
            <a href="/login" class="btn-solid btn-sm">Masuk Sekarang</a>
          </div>
        <?php endif; ?>

        <div class="comment-thread-list" id="commentThreadList">
          <?php if (empty($chats)): ?>
            <p class="small muted">Belum ada tanggapan pada topik ini. Jadilah warga pertama yang berkomentar!</p>
          <?php else: ?>
            <?php foreach ($chats as $rep): ?>
              <?php
                $isDeleted   = !empty($rep['dihapus']);
                $isChatOwner = $isWarga && !$isDeleted && $currentUid === (string)$rep['warga_id'];
              ?>
              <div class="comment-card" id="chat-<?= e($rep['id']) ?>">
                <div class="comment-head">
                  <span class="avatar avatar-sm av-<?= (int)$rep['color'] ?>"><?= e(initials($rep['author_name'])) ?></span>
                  <div class="meta small">
                    <strong><?= e($rep['author_name']) ?></strong>
                    <span class="muted">&middot; <?= e($rep['time_formatted']) ?></span>
                  </div>
                </div>

                <p class="comment-text<?= $isDeleted ? ' muted' : '' ?>" style="<?= $isDeleted ? 'font-style:italic;' : '' ?>">
                  <?= e($rep['isi_tampil']) ?>
                </p>

                <div class="comment-actions tiny muted">
                  <?php if ($isWarga && !$isDeleted): ?>
                    <button type="button"
                            class="action-inline reply-to-btn"
                            data-reply-id="<?= e($rep['id']) ?>"
                            data-reply-author="<?= e($rep['author_name']) ?>">
                      <?= icon('chat', 14) ?> Balas
                    </button>
                  <?php endif; ?>

                  <?php if ($isChatOwner): ?>
                    <button type="button" class="action-inline toggle-edit-chat" data-chat-id="<?= e($rep['id']) ?>">
                      Edit
                    </button>
                  <?php endif; ?>

                  <?php if ($isAdmin && !$isDeleted): ?>
                    <button type="button" class="action-inline toggle-mod-chat" data-chat-id="<?= e($rep['id']) ?>" style="color: var(--danger);">
                      <?= icon('trash', 13) ?> Moderasi / Hapus
                    </button>
                  <?php endif; ?>
                </div>

                <?php if ($isChatOwner): ?>
                  <form method="POST" action="/diskusi/<?= e($discussion['id']) ?>/chat/<?= e($rep['id']) ?>/update"
                        id="edit-chat-form-<?= e($rep['id']) ?>" class="hidden" style="margin-top: 10px;">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <textarea name="isi" rows="2" maxlength="2000" required style="margin-bottom:6px;"><?= e($rep['isi_asli']) ?></textarea>
                    <button type="submit" class="btn-solid btn-sm">Simpan Edit</button>
                  </form>
                <?php endif; ?>

                <?php if ($isAdmin && !$isDeleted): ?>
                  <form method="POST" action="/diskusi/<?= e($discussion['id']) ?>/chat/<?= e($rep['id']) ?>/hapus"
                        id="mod-chat-form-<?= e($rep['id']) ?>" class="hidden" style="margin-top: 10px; padding: 10px; background: #fef2f2; border-radius: 8px;">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <label class="tiny" style="display:block; margin-bottom:4px; color:#991b1b;">Alasan Penghapusan (ditampilkan ke publik):</label>
                    <div style="display:flex; gap:8px;">
                      <input type="text" name="alasan_hapus" required maxlength="500" placeholder="Contoh: mengandung tautan spam / kata tidak pantas">
                      <button type="submit" class="btn-danger btn-sm">Hapus Pesan</button>
                    </div>
                  </form>
                <?php endif; ?>

                <?php if (!empty($rep['children'])): ?>
                  <div class="nested-replies">
                    <?php foreach ($rep['children'] as $child): ?>
                      <?php
                        $childDeleted = !empty($child['dihapus']);
                        $childOwner   = $isWarga && !$childDeleted && $currentUid === (string)$child['warga_id'];
                      ?>
                      <div class="nested-reply-item" id="chat-<?= e($child['id']) ?>">
                        <div class="comment-head">
                          <span class="avatar avatar-sm av-<?= (int)$child['color'] ?>"><?= e(initials($child['author_name'])) ?></span>
                          <div class="meta small">
                            <strong><?= e($child['author_name']) ?></strong>
                            <span class="muted">&middot; <?= e($child['time_formatted']) ?></span>
                          </div>
                        </div>
                        <p class="comment-text<?= $childDeleted ? ' muted' : '' ?>" style="<?= $childDeleted ? 'font-style:italic;' : '' ?>">
                          <?= e($child['isi_tampil']) ?>
                        </p>

                        <div class="comment-actions tiny muted">
                          <?php if ($childOwner): ?>
                            <button type="button" class="action-inline toggle-edit-chat" data-chat-id="<?= e($child['id']) ?>">Edit</button>
                          <?php endif; ?>
                          <?php if ($isAdmin && !$childDeleted): ?>
                            <button type="button" class="action-inline toggle-mod-chat" data-chat-id="<?= e($child['id']) ?>" style="color: var(--danger);">
                              <?= icon('trash', 13) ?> Moderasi
                            </button>
                          <?php endif; ?>
                        </div>

                        <?php if ($childOwner): ?>
                          <form method="POST" action="/diskusi/<?= e($discussion['id']) ?>/chat/<?= e($child['id']) ?>/update"
                                id="edit-chat-form-<?= e($child['id']) ?>" class="hidden" style="margin-top: 8px;">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <textarea name="isi" rows="2" maxlength="2000" required style="margin-bottom:6px;"><?= e($child['isi_asli']) ?></textarea>
                            <button type="submit" class="btn-solid btn-sm">Simpan Edit</button>
                          </form>
                        <?php endif; ?>

                        <?php if ($isAdmin && !$childDeleted): ?>
                          <form method="POST" action="/diskusi/<?= e($discussion['id']) ?>/chat/<?= e($child['id']) ?>/hapus"
                                id="mod-chat-form-<?= e($child['id']) ?>" class="hidden" style="margin-top: 8px; padding: 8px; background: #fef2f2; border-radius: 8px;">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <div style="display:flex; gap:8px;">
                              <input type="text" name="alasan_hapus" required maxlength="500" placeholder="Alasan penghapusan pesan...">
                              <button type="submit" class="btn-danger btn-sm">Hapus</button>
                            </div>
                          </form>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>
    </div>

    <aside class="detail-side-col">
      <?php if ($linkedProject): ?>
        <?php
          $pKey  = $linkedProject['status_proyek'];
          $pTone = $pKey === 'selesai' ? 'green' : ($pKey === 'direncanakan' ? 'amber' : 'blue');
        ?>
        <div class="card panel-card">
          <span class="eyebrow">PROYEK TERKAIT</span>
          <div class="inspector-banner tone-<?= (int)$linkedProject['tone'] ?>" style="margin-top: 8px;">
            <span class="pill pill-<?= $pTone ?>"><?= e($statusLabels[$pKey] ?? $pKey) ?></span>
          </div>
          <h4 style="margin-top: 10px; font-size: 0.98rem;"><?= e($linkedProject['judul']) ?></h4>
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
              <strong><?= e($rd['judul']) ?></strong>
              <span class="tiny muted"><?= e($rd['author_name']) ?> &middot; <?= (int)$rd['comments_count'] ?> tanggapan</span>
            </a>
          <?php endforeach; ?>
        </div>
        <a href="/diskusi" class="btn-outline btn-block btn-sm" style="margin-top: 14px;">&larr; Kembali ke Ruang Diskusi</a>
      </div>
    </aside>
  </div>
</section>