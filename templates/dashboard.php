<?php
$roleLabel = $activeRole === 'admin'
    ? 'Administrator'
    : ($activeRole === 'pemerintah' ? 'Instansi Pemerintah' : 'Warga Jakarta');
?>
<section class="page-header-band">
  <div class="container">
    <span class="eyebrow">Selamat datang kembali,</span>
    <div class="title-row">
      <div>
        <h1><?= e($userName) ?></h1>
        <p class="muted">Peran aktif: <strong><?= e($roleLabel) ?></strong> &middot; Data terhubung langsung ke PostgreSQL.</p>
      </div>
      <div style="display:flex; gap:10px;">
        <a href="/proyek" class="btn-outline btn-sm">Direktori Proyek</a>
        <a href="/diskusi" class="btn-solid btn-sm">Ruang Diskusi</a>
      </div>
    </div>
  </div>
</section>

<section class="page-content-band">
  <div class="container">
    <?php if ($activeRole === 'warga'): ?>
      <!-- DASHBOARD WARGA -->
      <div class="kpi-grid-3">
        <div class="card kpi-card">
          <span class="stat-icon stat-blue"><?= icon('building', 20) ?></span>
          <div>
            <strong><?= (int)($proyekStats['total_proyek'] ?? 0) ?></strong>
            <span>Proyek dipantau</span>
          </div>
        </div>
        <div class="card kpi-card">
          <span class="stat-icon stat-indigo"><?= icon('chat', 20) ?></span>
          <div>
            <strong><?= (int)($wargaStats['forum_dibuat'] ?? 0) ?></strong>
            <span>Topik diskusi saya</span>
          </div>
        </div>
        <div class="card kpi-card">
          <span class="stat-icon stat-green"><?= icon('send', 20) ?></span>
          <div>
            <strong><?= (int)($wargaStats['chat_dikirim'] ?? 0) ?></strong>
            <span>Tanggapan dikirim</span>
          </div>
        </div>
      </div>

      <div class="card panel-card">
        <div class="panel-head">
          <h3>Proyek Pembangunan Terkini</h3>
          <a href="/proyek" class="link-more small">Lihat semua <?= icon('arrow-right', 14) ?></a>
        </div>
        <div class="followed-list">
          <?php foreach (array_slice($projects, 0, 5) as $fp): ?>
            <div class="followed-item">
              <div class="followed-thumb tone-<?= (int)$fp['tone'] ?>"></div>
              <div class="followed-main">
                <div class="followed-top">
                  <div>
                    <strong><a href="/proyek/<?= e($fp['id']) ?>"><?= e($fp['judul']) ?></a></strong>
                    <span class="tiny muted"><?= e($fp['location']) ?> &middot; <?= e($fp['agency']) ?></span>
                  </div>
                  <strong class="small"><?= (int)$fp['progress'] ?>%</strong>
                </div>
                <progress max="100" value="<?= (int)$fp['progress'] ?>"><?= (int)$fp['progress'] ?>%</progress>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    <?php elseif ($activeRole === 'pemerintah'): ?>
      <!-- DASHBOARD PEMERINTAH -->
      <div class="kpi-grid-3">
        <div class="card kpi-card">
          <span class="stat-icon stat-blue"><?= icon('building', 20) ?></span>
          <div>
            <strong><?= (int)($proyekStats['total_proyek'] ?? 0) ?></strong>
            <span>Total proyek terdaftar</span>
          </div>
        </div>
        <div class="card kpi-card">
          <span class="stat-icon stat-amber"><?= icon('clock', 20) ?></span>
          <div>
            <strong><?= (int)($proyekStats['dalam_pengerjaan'] ?? 0) ?></strong>
            <span>Dalam pengerjaan</span>
          </div>
        </div>
        <div class="card kpi-card">
          <span class="stat-icon stat-green"><?= icon('check-circle', 20) ?></span>
          <div>
            <strong><?= (int)($proyekStats['selesai'] ?? 0) ?></strong>
            <span>Proyek selesai</span>
          </div>
        </div>
      </div>

      <div class="card panel-card">
        <div class="panel-head">
          <h3>Daftar Proyek &amp; Status Konfirmasi</h3>
          <span class="tiny muted">Klik proyek untuk mengisi RAB, tahapan &amp; konfirmasi</span>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Judul Proyek</th>
                <th>Biaya Pengadaan</th>
                <th>Progres</th>
                <th>Konfirmasi</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($projects as $ap): ?>
                <tr>
                  <td>
                    <strong><a href="/proyek/<?= e($ap['id']) ?>"><?= e($ap['judul']) ?></a></strong>
                    <span class="tiny muted"><?= e($ap['location']) ?></span>
                  </td>
                  <td class="small"><?= e($ap['budget_formatted']) ?></td>
                  <td><strong><?= (int)$ap['progress'] ?>%</strong></td>
                  <td>
                    <span class="pill <?= $ap['status_konfirmasi'] === 'terkonfirmasi' ? 'pill-green' : 'pill-amber' ?> pill-xs">
                      <?= e($ap['status_konfirmasi']) ?>
                    </span>
                  </td>
                  <td><a href="/proyek/<?= e($ap['id']) ?>" class="btn-outline btn-sm">Kelola</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php else: ?>
      <!-- DASHBOARD ADMIN -->
      <div class="kpi-grid-3">
        <div class="card kpi-card">
          <span class="stat-icon stat-blue"><?= icon('users', 20) ?></span>
          <div>
            <strong><?= (int)($adminStats['jumlah_warga'] ?? 0) ?></strong>
            <span>Warga terdaftar</span>
          </div>
        </div>
        <div class="card kpi-card">
          <span class="stat-icon stat-indigo"><?= icon('building', 20) ?></span>
          <div>
            <strong><?= (int)($adminStats['jumlah_pemerintah'] ?? 0) ?></strong>
            <span>Akun instansi pemerintah</span>
          </div>
        </div>
        <div class="card kpi-card">
          <span class="stat-icon stat-green"><?= icon('shield', 20) ?></span>
          <div>
            <strong><?= !empty($adminStats['hash_chain_valid']) ? 'Valid' : 'Periksa!' ?></strong>
            <span>Integritas Hash Chain Log</span>
          </div>
        </div>
      </div>

      <div class="dashboard-split">
        <div class="card panel-card">
          <div class="panel-head">
            <h3>Manajemen Proyek Pembangunan</h3>
            <a href="/proyek" class="btn-solid btn-sm"><?= icon('plus', 15) ?> Kelola / Tambah</a>
          </div>

          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Judul Proyek</th>
                  <th>Progres</th>
                  <th>Konfirmasi</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($projects as $ap): ?>
                  <tr>
                    <td>
                      <strong><a href="/proyek/<?= e($ap['id']) ?>"><?= e($ap['judul']) ?></a></strong>
                      <span class="tiny muted"><?= e($ap['agency']) ?></span>
                    </td>
                    <td><strong><?= (int)$ap['progress'] ?>%</strong></td>
                    <td>
                      <span class="pill <?= $ap['status_konfirmasi'] === 'terkonfirmasi' ? 'pill-green' : 'pill-amber' ?> pill-xs">
                        <?= e($ap['status_konfirmasi']) ?>
                      </span>
                    </td>
                    <td><a href="/proyek/<?= e($ap['id']) ?>" class="btn-outline btn-sm">Edit</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card panel-card">
          <div class="panel-head">
            <h3>Jejak Audit Log Terbaru (Append-Only)</h3>
          </div>

          <div class="activity-list">
            <?php if (empty($auditLogs)): ?>
              <p class="small muted">Belum ada entri log aktivitas.</p>
            <?php else: ?>
              <?php foreach ($auditLogs as $log): ?>
                <div class="activity-item">
                  <span class="activity-tag"><span class="dot dot-blue"></span> <?= e($log['sumber']) ?> &middot; <?= e(strtoupper($log['aksi'])) ?></span>
                  <strong class="small">Aktor: <?= e($log['role']) ?></strong>
                  <span class="tiny muted"><?= e(date('d M Y H:i:s', strtotime($log['created_at']))) ?></span>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>