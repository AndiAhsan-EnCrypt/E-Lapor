<?php
// logout.php - dengan konfirmasi
require_once __DIR__ . '/includes/auth.php';

// Jika user menekan "Ya, Logout" (POST) -> lakukan logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    session_destroy();
    header("Location: index.php?loggedout=1");
    exit;
}

// Jika belum login, langsung ke beranda
if (!is_logged_in()) {
    header("Location: index.php");
    exit;
}

$pageTitle = "Konfirmasi Logout";
include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card p-4 text-center">
      <i class="bi bi-box-arrow-right display-4 text-danger"></i>
      <h4 class="mt-3">Konfirmasi Logout</h4>
      <p class="text-muted">Apakah Anda yakin ingin keluar dari akun <strong><?= h(current_user()['nama']) ?></strong>?</p>
      <form method="post" class="d-flex justify-content-center gap-2">
        <button name="confirm_logout" value="1" class="btn btn-danger">Ya, Logout</button>
        <a href="javascript:history.back()" class="btn btn-secondary">Batal</a>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>