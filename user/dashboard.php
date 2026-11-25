<?php
// user/dashboard.php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (is_admin()) { header("Location: ../admin/dashboard.php"); exit; }

$uid = (int) current_user()['id'];
$stats = [];
foreach (['Baru','Diproses','Selesai','Ditolak'] as $s) {
    $r = mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM tickets WHERE user_id=$uid AND status='$s'"));
    $stats[$s] = (int)$r['c'];
}
$totalMine = array_sum($stats);

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$totalRes = mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM tickets WHERE user_id=$uid"));
$totalRows = (int)$totalRes['c'];
$totalPages = max(1, ceil($totalRows / $perPage));
$res = mysqli_query($konek, "SELECT t.*, c.nama AS kategori FROM tickets t JOIN categories c ON c.id=t.category_id WHERE t.user_id=$uid ORDER BY t.created_at DESC LIMIT $perPage OFFSET $offset");

$pageTitle = "Dashboard Saya";
include __DIR__ . '/../includes/header.php';
?>
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h3 class="mb-0 fw-bold"><i class="bi bi-grid"></i> Dashboard Saya</h3>
    <p class="text-muted small mb-0">Halo, <?= h(current_user()['nama']) ?> — pantau pengaduan Anda di sini</p>
  </div>
  <a href="buat_tiket.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Buat Pengaduan Baru</a>
</div>

<!-- Stat cards -->
<div class="row g-3 mb-4">
  <?php
  $statItems = [
    ['Total Pengaduan', $totalMine, 'bi-inbox', 'primary'],
    ['Baru', $stats['Baru'], 'bi-plus-circle', 'secondary'],
    ['Diproses', $stats['Diproses'], 'bi-gear', 'info'],
    ['Selesai', $stats['Selesai'], 'bi-check-circle', 'success'],
    ['Ditolak', $stats['Ditolak'], 'bi-x-circle', 'danger'],
  ];
  foreach ($statItems as [$label, $val, $icon, $color]): ?>
    <div class="col-6 col-md-4 col-lg">
      <div class="card stat-card text-center p-3 h-100 border-0">
        <div class="mb-2"><i class="bi <?= $icon ?> fs-4 text-<?= $color ?>"></i></div>
        <div class="num text-<?= $color ?>"><?= $val ?></div>
        <div class="lbl"><?= $label ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Tabel pengaduan -->
<div class="card">
  <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3">
    <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul"></i> Pengaduan Saya</h6>
    <span class="badge bg-primary rounded-pill"><?= $totalRows ?> total</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>#</th><th>Judul</th><th>Kategori</th><th>Prioritas</th><th>Status</th><th>Tanggal</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
          <?php $no = $offset + 1; $ada = false; while ($t = mysqli_fetch_assoc($res)): $ada = true; ?>
            <tr>
              <td class="text-muted small"><?= $no++ ?></td>
              <td>
                <a href="tiket_detail.php?id=<?= (int)$t['id'] ?>" class="text-decoration-none fw-semibold">
                  <?= h(mb_strimwidth($t['judul'], 0, 40, '…')) ?>
                </a>
              </td>
              <td class="small text-muted"><?= h($t['kategori']) ?></td>
              <td><span class="badge badge-prio-<?= h($t['prioritas']) ?>"><?= h($t['prioritas']) ?></span></td>
              <td><span class="badge badge-status-<?= h($t['status']) ?>"><?= h($t['status']) ?></span></td>
              <td class="small text-muted"><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
              <td class="text-end">
                <a href="tiket_detail.php?id=<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-primary" title="Detail">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
          <?php endwhile; if (!$ada): ?>
            <tr>
              <td colspan="7" class="text-center py-5">
                <i class="bi bi-inbox display-6 text-muted"></i>
                <p class="text-muted mt-2 mb-3">Belum ada pengaduan.</p>
                <a href="buat_tiket.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Buat sekarang</a>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if ($totalPages > 1): ?>
  <div class="card-footer bg-transparent">
    <nav>
      <ul class="pagination pagination-sm justify-content-center mb-0">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="dashboard.php?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>