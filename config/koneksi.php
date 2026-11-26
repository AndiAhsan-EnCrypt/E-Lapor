<?php
// config/koneksi.php
// Konfigurasi koneksi ke database MySQL.
// Sesuaikan nama host/user/password/database dengan lingkungan Anda.

$DB_HOST = getenv("MYSQLHOST") ?: "localhost";
$DB_USER = getenv("MYSQLUSER") ?: "root";
$DB_PASS = getenv("MYSQLPASSWORD") ?: "";
$DB_NAME = getenv("MYSQLDATABASE") ?: "e_lapor";
$DB_PORT = (int)(getenv("MYSQLPORT") ?: 3306);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $konek = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
    mysqli_set_charset($konek, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Jika koneksi gagal, tampilkan pesan (jangan tampilkan di produksi)
    http_response_code(500);
    exit("Koneksi ke basis data gagal: " . $e->getMessage());
}
