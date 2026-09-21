<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

$peralatanId = $_GET['peralatan'] ?? '';
$merekId     = $_GET['merek'] ?? '';

if ($peralatanId !== '') {
    $stmt = $pdo->prepare(
        'SELECT DISTINCT m.id, m.nama_merek
         FROM barang b
         JOIN merek m ON m.id = b.merek_id
         WHERE b.nama_peralatan_id = ?
         ORDER BY m.nama_merek'
    );
    $stmt->execute([$peralatanId]);
} else {
    $stmt = $pdo->query('SELECT id, nama_merek FROM merek ORDER BY nama_merek');
}
$daftarMerek = $stmt->fetchAll();

$sqlTipe = 'SELECT DISTINCT tipe_barang FROM barang WHERE tipe_barang IS NOT NULL AND tipe_barang <> ""';
$paramsTipe = [];

if ($peralatanId !== '') {
    $sqlTipe .= ' AND nama_peralatan_id = ?';
    $paramsTipe[] = $peralatanId;
}
if ($merekId !== '') {
    $sqlTipe .= ' AND merek_id = ?';
    $paramsTipe[] = $merekId;
}
$sqlTipe .= ' ORDER BY tipe_barang';

$stmt = $pdo->prepare($sqlTipe);
$stmt->execute($paramsTipe);
$daftarTipe = $stmt->fetchAll(PDO::FETCH_COLUMN);

header('Content-Type: application/json');
echo json_encode([
    'merek' => $daftarMerek,
    'tipe'  => $daftarTipe,
]);