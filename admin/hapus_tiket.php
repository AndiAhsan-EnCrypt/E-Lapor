<?php
// admin/hapus_tiket.php - Hapus pengaduan (admin only)
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) { header("Location: kelola_tiket.php"); exit; }

// Hapus file lampiran dari disk
$att = mysqli_query($konek, "SELECT file_path FROM ticket_attachments WHERE ticket_id=$id");
while ($a = mysqli_fetch_assoc($att)) {
    $full = __DIR__ . '/../' . $a['file_path'];
    if (is_file($full)) @unlink($full);
}

// Hapus tiket (cascade hapus komentar & lampiran di DB)
mysqli_query($konek, "DELETE FROM tickets WHERE id=$id");

header("Location: kelola_tiket.php?deleted=1");
exit;