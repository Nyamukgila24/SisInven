<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

$dataRingkasan = ambilRingkasanBarang($pdo);

$namaFile = 'ringkasan_inventaris_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $namaFile . '"');

$output = fopen('php://output', 'w');
fputs($output, "\xEF\xBB\xBF"); 

$header = ['Nama Barang', 'Jumlah Barang'];
foreach ($dataRingkasan['daftar_kondisi'] as $k) {
    $header[] = $k['nama_kondisi'];
}
fputcsv($output, $header);

foreach ($dataRingkasan['ringkasan'] as $nama => $info) {
    $row = [$nama, $info['total']];
    foreach ($dataRingkasan['daftar_kondisi'] as $k) {
        $row[] = $info['kondisi'][$k['id']] ?? 0;
    }
    fputcsv($output, $row);
}

fclose($output);
exit;