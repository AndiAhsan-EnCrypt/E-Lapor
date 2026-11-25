<?php
// admin/dashboard.php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$stats = [];
foreach (['Baru','Diproses','Selesai','Ditolak'] as $s) {
    $r = mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM tickets WHERE status='$s'"));
    $stats[$s] = (int)$r['c'];
}
$totalTiket = array_sum($stats);
$totalWarga = (int)mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM users WHERE role='warga'"))['c'];
$totalAdmin = (int)mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM users WHERE role='admin'"))['c'];
$totalKategori = (int)mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM categories"))['c'];

$perKatRes = mysqli_query($konek,
    "SELECT c.nama, COUNT(t.id) AS jml FROM categories c LEFT JOIN tickets t ON t.category_id=c.id GROUP BY c.id ORDER BY jml DESC, c.nama ASC");
$katLabels = []; $katValues = []; $katRows = [];
while ($k = mysqli_fetch_assoc($perKatRes)) {
    $katRows[] = $k;
    if ($k['jml'] > 0) { $katLabels[] = $k['nama']; $katValues[] = (int)$k['jml']; }
}

$recent = mysqli_query($konek,
    "SELECT t.id,t.judul,t.status,t.prioritas,t.created_at,u.nama AS pelapor,c.nama AS kategori
     FROM tickets t JOIN users u ON u.id=t.user_id JOIN categories c ON c.id=t.category_id
     ORDER BY t.created_at DESC LIMIT 6");

$pageTitle = "Dashboard Admin";
include __DIR__ . '/../includes/header.php';
?>
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h3 class="mb-0 fw-bold"><i class="bi bi-speedometer2"></i> Dashboard Admin</h3>
    <p class="text-muted small mb-0">Ringkasan aktivitas sistem pengaduan masyarakat</p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="kelola_tiket.php" class="btn btn-primary btn-sm"><i class="bi bi-list-task"></i> Kelola Tiket</a>
    <a href="users.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-people"></i> Warga</a>
    <a href="kategori.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-tags"></i> Kategori</a>
    <a href="export.php" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Export</a>
  </div>
</div>

<!-- Stat cards -->
<div class="row g-3 mb-4">
  <?php
  $statItems = [
    ['Total Pengaduan', $totalTiket, 'bi-inbox', 'primary'],
    ['Baru', $stats['Baru'], 'bi-plus-circle', 'secondary'],
    ['Diproses', $stats['Diproses'], 'bi-gear', 'info'],
    ['Selesai', $stats['Selesai'], 'bi-check-circle', 'success'],
    ['Ditolak', $stats['Ditolak'], 'bi-x-circle', 'danger'],
    ['Warga Terdaftar', $totalWarga, 'bi-people', 'warning'],
  ];
  foreach ($statItems as [$label, $val, $icon, $color]): ?>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card stat-card text-center p-3 h-100 border-0">
        <div class="mb-2"><i class="bi <?= $icon ?> fs-4 text-<?= $color ?>"></i></div>
        <div class="num text-<?= $color ?>"><?= $val ?></div>
        <div class="lbl"><?= $label ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <!-- Kiri: Pengaduan terbaru (lebih lebar) -->
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history"></i> Pengaduan Terbaru</h6>
        <a href="kelola_tiket.php" class="btn btn-sm btn-link p-0">Lihat semua →</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr><th>Judul</th><th>Pelapor</th><th>Kategori</th><th>Status</th><th>Prioritas</th><th>Tanggal</th></tr>
            </thead>
            <tbody>
              <?php $ada=false; while ($t = mysqli_fetch_assoc($recent)): $ada=true; ?>
                <tr>
                  <td>
                    <a href="tiket_detail.php?id=<?= (int)$t['id'] ?>" class="text-decoration-none fw-semibold">
                      <?= h(mb_strimwidth($t['judul'], 0, 38, '…')) ?>
                    </a>
                  </td>
                  <td class="small"><?= h($t['pelapor']) ?></td>
                  <td class="small text-muted"><?= h($t['kategori']) ?></td>
                  <td><span class="badge badge-status-<?= h($t['status']) ?>"><?= h($t['status']) ?></span></td>
                  <td><span class="badge badge-prio-<?= h($t['prioritas']) ?>"><?= h($t['prioritas']) ?></span></td>
                  <td class="small text-muted"><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
                </tr>
              <?php endwhile; if (!$ada): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pengaduan.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Kanan: kategori + ringkasan -->
  <div class="col-lg-4 d-flex flex-column gap-3">
    <!-- Pengaduan per kategori -->
    <div class="card">
      <div class="card-header bg-transparent border-0 py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart"></i> Pengaduan per Kategori</h6>
      </div>
      <div class="card-body pt-0">
        <?php if (empty($katRows) || $totalTiket === 0): ?>
          <p class="text-muted small mb-0">Belum ada data.</p>
        <?php else: ?>
          <?php foreach ($katRows as $k): if ($k['jml'] == 0) continue; $pct = round(($k['jml'] / $totalTiket) * 100); ?>
            <div class="mb-2">
              <div class="d-flex justify-content-between small mb-1">
                <span><?= h($k['nama']) ?></span>
                <span class="fw-bold"><?= (int)$k['jml'] ?> <span class="text-muted">(<?= $pct ?>%)</span></span>
              </div>
              <div class="progress" style="height:6px;">
                <div class="progress-bar" style="width:<?= $pct ?>%; background: linear-gradient(90deg,#4f46e5,#06b6d4);"></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Ringkasan sistem (tanpa tombol) -->
    <div class="card">
      <div class="card-header bg-transparent border-0 py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle"></i> Ringkasan Sistem</h6>
      </div>
      <div class="card-body pt-0">
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            Admin terdaftar <span class="badge bg-primary rounded-pill"><?= $totalAdmin ?></span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            Warga terdaftar <span class="badge bg-success rounded-pill"><?= $totalWarga ?></span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            Kategori <span class="badge bg-info rounded-pill"><?= $totalKategori ?></span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            Tiket selesai <span class="badge bg-success rounded-pill"><?= $stats['Selesai'] ?></span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            Tiket ditolak <span class="badge bg-danger rounded-pill"><?= $stats['Ditolak'] ?></span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Grafik -->
<div class="row g-3 mt-2">
  <div class="col-lg-6">
    <div class="card p-3">
      <h6 class="mb-3 fw-bold"><i class="bi bi-pie-chart-fill"></i> Distribusi Status</h6>
      <canvas id="chartStatus" height="220"></canvas>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card p-3">
      <h6 class="mb-3 fw-bold"><i class="bi bi-bar-chart-fill"></i> Pengaduan per Kategori</h6>
      <canvas id="chartKategori" height="220"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  new Chart(document.getElementById('chartStatus'), {
    type: 'doughnut',
    data: {
      labels: ['Baru','Diproses','Selesai','Ditolak'],
      datasets: [{ data: [<?= (int)$stats['Baru'] ?>, <?= (int)$stats['Diproses'] ?>, <?= (int)$stats['Selesai'] ?>, <?= (int)$stats['Ditolak'] ?>],
        backgroundColor: ['#64748b','#06b6d4','#10b981','#ef4444'], borderWidth: 0 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });
  new Chart(document.getElementById('chartKategori'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($katLabels) ?>,
      datasets: [{ label: 'Jumlah', data: <?= json_encode($katValues) ?>, backgroundColor: '#4f46e5', borderRadius: 8 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
  });
})();
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>