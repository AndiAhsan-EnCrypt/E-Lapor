<?php
// user/buat_tiket.php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (is_admin()) { header("Location: ../admin/dashboard.php"); exit; }

$err = ''; $ok = '';
$kategoriList = mysqli_query($konek, "SELECT * FROM categories ORDER BY nama");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int) current_user()['id'];
    $cat = (int)($_POST['category_id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $desk = trim($_POST['deskripsi'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    // Prioritas ditentukan oleh admin yang menangani laporan (default Sedang)
    $prio = 'Sedang';

    if ($judul==='' || $desk==='' || $cat===0) {
        $err = "Kategori, judul, dan deskripsi wajib diisi.";
    } else {
        $stmt = mysqli_prepare($konek, "INSERT INTO tickets (user_id,category_id,judul,deskripsi,lokasi,prioritas) VALUES (?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "iissss", $uid, $cat, $judul, $desk, $lokasi, $prio);
        mysqli_stmt_execute($stmt);
        $tid = mysqli_insert_id($konek);

        // upload lampiran (opsional, bisa banyak) - dengan validasi MIME & ukuran
        $maxSize = 2 * 1024 * 1024; // 2 MB per file
        $allowedExt = ['jpg','jpeg','png','gif','webp'];
        $allowedMime = ['image/jpeg','image/png','image/gif','image/webp'];
        $maxFiles = 5;
        $uploadedCount = 0;
        if (!empty($_FILES['lampiran']['name'][0])) {
            $dir = __DIR__ . '/../uploads/';
            foreach ($_FILES['lampiran']['tmp_name'] as $i => $tmp) {
                if ($uploadedCount >= $maxFiles) break;
                if ($_FILES['lampiran']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $ext = strtolower(pathinfo($_FILES['lampiran']['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt)) continue;
                if ($_FILES['lampiran']['size'][$i] > $maxSize) continue;
                // Validasi MIME type via finfo
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($tmp);
                if (!in_array($mime, $allowedMime)) continue;
                $newName = 'tiket_' . $tid . '_' . time() . '_' . $i . '.' . $ext;
                if (move_uploaded_file($tmp, $dir . $newName)) {
                    $path = 'uploads/' . $newName;
                    $st = mysqli_prepare($konek, "INSERT INTO ticket_attachments (ticket_id,file_path) VALUES (?,?)");
                    mysqli_stmt_bind_param($st, "is", $tid, $path);
                    mysqli_stmt_execute($st);
                    $uploadedCount++;
                }
            }
        }
        $ok = "Pengaduan berhasil dikirim dengan nomor #$tid.";
    }
}

$pageTitle = "Buat Pengaduan";
include __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-3">Buat Pengaduan Baru</h3>
<?php if ($err): ?><div class="alert alert-danger"><?= h($err) ?></div><?php endif; ?>
<?php if ($ok): ?><div class="alert alert-success"><?= h($ok) ?> <a href="dashboard.php">Kembali ke dashboard</a></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card p-4">
  <div class="mb-3">
    <label class="form-label">Kategori</label>
    <select name="category_id" class="form-select" required>
      <option value="">-- Pilih Kategori --</option>
      <?php while ($k = mysqli_fetch_assoc($kategoriList)): ?>
        <option value="<?= (int)$k['id'] ?>"><?= h($k['nama']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>
  <div class="mb-3">
    <label class="form-label">Judul Pengaduan</label>
    <input type="text" name="judul" class="form-control" maxlength="150" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Deskripsi Lengkap</label>
    <textarea name="deskripsi" class="form-control" rows="5" required></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">Lokasi Kejadian</label>
    <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Jl. Sudirman km 2">
  </div>
  <div class="alert alert-info small">
    <i class="bi bi-info-circle"></i> Tingkat prioritas akan ditentukan oleh admin yang menangani laporan Anda.
  </div>
  <div class="mb-3">
    <label class="form-label">Lampiran Foto (opsional, bisa banyak)</label>
    <input type="file" name="lampiran[]" id="lampiran" class="form-control" accept="image/*" multiple>
    <img id="previewImg" class="upload-preview" style="display:none;" alt="preview">
  </div>
  <button class="btn btn-primary"><i class="bi bi-send"></i> Kirim Pengaduan</button>
  <a href="dashboard.php" class="btn btn-secondary">Batal</a>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>