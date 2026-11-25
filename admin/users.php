<?php
// admin/users.php - Daftar warga terdaftar
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$cari = trim($_GET['cari'] ?? '');
$where = '';
if ($cari !== '') {
    $kw = mysqli_real_escape_string($konek, $cari);
    $where = "WHERE (nama LIKE '%$kw%' OR email LIKE '%$kw%')";
}

// Pagination
$perPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$totalRes = mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM users WHERE role='warga' " . ($cari ? "AND (nama LIKE '%$kw%' OR email LIKE '%$kw%')" : '')));
$totalRows = (int)$totalRes['c'];
$totalPages = max(1, ceil($totalRows / $perPage));

$res = mysqli_query($konek,
    "SELECT u.*, (SELECT COUNT(*) FROM tickets t WHERE t.user_id=u.id) AS jml_tiket
     FROM users u
     WHERE u.role='warga' " . ($cari ? "AND (u.nama LIKE '%$kw%' OR u.email LIKE '%$kw%')" : '') . "
     ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset");

$pageTitle = "Daftar Warga";
include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h3 class="mb-0"><i class="bi bi-people"></i> Daftar Warga Terdaftar</h3>
  <a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<form method="get" class="mb-3">
  <div class="input-group">
    <input type="text" name="cari" class="form-control" placeholder="Cari nama atau email..." value="<?= h($cari) ?>">
    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
    <?php if ($cari): ?><a href="users.php" class="btn btn-outline-secondary">Reset</a><?php endif; ?>
  </div>
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr><th>#</th><th>Nama</th><th>Email</th><th>Telepon</th><th>Alamat</th><th>Pengaduan</th><th>Terdaftar</th></tr>
      </thead>
      <tbody>
        <?php $no = $offset + 1; $ada = false; while ($u = mysqli_fetch_assoc($res)): $ada = true; ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= h($u['nama']) ?></td>
            <td><?= h($u['email']) ?></td>
            <td><?= h($u['telepon'] ?: '-') ?></td>
            <td class="text-truncate" style="max-width:160px;" title="<?= h($u['alamat']) ?>"><?= h($u['alamat'] ?: '-') ?></td>
            <td class="text-center">
              <span class="badge bg-primary"><?= (int)$u['jml_tiket'] ?></span>
            </td>
            <td class="small text-muted"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          </tr>
        <?php endwhile; if (!$ada): ?>
          <tr><td colspan="7" class="text-center text-muted">Tidak ada warga terdaftar.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-3">
  <ul class="pagination pagination-sm justify-content-center mb-0">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <li class="page-item <?= $i === $page ? 'active' : '' ?>">
        <a class="page-link" href="users.php?page=<?= $i ?><?= $cari ? '&cari=' . urlencode($cari) : '' ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
  </ul>
  <p class="text-center text-muted small mt-2">Halaman <?= $page ?> dari <?= $totalPages ?> (total <?= $totalRows ?> warga)</p>
</nav>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>