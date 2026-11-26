<?php
// includes/header.php
// Header HTML + navbar. Variabel $pageTitle bisa diset sebelum include.
if (!isset($pageTitle)) $pageTitle = "E-Lapor";
require_once __DIR__ . '/auth.php';
// Cegah cache (agar logout efektif & Back button tidak menampilkan halaman lama)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Cegah cache halaman (agar setelah logout, tombol Back tidak menampilkan halaman lama) -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= h($pageTitle) ?> - E-Lapor Masyarakat</title>
    <!-- Bootstrap 5 (CDN) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css?v=11">
</head>
<body>
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$navActive = function($pages) use ($currentPage) {
    return in_array($currentPage, (array)$pages) ? 'active' : '';
};
?>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/index.php">
      <i class="bi bi-megaphone-fill"></i> E-Lapor
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
        <li class="nav-item">
          <a class="nav-link <?= $navActive('index.php') ?>" href="/index.php">
            <i class="bi bi-house-door"></i> Beranda
          </a>
        </li>
        <?php if (is_logged_in()): ?>
            <?php if (is_admin()): ?>
                <li class="nav-item">
                  <a class="nav-link <?= $navActive(['dashboard.php','kelola_tiket.php','kategori.php','tiket_detail.php','export.php']) ?>" href="/admin/dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard Admin
                  </a>
                </li>

            <?php else: ?>
                <li class="nav-item">
                  <a class="nav-link <?= $navActive('dashboard.php') ?>" href="/user/dashboard.php">
                    <i class="bi bi-grid"></i> Dashboard
                  </a>
                </li>

            <?php endif; ?>
            <li class="nav-item ms-lg-2">
              <a class="nav-link text-warning" href="/logout.php">
                <i class="bi bi-box-arrow-right"></i> Logout
              </a>
            </li>
            <li class="nav-item">
              <span class="navbar-text small text-white-50 ms-2">
                <i class="bi bi-person-circle"></i> <?= h(current_user()['nama']) ?>
              </span>
            </li>
        <?php else: ?>
            <li class="nav-item">
              <a class="nav-link <?= $navActive('login.php') ?>" href="/login.php">
                <i class="bi bi-box-arrow-in-right"></i> Login
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $navActive('register.php') ?>" href="/register.php">
                <i class="bi bi-person-plus"></i> Daftar
              </a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<?php $isHome = basename($_SERVER['PHP_SELF']) === 'index.php'; ?>
<?php if (!$isHome): ?>
<div class="container mt-3 mb-0">
  <button onclick="if (history.length > 1) history.back(); else window.location.href='/index.php';"
          class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Kembali
  </button>
</div>
<?php endif; ?>
<main class="container my-4">