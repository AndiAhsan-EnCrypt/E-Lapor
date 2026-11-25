<?php
// admin/kategori.php - kelola kategori pengaduan
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$err=''; $ok='';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['tambah'])) {
    $nama = trim($_POST['nama'] ?? '');
    $desk = trim($_POST['deskripsi'] ?? '');
    if ($nama==='') $err="Nama kategori wajib diisi.";
    else {
        $st = mysqli_prepare($konek, "INSERT INTO categories (nama,deskripsi) VALUES (?,?)");
        mysqli_stmt_bind_param($st, "ss", $nama, $desk);
        try { mysqli_stmt_execute($st); $ok="Kategori ditambahkan."; }
        catch (mysqli_sql_exception $e) { $err="Gagal: ".$e->getMessage(); }
    }
}
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($konek, "DELETE FROM categories WHERE id=$id");
    header("Location: kategori.php"); exit;
}

$res = mysqli_query($konek, "SELECT c.*, (SELECT COUNT(*) FROM tickets t WHERE t.category_id=c.id) AS jml FROM categories c ORDER BY nama");

$pageTitle = "Kelola Kategori";
include __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-3">Kelola Kategori Pengaduan</h3>
<?php if ($err): ?><div class="alert alert-danger"><?= h($err) ?></div><?php endif; ?>
<?php if ($ok): ?><div class="alert alert-success"><?= h($ok) ?></div><?php endif; ?>

<div class="card p-3 mb-3">
  <form method="post" class="row g-2">
    <div class="col-md-4"><input type="text" name="nama" class="form-control" placeholder="Nama kategori" required></div>
    <div class="col-md-6"><input type="text" name="deskripsi" class="form-control" placeholder="Deskripsi"></div>
    <div class="col-md-2"><button name="tambah" class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Tambah</button></div>
  </form>
</div>

<div class="card">
  <table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Nama</th><th>Deskripsi</th><th class="text-end">Jumlah Tiket</th><th>Aksi</th></tr></thead>
    <tbody>
      <?php while ($k = mysqli_fetch_assoc($res)): ?>
        <tr>
          <td><?= h($k['nama']) ?></td>
          <td><?= h($k['deskripsi']) ?></td>
          <td class="text-end"><?= (int)$k['jml'] ?></td>
          <td><a href="kategori.php?hapus=<?= (int)$k['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kategori ini?')" title="Hapus"><i class="bi bi-trash"></i></a></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<a href="dashboard.php" class="btn btn-secondary mt-3">Kembali</a>
<?php include __DIR__ . '/../includes/footer.php'; ?>