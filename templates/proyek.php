<section class="page-header-band">
  <div class="container">
    <span class="pill pill-blue">Direktori pembangunan</span>
    <div class="title-row">
      <div>
        <h1>Jelajahi proyek Jakarta</h1>
        <p class="muted">Temukan dan pantau pembangunan di lingkungan Anda.</p>
      </div>

      <div class="segmented-control" role="tablist" aria-label="Mode tampilan proyek">
        <button type="button" class="seg-btn active" data-view-target="grid"><?= icon('grid', 15) ?> Grid</button>
        <button type="button" class="seg-btn" data-view-target="map"><?= icon('map', 15) ?> Peta</button>
      </div>
    </div>

    <div class="filter-toolbar card">
      <div class="search-input-wrap">
        <?= icon('search', 17) ?>
        <input type="text" id="projectSearch" placeholder="Cari proyek, lokasi, atau instansi..." aria-label="Cari proyek">
      </div>

      <div class="filter-selects">
        <div class="select-wrap">
          <?= icon('pin', 15) ?>
          <select id="projectWilayah" aria-label="Filter wilayah">
            <option value="">Seluruh Jakarta</option>
            <option value="Jakarta Pusat">Jakarta Pusat</option>
            <option value="Jakarta Selatan">Jakarta Selatan</option>
            <option value="Jakarta Barat">Jakarta Barat</option>
            <option value="Jakarta Timur">Jakarta Timur</option>
            <option value="Jakarta Utara">Jakarta Utara</option>
          </select>
        </div>

        <div class="select-wrap">
          <?= icon('filter', 15) ?>
          <select id="projectCategory" aria-label="Filter kategori">
            <option value="">Kategori</option>
            <option value="Infrastruktur">Infrastruktur</option>
            <option value="Transportasi">Transportasi</option>
            <option value="Lingkungan">Lingkungan</option>
            <option value="Fasilitas Publik">Fasilitas Publik</option>
          </select>
        </div>

        <div class="select-wrap">
          <select id="projectStatus" aria-label="Filter status">
            <option value="">Semua</option>
            <option value="ongoing">Dalam pengerjaan</option>
            <option value="done">Selesai</option>
            <option value="planned">Direncanakan</option>
          </select>
        </div>
      </div>
    </div>

    <div class="results-bar small muted">
      <span>Menampilkan <strong id="projectCount"><?= count($projects) ?></strong> proyek</span>
      <div class="sort-inline">
        <span>Urutkan:</span>
        <select id="projectSort" aria-label="Urutkan proyek">
          <option value="latest">Terbaru</option>
          <option value="progress_desc">Progres tertinggi</option>
          <option value="progress_asc">Progres terendah</option>
        </select>
      </div>
    </div>
  </div>
</section>

<section class="page-content-band">
  <div class="container">
    <div id="projectsGridView" class="grid-3">
      <?php foreach ($projects as $p): ?>
        <div class="project-filter-item"
             data-title="<?= e(mb_strtolower($p['title'] . ' ' . $p['location'] . ' ' . $p['agency'])) ?>"
             data-wilayah="<?= e($p['wilayah']) ?>"
             data-category="<?= e($p['category']) ?>"
             data-status="<?= e($p['status']) ?>"
             data-progress="<?= (int)$p['progress'] ?>">
          <?php include __DIR__ . '/partials/project_card.php'; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div id="projectsMapView" class="card map-placeholder hidden">
      <div class="map-canvas">
        <div class="map-pin-card" style="top: 34%; left: 48%;">
          <span class="pill pill-blue pill-xs">72%</span>
          <strong>Revitalisasi Trotoar Jl. HR Rasuna Said</strong>
          <span class="tiny muted">Setiabudi, Jakarta Selatan</span>
        </div>
        <div class="map-pin-card" style="top: 58%; left: 36%;">
          <span class="pill pill-blue pill-xs">48%</span>
          <strong>Peningkatan Drainase Kawasan Kemang</strong>
          <span class="tiny muted">Mampang Prapatan, Jakarta Selatan</span>
        </div>
        <div class="map-pin-card" style="top: 46%; left: 26%;">
          <span class="pill pill-green pill-xs">100%</span>
          <strong>Penataan Taman Literasi Blok M</strong>
          <span class="tiny muted">Kebayoran Baru, Jakarta Selatan</span>
        </div>
        <div class="map-pin-card" style="top: 22%; left: 56%;">
          <span class="pill pill-blue pill-xs">31%</span>
          <strong>Integrasi Halte Transjakarta Dukuh Atas</strong>
          <span class="tiny muted">Tanah Abang, Jakarta Pusat</span>
        </div>
      </div>
    </div>
  </div>
</section>
