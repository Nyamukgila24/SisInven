<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $pdo = getKoneksi();
    $pdo->prepare('DELETE FROM barang WHERE id = ?')->execute([$_POST['id']]);
    setFlash('sukses', 'Data barang berhasil dihapus.');
}

header('Location: index.php');
exit;
