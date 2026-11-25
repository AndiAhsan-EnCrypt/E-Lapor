<?php
// admin/kelola_tiket.php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$filter = $_GET['status'] ?? '';
$where = $filter ? "WHERE t.status='" . mysqli_real_escape_string($konek,$filter) . "'" : '';

// Pagination
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$totalRes = mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM tickets t $where"));
$totalRows = (int)$totalRes['c'];
$totalPages = max(1, ceil($totalRows / $perPage));

$res = mysqli_query($konek,
    "SELECT t.id,t.judul,t.status,t.prioritas,t.created_at,u.nama AS pelapor,c.nama AS kategori
     FROM tickets t JOIN users u ON u.id=t.user_id JOIN categories c ON c.id=t.category_id
     $where ORDER BY t.created_at DESC LIMIT $perPage OFFSET $offset");

$pageTitle = "Kelola Tiket";
include __DIR__ . '/../includes/header.php';
?>
<?php if (isset($_GET['deleted'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    Pengaduan berhasil dihapus.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h3 class="mb-0">Kelola Semua Pengaduan</h3>
  <div class="d-flex gap-1">
    <a href="export_excel.php<?= $filter ? '?status=' . urlencode($filter) : '' ?>" class="btn btn-success btn-sm">
      <i class="bi bi-file-earmark-excel"></i> Export Excel
    </a>
    <a href="export.php" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-sliders"></i> Lanjutan
    </a>
  </div>
</div>
<div class="mb-3">
  <div class="btn-group btn-group-sm" role="group">
    <a href="kelola_tiket.php" class="btn btn-outline-secondary <?= !$filter ? 'active' : '' ?>">Semua</a>
    <?php foreach (['Baru','Diproses','Selesai','Ditolak'] as $s): ?>
      <a href="kelola_tiket.php?status=<?= urlencode($s) ?>" class="btn btn-outline-secondary <?= $filter===$s ? 'active' : '' ?>"><?= $s ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr><th>#</th><th>Judul</th><th>Pelapor</th><th>Kategori</th><th>Prioritas</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php $ada=false; while ($t = mysqli_fetch_assoc($res)): $ada=true; ?>
          <tr>
            <td><?= (int)$t['id'] ?></td>
            <td><?= h($t['judul']) ?></td>
            <td><?= h($t['pelapor']) ?></td>
            <td><?= h($t['kategori']) ?></td>
            <td><span class="badge badge-prio-<?= h($t['prioritas']) ?>"><?= h($t['prioritas']) ?></span></td>
            <td><span class="badge badge-status-<?= h($t['status']) ?>"><?= h($t['status']) ?></span></td>
            <td><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
            <td>
              <div class="btn-group btn-group-sm">
                <a href="tiket_detail.php?id=<?= (int)$t['id'] ?>" class="btn btn-outline-primary" title="Proses">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <a href="hapus_tiket.php?id=<?= (int)$t['id'] ?>" class="btn btn-outline-danger" title="Hapus"
                   onclick="return confirm('Hapus pengaduan ini beserta komentar & lampirannya?')">
                  <i class="bi bi-trash"></i>
                </a>
              </div>
            </td>
          </tr>
        <?php endwhile; if (!$ada): ?>
          <tr><td colspan="8" class="text-center text-muted">Tidak ada data.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($totalPages > 1): ?>
  <nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center mb-0">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="kelola_tiket.php?page=<?= $i ?><?= $filter ? '&status=' . urlencode($filter) : '' ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
    <p class="text-center text-muted small mt-2">Halaman <?= $page ?> dari <?= $totalPages ?> (total <?= $totalRows ?> data)</p>
  </nav>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>