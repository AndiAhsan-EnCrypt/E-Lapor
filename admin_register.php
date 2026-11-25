<?php
// admin_register.php - Pendaftaran admin mandiri (butuh kode rahasia)
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/auth.php';

// Kode rahasia admin. UBAH nilai ini sebelum deploy! Jangan commit ke repo publik.
define('ADMIN_SECRET_CODE', 'ELAPOR-ADMIN-2026');

$err=''; $ok='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $code = trim($_POST['kode'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if ($code !== ADMIN_SECRET_CODE) $err = "Kode rahasia admin salah.";
    elseif ($nama==='' || $email==='' || $pass==='') $err = "Semua field wajib diisi.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = "Format email tidak valid.";
    elseif (strlen($pass) < 8) $err = "Password minimal 8 karakter.";
    elseif ($pass !== $pass2) $err = "Konfirmasi password tidak cocok.";
    else {
        $stmt = mysqli_prepare($konek, "SELECT id FROM users WHERE email=?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_get_result($stmt)->num_rows > 0) {
            $err = "Email sudah terdaftar.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $st = mysqli_prepare($konek, "INSERT INTO users (nama,email,password,role) VALUES (?,?,?,'admin')");
            mysqli_stmt_bind_param($st, "sss", $nama, $email, $hash);
            mysqli_stmt_execute($st);
            $ok = "Akun admin berhasil dibuat. Silakan login.";
        }
    }
}

$pageTitle = "Daftar Admin";
include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card p-4">
      <h3 class="mb-3 text-center"><i class="bi bi-shield-lock"></i> Daftar Admin</h3>
      <div class="alert alert-warning small">Memerlukan kode rahasia admin. Hubungi pengelola sistem.</div>
      <?php if ($err): ?><div class="alert alert-danger"><?= h($err) ?></div><?php endif; ?>
      <?php if ($ok): ?><div class="alert alert-success"><?= h($ok) ?> <a href="login.php">Login</a></div><?php endif; ?>
      <form method="post">
        <div class="mb-3"><label class="form-label">Kode Rahasia Admin</label><input type="password" name="kode" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text" name="nama" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Password (min 8 karakter)</label><input type="password" name="password" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Konfirmasi Password</label><input type="password" name="password2" class="form-control" required></div>
        <button class="btn btn-primary w-100">Daftar sebagai Admin</button>
      </form>
      <p class="text-center mt-3 small"><a href="login.php">Kembali ke Login</a></p>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>