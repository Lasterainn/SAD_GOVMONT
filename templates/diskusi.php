<?php
$isWarga = !empty($_SESSION['logged_in']) && ($_SESSION['user_role'] ?? '') === 'warga';
?>
<section class="page-header-band">
  <div class="container">
    <span class="pill pill-blue">Forum warga</span>
    <div class="title-row">
      <div>
        <h1>Ruang diskusi Jakarta</h1>
        <p class="muted">Tempat bertanya, berbagi gagasan, dan mendapatkan tanggapan.</p>
      </div>

      <?php if ($isWarga): ?>
        <button type="button" class="btn-solid" id="openNewDiscussionModal">
          <?= icon('plus', 17) ?> Buat Diskusi
        </button>
      <?php else: ?>
        <a href="/login" class="btn-solid">
          <?= icon('plus', 17) ?> Masuk sebagai Warga untuk Berdiskusi
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="page-content-band">
  <div class="container forum-layout">
    <aside class="card category-sidebar">
      <h3>Kategori</h3>
      <ul class="category-list" id="forumCategoryList">
        <?php foreach ($categories as $idx => $cat): ?>
          <li>
            <button type="button"
                    class="cat-btn<?= $idx === 0 ? ' active' : '' ?>"
                    data-category="<?= e($cat['enum']) ?>">
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
            <option value="latest">Terbaru</option>
            <option value="comments">Tanggapan terbanyak</option>
          </select>
        </div>
      </div>

      <div class="quick-filter-chips">
        <button type="button" class="chip-btn active" data-disc-quick="all">Semua Topik</button>
        <button type="button" class="chip-btn" data-disc-quick="project">Terkait Proyek</button>
        <button type="button" class="chip-btn" data-disc-quick="saved">Tersimpan</button>
        <span class="tiny muted" style="margin-left: auto;">Menampilkan <strong id="discussionCount"><?= count($discussions) ?></strong> diskusi</span>
      </div>

      <div class="discussion-stack" id="discussionFeed">
        <?php $full = true; ?>
        <?php foreach ($discussions as $idx => $d): ?>
          <div class="discussion-filter-item"
               data-order="<?= $idx ?>"
               data-disc-id="<?= e($d['id']) ?>"
               data-search="<?= e(mb_strtolower($d['judul'] . ' ' . $d['excerpt'] . ' ' . $d['author_name'] . ' ' . $d['category_label'])) ?>"
               data-category="<?= e($d['kategori']) ?>"
               data-has-project="<?= !empty($d['proyek_id']) ? '1' : '0' ?>"
               data-comments="<?= (int)$d['comments_count'] ?>">
            <?php include __DIR__ . '/partials/discussion_item.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <div id="discussionsEmptyState" class="card empty-state<?= empty($discussions) ? '' : ' hidden' ?>">
        <h3>Diskusi tidak ditemukan</h3>
        <p class="muted small">Belum ada utas yang sesuai dengan filter atau kata kunci pencarian Anda.</p>
        <button type="button" class="btn-solid btn-sm" id="resetDiscussionFilters">Reset Filter Diskusi</button>
      </div>
    </div>
  </div>
</section>

<?php if ($isWarga): ?>
<!-- Modal Buat Diskusi Baru (Warga -> PostgreSQL) -->
<div class="modal-backdrop hidden" id="newDiscussionModal" role="dialog" aria-modal="true" aria-labelledby="modalDiscussionTitle">
  <div class="modal-card card">
    <div class="modal-head">
      <h3 id="modalDiscussionTitle">Buat Diskusi Baru</h3>
      <button type="button" class="icon-btn" id="closeNewDiscussionModal" aria-label="Tutup">&times;</button>
    </div>
    <form method="POST" action="/diskusi/create" class="modal-form">
      <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

      <div class="form-group">
        <label for="discTitle">Judul Topik *</label>
        <input type="text" id="discTitle" name="judul" required minlength="3" maxlength="200" placeholder="Contoh: Usulan jalur sepeda di Jl. Pemuda">
      </div>

      <div class="form-group">
        <label for="discCategory">Kategori *</label>
        <select id="discCategory" name="kategori" class="form-select">
          <option value="infrastruktur">Infrastruktur</option>
          <option value="transportasi">Transportasi</option>
          <option value="lingkungan">Lingkungan</option>
          <option value="fasilitas_publik">Fasilitas Publik</option>
          <option value="acara_jakarta">Acara Jakarta</option>
        </select>
      </div>

      <div class="form-group">
        <label for="discProject">Terkait Proyek Pembangunan (Opsional)</label>
        <select id="discProject" name="proyek_id" class="form-select">
          <option value="">-- Tidak terikat proyek tertentu --</option>
          <?php foreach ($projects as $prj): ?>
            <option value="<?= e($prj['id']) ?>"><?= e($prj['judul']) ?> (<?= e($prj['lokasi_kota'] ?? '') ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="discBody">Isi Pertanyaan atau Aspirasi</label>
        <textarea id="discBody" name="deskripsi" rows="4" maxlength="1000" required placeholder="Jelaskan detail lokasi, kondisi, atau usulan Anda..."></textarea>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-outline" id="cancelNewDiscussion">Batal</button>
        <button type="submit" class="btn-solid">Publikasikan Diskusi</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>