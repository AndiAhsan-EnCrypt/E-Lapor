<?php
// admin/export.php - Halaman form export laporan
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$categories = mysqli_query($konek, "SELECT id, nama FROM categories ORDER BY nama");

$pageTitle = "Export Laporan";
include __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-3"><i class="bi bi-file-earmark-excel"></i> Export Laporan Pengaduan</h3>
<p class="text-muted small">Pilih filter, lalu klik Export. Hasil berupa file Excel (.xls) rapi.</p>

<div class="card p-4">
  <form method="get" action="export_excel.php" target="_blank">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <?php $preStatus = $_GET['status'] ?? ''; ?>
        <select name="status" class="form-select">
          <option value="">Semua Status</option>
          <?php foreach (['Baru','Diproses','Selesai','Ditolak'] as $s): ?>
            <option value="<?= $s ?>" <?= $preStatus===$s?'selected':'' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Kategori</label>
        <select name="kategori" class="form-select">
          <option value="0">Semua Kategori</option>
          <?php while ($c = mysqli_fetch_assoc($categories)): ?>
            <option value="<?= (int)$c['id'] ?>"><?= h($c['nama']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Dari Tanggal</label>
        <input type="date" name="date_from" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Sampai Tanggal</label>
        <input type="date" name="date_to" class="form-control">
      </div>
      <div class="col-md-8 d-flex align-items-end">
        <div class="form-check me-4">
          <input class="form-check-input" type="checkbox" name="with_comments" value="1" id="wc">
          <label class="form-check-label" for="wc">Sertakan komentar</label>
        </div>
      </div>
    </div>
    <hr>
    <button type="submit" class="btn btn-success"><i class="bi bi-download"></i> Export Excel</button>
    <a href="kelola_tiket.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<div class="card p-3 mt-3">
  <h6>Contoh penggunaan</h6>
  <ul class="small text-muted mb-0">
    <li><strong>Laporan bulanan</strong>: isi Dari/Sampai tanggal bulan tersebut, Status "Semua", lalu Export.</li>
    <li><strong>Hanya yang selesai</strong>: Status = "Selesai", lalu Export.</li>
    <li><strong>Per kategori</strong>: pilih Kategori, lalu Export.</li>
    <li>Centang "Sertakan komentar" untuk dokumentasi tindak lanjut.</li>
  </ul>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>