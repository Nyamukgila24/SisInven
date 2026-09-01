<?php
/**
 * Endpoint kecil untuk fitur Dropdown Bertingkat (FRD 3.1).
 * Dipanggil lewat JavaScript saat pengguna memilih Kategori pada form
 * barang, mengembalikan daftar Subkategori JSON yang sesuai kategori
 * tersebut saja (bukan semua subkategori di database).
 */
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
header('Content-Type: application/json');

$pdo = getKoneksi();
$kategoriId = $_GET['kategori_id'] ?? 0;

$stmt = $pdo->prepare('SELECT id, nama_subkategori FROM subkategori WHERE kategori_id = ? ORDER BY nama_subkategori');
$stmt->execute([$kategoriId]);
echo json_encode($stmt->fetchAll());
