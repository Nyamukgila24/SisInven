<?php
/**
 * EKSPOR KE PDF
 * -------------------------------------------------------------
 * Menampilkan halaman khusus cetak yang rapi. Pengguna tinggal
 * menekan tombol "Cetak / Simpan sebagai PDF" lalu memilih
 * "Save as PDF" pada kotak dialog print di browser (Chrome/Edge/Firefox).
 * Cara ini dipilih supaya aplikasi tidak bergantung pada library PHP
 * tambahan (seperti TCPDF/mPDF) yang butuh instalasi lewat Composer -
 * sehingga lebih mudah dipasang & dirawat di server kantor mana pun.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

$kondisi = $_GET['kondisi'] ?? '';
$statusAset = $_GET['status_aset'] ?? '';
$data = ambilDataLaporan($pdo, $kondisi, $statusAset);

$judulFilter = [];
if ($kondisi !== '') $judulFilter[] = "Kondisi: {$kondisi}";
if ($statusAset === 'ada') $judulFilter[] = 'Punya Nomor Inventaris Kantor';
if ($statusAset === 'tanpa') $judulFilter[] = 'Tanpa Nomor Inventaris Kantor';
$keteranganFilter = $judulFilter ? implode(' | ', $judulFilter) : 'Semua Data';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Inventaris - Cetak PDF</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { padding: 30px; }
  table { font-size: 12px; }
  @media print {
    .no-print { display: none !important; }
    body { padding: 0; }
  }
</style>
</head>
<body>

<div class="no-print mb-3 d-flex justify-content-between">
  <a href="index.php" class="btn btn-outline-secondary">&larr; Kembali</a>
  <button onclick="window.print()" class="btn btn-danger">
    Cetak / Simpan sebagai PDF
  </button>
</div>

<div class="text-center mb-4">
  <h4 class="mb-0">LAPORAN INVENTARIS BARANG</h4>
  <div>UPT Balai Latihan Kerja (BLK) Surabaya</div>
  <small class="text-muted">Filter: <?= amankan($keteranganFilter) ?> &mdash; Dicetak: <?= date('d-m-Y H:i') ?></small>
</div>

<table class="table table-bordered table-sm">
  <thead>
    <tr>
      <th>No</th><th>Kode Barang</th><th>Peralatan</th><th>Kategori</th><th>Merek</th>
      <th>Lokasi</th><th>No. Inventaris</th><th>Kondisi</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!$data): ?>
      <tr><td colspan="8" class="text-center">Tidak ada data.</td></tr>
    <?php endif; ?>
    <?php foreach ($data as $i => $row): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= amankan($row['kode_barang']) ?></td>
        <td><?= amankan($row['nama_peralatan']) ?></td>
        <td><?= amankan($row['nama_kategori']) ?> / <?= amankan($row['nama_subkategori']) ?></td>
        <td><?= amankan($row['nama_merek']) ?></td>
        <td><?= amankan($row['nama_ruangan']) ?></td>
        <td><?= $row['nomor_inventaris_kantor'] ? amankan($row['nomor_inventaris_kantor']) : '-' ?></td>
        <td><?= amankan($row['kondisi']) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<p class="mt-3">Total: <?= count($data) ?> barang</p>

</body>
</html>
