-- Tambah kategori baru ke database yang sudah ada (tanpa reset data)
-- Jalankan di phpMyAdmin -> tab SQL (database e_lapor sudah dipilih)

INSERT IGNORE INTO categories (nama, deskripsi, icon) VALUES
('Transportasi Publik', 'Angkutan umum, halte, trayek, tarif', 'bi-bus-front'),
('Air Bersih & PDAM', 'Air mati, keruh, bocor, tagihan', 'bi-droplet'),
('Listrik & PLN', 'Pemadaman, tiang listrik, meteran', 'bi-lightning'),
('Keamanan & Ketertiban', 'Pencurian, tawuran, premanisme, CCTV', 'bi-shield-check'),
('Kesehatan', 'Puskesmas, posyandu, ambulans, vaksin', 'bi-hospital'),
('Pendidikan', 'Sekolah, beasiswa, fasilitas belajar', 'bi-book'),
('Lingkungan & Taman', 'Pohon tumbang, polusi, ruang hijau', 'bi-tree'),
('Perizinan & Administrasi', 'KTP, KK, akta, surat menyurat', 'bi-file-earmark-text'),
('Infrastruktur Jembatan', 'Jembatan rusak, ambruk, karat', 'bi-bridge'),
('Pasar & Perdagangan', 'Pasar kotor, pedagang liar, harga', 'bi-shop'),
('Bencana Alam', 'Gempa, banjir, longsor, kebakaran', 'bi-exclamation-triangle'),
('Telekomunikasi & Internet', 'Sinyal lemah, tower, kabel putus', 'bi-wifi'),
('Hewan Liar / Ternak', 'Hewan berkeliaran, gigitan, gangguan', 'bi-bug');
