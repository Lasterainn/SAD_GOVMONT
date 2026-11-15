<section class="page-header-band">
  <div class="container">
    <span class="pill pill-blue">Direktori pembangunan</span>
    <div class="title-row">
      <div>
        <h1>Jelajahi proyek Jakarta</h1>
        <p class="muted">Temukan dan pantau pembangunan di lingkungan Anda.</p>
      </div>

      <div class="segmented-control" role="tablist" aria-label="Mode tampilan proyek">
        <button type="button" class="seg-btn<?= $initialView === 'grid' ? ' active' : '' ?>" data-view-target="grid"><?= icon('grid', 15) ?> Grid</button>
        <button type="button" class="seg-btn<?= $initialView === 'map' ? ' active' : '' ?>" data-view-target="map"><?= icon('map', 15) ?> Peta</button>
      </div>
    </div>

    <div class="filter-toolbar card">
      <div class="search-input-wrap">
        <?= icon('search', 17) ?>
        <input type="text" id="projectSearch" placeholder="Cari proyek, lokasi, kelurahan, atau instansi..." aria-label="Cari proyek">
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
            <option value="">Semua Kategori</option>
            <option value="Infrastruktur">Infrastruktur</option>
            <option value="Transportasi">Transportasi</option>
            <option value="Lingkungan">Lingkungan</option>
            <option value="Fasilitas Publik">Fasilitas Publik</option>
          </select>
        </div>

        <div class="select-wrap">
          <select id="projectStatus" aria-label="Filter status">
            <option value="">Semua Status</option>
            <option value="ongoing">Dalam pengerjaan</option>
            <option value="done">Selesai</option>
            <option value="planned">Direncanakan</option>
          </select>
        </div>

        <button type="button" class="btn-outline btn-sm hidden" id="resetProjectFilters">Reset</button>
      </div>
    </div>

    <div class="results-bar small muted">
      <span>Menampilkan <strong id="projectCount"><?= count($projects) ?></strong> dari <?= count($projects) ?> proyek</span>
      <div class="sort-inline">
        <span>Urutkan:</span>
        <select id="projectSort" aria-label="Urutkan proyek">
          <option value="latest">Terbaru</option>
          <option value="progress_desc">Progres tertinggi</option>
          <option value="progress_asc">Progres terendah</option>
          <option value="title_asc">Judul (A–Z)</option>
        </select>
      </div>
    </div>
  </div>
</section>

<section class="page-content-band">
  <div class="container">
    <!-- MODE 1: GRID KARTU PROYEK -->
    <div id="projectsGridView" class="grid-3<?= $initialView === 'grid' ? '' : ' hidden' ?>">
      <?php foreach ($projects as $idx => $p): ?>
        <div class="project-filter-item"
             data-order="<?= $idx ?>"
             data-title="<?= e(mb_strtolower($p['title'] . ' ' . $p['location'] . ' ' . $p['kelurahan'] . ' ' . $p['agency'])) ?>"
             data-raw-title="<?= e(mb_strtolower($p['title'])) ?>"
             data-wilayah="<?= e($p['wilayah']) ?>"
             data-category="<?= e($p['category']) ?>"
             data-status="<?= e($p['status']) ?>"
             data-progress="<?= (int)$p['progress'] ?>">
          <?php include __DIR__ . '/partials/project_card.php'; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div id="projectsEmptyState" class="card empty-state hidden">
      <h3>Tidak ada proyek yang sesuai filter</h3>
      <p class="muted small">Coba ubah kata kunci pencarian atau atur ulang filter wilayah dan kategori.</p>
      <button type="button" class="btn-solid btn-sm" id="emptyResetProjects">Tampilkan Semua Proyek</button>
    </div>

    <!-- MODE 2: PETA INTERAKTIF WILAYAH DKI JAKARTA -->
    <div id="projectsMapView" class="map-split-layout<?= $initialView === 'map' ? '' : ' hidden' ?>">
      <div class="card map-stage-card">
        <div class="map-top-bar">
          <div class="map-legend small">
            <span><i class="legend-dot dot-blue"></i> Dalam pengerjaan</span>
            <span><i class="legend-dot dot-green"></i> Selesai</span>
            <span><i class="legend-dot dot-amber"></i> Direncanakan</span>
          </div>
          <div class="map-zoom-controls">
            <button type="button" class="map-zoom-btn" id="mapZoomIn" title="Perbesar">+</button>
            <button type="button" class="map-zoom-btn" id="mapZoomOut" title="Perkecil">&minus;</button>
            <button type="button" class="map-zoom-btn" id="mapZoomReset" title="Reset posisi">Reset</button>
          </div>
        </div>

        <div class="map-canvas" id="jakartaMapCanvas">
          <div class="map-zoom-layer" id="jakartaMapLayer">
            <svg viewBox="0 0 800 520" class="jakarta-map-svg" aria-label="Peta Proyek DKI Jakarta">
              <rect width="800" height="520" fill="#e6effa"/>
              <path d="M0,0 L800,0 L800,115 Q620,130 470,118 Q310,108 160,135 L0,125 Z" fill="#cbe0f8"/>
              <text x="530" y="68" fill="#5b84b8" font-size="13" font-weight="700" letter-spacing="2">TELUK JAKARTA</text>

              <path d="M120,135 Q310,108 470,118 Q620,130 740,120 L735,185 L520,180 L250,182 L135,175 Z" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2"/>
              <text x="545" y="156" fill="#64748b" font-size="11" font-weight="700">JAKARTA UTARA</text>

              <path d="M135,175 L330,180 L345,280 L215,310 L145,255 Z" fill="#ffffff" stroke="#cbd5e1" stroke-width="2"/>
              <text x="195" y="232" fill="#64748b" font-size="11" font-weight="700">JAKARTA BARAT</text>

              <path d="M330,180 L475,180 L485,268 L345,280 Z" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="2"/>
              <text x="366" y="212" fill="#64748b" font-size="11" font-weight="700">JAKARTA PUSAT</text>

              <path d="M475,180 L735,185 L715,435 L540,445 L485,268 Z" fill="#ffffff" stroke="#cbd5e1" stroke-width="2"/>
              <text x="585" y="305" fill="#64748b" font-size="11" font-weight="700">JAKARTA TIMUR</text>

              <path d="M215,310 L345,280 L485,268 L540,445 L330,475 L245,405 Z" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2"/>
              <text x="345" y="425" fill="#64748b" font-size="11" font-weight="700">JAKARTA SELATAN</text>

              <path d="M395,140 L392,240 L375,365 L350,460" fill="none" stroke="#93c5fd" stroke-width="4" stroke-dasharray="6 4"/>
              <path d="M170,225 L410,235 L690,245" fill="none" stroke="#93c5fd" stroke-width="3.5" stroke-dasharray="6 4"/>
            </svg>

            <?php foreach ($projects as $idx => $p): ?>
              <?php
                $pinColor = $p['status'] === 'done' ? 'pin-green' : ($p['status'] === 'planned' ? 'pin-amber' : 'pin-blue');
              ?>
              <button type="button"
                      class="jakarta-map-pin <?= $pinColor ?><?= $idx === 0 ? ' active' : '' ?>"
                      style="left: <?= (int)$p['map_x'] ?>%; top: <?= (int)$p['map_y'] ?>%;"
                      data-pin-id="<?= e($p['id']) ?>"
                      data-title="<?= e(mb_strtolower($p['title'] . ' ' . $p['location'] . ' ' . $p['kelurahan'] . ' ' . $p['agency'])) ?>"
                      data-wilayah="<?= e($p['wilayah']) ?>"
                      data-category="<?= e($p['category']) ?>"
                      data-status="<?= e($p['status']) ?>"
                      title="<?= e($p['title']) ?>">
                <span class="pin-badge"><?= (int)$p['progress'] ?>%</span>
                <span class="pin-label"><?= e($p['title']) ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <aside class="card map-inspector-card" id="mapInspector">
        <span class="eyebrow">TITIK PROYEK TERPILIH</span>
        <?php foreach ($projects as $idx => $p): ?>
          <?php $pillTone = $p['status'] === 'done' ? 'green' : ($p['status'] === 'planned' ? 'amber' : 'blue'); ?>
          <div class="map-inspector-item<?= $idx === 0 ? '' : ' hidden' ?>" data-inspector-id="<?= e($p['id']) ?>">
            <div class="inspector-banner tone-<?= (int)$p['tone'] ?>">
              <span class="pill pill-<?= $pillTone ?>"><?= e($statusLabels[$p['status']] ?? '') ?></span>
            </div>
            <h3><?= e($p['title']) ?></h3>
            <p class="small text-primary" style="font-weight: 600; margin: 6px 0 4px;">
              <?= icon('pin', 14) ?> <?= e($p['location']) ?> (Kel. <?= e($p['kelurahan']) ?>)
            </p>
            <p class="tiny muted" style="margin-bottom: 14px;"><?= e($p['agency']) ?> &middot; Koordinat: <?= e($p['coords']) ?></p>

            <div class="progress-row small">
              <span class="muted">Progres pembangunan</span>
              <strong><?= (int)$p['progress'] ?>%</strong>
            </div>
            <progress max="100" value="<?= (int)$p['progress'] ?>"><?= (int)$p['progress'] ?>%</progress>

            <div class="inspector-meta-grid">
              <div>
                <span class="tiny muted">Anggaran</span>
                <strong class="small"><?= e($p['budget']) ?></strong>
              </div>
              <div>
                <span class="tiny muted">Jadwal</span>
                <strong class="small"><?= e($p['target']) ?></strong>
              </div>
            </div>

            <p class="small muted" style="margin: 14px 0 18px;"><?= e($p['description']) ?></p>

            <a href="/proyek/<?= e($p['id']) ?>" class="btn-solid btn-block">
              Lihat Detail Lengkap Proyek <?= icon('arrow-right', 16) ?>
            </a>
          </div>
        <?php endforeach; ?>
      </aside>
    </div>
  </div>
</section>
