# E-Lapor Masyarakat — Sistem Pengaduan Masyarakat

Project UAS Pemrograman Web. Website dinamis (PHP + MySQL) yang memungkinkan warga mengirim pengaduan fasilitas/layanan publik, dan admin menindaklanjuti (ubah status + komentar).

## Struktur Folder
- `config/koneksi.php` — konfigurasi database
- `includes/` — header, footer, auth helper
- `user/` — halaman warga (dashboard, buat tiket, detail)
- `admin/` — halaman admin (dashboard, kelola tiket, kategori)
- `assets/` — CSS, JS, gambar
- `uploads/` — lampiran foto pengaduan
- `database.sql` — skema + data awal

## Cara Menjalankan (lokal - XAMPP)
1. Copy folder `e-lapor` ke `C:\xampp\htdocs\`
2. Jalankan Apache & MySQL di XAMPP Control Panel
3. Buka phpMyAdmin → buat database `e_lapor` → import `database.sql`
4. Akses `http://localhost/e-lapor/`

## Akun Demo
- Admin: `admin@elapor.id` / `password`
- Warga: `budi@contoh.id` / `password`

## Deploy (agar bisa diakses orang lain)
Gunakan hosting gratis dengan PHP+MySQL: InfinityFree, 000webhost, atau Hostinger. Upload semua file + import database.sql, lalu sesuaikan `config/koneksi.php`.

## Fitur
- Registrasi & login (warga & admin) dengan password_hash
- CRUD pengaduan (judul, deskripsi, kategori, prioritas, lokasi, lampiran foto)
- Tracking status: Baru → Diproses → Selesai / Ditolak
- Komentar admin & warga
- Dashboard statistik admin
- Filter tiket berdasarkan status
- CRUD kategori
- Responsif (Bootstrap 5)