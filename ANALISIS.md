# Analisis & Rencana Peningkatan - E-Lapor

## Yang sudah diimplementasikan
1. Admin self-register via `admin_register.php` (butuh kode rahasia `ADMIN_SECRET_CODE`)
2. Prioritas ditentukan oleh admin (warga tidak memilih prioritas; default Sedang)
3. Notifikasi email (PHPMailer jika terinstall, fallback mail()) saat status berubah
4. Keamanan: session_regenerate_id, .htaccess di uploads (blokir eksekusi PHP), upload validasi ekstensi

## Kondisi/risiko yang diantisipasi
- Brute force login -> tambahkan rate limiting / captcha (belum)
- Upload file besar -> batasi ukuran di php.ini (upload_max_filesize) & validasi di PHP
- XSS -> sudah pakai htmlspecialchars (h()) di output
- SQL injection -> prepared statements dipakai
- Session fixation -> session_regenerate_id (sudah)
- File upload berbahaya -> validasi ekstensi + .htaccess (sudah); tambahkan validasi MIME type
- Email tidak terkirim di hosting gratis (mail() diblokir) -> gunakan SMTP (PHPMailer + App Password)
- Error DB terekspos -> di produksi, matikan display_errors di php.ini

## Saran peningkatan lanjutan (opsional)
- Pagination daftar tiket
- Export laporan ke CSV/PDF (admin)
- Dashboard grafik (Chart.js)
- Riwayat perubahan status (audit log)
- Lupa password via email
- Captcha di login/register
- Validasi MIME type upload
- Soft delete tiket
