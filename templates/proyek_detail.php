<?php
$statusKey     = $project['status_proyek'];
$pillTone      = $statusKey === 'selesai' ? 'green' : ($statusKey === 'direncanakan' ? 'amber' : 'blue');
$konfirmasiKey = $project['status_konfirmasi'] ?? 'draft_admin';
$konfTone      = $konfirmasiKey === 'terkonfirmasi' ? 'green' : ($konfirmasiKey === 'menunggu_konfirmasi' ? 'amber' : 'blue');
$role          = $_SESSION['user_role'] ?? '';
$isLoggedIn    = !empty($_SESSION['logged_in']);
$canAdminEdit  = $isLoggedIn && $role === 'admin';
$canGovEdit    = $isLoggedIn && $role === 'pemerintah';
$bgStyle       = !empty($project['gambar_url'])
    ? ' style="background-image: linear-gradient(180deg, rgba(15,23,42,.35), rgba(15,23,42,.65)), url(\'' . e($project['gambar_url']) . '\'); background-size: cover; background-position: center;"'
    : '';
?>
<section class="page-header-band">
  <div class="container">
    <nav class="breadcrumb small muted" aria-label="Breadcrumb">
      <a href="/">Beranda</a>
      <span>/</span>
      <a href="/proyek">Proyek</a>
      <span>/</span>
      <span><?= e($project['lokasi_kota'] ?? 'Jakarta') ?></span>
    </nav>

    <div class="detail-hero-card card">
      <div class="detail-hero-banner tone-<?= (int)$project['tone'] ?>"<?= $bgStyle ?>>
        <div class="detail-badges">
          <span class="pill pill-<?= $pillTone ?>"><?= e($statusLabels[$statusKey] ?? $statusKey) ?></span>
          <span class="pill pill-<?= $konfTone ?>"><?= icon('shield', 13) ?> <?= e($konfirmasiLabels[$konfirmasiKey] ?? $konfirmasiKey) ?></span>
          <?php if (!empty($project['kategori'])): ?>
            <span class="tag"><?= e($project['kategori']) ?></span>
          <?php endif; ?>
        </div>

        <div class="detail-hero-actions">
          <button type="button" class="btn-outline btn-sm bookmark-toggle" data-bookmark-id="<?= e($project['id']) ?>">
            <?= icon('bookmark', 15) ?> <span class="bookmark-label">Ikuti Proyek</span>
          </button>
          <button type="button" class="btn-outline btn-sm copy-link-btn">
            Bagikan Tautan
          </button>
        </div>
      </div>

      <div class="detail-hero-body">
        <div class="loc"><?= icon('pin', 15) ?> <?= e($project['location']) ?></div>
        <h1><?= e($project['judul']) ?></h1>
        <p class="muted"><?= e($project['agency']) ?> &middot; Diperbarui <?= e($project['updated_formatted']) ?></p>

        <div class="detail-metrics-grid">
          <div class="metric-box">
            <span class="tiny muted">Progres Fisik</span>
            <div class="metric-progress-head">
              <strong><?= (int)$project['progress'] ?>%</strong>
              <span class="tiny text-primary"><?= e($statusLabels[$statusKey] ?? '') ?></span>
            </div>
            <progress max="100" value="<?= (int)$project['progress'] ?>"><?= (int)$project['progress'] ?>%</progress>
          </div>

          <div class="metric-box">
            <span class="tiny muted">Biaya Pengadaan</span>
            <strong><?= e($project['budget_formatted']) ?></strong>
            <span class="tiny muted">APBD DKI Jakarta</span>
          </div>

          <div class="metric-box">
            <span class="tiny muted">Periode Pelaksanaan</span>
            <strong><?= e($project['target_formatted']) ?></strong>
            <span class="tiny muted">Mulai <?= e($project['start_formatted']) ?></span>
          </div>

          <div class="metric-box">
            <span class="tiny muted">Titik Koordinat</span>
            <strong><?= e($project['coords']) ?></strong>
            <a href="/proyek?view=map" class="tiny text-primary">Lihat di Peta Jakarta &rarr;</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="page-content-band">
  <div class="container detail-layout-grid">
    <div class="detail-main-col">
      <!-- PANEL CRUD PEMERINTAH: Lengkapi RAB, Tahapan, Progres & Konfirmasi -->
      <?php if ($canGovEdit): ?>
        <article class="card panel-card" style="border-color: #93c5fd;">
          <div class="panel-head">
            <div>
              <span class="pill pill-blue pill-xs">Panel Instansi Pemerintah</span>
              <h3 style="margin-top:6px;">Perbarui Detail, Progres &amp; Konfirmasi Proyek</h3>
            </div>
          </div>

          <form method="POST" action="/proyek/<?= e($project['id']) ?>/update" enctype="multipart/form-data" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
              <div class="form-group">
                <label for="govBiaya">Biaya Pengadaan (Rp) *</label>
                <input type="number" step="0.01" min="0" id="govBiaya" name="biaya_pengadaan" required
                       value="<?= e($project['biaya_pengadaan'] !== null ? (string)$project['biaya_pengadaan'] : '') ?>">
              </div>
              <div class="form-group">
                <label for="govStatus">Status Pengerjaan</label>
                <select id="govStatus" name="status_proyek" class="form-select">
                  <option value="direncanakan"<?= $statusKey === 'direncanakan' ? ' selected' : '' ?>>Direncanakan</option>
                  <option value="dalam_pengerjaan"<?= $statusKey === 'dalam_pengerjaan' ? ' selected' : '' ?>>Dalam pengerjaan</option>
                  <option value="selesai"<?= $statusKey === 'selesai' ? ' selected' : '' ?>>Selesai</option>
                </select>
              </div>
              <div class="form-group">
                <label for="govProgres">Progres Fisik (%)</label>
                <input type="number" min="0" max="100" id="govProgres" name="progres_persen" value="<?= (int)$project['progress'] ?>">
              </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
              <div class="form-group">
                <label for="govMulai">Tanggal Mulai</label>
                <input type="date" id="govMulai" name="tanggal_mulai" value="<?= e($project['tanggal_mulai'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="govSelesai">Target Selesai</label>
                <input type="date" id="govSelesai" name="tanggal_target_selesai" value="<?= e($project['tanggal_target_selesai'] ?? '') ?>">
              </div>
            </div>

            <div class="form-group">
              <label>Tahapan Pengerjaan (Minimal 1 Tahap untuk Konfirmasi)</label>
              <?php
                $existingStages = !empty($project['tahap_list'])
                    ? $project['tahap_list']
                    : [['tahap' => 'Persiapan & Survei', 'status' => 'selesai', 'persen' => 100], ['tahap' => 'Konstruksi Utama', 'status' => 'berjalan', 'persen' => 50]];
              ?>
              <?php foreach ($existingStages as $idx => $st): ?>
                <div style="display:grid; grid-template-columns:2fr 1fr 80px; gap:8px; margin-bottom:8px;">
                  <input type="text" name="tahap_nama[]" value="<?= e($st['tahap'] ?? '') ?>" placeholder="Nama tahapan">
                  <select name="tahap_status[]" class="form-select">
                    <option value="selesai"<?= ($st['status'] ?? '') === 'selesai' ? ' selected' : '' ?>>Selesai</option>
                    <option value="berjalan"<?= ($st['status'] ?? '') === 'berjalan' ? ' selected' : '' ?>>Berjalan</option>
                    <option value="rencana"<?= ($st['status'] ?? '') === 'rencana' ? ' selected' : '' ?>>Rencana</option>
                  </select>
                  <input type="number" min="0" max="100" name="tahap_persen[]" value="<?= (int)($st['persen'] ?? 0) ?>">
                </div>
              <?php endforeach; ?>
            </div>

            <div class="form-group">
              <label for="govGambar">Perbarui Foto Lapangan (Opsional, JPG/PNG/WEBP)</label>
              <input type="file" id="govGambar" name="gambar" accept="image/jpeg,image/png,image/webp">
            </div>

            <div class="form-group-checkbox" style="margin: 10px 0;">
              <label class="checkbox-label">
                <input type="checkbox" name="konfirmasi_publikasi" value="1"<?= $konfirmasiKey === 'terkonfirmasi' ? ' checked' : '' ?>>
                <span>Konfirmasi &amp; publikasikan detail resmi proyek ini ke warga</span>
              </label>
            </div>

            <button type="submit" class="btn-solid btn-sm">Simpan Pembaruan &amp; Konfirmasi</button>
          </form>
        </article>
      <?php endif; ?>

      <!-- PANEL CRUD ADMIN: Edit Kerangka Proyek & Hapus Proyek -->
      <?php if ($canAdminEdit): ?>
        <article class="card panel-card" style="border-color: #cbd5e1;">
          <div class="panel-head">
            <div>
              <span class="pill pill-amber pill-xs">Panel Administrator</span>
              <h3 style="margin-top:6px;">Kelola Data Proyek</h3>
            </div>
            <form method="POST" action="/proyek/<?= e($project['id']) ?>/delete" class="confirm-delete-form">
              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
              <button type="submit" class="btn-danger btn-sm"><?= icon('trash', 14) ?> Hapus Proyek</button>
            </form>
          </div>

          <form method="POST" action="/proyek/<?= e($project['id']) ?>/update" enctype="multipart/form-data" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <div class="form-group">
              <label for="admJudul">Judul Proyek</label>
              <input type="text" id="admJudul" name="judul" required value="<?= e($project['judul']) ?>">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
              <div class="form-group">
                <label for="admKategori">Kategori</label>
                <select id="admKategori" name="kategori" class="form-select">
                  <?php foreach (['Infrastruktur', 'Transportasi', 'Lingkungan', 'Fasilitas Publik'] as $katOpt): ?>
                    <option value="<?= e($katOpt) ?>"<?= ($project['kategori'] ?? '') === $katOpt ? ' selected' : '' ?>><?= e($katOpt) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="admKota">Wilayah Kota</label>
                <select id="admKota" name="lokasi_kota" class="form-select">
                  <?php foreach (['Jakarta Pusat', 'Jakarta Selatan', 'Jakarta Barat', 'Jakarta Timur', 'Jakarta Utara'] as $kotaOpt): ?>
                    <option value="<?= e($kotaOpt) ?>"<?= ($project['lokasi_kota'] ?? '') === $kotaOpt ? ' selected' : '' ?>><?= e($kotaOpt) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
              <div class="form-group">
                <label for="admKel">Kelurahan</label>
                <input type="text" id="admKel" name="lokasi_kelurahan" value="<?= e($project['lokasi_kelurahan'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="admInstansi">Instansi Pelaksana</label>
                <input type="text" id="admInstansi" name="instansi_pelaksana" value="<?= e($project['instansi_pelaksana'] ?? '') ?>">
              </div>
            </div>

            <div class="form-group">
              <label for="admPemilik">Akun Pemerintah Penanggung Jawab</label>
              <select id="admPemilik" name="pemilik_pemerintah_id" class="form-select">
                <option value="">-- Belum ditugaskan --</option>
                <?php foreach ($pemerintahList as $pem): ?>
                  <option value="<?= e($pem['id']) ?>"<?= ($project['pemilik_pemerintah_id'] ?? '') === $pem['id'] ? ' selected' : '' ?>>
                    <?= e($pem['instansi']) ?> (<?= e($pem['nama_lengkap']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
              <div class="form-group">
                <label for="admStatus">Status Proyek</label>
                <select id="admStatus" name="status_proyek" class="form-select">
                  <option value="direncanakan"<?= $statusKey === 'direncanakan' ? ' selected' : '' ?>>Direncanakan</option>
                  <option value="dalam_pengerjaan"<?= $statusKey === 'dalam_pengerjaan' ? ' selected' : '' ?>>Dalam pengerjaan</option>
                  <option value="selesai"<?= $statusKey === 'selesai' ? ' selected' : '' ?>>Selesai</option>
                </select>
              </div>
              <div class="form-group">
                <label for="admProgres">Progres (%)</label>
                <input type="number" min="0" max="100" id="admProgres" name="progres_persen" value="<?= (int)$project['progress'] ?>">
              </div>
            </div>

            <div class="form-group">
              <label for="admDesk">Deskripsi</label>
              <textarea id="admDesk" name="deskripsi" rows="3"><?= e($project['deskripsi'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
              <label for="admGambar">Ganti Gambar Proyek (Opsional)</label>
              <input type="file" id="admGambar" name="gambar" accept="image/jpeg,image/png,image/webp">
            </div>

            <button type="submit" class="btn-solid btn-sm">Simpan Perubahan Admin</button>
          </form>
        </article>
      <?php endif; ?>

      <article class="card panel-card">
        <h3>Deskripsi &amp; Ruang Lingkup Pekerjaan</h3>
        <p class="detail-paragraph"><?= e($project['deskripsi'] ?: 'Deskripsi lengkap proyek sedang dilengkapi oleh instansi pelaksana.') ?></p>
      </article>

      <article class="card panel-card">
        <div class="panel-head">
          <h3>Tahapan Pengerjaan</h3>
          <span class="tiny muted"><?= count($project['tahap_list']) ?> tahapan tercatat</span>
        </div>

        <?php if (empty($project['tahap_list'])): ?>
          <p class="small muted">Rincian tahapan pengerjaan belum dipublikasikan oleh instansi pemerintah.</p>
        <?php else: ?>
          <div class="timeline-list">
            <?php foreach ($project['tahap_list'] as $i => $st): ?>
              <?php
                $stState = $st['status'] ?? 'rencana';
                $stBadge = $stState === 'selesai' ? 'pill-green' : ($stState === 'berjalan' ? 'pill-blue' : 'pill-amber');
                $stLabel = $stState === 'selesai' ? 'Selesai' : ($stState === 'berjalan' ? 'Sedang Berjalan' : 'Direncanakan');
                $periode = trim(($st['mulai'] ?? '') . (!empty($st['selesai']) ? ' s.d. ' . $st['selesai'] : ''));
              ?>
              <div class="timeline-item">
                <div class="timeline-step <?= e($stState) ?>"><?= $i + 1 ?></div>
                <div class="timeline-content">
                  <div class="timeline-top">
                    <strong><?= e($st['tahap'] ?? 'Tahap ' . ($i + 1)) ?></strong>
                    <span class="pill <?= $stBadge ?> pill-xs"><?= e($stLabel) ?><?= isset($st['persen']) ? ' (' . (int)$st['persen'] . '%)' : '' ?></span>
                  </div>
                  <?php if ($periode !== ''): ?>
                    <span class="tiny muted"><?= icon('calendar', 13) ?> <?= e($periode) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </article>

      <article class="card panel-card">
        <div class="panel-head">
          <div>
            <h3>Diskusi Warga Terkait Proyek</h3>
            <p class="tiny muted">Sampaikan pertanyaan atau masukan terkait pelaksanaan proyek ini.</p>
          </div>
          <a href="/diskusi" class="btn-solid btn-sm"><?= icon('plus', 15) ?> Buat Topik</a>
        </div>

        <?php if (empty($relatedDiscussions)): ?>
          <p class="small muted">Belum ada diskusi warga yang terhubung langsung dengan proyek ini.</p>
        <?php else: ?>
          <div class="discussion-stack">
            <?php $full = false; ?>
            <?php foreach ($relatedDiscussions as $d): ?>
              <?php include __DIR__ . '/partials/discussion_item.php'; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </article>
    </div>

    <aside class="detail-side-col">
      <div class="card panel-card">
        <span class="eyebrow">INSTANSI PELAKSANA</span>
        <div class="agency-box">
          <span class="avatar av-2"><?= e(initials($project['agency'])) ?></span>
          <div>
            <strong><?= e($project['agency']) ?></strong>
            <span class="pill pill-<?= $konfTone ?> pill-xs" style="margin-top: 4px;">
              <?= icon('shield', 12) ?> <?= e($konfirmasiLabels[$konfirmasiKey] ?? $konfirmasiKey) ?>
            </span>
          </div>
        </div>
        <p class="tiny muted" style="margin-top: 12px;">
          <?php if (!empty($project['pemerintah_nama'])): ?>
            Penanggung jawab: <strong><?= e($project['pemerintah_nama']) ?></strong>.
          <?php else: ?>
            Menunggu konfirmasi detail oleh akun instansi pemerintah terkait.
          <?php endif; ?>
        </p>
      </div>

      <div class="card panel-card">
        <span class="eyebrow">LOKASI PROYEK</span>
        <div class="mini-map-box">
          <div class="mini-map-pin" style="left: <?= (int)$project['map_x'] ?>%; top: <?= (int)$project['map_y'] ?>%;">
            <span><?= icon('pin', 16) ?></span>
          </div>
        </div>
        <strong class="small" style="display:block; margin-top: 10px;"><?= e($project['location']) ?></strong>
        <span class="tiny muted">Koordinat: <?= e($project['coords']) ?></span>
        <a href="/proyek?view=map" class="btn-outline btn-block btn-sm" style="margin-top: 12px;">Buka Peta Interaktif Jakarta</a>
      </div>

      <?php if (!empty($otherProjects)): ?>
        <div class="card panel-card">
          <span class="eyebrow">PROYEK LAINNYA</span>
          <div class="side-project-list">
            <?php foreach ($otherProjects as $op): ?>
              <a href="/proyek/<?= e($op['id']) ?>" class="side-project-link">
                <strong><?= e($op['judul']) ?></strong>
                <span class="tiny muted"><?= e($op['location']) ?> &middot; <?= (int)$op['progress'] ?>%</span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </aside>
  </div>
</section>