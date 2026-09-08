<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();
$base = urlDasar();

$kondisi = $_GET['kondisi'] ?? '';
$statusAset = $_GET['status_aset'] ?? '';
$data = ambilDataLaporan($pdo, $kondisi, $statusAset);

$judulFilter = [];
if ($kondisi !== '') $judulFilter[] = "Kondisi: {$kondisi}";
if ($statusAset === 'ada') $judulFilter[] = 'Punya Nomor Inventaris Kantor';
if ($statusAset === 'tanpa') $judulFilter[] = 'Tanpa Nomor Inventaris Kantor';
$keteranganFilter = $judulFilter ? implode(' | ', $judulFilter) : 'Semua Data';

function getBadgeClass($kondisi) {
    $map = [
        'Baik' => 'bagus',
        'Rusak' => 'rusak',
        'Sedang Diperbaiki' => 'diperbaiki'
    ];
    return $map[$kondisi] ?? '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Inventaris - Cetak PDF</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= $base ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="no-print mb-3 d-flex justify-content-between">
    <a href="<?= $base ?>/laporan/index.php" class="btn btn-outline-secondary">&larr; Kembali</a>
    <button onclick="window.print()" class="btn btn-danger">
        <i class="bi bi-printer"></i> Cetak / Simpan sebagai PDF
    </button>
</div>

<div class="kop-surat">
    <img src="<?= $base ?>/img/logo.png" alt="Logo" class="kop-logo">
    <div class="kop-pemerintah">PEMERINTAH PROVINSI JAWA TIMUR</div>
    <div class="kop-dinas">DINAS TENAGA KERJA DAN TRANSMIGRASI</div>
    <div class="kop-instansi">UNIT PELAKSANA TEKNIS BALAI LATIHAN KERJA SURABAYA</div>
    <div class="kop-alamat">Jl. Dukuh Menanggal III Nomor 29, Surabaya, Jawa Timur 60123 | Telp. (031) 8290071</div>
</div>

<div class="judul-laporan">LAPORAN INVENTARIS BARANG</div>

<div class="info-cetak">Dicetak: <?= date('d-m-Y H:i') ?></div>
<div class="info-filter">Filter: <?= amankan($keteranganFilter) ?></div>

<table class="table-laporan">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="12%">Kode Barang</th>
            <th width="18%">Nama Peralatan</th>
            <th width="15%">Kategori / Sub</th>
            <th width="12%">Merek</th>
            <th width="12%">Lokasi</th>
            <th width="14%">No. Inventaris</th>
            <th width="12%">Kondisi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!$data): ?>
            <tr><td colspan="8" class="text-center py-4">Tidak ada data yang ditemukan.</td></tr>
        <?php endif; ?>
        <?php foreach ($data as $i => $row): ?>
            <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td><?= amankan($row['kode_barang']) ?></td>
                <td><?= amankan($row['nama_peralatan']) ?></td>
                <td><?= amankan($row['nama_kategori']) ?> / <?= amankan($row['nama_subkategori']) ?></td>
                <td><?= amankan($row['nama_merek']) ?></td>
                <td><?= amankan($row['nama_ruangan']) ?></td>
                <td class="text-center"><?= $row['nomor_inventaris_kantor'] ? amankan($row['nomor_inventaris_kantor']) : '-' ?></td>
                <td class="text-center">
                    <span class="badge-kondisi <?= getBadgeClass($row['kondisi']) ?>">
                        <?= amankan($row['kondisi']) ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="total-data">Total Data: <?= count($data) ?> barang</div>

</body>
</html>