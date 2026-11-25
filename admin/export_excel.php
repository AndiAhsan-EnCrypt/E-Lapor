<?php
// admin/export_excel.php - Export laporan ke Excel (.xls) dengan filter & format rapi
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// ---- Filter dari query string ----
$status    = $_GET['status'] ?? '';
$kategori  = (int)($_GET['kategori'] ?? 0);
$dateFrom  = trim($_GET['date_from'] ?? '');
$dateTo    = trim($_GET['date_to'] ?? '');
$withComments = isset($_GET['with_comments']) && $_GET['with_comments'] === '1';

$where = "WHERE 1=1";
if ($status !== '' && in_array($status, ['Baru','Diproses','Selesai','Ditolak'])) {
    $where .= " AND t.status='" . mysqli_real_escape_string($konek, $status) . "'";
}
if ($kategori > 0) {
    $where .= " AND t.category_id=" . $kategori;
}
if ($dateFrom !== '') {
    $where .= " AND DATE(t.created_at) >= '" . mysqli_real_escape_string($konek, $dateFrom) . "'";
}
if ($dateTo !== '') {
    $where .= " AND DATE(t.created_at) <= '" . mysqli_real_escape_string($konek, $dateTo) . "'";
}

$res = mysqli_query($konek,
    "SELECT t.id, t.judul, t.deskripsi, t.lokasi, t.prioritas, t.status, t.created_at, t.updated_at,
            u.nama AS pelapor, u.email, c.nama AS kategori, c.id AS category_id
     FROM tickets t JOIN users u ON u.id=t.user_id JOIN categories c ON c.id=t.category_id
     $where ORDER BY t.created_at DESC");

// ---- Statistik ringkasan ----
$statRes = mysqli_query($konek, "SELECT status, COUNT(*) c FROM tickets t $where GROUP BY status");
$statMap = ['Baru'=>0,'Diproses'=>0,'Selesai'=>0,'Ditolak'=>0];
while ($s = mysqli_fetch_assoc($statRes)) { $statMap[$s['status']] = (int)$s['c']; }
$totalFiltered = array_sum($statMap);

$filename = "laporan_pengaduan_" . date('Ymd_His') . ".xls";
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$esc = function($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta charset="UTF-8">';
echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Laporan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
echo '<style>';
echo 'table{border-collapse:collapse;} th,td{border:1px solid #999;padding:6px;font-family:Calibri;font-size:11pt;vertical-align:top;} ';
echo 'th{background:#4f46e5;color:#fff;font-weight:bold;text-align:center;} ';
echo 'tr:nth-child(even){background:#f2f2f2;} ';
echo '.title{font-size:14pt;font-weight:bold;color:#4f46e5;} ';
echo '.sub{font-size:10pt;color:#555;} ';
echo '.stat th{background:#06b6d4;} ';
echo '.comment{background:#fffbeb;font-style:italic;} ';
echo '</style></head><body>';

// Judul
echo '<table>';
echo '<tr><td colspan="11" class="title">LAPORAN PENGADUAN MASYARAKAT - E-LAPOR</td></tr>';
echo '<tr><td colspan="11" class="sub">Diekspor: ' . date('d/m/Y H:i:s') . '</td></tr>';
$filterInfo = [];
if ($status !== '') $filterInfo[] = 'Status: ' . $status;
if ($kategori > 0) $filterInfo[] = 'Kategori ID: ' . $kategori;
if ($dateFrom !== '') $filterInfo[] = 'Dari: ' . $dateFrom;
if ($dateTo !== '') $filterInfo[] = 'Sampai: ' . $dateTo;
if ($filterInfo) echo '<tr><td colspan="11" class="sub">Filter: ' . implode(' | ', $filterInfo) . '</td></tr>';
echo '<tr></tr>';

// Ringkasan
echo '<tr><td colspan="11" style="font-weight:bold;">RINGKASAN</td></tr>';
echo '<tr class="stat"><th>Status</th><th>Jumlah</th><th colspan="9"></th></tr>';
foreach (['Baru','Diproses','Selesai','Ditolak'] as $s) {
    echo '<tr><td>' . $s . '</td><td style="text-align:center;">' . $statMap[$s] . '</td><td colspan="9"></td></tr>';
}
echo '<tr><td style="font-weight:bold;">Total</td><td style="text-align:center;font-weight:bold;">' . $totalFiltered . '</td><td colspan="9"></td></tr>';
echo '<tr></tr>';

// Header tabel
echo '<tr>';
foreach (['ID','Judul','Deskripsi','Lokasi','Prioritas','Status','Kategori','Pelapor','Email','Dibuat','Diperbarui'] as $h) {
    echo '<th>' . $esc($h) . '</th>';
}
echo '</tr>';

// Data
while ($row = mysqli_fetch_assoc($res)) {
    echo '<tr>';
    echo '<td style="text-align:center;">' . (int)$row['id'] . '</td>';
    echo '<td>' . $esc($row['judul']) . '</td>';
    echo '<td>' . $esc($row['deskripsi']) . '</td>';
    echo '<td>' . $esc($row['lokasi']) . '</td>';
    echo '<td style="text-align:center;">' . $esc($row['prioritas']) . '</td>';
    echo '<td style="text-align:center;">' . $esc($row['status']) . '</td>';
    echo '<td>' . $esc($row['kategori']) . '</td>';
    echo '<td>' . $esc($row['pelapor']) . '</td>';
    echo '<td>' . $esc($row['email']) . '</td>';
    echo '<td>' . $esc($row['created_at']) . '</td>';
    echo '<td>' . $esc($row['updated_at']) . '</td>';
    echo '</tr>';

    if ($withComments) {
        $cRes = mysqli_query($konek, "SELECT cm.komentar, cm.created_at, u.nama, u.role FROM ticket_comments cm JOIN users u ON u.id=cm.user_id WHERE cm.ticket_id=" . (int)$row['id'] . " ORDER BY cm.created_at ASC");
        while ($c = mysqli_fetch_assoc($cRes)) {
            echo '<tr class="comment">';
            echo '<td colspan="2" style="text-align:right;font-weight:bold;">↳ Komentar (' . $esc($c['role']) . ')</td>';
            echo '<td colspan="6">' . $esc($c['komentar']) . '</td>';
            echo '<td colspan="2">' . $esc($c['nama']) . '</td>';
            echo '<td>' . $esc($c['created_at']) . '</td>';
            echo '</tr>';
        }
    }
}
echo '</table></body></html>';
exit;