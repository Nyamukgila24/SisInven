-- =====================================================================
-- SISINVEN - Sistem Manajemen Inventaris Barang
-- File Skema Database
-- =====================================================================
-- Cara pakai:
-- 1. Buka phpMyAdmin (biasanya di http://localhost/phpmyadmin)
-- 2. Buat database baru bernama "sisinven"
-- 3. Klik database "sisinven", buka tab "Import"
-- 4. Pilih file ini, lalu klik "Go" / "Kirim"
-- =====================================================================

CREATE DATABASE IF NOT EXISTS sisinven CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sisinven;

-- ---------------------------------------------------------------------
-- TABEL: users
-- Menyimpan akun pegawai yang boleh login. Semua akun setingkat
-- (single role), sesuai kebutuhan: tidak ada super admin vs staf.
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL MASTER: kategori
-- kode_kategori diisi manual oleh pengguna saat menambah kategori
-- (contoh: 'Umum' = 1, 'Jaringan' = 2), sesuai contoh pada dokumen FRD.
-- ---------------------------------------------------------------------
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_kategori INT NOT NULL UNIQUE,
    nama_kategori VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL MASTER: subkategori
-- Wajib berelasi ke Kategori Induk (cascading dropdown).
-- kode_subkategori dibuat OTOMATIS oleh sistem = urutan subkategori
-- di dalam kategori induknya (1, 2, 3, ...), supaya pengguna tidak
-- perlu memikirkan penomoran kode sendiri.
-- ---------------------------------------------------------------------
CREATE TABLE subkategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT NOT NULL,
    kode_subkategori INT NOT NULL,
    nama_subkategori VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE RESTRICT,
    UNIQUE KEY unik_kode_per_kategori (kategori_id, kode_subkategori)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL MASTER: merek
-- Daftar merek terstandardisasi (dropdown, tidak boleh ketik manual).
-- ---------------------------------------------------------------------
CREATE TABLE merek (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_merek VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL MASTER: lokasi
-- Ruangan / rak tempat barang disimpan.
-- ---------------------------------------------------------------------
CREATE TABLE lokasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_ruangan VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL MASTER: nama_peralatan
-- Standardisasi nama peralatan (mis. "Komputer", "Printer").
-- kode_peralatan dibuat OTOMATIS = urutan penambahan peralatan
-- (global, karena master ini tidak berelasi ke kategori tertentu).
-- ---------------------------------------------------------------------
CREATE TABLE nama_peralatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_peralatan INT NOT NULL UNIQUE,
    nama_peralatan VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: barang
-- Data transaksi utama. kode_barang di-generate otomatis oleh sistem
-- (lihat includes/functions.php -> generateKodeBarang()), TIDAK BOLEH
-- diisi manual oleh pengguna, sesuai aturan pada dokumen FRD bagian 3.2.
--
-- nomor_urut dihitung per kombinasi kategori + subkategori + peralatan,
-- sehingga menghasilkan kode seperti 1.1.1.1 lalu 1.1.1.2 dst.
-- ---------------------------------------------------------------------
CREATE TABLE barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang VARCHAR(50) NOT NULL UNIQUE,
    kategori_id INT NOT NULL,
    subkategori_id INT NOT NULL,
    merek_id INT NOT NULL,
    lokasi_id INT NOT NULL,
    nama_peralatan_id INT NOT NULL,
    nomor_urut INT NOT NULL,
    spesifikasi TEXT NOT NULL,
    nomor_inventaris_kantor VARCHAR(100) NULL,
    kondisi ENUM('Baik', 'Rusak', 'Sedang Diperbaiki') NOT NULL DEFAULT 'Baik',
    user_last_edit VARCHAR(100) NOT NULL,
    timestamp_last_edit DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE RESTRICT,
    FOREIGN KEY (subkategori_id) REFERENCES subkategori(id) ON DELETE RESTRICT,
    FOREIGN KEY (merek_id) REFERENCES merek(id) ON DELETE RESTRICT,
    FOREIGN KEY (lokasi_id) REFERENCES lokasi(id) ON DELETE RESTRICT,
    FOREIGN KEY (nama_peralatan_id) REFERENCES nama_peralatan(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- DATA CONTOH (boleh dihapus) supaya aplikasi tidak kosong saat
-- pertama kali dicoba.
-- ---------------------------------------------------------------------

-- Akun contoh: username "admin", password "admin123"
-- (hash di bawah dibuat dengan password_hash('admin123', PASSWORD_DEFAULT))
INSERT INTO users (username, email, password, nama_lengkap) VALUES
('admin', 'admin@blksurabaya.go.id', '$2y$10$.CuJiPr5j0sd/zGXjuIhe./ZCc.q9GDHDzCSmcUyTb3sv7JMTTRLC', 'Administrator BLK');

INSERT INTO kategori (kode_kategori, nama_kategori) VALUES
(1, 'Umum'),
(2, 'Jaringan');

INSERT INTO subkategori (kategori_id, kode_subkategori, nama_subkategori) VALUES
(1, 1, 'Komputer & Laptop'),
(1, 2, 'Furnitur'),
(2, 1, 'Perangkat Jaringan');

INSERT INTO merek (nama_merek) VALUES ('HP'), ('Lenovo'), ('TP-Link'), ('Tanpa Merek');

INSERT INTO lokasi (nama_ruangan) VALUES
('Ruang Lab Komputer 1'),
('Gudang (Rak 1)'),
('Gudang (Rak 2)');

INSERT INTO nama_peralatan (kode_peralatan, nama_peralatan) VALUES
(1, 'Komputer'),
(2, 'Kursi'),
(3, 'Router');
