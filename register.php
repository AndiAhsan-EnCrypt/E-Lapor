<?php
// register.php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/auth.php';

$err = ''; $ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if ($nama==='' || $email==='' || $pass==='') $err = "Nama, email, dan password wajib diisi.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = "Format email tidak valid.";
    elseif (strlen($pass) < 6) $err = "Password minimal 6 karakter.";
    elseif ($pass !== $pass2) $err = "Konfirmasi password tidak cocok.";
    else {
        // cek email duplikat
        $stmt = mysqli_prepare($konek, "SELECT id FROM users WHERE email=?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_get_result($stmt)->num_rows > 0) {
            $err = "Email sudah terdaftar.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($konek, "INSERT INTO users (nama,email,password,role,telepon,alamat) VALUES (?,?,?,'warga',?,?)");
            mysqli_stmt_bind_param($stmt, "sssss", $nama, $email, $hash, $telepon, $alamat);
            mysqli_stmt_execute($stmt);
            $ok = "Registrasi berhasil. Silakan login.";
        }
    }
}

$pageTitle = "Daftar";
include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card p-4">
      <h3 class="mb-3 text-center"><i class="bi bi-person-plus"></i> Daftar Warga</h3>
      <?php if ($err): ?><div class="alert alert-danger"><?= h($err) ?></div><?php endif; ?>
      <?php if ($ok): ?><div class="alert alert-success"><?= h($ok) ?> <a href="login.php">Login</a></div><?php endif; ?>
      <form method="post">
        <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text" name="nama" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Telepon</label><input type="text" name="telepon" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" class="form-control" rows="2"></textarea></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Konfirmasi Password</label><input type="password" name="password2" class="form-control" required></div>
        <button class="btn btn-primary w-100">Daftar</button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>