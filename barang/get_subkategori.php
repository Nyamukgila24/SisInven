<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
header('Content-Type: application/json');

$pdo = getKoneksi();
$kategoriId = $_GET['kategori_id'] ?? 0;

$stmt = $pdo->prepare('SELECT id, nama_subkategori FROM subkategori WHERE kategori_id = ? ORDER BY nama_subkategori');
$stmt->execute([$kategoriId]);
echo json_encode($stmt->fetchAll());
