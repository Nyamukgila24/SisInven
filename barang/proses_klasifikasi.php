<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$pdo = getKoneksi();

$kategoriLama  = $_POST['kategori_lama'];
$subLama       = $_POST['sub_lama'];
$peralatanLama = $_POST['peralatan_lama'];

$kategoriBaru  = $_POST['kategori_baru'];
$subBaru       = $_POST['sub_baru'];
$peralatanBaru = trim($_POST['peralatan_baru']);

if (!$kategoriBaru || !$subBaru || $peralatanBaru === '') {
    setFlash('gagal', 'Semua kolom baru wajib diisi.');
    header('Location: index.php');
    exit;
}

try {
    $pdo->beginTransaction();
    $peralatanBaruId = cariAtauBuatPeralatan($pdo, $peralatanBaru);

    $stmt = $pdo->prepare('SELECT kode_kategori FROM kategori WHERE id = ?');
    $stmt->execute([$kategoriBaru]);
    $kodeKat = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT kode_subkategori FROM subkategori WHERE id = ?');
    $stmt->execute([$subBaru]);
    $kodeSub = $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT id FROM barang 
        WHERE kategori_id = ? AND subkategori_id = ? AND nama_peralatan_id = ?'
    );
    $stmt->execute([$kategoriLama, $subLama, $peralatanLama]);
    $daftarBarang = $stmt->fetchAll();

    if (count($daftarBarang) === 0) {
        throw new Exception('Tidak ada barang yang cocok dengan klasifikasi lama tersebut.');
    }

    $idBarangArray = array_column($daftarBarang, 'id');

    $kodePer = getKodePeralatanSpesifik($pdo, $subBaru, $peralatanBaruId, $idBarangArray);

    if ($kodeKat === false || $kodeSub === false || $kodePer === false) {
        throw new Exception('Data master kategori/sub/peralatan tidak ditemukan.');
    }

    $stmt = $pdo->prepare(
        'SELECT id FROM barang 
         WHERE kategori_id = ? AND subkategori_id = ? AND nama_peralatan_id = ?'
    );
    $stmt->execute([$kategoriLama, $subLama, $peralatanLama]);
    $daftarBarang = $stmt->fetchAll();

    if (count($daftarBarang) === 0) {
        throw new Exception('Tidak ada barang yang cocok dengan klasifikasi lama tersebut.');
    }

    $stmt = $pdo->prepare(
        'SELECT COALESCE(MAX(nomor_urut), 0) 
         FROM barang 
         WHERE kategori_id = ? AND subkategori_id = ? AND nama_peralatan_id = ?'
    );
    $stmt->execute([$kategoriBaru, $subBaru, $peralatanBaruId]);
    $nomorUrutTerakhir = (int) $stmt->fetchColumn();

    $waktuSekarang = date('Y-m-d H:i:s');
    $namaPengguna  = $_SESSION['nama_lengkap'] ?? 'Sistem';

    $stmtUpdate = $pdo->prepare(
        'UPDATE barang 
         SET kategori_id = ?, subkategori_id = ?, nama_peralatan_id = ?, 
             nomor_urut = ?, kode_barang = ?, user_last_edit = ?, timestamp_last_edit = ?
         WHERE id = ?'
    );

    $stmtTemp = $pdo->prepare('UPDATE barang SET kode_barang = ? WHERE id = ?');

    foreach ($daftarBarang as $b) {
        $kodeTemp = 'TEMP_' . uniqid() . '_' . $b['id'];
        $stmtTemp->execute([$kodeTemp, $b['id']]);
    }

    foreach ($daftarBarang as $b) {
        $nomorUrutTerakhir++; 

        $kodeBarangBaru = "{$kodeKat}.{$kodeSub}.{$kodePer}.{$nomorUrutTerakhir}";

        $stmtUpdate->execute([
            $kategoriBaru, 
            $subBaru, 
            $peralatanBaruId, 
            $nomorUrutTerakhir, 
            $kodeBarangBaru, 
            $namaPengguna, 
            $waktuSekarang, 
            $b['id']
        ]);
    }

    $pdo->commit();
    setFlash('sukses', count($daftarBarang) . ' barang berhasil dipindahkan klasifikasinya dan kodenya diperbarui.');

} catch (Exception $e) {
    $pdo->rollBack();
    setFlash('gagal', 'Gagal mengubah klasifikasi: ' . $e->getMessage());
}

header('Location: index.php');
exit;