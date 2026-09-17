-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 15 Sep 2026 pada 05.54
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

INSERT INTO `barang` (`id`, `kode_barang`, `kategori_id`, `subkategori_id`, `merek_id`, `lokasi_id`, `nama_peralatan_id`, `foto_peralatan`, `nomor_urut`, `spesifikasi`, `nomor_inventaris_kantor`, `kondisi_id`, `user_last_edit`, `timestamp_last_edit`, `created_at`) VALUES
(1, '1.1.1.1', 1, 1, 2, 2, 1, NULL, 1, 'Intel Core i5', NULL, 1, 'Administrator BLK', '2026-09-08 02:43:17', '2026-09-08 07:43:17'),
(2, '1.2.2.1', 1, 2, 5, 1, 2, NULL, 1, 'Warna Biru', NULL, 3, 'Admin 2', '2026-09-15 05:13:52', '2026-09-08 09:13:52'),
(3, '1.2.2.2', 1, 2, 5, 1, 2, NULL, 2, '6 Bagus 2 Rusak', NULL, 1, 'Admin 2', '2026-09-08 04:26:57', '2026-09-08 09:26:57'),
(4, '1.2.2.3', 1, 2, 5, 1, 2, NULL, 3, '6 Bagus 2 Rusak', NULL, 1, 'Admin 2', '2026-09-08 04:26:57', '2026-09-08 09:26:57'),
(5, '1.2.2.4', 1, 2, 5, 1, 2, NULL, 4, '6 Bagus 2 Rusak', NULL, 1, 'Admin 2', '2026-09-08 04:26:57', '2026-09-08 09:26:57'),
(6, '1.2.2.5', 1, 2, 5, 1, 2, NULL, 5, '6 Bagus 2 Rusak', NULL, 1, 'Admin 2', '2026-09-08 04:26:57', '2026-09-08 09:26:57'),
(7, '1.2.2.6', 1, 2, 5, 1, 2, NULL, 6, '6 Bagus 2 Rusak', NULL, 1, 'Admin 2', '2026-09-08 04:26:57', '2026-09-08 09:26:57'),
(8, '1.2.2.7', 1, 2, 5, 1, 2, NULL, 7, '6 Bagus 2 Rusak', NULL, 1, 'Admin 2', '2026-09-08 04:26:57', '2026-09-08 09:26:57'),
(9, '1.2.2.8', 1, 2, 5, 1, 2, NULL, 8, '6 Bagus 2 Rusak', NULL, 1, 'Admin 2', '2026-09-08 04:26:57', '2026-09-08 09:26:57'),
(10, '1.2.2.9', 1, 2, 5, 1, 2, NULL, 9, '6 Bagus 2 Rusak', NULL, 1, 'Admin 2', '2026-09-08 04:26:57', '2026-09-08 09:26:57'),
(11, '1.1.5.1', 1, 1, 2, 1, 5, NULL, 1, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(12, '1.1.5.2', 1, 1, 2, 1, 5, NULL, 2, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(13, '1.1.5.3', 1, 1, 2, 1, 5, NULL, 3, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(14, '1.1.5.4', 1, 1, 2, 1, 5, NULL, 4, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(15, '1.1.5.5', 1, 1, 2, 1, 5, NULL, 5, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(16, '1.1.5.6', 1, 1, 2, 1, 5, NULL, 6, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(17, '1.1.5.7', 1, 1, 2, 1, 5, NULL, 7, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(18, '1.1.5.8', 1, 1, 2, 1, 5, NULL, 8, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(19, '1.1.5.9', 1, 1, 2, 1, 5, NULL, 9, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(20, '1.1.5.10', 1, 1, 2, 1, 5, NULL, 10, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(21, '1.1.5.11', 1, 1, 2, 1, 5, NULL, 11, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(22, '1.1.5.12', 1, 1, 2, 1, 5, NULL, 12, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(23, '1.1.5.13', 1, 1, 2, 1, 5, NULL, 13, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(24, '1.1.5.14', 1, 1, 2, 1, 5, NULL, 14, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(25, '1.1.5.15', 1, 1, 2, 1, 5, NULL, 15, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(26, '1.1.5.16', 1, 1, 2, 1, 5, NULL, 16, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(27, '1.1.5.17', 1, 1, 2, 1, 5, NULL, 17, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(28, '1.1.5.18', 1, 1, 2, 1, 5, NULL, 18, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(29, '1.1.5.19', 1, 1, 2, 1, 5, NULL, 19, 'Masih Layak', NULL, 1, 'Admin 2', '2026-09-08 08:49:56', '2026-09-08 13:49:56'),
(30, '1.1.5.20', 1, 1, 2, 1, 5, NULL, 20, 'Masih Layak', 'B.PUSAT.2020.01', 1, 'Admin 2', '2026-09-08 08:59:27', '2026-09-08 13:49:56');

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
(5, 'Polaris', '2026-09-08 09:12:53');

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
(5, 5, 'Keyboard', '2026-09-08 13:49:27');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `kondisi_barang`
--
ALTER TABLE `kondisi_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `lokasi`
--
ALTER TABLE `lokasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `merek`
--
ALTER TABLE `merek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `nama_peralatan`
--
ALTER TABLE `nama_peralatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
