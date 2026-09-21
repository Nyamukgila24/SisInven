<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();
$base = urlDasar();
$dataRingkasan = ambilRingkasanBarang($pdo);

// Baca SEMUA filter dari URL
$kondisi        = $_GET['kondisi'] ?? '';
$statusAset     = $_GET['status_aset'] ?? '';
$tipeBarang     = $_GET['tipe'] ?? '';
$tahunPerolehan = $_GET['tahun'] ?? '';
$peralatanId    = $_GET['peralatan'] ?? '';
$merekId        = $_GET['merek'] ?? '';
$lokasiId       = $_GET['lokasi'] ?? '';

// Ambil data (tanpa pagination)
$data = ambilDataLaporan($pdo, $kondisi, $statusAset, $tipeBarang, $tahunPerolehan,
                         $peralatanId, $merekId, $lokasiId);

// Bangun keterangan filter untuk ditampilkan di PDF
$judulFilter = [];
if ($kondisi !== '') {
    $stmt = $pdo->prepare('SELECT nama_kondisi FROM kondisi_barang WHERE id = ?');
    $stmt->execute([$kondisi]);
    $namaKondisi = $stmt->fetchColumn() ?: $kondisi;
    $judulFilter[] = "Kondisi: {$namaKondisi}";
}
if ($peralatanId !== '') {
    $stmt = $pdo->prepare('SELECT nama_peralatan FROM nama_peralatan WHERE id = ?');
    $stmt->execute([$peralatanId]);
    $namaPeralatan = $stmt->fetchColumn() ?: $peralatanId;
    $judulFilter[] = "Peralatan: {$namaPeralatan}";
}
if ($merekId !== '') {
    $stmt = $pdo->prepare('SELECT nama_merek FROM merek WHERE id = ?');
    $stmt->execute([$merekId]);
    $namaMerek = $stmt->fetchColumn() ?: $merekId;
    $judulFilter[] = "Merek: {$namaMerek}";
}
if ($lokasiId !== '') {
    $stmt = $pdo->prepare('SELECT nama_ruangan FROM lokasi WHERE id = ?');
    $stmt->execute([$lokasiId]);
    $namaLokasi = $stmt->fetchColumn() ?: $lokasiId;
    $judulFilter[] = "Lokasi: {$namaLokasi}";
}
if ($tipeBarang !== '') {
    $judulFilter[] = "Tipe: {$tipeBarang}";
}
if ($tahunPerolehan !== '') {
    $judulFilter[] = "Tahun Perolehan: {$tahunPerolehan}";
}
if ($statusAset === 'ada') {
    $judulFilter[] = 'Punya Nomor Inventaris Kantor';
} elseif ($statusAset === 'tanpa') {
    $judulFilter[] = 'Tanpa Nomor Inventaris Kantor';
}
$keteranganFilter = $judulFilter ? implode(' | ', $judulFilter) : 'Semua Data';

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

<div class="judul-laporan">RINGKASAN INVENTARIS BARANG</div>

<div class="info-cetak">Dicetak: <?= date('d-m-Y H:i') ?></div>

<table class="table-laporan">
   <thead>
        <tr>
            <th width="30%">Nama Barang</th>
            <th width="15%">Jumlah Barang</th>
            <?php foreach ($dataRingkasan['daftar_kondisi'] as $k): ?>
                <th><?= amankan($k['nama_kondisi']) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (!$dataRingkasan['ringkasan']): ?>
            <tr><td colspan="<?= 2 + count($dataRingkasan['daftar_kondisi']) ?>" class="text-center py-4">Belum ada data.</td></tr>
        <?php endif; ?>
        <?php foreach ($dataRingkasan['ringkasan'] as $nama => $info): ?>
            <tr>
                <td><?= amankan($nama) ?></td>
                <td class="text-center fw-bold"><?= $info['total'] ?></td>
                <?php foreach ($dataRingkasan['daftar_kondisi'] as $k): ?>
                    <td class="text-center"><?= $info['kondisi'][$k['id']] ?? 0 ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="total-data">
    Total Peralatan : <?= count($dataRingkasan['ringkasan']) ?> jenis   
</div>

</body>
</html>