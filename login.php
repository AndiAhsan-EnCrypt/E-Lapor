<?php
// login.php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/auth.php';

$err = '';
// Rate limiting login (max 5 percobaan, lock 15 menit)
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['login_lock_until'])) $_SESSION['login_lock_until'] = 0;

$locked = (time() < $_SESSION['login_lock_until']);
if ($locked) {
    $remaining = ceil(($_SESSION['login_lock_until'] - time()) / 60);
    $err = "Terlalu banyak percobaan login. Coba lagi dalam $remaining menit.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked) {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $stmt = mysqli_prepare($konek, "SELECT id,nama,email,password,role FROM users WHERE email=?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $u = mysqli_fetch_assoc($res);
    if ($u && password_verify($pass, $u['password'])) {
        // Reset counter on success
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_lock_until'] = 0;
        session_regenerate_id(true); // cegah session fixation
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['user'] = ['id'=>$u['id'],'nama'=>$u['nama'],'email'=>$u['email'],'role'=>$u['role']];
        header("Location: " . ($u['role']==='admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
        exit;
    } else {
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['login_lock_until'] = time() + (15 * 60);
            $err = "Terlalu banyak percobaan. Akun dikunci 15 menit.";
        } else {
            $left = 5 - $_SESSION['login_attempts'];
            $err = "Email atau password salah. Sisa percobaan: $left.";
        }
    }
}

$pageTitle = "Login";
include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card p-4">
      <h3 class="mb-3 text-center"><i class="bi bi-box-arrow-in-right"></i> Login</h3>
      <?php if ($err): ?><div class="alert alert-danger"><?= h($err) ?></div><?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Masuk</button>
      </form>
      <p class="text-center mt-3 small">Belum punya akun? <a href="register.php">Daftar</a></p>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>