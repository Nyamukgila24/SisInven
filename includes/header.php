<?php
/**
 * Header bersama: menu navigasi + notifikasi flash.
 * Wajib include includes/functions.php SEBELUM file ini di setiap halaman,
 * dan definisikan $judulHalaman sebelum include.
 */
$base = urlDasar();
$halamanAktif = basename($_SERVER['SCRIPT_NAME']);
$folderAktif = basename(dirname($_SERVER['SCRIPT_NAME']));
$flash = ambilFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= amankan($judulHalaman ?? 'SisInven') ?> - SisInven</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-semibold" href="<?= $base ?>/index.php">
      <i class="bi bi-box-seam-fill me-1"></i> SisInven
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link <?= $halamanAktif === 'index.php' && $folderAktif !== 'laporan' && $folderAktif !== 'master' && $folderAktif !== 'barang' && $folderAktif !== 'user' ? 'active' : '' ?>" href="<?= $base ?>/index.php">
            <i class="bi bi-speedometer2"></i> Dasbor
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $folderAktif === 'barang' ? 'active' : '' ?>" href="<?= $base ?>/barang/index.php">
            <i class="bi bi-boxes"></i> Data Barang
          </a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $folderAktif === 'master' ? 'active' : '' ?>" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-diagram-3"></i> Data Master
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?= $base ?>/master/kategori.php">Kategori</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>/master/subkategori.php">Subkategori</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>/master/merek.php">Merek</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>/master/lokasi.php">Lokasi / Ruangan</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>/master/peralatan.php">Nama Peralatan</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $folderAktif === 'laporan' ? 'active' : '' ?>" href="<?= $base ?>/laporan/index.php">
            <i class="bi bi-file-earmark-bar-graph"></i> Laporan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $folderAktif === 'user' ? 'active' : '' ?>" href="<?= $base ?>/user/index.php">
            <i class="bi bi-people"></i> Pengguna
          </a>
        </li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i> <?= amankan($_SESSION['nama_lengkap'] ?? '') ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item text-danger" href="<?= $base ?>/logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['tipe'] === 'sukses' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
      <?= amankan($flash['pesan']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
