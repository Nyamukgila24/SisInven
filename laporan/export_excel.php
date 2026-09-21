<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

// Baca SEMUA filter dari URL (termasuk yang baru)
$kondisi        = $_GET['kondisi'] ?? '';
$statusAset     = $_GET['status_aset'] ?? '';
$tipeBarang     = $_GET['tipe'] ?? '';
$tahunPerolehan = $_GET['tahun'] ?? '';
$peralatanId    = $_GET['peralatan'] ?? '';
$merekId        = $_GET['merek'] ?? '';
$lokasiId       = $_GET['lokasi'] ?? '';

$data = ambilDataLaporan($pdo, $kondisi, $statusAset, $tipeBarang, $tahunPerolehan,
                         $peralatanId, $merekId, $lokasiId);

$namaFile = 'laporan_inventaris_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $namaFile . '"');

$output = fopen('php://output', 'w');
fputs($output, "\xEF\xBB\xBF"); // BOM biar Excel baca UTF-8 dengan benar

fputcsv($output, [
    'Kode Barang', 'Nama Peralatan', 'Tipe Barang', 'Tahun Perolehan',
    'Kategori', 'Subkategori', 'Merek', 'Lokasi',
    'Spesifikasi', 'Nomor Inventaris Kantor', 'Kondisi',
    'Diubah Oleh', 'Waktu Perubahan Terakhir',
]);

foreach ($data as $row) {
    fputcsv($output, [
        $row['kode_barang'],
        $row['nama_peralatan'],
        $row['tipe_barang'],
        $row['tahun_perolehan'],
        $row['nama_kategori'],
        $row['nama_subkategori'],
        $row['nama_merek'],
        $row['nama_ruangan'],
        $row['spesifikasi'],
        $row['nomor_inventaris_kantor'] ?? '',
        $row['kondisi'],
        $row['user_last_edit'],
        $row['timestamp_last_edit'],
    ]);
}

fclose($output);
exit;