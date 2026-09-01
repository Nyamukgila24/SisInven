<?php
/**
 * EKSPOR KE EXCEL
 * -------------------------------------------------------------
 * Diekspor sebagai file .csv (Comma Separated Values). File ini
 * bisa langsung dibuka oleh Microsoft Excel maupun Google Sheets
 * tanpa perlu instalasi library PHP tambahan (seperti PhpSpreadsheet),
 * sehingga aplikasi tetap ringan dan mudah dipasang di server mana pun.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

$kondisi = $_GET['kondisi'] ?? '';
$statusAset = $_GET['status_aset'] ?? '';
$data = ambilDataLaporan($pdo, $kondisi, $statusAset);

$namaFile = 'laporan_inventaris_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $namaFile . '"');

$output = fopen('php://output', 'w');
fputs($output, "\xEF\xBB\xBF"); // BOM supaya karakter tampil benar saat dibuka Excel

fputcsv($output, [
    'Kode Barang', 'Nama Peralatan', 'Kategori', 'Subkategori', 'Merek', 'Lokasi',
    'Spesifikasi', 'Nomor Inventaris Kantor', 'Kondisi', 'Diubah Oleh', 'Waktu Perubahan Terakhir',
]);

foreach ($data as $row) {
    fputcsv($output, [
        $row['kode_barang'], $row['nama_peralatan'], $row['nama_kategori'], $row['nama_subkategori'],
        $row['nama_merek'], $row['nama_ruangan'], $row['spesifikasi'], $row['nomor_inventaris_kantor'] ?? '',
        $row['kondisi'], $row['user_last_edit'], $row['timestamp_last_edit'],
    ]);
}

fclose($output);
exit;
