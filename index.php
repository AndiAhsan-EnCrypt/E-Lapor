<?php
// index.php - Beranda publik + statistik
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/auth.php';

// Statistik: jika user login, tampilkan data miliknya agar sinkron dengan dashboard
if (is_logged_in() && !is_admin()) {
    $uid = (int) current_user()['id'];
    $totalTiket = (int) mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM tickets WHERE user_id=$uid"))['c'];
    $selesai    = (int) mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM tickets WHERE user_id=$uid AND status='Selesai'"))['c'];
    $recent = mysqli_query($konek,
        "SELECT t.judul, t.status, t.prioritas, t.created_at, c.nama AS kategori
         FROM tickets t JOIN categories c ON c.id=t.category_id
         WHERE t.user_id=$uid
         ORDER BY t.created_at DESC LIMIT 5");
} else {
    // Statistik publik (belum login / admin)
    $totalTiket = (int) mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM tickets"))['c'];
    $selesai    = (int) mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM tickets WHERE status='Selesai'"))['c'];
    $recent = mysqli_query($konek,
        "SELECT t.judul, t.status, t.prioritas, t.created_at, c.nama AS kategori
         FROM tickets t JOIN categories c ON c.id=t.category_id
         ORDER BY t.created_at DESC LIMIT 5");
}
$kategori = (int) mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM categories"))['c'];
$pengguna = (int) mysqli_fetch_assoc(mysqli_query($konek, "SELECT COUNT(*) c FROM users WHERE role='warga'"))['c'];

$pageTitle = "Beranda";
include __DIR__ . '/includes/header.php';
?>
<?php if (isset($_GET['loggedout'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    Anda berhasil logout.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<div class="hero mb-4 fade-in-up">
  <h1 class="display-5 fw-bold"><i class="bi bi-megaphone"></i> E-Lapor Masyarakat</h1>
  <p class="lead mb-3">Sampaikan pengaduan, keluhan, atau aspirasi Anda terhadap fasilitas &amp; layanan publik. Kami tindak lanjuti.</p>
  <?php if (!is_logged_in()): ?>
    <a href="register.php" class="btn btn-light btn-lg me-2"><i class="bi bi-person-plus"></i> Daftar Sekarang</a>
    <a href="login.php" class="btn btn-outline-light btn-lg">Login</a>
  <?php else: ?>
    <a href="user/buat_tiket.php" class="btn btn-light btn-lg"><i class="bi bi-plus-circle"></i> Buat Pengaduan</a>
  <?php endif; ?>
</div>

<div class="row g-3 mb-4 stagger">
  <div class="col-6 col-md-3"><div class="card stat-card text-center p-3"><div class="num text-primary"><?= (int)$totalTiket ?></div><div class="lbl">Total Pengaduan</div></div></div>
  <div class="col-6 col-md-3"><div class="card stat-card text-center p-3"><div class="num text-success"><?= (int)$selesai ?></div><div class="lbl">Selesai Ditangani</div></div></div>
  <div class="col-6 col-md-3"><div class="card stat-card text-center p-3"><div class="num text-info"><?= (int)$kategori ?></div><div class="lbl">Kategori</div></div></div>
  <div class="col-6 col-md-3"><div class="card stat-card text-center p-3"><div class="num text-warning"><?= (int)$pengguna ?></div><div class="lbl">Warga Terdaftar</div></div></div>
</div>

<h4 class="mb-3"><?= (is_logged_in() && !is_admin()) ? 'Pengaduan Saya Terbaru' : 'Pengaduan Terbaru' ?></h4>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-light"><tr><th>Judul</th><th>Kategori</th><th>Prioritas</th><th>Status</th><th>Tanggal</th></tr></thead>
      <tbody>
        <?php $ada=false; while ($r = mysqli_fetch_assoc($recent)): $ada=true; ?>
          <tr>
            <td><?= h($r['judul']) ?></td>
            <td><?= h($r['kategori']) ?></td>
            <td><span class="badge badge-prio-<?= h($r['prioritas']) ?>"><?= h($r['prioritas']) ?></span></td>
            <td><span class="badge badge-status-<?= h($r['status']) ?>"><?= h($r['status']) ?></span></td>
            <td><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
          </tr>
        <?php endwhile; if (!$ada): ?>
          <tr><td colspan="5" class="text-center text-muted">Belum ada pengaduan.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="row g-3 mt-4">
  <div class="col-md-4">
    <div class="card p-3 h-100">
      <h5><i class="bi bi-1-circle text-primary"></i> Daftar / Login</h5>
      <p class="text-muted small mb-0">Buat akun warga untuk dapat mengirim pengaduan.</p>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3 h-100">
      <h5><i class="bi bi-2-circle text-primary"></i> Kirim Pengaduan</h5>
      <p class="text-muted small mb-0">Isi formulir + lampirkan foto lokasi kejadian.</p>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3 h-100">
      <h5><i class="bi bi-3-circle text-primary"></i> Pantau Status</h5>
      <p class="text-muted small mb-0">Admin memproses &amp; memberi komentar; Anda menerima update.</p>
    </div>
  </div>
</div>

<!-- Kategori populer -->
<div class="mt-4">
  <h5 class="mb-3 section-title"><i class="bi bi-tags"></i> Kategori Pengaduan</h5>
  <div class="row g-2">
    <?php
    $catRes = mysqli_query($konek, "SELECT nama, icon FROM categories ORDER BY nama");
    while ($c = mysqli_fetch_assoc($catRes)): ?>
      <div class="col-6 col-md-3 col-lg-2">
        <div class="card text-center p-2 h-100">
          <i class="bi <?= h($c['icon']) ?> fs-4 text-primary"></i>
          <div class="small mt-1"><?= h($c['nama']) ?></div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>