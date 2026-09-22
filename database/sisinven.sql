-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 22 Sep 2026 pada 03.43
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sisinven`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang`
--

CREATE TABLE `barang` (
  `id` int(11) NOT NULL,
  `kode_barang` varchar(50) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `subkategori_id` int(11) NOT NULL,
  `merek_id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `nama_peralatan_id` int(11) NOT NULL,
  `tipe_barang` varchar(100) NOT NULL,
  `tahun_perolehan` year(4) NOT NULL,
  `foto_peralatan` varchar(255) DEFAULT NULL,
  `nomor_urut` int(11) NOT NULL,
  `spesifikasi` text NOT NULL,
  `nomor_inventaris_kantor` varchar(100) DEFAULT NULL,
  `kondisi_id` int(11) NOT NULL,
  `user_last_edit` varchar(100) NOT NULL,
  `timestamp_last_edit` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`id`, `kode_barang`, `kategori_id`, `subkategori_id`, `merek_id`, `lokasi_id`, `nama_peralatan_id`, `tipe_barang`, `tahun_perolehan`, `foto_peralatan`, `nomor_urut`, `spesifikasi`, `nomor_inventaris_kantor`, `kondisi_id`, `user_last_edit`, `timestamp_last_edit`, `created_at`) VALUES
(32, '1.1.5.2', 1, 1, 6, 1, 5, 'DVORAK', '2000', 'foto_6aab577c3c32c.png', 2, 'Layout standar terlengkap. Memiliki Alphanumeric, Function Row (F1-F12), Navigation Cluster (panah, dll), dan Numeric Keypad (Numpad) di sisi kanan.', NULL, 1, 'Admin 2', '2026-09-17 04:59:08', '2026-09-17 09:59:08'),
(33, '1.1.5.3', 1, 1, 6, 1, 5, 'DVORAK', '2020', 'foto_6aab577c3c32c.png', 3, 'Layout standar terlengkap. Memiliki Alphanumeric, Function Row (F1-F12), Navigation Cluster (panah, dll), dan Numeric Keypad (Numpad) di sisi kanan.', NULL, 3, 'Admin 2', '2026-09-17 06:57:40', '2026-09-17 09:59:08'),
(34, '1.1.5.4', 1, 1, 6, 1, 5, 'DVORAK', '2021', 'foto_6aab577c3c32c.png', 4, 'Layout standar terlengkap. Memiliki Alphanumeric, Function Row (F1-F12), Navigation Cluster (panah, dll), dan Numeric Keypad (Numpad) di sisi kanan.', NULL, 2, 'Admin 2', '2026-09-17 06:57:34', '2026-09-17 09:59:08'),
(35, '1.1.5.5', 1, 1, 6, 1, 5, 'DVORAK', '2020', 'foto_6aab577c3c32c.png', 5, 'Layout standar terlengkap. Memiliki Alphanumeric, Function Row (F1-F12), Navigation Cluster (panah, dll), dan Numeric Keypad (Numpad) di sisi kanan.', NULL, 1, 'Admin 2', '2026-09-17 06:57:23', '2026-09-17 09:59:08'),
(36, '1.1.5.6', 1, 1, 6, 1, 5, 'DVORAK', '2020', 'foto_6aab577c3c32c.png', 6, 'Layout standar terlengkap. Memiliki Alphanumeric, Function Row (F1-F12), Navigation Cluster (panah, dll), dan Numeric Keypad (Numpad) di sisi kanan.', NULL, 1, 'Admin 2', '2026-09-17 06:57:18', '2026-09-17 09:59:08'),
(37, '1.1.5.7', 1, 1, 6, 1, 5, 'QWERTY', '2020', 'foto_6aab577c3c32c.png', 7, 'Layout standar terlengkap. Memiliki Alphanumeric, Function Row (F1-F12), Navigation Cluster (panah, dll), dan Numeric Keypad (Numpad) di sisi kanan.', NULL, 2, 'Admin 2', '2026-09-17 06:57:12', '2026-09-17 09:59:08'),
(38, '1.1.5.8', 1, 1, 6, 1, 5, 'QWERTY', '2020', 'foto_6aab577c3c32c.png', 8, 'Layout standar terlengkap. Memiliki Alphanumeric, Function Row (F1-F12), Navigation Cluster (panah, dll), dan Numeric Keypad (Numpad) di sisi kanan.', NULL, 1, 'Admin 2', '2026-09-17 06:57:07', '2026-09-17 09:59:08'),
(39, '1.1.5.9', 1, 1, 6, 1, 5, 'QWERTY', '2025', 'foto_6aab577c3c32c.png', 9, 'Layout standar terlengkap. Memiliki Alphanumeric, Function Row (F1-F12), Navigation Cluster (panah, dll), dan Numeric Keypad (Numpad) di sisi kanan.', NULL, 1, 'Admin 2', '2026-09-17 04:59:33', '2026-09-17 09:59:08'),
(40, '1.1.5.10', 1, 1, 6, 1, 5, 'AZERTY', '2026', 'foto_6aab577c3c32c.png', 10, 'Layout standar terlengkap. Memiliki Alphanumeric, Function Row (F1-F12), Navigation Cluster (panah, dll), dan Numeric Keypad (Numpad) di sisi kanan.', NULL, 1, 'Admin 2', '2026-09-17 04:59:25', '2026-09-17 09:59:08'),
(41, '1.1.6.1', 1, 1, 1, 1, 6, 'HP E-Series', '2026', 'foto_6ab1cf6d6dada.jpg', 1, 'LED 144 Hz', NULL, 1, 'Admin 2', '2026-09-22 02:44:29', '2026-09-22 07:44:29'),
(42, '1.1.6.2', 1, 1, 1, 1, 6, 'HP E-Series', '2026', 'foto_6ab1cf6d6dada.jpg', 2, 'LED 144 Hz', NULL, 1, 'Admin 2', '2026-09-22 02:44:29', '2026-09-22 07:44:29'),
(43, '1.1.6.3', 1, 1, 1, 1, 6, 'HP E-Series', '2026', 'foto_6ab1cf6d6dada.jpg', 3, 'LED 144 Hz', NULL, 1, 'Admin 2', '2026-09-22 02:44:29', '2026-09-22 07:44:29'),
(44, '1.1.6.4', 1, 1, 1, 1, 6, 'HP E-Series', '2026', 'foto_6ab1cf6d6dada.jpg', 4, 'LED 144 Hz', NULL, 1, 'Admin 2', '2026-09-22 02:44:29', '2026-09-22 07:44:29'),
(45, '1.1.6.5', 1, 1, 1, 1, 6, 'HP E-Series', '2026', 'foto_6ab1cf6d6dada.jpg', 5, 'LED 144 Hz', NULL, 1, 'Admin 2', '2026-09-22 02:44:29', '2026-09-22 07:44:29'),
(46, '1.1.6.6', 1, 1, 1, 1, 6, 'HP E-Series', '2026', 'foto_6ab1cf6d6dada.jpg', 6, 'LED 144 Hz', NULL, 1, 'Admin 2', '2026-09-22 02:44:29', '2026-09-22 07:44:29'),
(47, '1.1.6.7', 1, 1, 1, 1, 6, 'HP E-Series', '2026', 'foto_6ab1cf6d6dada.jpg', 7, 'LED 144 Hz', NULL, 1, 'Admin 2', '2026-09-22 02:44:29', '2026-09-22 07:44:29'),
(48, '1.1.6.8', 1, 1, 1, 1, 6, 'HP E-Series', '2026', 'foto_6ab1cf6d6dada.jpg', 8, 'LED 144 Hz', NULL, 1, 'Admin 2', '2026-09-22 02:44:29', '2026-09-22 07:44:29'),
(49, '1.1.6.9', 1, 1, 1, 1, 6, 'HP E-Series', '2026', 'foto_6ab1cf6d6dada.jpg', 9, 'LED 144 Hz', NULL, 1, 'Admin 2', '2026-09-22 02:44:29', '2026-09-22 07:44:29'),
(50, '1.1.6.10', 1, 1, 1, 1, 6, 'HP E-Series', '2026', 'foto_6ab1cf6d6dada.jpg', 10, 'LED 144 Hz', NULL, 1, 'Admin 2', '2026-09-22 02:44:29', '2026-09-22 07:44:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `kode_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `kode_kategori`, `nama_kategori`, `created_at`) VALUES
(1, 1, 'Umum', '2026-09-01 08:39:52'),
(2, 2, 'Jaringan', '2026-09-01 08:39:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kondisi_barang`
--

CREATE TABLE `kondisi_barang` (
  `id` int(11) NOT NULL,
  `nama_kondisi` varchar(50) NOT NULL,
  `kode_warna` varchar(7) NOT NULL DEFAULT '#6c757d',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kondisi_barang`
--

INSERT INTO `kondisi_barang` (`id`, `nama_kondisi`, `kode_warna`, `created_at`) VALUES
(1, 'Baik', '#198754', '2026-09-15 08:50:14'),
(2, 'Rusak Ringan', '#fd7e14', '2026-09-15 08:50:14'),
(3, 'Rusak Berat', '#dc3545', '2026-09-15 08:50:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `lokasi`
--

CREATE TABLE `lokasi` (
  `id` int(11) NOT NULL,
  `nama_ruangan` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `lokasi`
--

INSERT INTO `lokasi` (`id`, `nama_ruangan`, `created_at`) VALUES
(1, 'Ruang Lab Komputer 1', '2026-09-01 08:39:52'),
(2, 'Gudang (Rak 1)', '2026-09-01 08:39:52'),
(3, 'Gudang (Rak 2)', '2026-09-01 08:39:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `merek`
--

CREATE TABLE `merek` (
  `id` int(11) NOT NULL,
  `nama_merek` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `merek`
--

INSERT INTO `merek` (`id`, `nama_merek`, `created_at`) VALUES
(1, 'HP', '2026-09-01 08:39:52'),
(2, 'Lenovo', '2026-09-01 08:39:52'),
(3, 'TP-Link', '2026-09-01 08:39:52'),
(4, 'Tanpa Merek', '2026-09-01 08:39:52'),
(5, 'Polaris', '2026-09-08 09:12:53'),
(6, 'Logitech', '2026-09-17 09:56:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `nama_peralatan`
--

CREATE TABLE `nama_peralatan` (
  `id` int(11) NOT NULL,
  `kode_peralatan` int(11) NOT NULL,
  `nama_peralatan` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `nama_peralatan`
--

INSERT INTO `nama_peralatan` (`id`, `kode_peralatan`, `nama_peralatan`, `created_at`) VALUES
(1, 1, 'Komputer Advan', '2026-09-01 08:39:52'),
(2, 2, 'Kursi', '2026-09-01 08:39:52'),
(3, 3, 'Router', '2026-09-01 08:39:52'),
(4, 4, 'Meja', '2026-09-08 09:13:10'),
(5, 5, 'Keyboard', '2026-09-08 13:49:27'),
(6, 6, 'Monitor', '2026-09-22 07:44:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `subkategori`
--

CREATE TABLE `subkategori` (
  `id` int(11) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `kode_subkategori` int(11) NOT NULL,
  `nama_subkategori` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `subkategori`
--

INSERT INTO `subkategori` (`id`, `kategori_id`, `kode_subkategori`, `nama_subkategori`, `created_at`) VALUES
(1, 1, 1, 'Komputer & Laptop', '2026-09-01 08:39:52'),
(2, 1, 2, 'Furnitur', '2026-09-01 08:39:52'),
(3, 2, 1, 'Perangkat Jaringan', '2026-09-01 08:39:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `nama_lengkap`, `created_at`) VALUES
(1, 'admin', 'admin@blksurabaya.go.id', '$2y$10$.CuJiPr5j0sd/zGXjuIhe./ZCc.q9GDHDzCSmcUyTb3sv7JMTTRLC', 'Administrator BLK', '2026-09-01 08:39:52'),
(3, 'admin 2', 'ngodingseseruitu@gmail.com', '$2y$10$cZ.xzJ7Xm4x21BgEhVIBAuL0KjKgFh2oZ65NkQ4izounu1loU6bwK', 'Admin 2', '2026-09-08 08:18:22');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`),
  ADD KEY `kategori_id` (`kategori_id`),
  ADD KEY `subkategori_id` (`subkategori_id`),
  ADD KEY `merek_id` (`merek_id`),
  ADD KEY `lokasi_id` (`lokasi_id`),
  ADD KEY `nama_peralatan_id` (`nama_peralatan_id`),
  ADD KEY `fk_barang_kondisi` (`kondisi_id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_kategori` (`kode_kategori`);

--
-- Indeks untuk tabel `kondisi_barang`
--
ALTER TABLE `kondisi_barang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_kondisi` (`nama_kondisi`);

--
-- Indeks untuk tabel `lokasi`
--
ALTER TABLE `lokasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_ruangan` (`nama_ruangan`);

--
-- Indeks untuk tabel `merek`
--
ALTER TABLE `merek`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_merek` (`nama_merek`);

--
-- Indeks untuk tabel `nama_peralatan`
--
ALTER TABLE `nama_peralatan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_peralatan` (`kode_peralatan`),
  ADD UNIQUE KEY `nama_peralatan` (`nama_peralatan`);

--
-- Indeks untuk tabel `subkategori`
--
ALTER TABLE `subkategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unik_kode_per_kategori` (`kategori_id`,`kode_subkategori`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `kondisi_barang`
--
ALTER TABLE `kondisi_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `lokasi`
--
ALTER TABLE `lokasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `merek`
--
ALTER TABLE `merek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `nama_peralatan`
--
ALTER TABLE `nama_peralatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `subkategori`
--
ALTER TABLE `subkategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `barang_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`),
  ADD CONSTRAINT `barang_ibfk_2` FOREIGN KEY (`subkategori_id`) REFERENCES `subkategori` (`id`),
  ADD CONSTRAINT `barang_ibfk_3` FOREIGN KEY (`merek_id`) REFERENCES `merek` (`id`),
  ADD CONSTRAINT `barang_ibfk_4` FOREIGN KEY (`lokasi_id`) REFERENCES `lokasi` (`id`),
  ADD CONSTRAINT `barang_ibfk_5` FOREIGN KEY (`nama_peralatan_id`) REFERENCES `nama_peralatan` (`id`),
  ADD CONSTRAINT `fk_barang_kondisi` FOREIGN KEY (`kondisi_id`) REFERENCES `kondisi_barang` (`id`);

--
-- Ketidakleluasaan untuk tabel `subkategori`
--
ALTER TABLE `subkategori`
  ADD CONSTRAINT `subkategori_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
