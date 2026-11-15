<section class="page-header-band">
  <div class="container">
    <span class="pill pill-blue">Forum warga</span>
    <div class="title-row">
      <div>
        <h1>Ruang diskusi Jakarta</h1>
        <p class="muted">Tempat bertanya, berbagi gagasan, dan mendapatkan tanggapan.</p>
      </div>

      <button type="button" class="btn-solid" id="openNewDiscussionModal">
        <?= icon('plus', 17) ?> Buat Diskusi
      </button>
    </div>
  </div>
</section>

<section class="page-content-band">
  <div class="container forum-layout">
    <aside class="card category-sidebar">
      <h3>Kategori</h3>
      <ul class="category-list" id="forumCategoryList">
        <?php foreach ($categories as $cat): ?>
          <li>
            <button type="button"
                    class="cat-btn<?= $cat['active'] ? ' active' : '' ?>"
                    data-category="<?= e($cat['name'] === 'Semua diskusi' ? '' : $cat['name']) ?>">
              <span><?= e($cat['name']) ?></span>
              <span class="cat-count"><?= (int)$cat['count'] ?></span>
            </button>
          </li>
        <?php endforeach; ?>
      </ul>
    </aside>

    <div class="forum-main">
      <div class="forum-toolbar">
        <div class="search-input-wrap card">
          <?= icon('search', 17) ?>
          <input type="text" id="discussionSearch" placeholder="Cari judul diskusi, isi topik, atau penulis..." aria-label="Cari diskusi">
        </div>

        <div class="select-wrap card">
          <?= icon('trending', 15) ?>
          <select id="discussionSort" aria-label="Urutkan diskusi">
            <option value="popular">Terpopuler</option>
            <option value="latest">Terbaru</option>
            <option value="comments">Tanggapan terbanyak</option>
          </select>
        </div>
      </div>

      <div class="quick-filter-chips">
        <button type="button" class="chip-btn active" data-disc-quick="all">Semua Topik</button>
        <button type="button" class="chip-btn" data-disc-quick="official">Ada Tanggapan Resmi</button>
        <button type="button" class="chip-btn" data-disc-quick="project">Terkait Proyek</button>
        <button type="button" class="chip-btn" data-disc-quick="saved">Tersimpan</button>
        <span class="tiny muted" style="margin-left: auto;">Menampilkan <strong id="discussionCount"><?= count($discussions) ?></strong> diskusi</span>
      </div>

      <div class="discussion-stack" id="discussionFeed">
        <?php $full = true; ?>
        <?php foreach ($discussions as $idx => $d): ?>
          <?php
            $hasOfficial = !empty($d['official']);
            if (!$hasOfficial && !empty($d['replies'])) {
                foreach ($d['replies'] as $rep) {
                    if (!empty($rep['official'])) {
                        $hasOfficial = true;
                        break;
                    }
                }
            }
          ?>
          <div class="discussion-filter-item"
               data-order="<?= $idx ?>"
               data-disc-id="<?= e($d['id']) ?>"
               data-search="<?= e(mb_strtolower($d['title'] . ' ' . $d['excerpt'] . ' ' . $d['author'] . ' ' . $d['category'])) ?>"
               data-category="<?= e($d['category']) ?>"
               data-official="<?= $hasOfficial ? '1' : '0' ?>"
               data-has-project="<?= !empty($d['project_id']) ? '1' : '0' ?>"
               data-likes="<?= (int)$d['likes'] ?>"
               data-comments="<?= (int)$d['comments'] ?>">
            <?php include __DIR__ . '/partials/discussion_item.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <div id="discussionsEmptyState" class="card empty-state hidden">
        <h3>Diskusi tidak ditemukan</h3>
        <p class="muted small">Belum ada utas yang sesuai dengan filter atau kata kunci pencarian Anda.</p>
        <button type="button" class="btn-solid btn-sm" id="resetDiscussionFilters">Reset Filter Diskusi</button>
      </div>
    </div>
  </div>
</section>

<!-- Modal Flat Buat Diskusi -->
<div class="modal-backdrop hidden" id="newDiscussionModal" role="dialog" aria-modal="true" aria-labelledby="modalDiscussionTitle">
  <div class="modal-card card">
    <div class="modal-head">
      <h3 id="modalDiscussionTitle">Buat Diskusi Baru</h3>
      <button type="button" class="icon-btn" id="closeNewDiscussionModal" aria-label="Tutup">&times;</button>
    </div>
    <form id="newDiscussionForm" class="modal-form">
      <div class="form-group">
        <label for="discTitle">Judul Topik</label>
        <input type="text" id="discTitle" required placeholder="Contoh: Usulan jalur sepeda di Jl. Pemuda">
      </div>
      <div class="form-group">
        <label for="discCategory">Kategori</label>
        <select id="discCategory" class="form-select">
          <option value="Infrastruktur">Infrastruktur</option>
          <option value="Transportasi">Transportasi</option>
          <option value="Lingkungan">Lingkungan</option>
          <option value="Fasilitas Publik">Fasilitas Publik</option>
          <option value="Acara Jakarta">Acara Jakarta</option>
        </select>
      </div>
      <div class="form-group">
        <label for="discBody">Isi Pertanyaan atau Aspirasi</label>
        <textarea id="discBody" rows="4" required placeholder="Jelaskan detail lokasi, kondisi, atau usulan Anda..."></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-outline" id="cancelNewDiscussion">Batal</button>
        <button type="submit" class="btn-solid">Publikasikan Diskusi</button>
      </div>
    </form>
  </div>
</div>
