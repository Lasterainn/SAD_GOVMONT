<section class="page-header-band">
  <div class="container">
    <span class="eyebrow">Selamat datang kembali,</span>
    <div class="title-row">
      <div>
        <h1><?= e($userName) ?></h1>
        <p class="muted">Berikut ringkasan aktivitas Jakarta Connect hari ini.</p>
      </div>

      <div class="role-preview-box">
        <span class="tiny muted">Pratinjau peran</span>
        <div class="segmented-control" role="tablist" aria-label="Pratinjau peran dashboard">
          <button type="button" class="seg-btn<?= $activeRole === 'masyarakat' ? ' active' : '' ?>" data-role-tab="masyarakat">Masyarakat</button>
          <button type="button" class="seg-btn<?= $activeRole === 'pemerintah' ? ' active' : '' ?>" data-role-tab="pemerintah">Pemerintah</button>
          <button type="button" class="seg-btn<?= $activeRole === 'admin' ? ' active' : '' ?>" data-role-tab="admin">Admin</button>
        </div>
        <span class="tiny muted">Mode demo &middot; tampilan menyesuaikan peran</span>
      </div>
    </div>
  </div>
</section>

<section class="page-content-band">
  <div class="container">
    <!-- PANEL 1: MASYARAKAT (Sesuai Gambar 3) -->
    <div class="role-panel<?= $activeRole === 'masyarakat' ? '' : ' hidden' ?>" id="panel-masyarakat">
      <div class="kpi-grid-3">
        <div class="card kpi-card">
          <span class="stat-icon stat-blue"><?= icon('bookmark', 20) ?></span>
          <div>
            <strong>8</strong>
            <span>Proyek diikuti</span>
          </div>
        </div>
        <div class="card kpi-card">
          <span class="stat-icon stat-indigo"><?= icon('chat', 20) ?></span>
          <div>
            <strong>12</strong>
            <span>Diskusi saya</span>
          </div>
        </div>
        <div class="card kpi-card">
          <span class="stat-icon stat-amber"><?= icon('bell', 20) ?></span>
          <div>
            <strong>5</strong>
            <span>Notifikasi baru</span>
          </div>
        </div>
      </div>

      <div class="dashboard-split">
        <div class="card panel-card">
          <div class="panel-head">
            <h3>Proyek yang Anda ikuti</h3>
            <a href="/proyek" class="link-more small">Lihat semua <?= icon('arrow-right', 14) ?></a>
          </div>

          <div class="followed-list">
            <?php foreach ($followedProjects as $fp): ?>
              <div class="followed-item">
                <div class="followed-thumb tone-<?= (int)$fp['tone'] ?>"></div>
                <div class="followed-main">
                  <div class="followed-top">
                    <div>
                      <strong><a href="/proyek/<?= e($fp['id']) ?>"><?= e($fp['title']) ?></a></strong>
                      <span class="tiny muted"><?= e($fp['location']) ?></span>
                    </div>
                    <strong class="small"><?= (int)$fp['progress'] ?>%</strong>
                  </div>
                  <progress max="100" value="<?= (int)$fp['progress'] ?>"><?= (int)$fp['progress'] ?>%</progress>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card panel-card">
          <div class="panel-head">
            <h3>Aktivitas terbaru</h3>
          </div>

          <div class="activity-list">
            <div class="activity-item">
              <span class="activity-tag"><span class="dot dot-blue"></span> KEMAJUAN PROYEK</span>
              <strong>Trotoar Rasuna Said mencapai 72%</strong>
              <span class="tiny muted">2 jam</span>
            </div>
            <div class="activity-item">
              <span class="activity-tag"><span class="dot dot-indigo"></span> BALASAN BARU</span>
              <strong>Dinas Perhubungan menjawab diskusi Anda</strong>
              <span class="tiny muted">5 jam</span>
            </div>
            <div class="activity-item">
              <span class="activity-tag"><span class="dot dot-green"></span> PENGUMUMAN</span>
              <strong>Konsultasi publik Kota Tua dibuka</strong>
              <span class="tiny muted">Kemarin</span>
            </div>
          </div>

          <a href="/diskusi" class="btn-outline btn-block" style="margin-top: 16px;">Lihat semua notifikasi</a>
        </div>
      </div>
    </div>

    <!-- PANEL 2: PEMERINTAH (Flat View) -->
    <div class="role-panel<?= $activeRole === 'pemerintah' ? '' : ' hidden' ?>" id="panel-pemerintah">
      <div class="kpi-grid-3">
        <div class="card kpi-card">
          <span class="stat-icon stat-blue"><?= icon('building', 20) ?></span>
          <div>
            <strong>14</strong>
            <span>Proyek instansi</span>
          </div>
        </div>
        <div class="card kpi-card">
          <span class="stat-icon stat-amber"><?= icon('clock', 20) ?></span>
          <div>
            <strong>3</strong>
            <span>Menunggu konfirmasi</span>
          </div>
        </div>
        <div class="card kpi-card">
          <span class="stat-icon stat-green"><?= icon('chat', 20) ?></span>
          <div>
            <strong>29</strong>
            <span>Aspirasi warga masuk</span>
          </div>
        </div>
      </div>

      <div class="dashboard-split">
        <div class="card panel-card">
          <div class="panel-head">
            <h3>Proyek Instansi &amp; Pembaruan Progres</h3>
            <a href="/proyek" class="link-more small">Direktori <?= icon('arrow-right', 14) ?></a>
          </div>
          <div class="followed-list">
            <?php foreach ($followedProjects as $fp): ?>
              <div class="followed-item">
                <div class="followed-thumb tone-<?= (int)$fp['tone'] ?>"></div>
                <div class="followed-main">
                  <div class="followed-top">
                    <div>
                      <strong><a href="/proyek/<?= e($fp['id']) ?>"><?= e($fp['title']) ?></a></strong>
                      <span class="tiny muted"><?= e($fp['agency']) ?> &middot; <?= e($fp['budget']) ?></span>
                    </div>
                    <span class="pill pill-blue pill-xs"><?= (int)$fp['progress'] ?>%</span>
                  </div>
                  <progress max="100" value="<?= (int)$fp['progress'] ?>"><?= (int)$fp['progress'] ?>%</progress>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card panel-card">
          <div class="panel-head">
            <h3>Tindak Lanjut &amp; Konfirmasi</h3>
          </div>
          <div class="activity-list">
            <div class="activity-item">
              <span class="activity-tag"><span class="dot dot-amber"></span> DRAFT ADMIN</span>
              <strong>Lengkapi RAB &amp; jadwal Revitalisasi JPO Pasar Minggu</strong>
              <span class="tiny muted">Baru ditugaskan</span>
            </div>
            <div class="activity-item">
              <span class="activity-tag"><span class="dot dot-blue"></span> DISKUSI TERKAIT</span>
              <strong>24 warga menanyakan pelican crossing Stasiun Manggarai</strong>
              <span class="tiny muted">20 menit lalu</span>
            </div>
          </div>
          <a href="/diskusi" class="btn-solid btn-block" style="margin-top: 16px;">Tanggapi Diskusi Warga</a>
        </div>
      </div>
    </div>

    <!-- PANEL 3: ADMIN (Tampilan Flat Dashboard Admin) -->
    <div class="role-panel<?= $activeRole === 'admin' ? '' : ' hidden' ?>" id="panel-admin">
      <div class="kpi-grid-3">
        <div class="card kpi-card">
          <span class="stat-icon stat-blue"><?= icon('users', 20) ?></span>
          <div>
            <strong>3.842</strong>
            <span>Warga terdaftar</span>
          </div>
        </div>
        <div class="card kpi-card">
          <span class="stat-icon stat-indigo"><?= icon('building', 20) ?></span>
          <div>
            <strong>18</strong>
            <span>Akun instansi pemerintah</span>
          </div>
        </div>
        <div class="card kpi-card">
          <span class="stat-icon stat-amber"><?= icon('shield', 20) ?></span>
          <div>
            <strong>4</strong>
            <span>Antrean moderasi &amp; proyek</span>
          </div>
        </div>
      </div>

      <div class="dashboard-split">
        <div class="card panel-card">
          <div class="panel-head">
            <h3>Kelola Kerangka Proyek &amp; Status</h3>
            <button type="button" class="btn-solid btn-sm" id="adminAddProjectBtn">
              <?= icon('plus', 15) ?> Tambah Proyek
            </button>
          </div>

          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Judul Proyek</th>
                  <th>Instansi</th>
                  <th>Progres</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($adminProjects as $ap): ?>
                  <tr>
                    <td>
                      <strong><a href="/proyek/<?= e($ap['id']) ?>"><?= e($ap['judul']) ?></a></strong>
                      <span class="tiny muted">Diperbarui <?= e($ap['updated']) ?></span>
                    </td>
                    <td class="small muted"><?= e($ap['instansi']) ?></td>
                    <td><strong><?= (int)$ap['progres'] ?>%</strong></td>
                    <td><span class="pill <?= e($ap['badge']) ?> pill-xs"><?= e($ap['status']) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card panel-card">
          <div class="panel-head">
            <h3>Moderasi Forum &amp; Audit Sistem</h3>
          </div>

          <div class="activity-list">
            <?php foreach ($adminModeration as $mod): ?>
              <div class="activity-item">
                <span class="activity-tag"><span class="dot <?= e($mod['dot']) ?>"></span> <?= e($mod['tag']) ?></span>
                <strong><?= e($mod['title']) ?></strong>
                <div class="mod-foot">
                  <span class="tiny muted"><?= e($mod['author']) ?></span>
                  <span class="pill pill-blue pill-xs"><?= e($mod['status']) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="admin-quick-actions">
            <a href="/proyek" class="btn-outline">Lihat Direktori Proyek</a>
            <a href="/diskusi" class="btn-solid">Buka Ruang Diskusi</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
