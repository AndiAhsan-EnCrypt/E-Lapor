<?php
// user/tiket_detail.php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$uid = (int) current_user()['id'];
$id = (int)($_GET['id'] ?? 0);
$t = mysqli_fetch_assoc(mysqli_query($konek, "SELECT t.*, c.nama AS kategori FROM tickets t JOIN categories c ON c.id=t.category_id WHERE t.id=$id AND t.user_id=$uid"));
if (!$t) { http_response_code(404); exit("Pengaduan tidak ditemukan."); }

$attachments = mysqli_query($konek, "SELECT * FROM ticket_attachments WHERE ticket_id=$id");
$comments = mysqli_query($konek, "SELECT cm.*, u.nama, u.role FROM ticket_comments cm JOIN users u ON u.id=cm.user_id WHERE cm.ticket_id=$id ORDER BY cm.created_at ASC");

// warga bisa tambah komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !is_admin()) {
    $k = trim($_POST['komentar'] ?? '');
    if ($k !== '') {
        $st = mysqli_prepare($konek, "INSERT INTO ticket_comments (ticket_id,user_id,komentar) VALUES (?,?,?)");
        mysqli_stmt_bind_param($st, "iis", $id, $uid, $k);
        mysqli_stmt_execute($st);
        header("Location: tiket_detail.php?id=$id"); exit;
    }
}

$pageTitle = "Detail Pengaduan";
include __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-1"><?= h($t['judul']) ?></h3>
<p class="text-muted small mb-3">Nomor #<?= (int)$t['id'] ?> &bull; <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></p>

<div class="row g-3">
  <div class="col-md-8">
    <div class="card p-3 mb-3">
      <h6>Deskripsi</h6>
      <p><?= nl2br(h($t['deskripsi'])) ?></p>
      <div class="row small text-muted">
        <div class="col-md-6"><strong>Kategori:</strong> <?= h($t['kategori']) ?></div>
        <div class="col-md-6"><strong>Lokasi:</strong> <?= h($t['lokasi'] ?: '-') ?></div>
        <div class="col-md-6"><strong>Prioritas:</strong> <span class="badge badge-prio-<?= h($t['prioritas']) ?>"><?= h($t['prioritas']) ?></span></div>
        <div class="col-md-6"><strong>Status:</strong> <span class="badge badge-status-<?= h($t['status']) ?>"><?= h($t['status']) ?></span></div>
      </div>
    </div>

    <?php if (mysqli_num_rows($attachments) > 0): ?>
    <div class="card p-3 mb-3">
      <h6>Lampiran Foto</h6>
      <div class="d-flex flex-wrap gap-2">
        <?php while ($a = mysqli_fetch_assoc($attachments)): ?>
          <img src="../<?= h($a['file_path']) ?>" class="thumb" alt="lampiran"
               style="cursor:pointer;" onclick="showImageModal(this.src)" title="Klik untuk memperbesar">
        <?php endwhile; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card p-3">
      <h6>Riwayat Komentar</h6>
      <?php $ada=false; while ($c = mysqli_fetch_assoc($comments)): $ada=true; ?>
        <div class="border-bottom pb-2 mb-2">
          <div class="d-flex justify-content-between">
            <strong><?= h($c['nama']) ?> <span class="badge bg-secondary"><?= h($c['role']) ?></span></strong>
            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></small>
          </div>
          <p class="mb-0 mt-1"><?= nl2br(h($c['komentar'])) ?></p>
        </div>
      <?php endwhile; if (!$ada): ?>
        <p class="text-muted small mb-0">Belum ada komentar.</p>
      <?php endif; ?>

      <?php if (!is_admin()): ?>
      <form method="post" class="mt-3">
        <textarea name="komentar" class="form-control mb-2" rows="2" placeholder="Tulis komentar..."></textarea>
        <button class="btn btn-sm btn-primary">Kirim Komentar</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-4">
    <a href="dashboard.php" class="btn btn-secondary w-100 mb-2">Kembali</a>
  </div>
</div>
<!-- Modal preview gambar (tidak pindah halaman) -->
<div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-body text-center p-0">
        <img id="imgModalSrc" src="" class="img-fluid rounded shadow" alt="preview" style="max-height:80vh;">
      </div>
    </div>
  </div>
</div>
<script>
function showImageModal(src) {
    document.getElementById('imgModalSrc').src = src;
    var m = new bootstrap.Modal(document.getElementById('imgModal'));
    m.show();
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>