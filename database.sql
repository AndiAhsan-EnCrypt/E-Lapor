-- ============================================
-- DATABASE: e_lapor (Sistem Pengaduan Masyarakat)
-- ============================================
-- Cara pakai:
-- 1. Buka phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Buat database baru bernama "e_lapor" (collation utf8mb4_unicode_ci)
-- 3. Import file ini (tab Import) atau paste di tab SQL
-- ============================================

CREATE DATABASE IF NOT EXISTS e_lapor
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE e_lapor;

-- -------------------------
-- TABEL: users (warga & admin)
-- -------------------------
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,        -- disimpan dengan password_hash()
    role        ENUM('warga','admin') NOT NULL DEFAULT 'warga',
    telepon     VARCHAR(20) DEFAULT NULL,
    alamat      VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------
-- TABEL: categories (kategori pengaduan)
-- -------------------------
CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(60) NOT NULL UNIQUE,
    deskripsi   VARCHAR(255) DEFAULT NULL,
    icon        VARCHAR(40) DEFAULT 'bi-exclamation-circle',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------
-- TABEL: tickets (pengaduan)
-- -------------------------
CREATE TABLE tickets (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    category_id  INT NOT NULL,
    judul        VARCHAR(150) NOT NULL,
    deskripsi    TEXT NOT NULL,
    lokasi       VARCHAR(255) DEFAULT NULL,
    prioritas    ENUM('Rendah','Sedang','Tinggi','Darurat') NOT NULL DEFAULT 'Sedang',
    status       ENUM('Baru','Diproses','Selesai','Ditolak') NOT NULL DEFAULT 'Baru',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -------------------------
-- TABEL: ticket_comments (komentar admin/warga)
-- -------------------------
CREATE TABLE ticket_comments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT NOT NULL,
    user_id     INT NOT NULL,
    komentar    TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------
-- TABEL: ticket_attachments (lampiran foto)
-- -------------------------
CREATE TABLE ticket_attachments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT NOT NULL,
    file_path   VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------
-- DATA AWAL: kategori
-- -------------------------
INSERT INTO categories (nama, deskripsi, icon) VALUES
('Jalan & Trotoar', 'Lubang, rusak, banjir di jalan/trotoar', 'bi-cone-striped'),
('Sampah & Kebersihan', 'Penumpukan sampah, TPS penuh, pencemaran', 'bi-trash'),
('Penerangan', 'Lampu jalan mati / kurang terang', 'bi-lightbulb'),
('Drainase & Banjir', 'Saluran air tersumbat, genangan', 'bi-cloud-rain'),
('Fasilitas Umum', 'Taman, bangku, halte, rambu rusak', 'bi-building'),
('Transportasi Publik', 'Angkutan umum, halte, trayek, tarif', 'bi-bus-front'),
('Air Bersih & PDAM', 'Air mati, keruh, bocor, tagihan', 'bi-droplet'),
('Listrik & PLN', 'Pemadaman, tiang listrik, meteran', 'bi-lightning'),
('Keamanan & Ketertiban', 'Pencurian, tawuran, premanisme, CCTV', 'bi-shield-check'),
('Kesehatan', 'Puskesmas, posyandu, ambulans, vaksin', 'bi-hospital'),
('Pendidikan', 'Sekolah, beasiswa, fasilitas belajar', 'bi-book'),
('Lingkungan & Taman', 'Pohon tumbang, polusi, ruang hijau', 'bi-tree'),
('Perizinan & Administrasi', 'KTP, KK, akta, surat menyurat', 'bi-file-earmark-text'),
('Infrastruktur Jembatan', 'Jembatan rusak, ambruk, karat', 'bi-signpost-2'),
('Pasar & Perdagangan', 'Pasar kotor, pedagang liar, harga', 'bi-shop'),
('Bencana Alam', 'Gempa, banjir, longsor, kebakaran', 'bi-exclamation-triangle'),
('Telekomunikasi & Internet', 'Sinyal lemah, tower, kabel putus', 'bi-wifi'),
('Hewan Liar / Ternak', 'Hewan berkeliaran, gigitan, gangguan', 'bi-bug'),
('Lainnya', 'Pengaduan di luar kategori di atas', 'bi-three-dots');

-- -------------------------
-- DATA AWAL: akun admin default
-- password: password
-- -------------------------
INSERT INTO users (nama, email, password, role) VALUES
('Administrator', 'admin@elapor.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- -------------------------
-- DATA AWAL: akun warga contoh
-- password: password
-- -------------------------
INSERT INTO users (nama, email, password, role, telepon, alamat) VALUES
('Budi Santoso', 'budi@contoh.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warga', '081234567890', 'Jl. Merdeka No. 10');

-- contoh tiket
INSERT INTO tickets (user_id, category_id, judul, deskripsi, lokasi, prioritas, status) VALUES
(2, 1, 'Lubang besar di Jl. Sudirman', 'Terdapat lubang besar di tengah jalan yang berbahaya bagi pengendara motor.', 'Jl. Sudirman km 2', 'Tinggi', 'Diproses'),
(2, 2, 'Sampah menumpuk di TPS Pasar', 'Sampah sudah 3 hari tidak diangkut, bau menyengat.', 'TPS Pasar Induk', 'Sedang', 'Baru');
