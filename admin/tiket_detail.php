<?php
// admin/tiket_detail.php - proses tiket (ubah status, komentar)
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notify.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$t = mysqli_fetch_assoc(mysqli_query($konek, "SELECT t.*, c.nama AS kategori, u.nama AS pelapor, u.email FROM tickets t JOIN categories c ON c.id=t.category_id JOIN users u ON u.id=t.user_id WHERE t.id=$id"));
if (!$t) { http_response_code(404); exit("Pengaduan tidak ditemukan."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminId = (int) current_user()['id'];
    if (isset($_POST['ubah_status'])) {
        $status = $_POST['status'];
        $prioritas = $_POST['prioritas'] ?? $t['prioritas'];
        $allowedStatus = ['Baru','Diproses','Selesai','Ditolak'];
        $allowedPrio = ['Rendah','Sedang','Tinggi','Darurat'];
        if (in_array($status, $allowedStatus) && in_array($prioritas, $allowedPrio)) {
            $st = mysqli_prepare($konek, "UPDATE tickets SET status=?, prioritas=? WHERE id=?");
            mysqli_stmt_bind_param($st, "ssi", $status, $prioritas, $id);
            mysqli_stmt_execute($st);
            // Kirim notifikasi email ke warga (best-effort)
            notify_status_changed($t['email'], $t['pelapor'], $id, $t['judul'], $status, $prioritas);
        }
        header("Location: tiket_detail.php?id=$id"); exit;
    }
    if (isset($_POST['komentar_btn'])) {
        $k = trim($_POST['komentar'] ?? '');
        if ($k !== '') {
            $st = mysqli_prepare($konek, "INSERT INTO ticket_comments (ticket_id,user_id,komentar) VALUES (?,?,?)");
            mysqli_stmt_bind_param($st, "iis", $id, $adminId, $k);
            mysqli_stmt_execute($st);
        }
        header("Location: tiket_detail.php?id=$id"); exit;
    }
}

$attachments = mysqli_query($konek, "SELECT * FROM ticket_attachments WHERE ticket_id=$id");
$comments = mysqli_query($konek, "SELECT cm.*, u.nama, u.role FROM ticket_comments cm JOIN users u ON u.id=cm.user_id WHERE cm.ticket_id=$id ORDER BY cm.created_at ASC");

$pageTitle = "Proses Pengaduan";
include __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-1"><?= h($t['judul']) ?></h3>
<p class="text-muted small mb-3">Nomor #<?= (int)$t['id'] ?> &bull; Pelapor: <?= h($t['pelapor']) ?> (<?= h($t['email']) ?>) &bull; <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></p>

<div class="row g-3">
  <div class="col-md-8">
    <div class="card p-3 mb-3">
      <h6>Detail Pengaduan</h6>
      <p><?= nl2br(h($t['deskripsi'])) ?></p>
      <div class="row small text-muted">
        <div class="col-md-6"><strong>Kategori:</strong> <?= h($t['kategori']) ?></div>
        <div class="col-md-6"><strong>Lokasi:</strong> <?= h($t['lokasi'] ?: '-') ?></div>
        <div class="col-md-6"><strong>Prioritas:</strong> <span class="badge badge-prio-<?= h($t['prioritas']) ?>"><?= h($t['prioritas']) ?></span></div>
        <div class="col-md-6"><strong>Status saat ini:</strong> <span class="badge badge-status-<?= h($t['status']) ?>"><?= h($t['status']) ?></span></div>
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

    <div class="card p-3 mb-3">
      <h6>Ubah Status</h6>
      <form method="post" class="row g-2">
        <div class="col-md-6">
          <label class="form-label small">Status</label>
          <select name="status" class="form-select">
            <?php foreach (['Baru','Diproses','Selesai','Ditolak'] as $s): ?>
              <option value="<?= $s ?>" <?= $t['status']===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label small">Prioritas (ditentukan admin)</label>
          <select name="prioritas" class="form-select">
            <?php foreach (['Rendah','Sedang','Tinggi','Darurat'] as $p): ?>
              <option value="<?= $p ?>" <?= $t['prioritas']===$p?'selected':'' ?>><?= $p ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <button name="ubah_status" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>

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
      <form method="post" class="mt-3">
        <textarea name="komentar" class="form-control mb-2" rows="2" placeholder="Tulis komentar/tindak lanjut..."></textarea>
        <button name="komentar_btn" class="btn btn-sm btn-primary">Kirim Komentar</button>
      </form>
    </div>
  </div>
  <div class="col-md-4">
    <a href="kelola_tiket.php" class="btn btn-secondary w-100 mb-2">Kembali</a>
    <a href="hapus_tiket.php?id=<?= (int)$t['id'] ?>" class="btn btn-danger w-100"
       onclick="return confirm('Hapus pengaduan ini beserta komentar & lampirannya? Tindakan tidak bisa dibatalkan.')">
      <i class="bi bi-trash"></i> Hapus Pengaduan
    </a>
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