<?php
// config/koneksi.php
// Konfigurasi koneksi ke database MySQL.
// Sesuaikan nama host/user/password/database dengan lingkungan Anda.

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "e_lapor";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $konek = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    mysqli_set_charset($konek, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Jika koneksi gagal, tampilkan pesan (jangan tampilkan di produksi)
    http_response_code(500);
    exit("Koneksi ke basis data gagal: " . $e->getMessage());
}
