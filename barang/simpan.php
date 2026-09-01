<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$pdo = getKoneksi();

$id = $_POST['id'] ?: null;
$kategoriId = $_POST['kategori_id'] ?? '';
$subkategoriId = $_POST['subkategori_id'] ?? '';
$peralatanId = $_POST['nama_peralatan_id'] ?? '';
$merekId = $_POST['merek_id'] ?? '';
$lokasiId = $_POST['lokasi_id'] ?? '';
$spesifikasi = trim($_POST['spesifikasi'] ?? '');
$nomorInventaris = trim($_POST['nomor_inventaris_kantor'] ?? '');
$kondisi = $_POST['kondisi'] ?? 'Baik';

// Validasi dasar
$kondisiValid = ['Baik', 'Rusak', 'Sedang Diperbaiki'];
if (!$kategoriId || !$subkategoriId || !$peralatanId || !$merekId || !$lokasiId || $spesifikasi === '' || !in_array($kondisi, $kondisiValid, true)) {
    setFlash('gagal', 'Semua kolom wajib (kecuali Nomor Inventaris) harus diisi dengan benar.');
    header('Location: ' . ($id ? "form.php?id={$id}" : 'form.php'));
    exit;
}

[$namaPengguna, $waktuSekarang] = catatJejakPerubahan();
$nomorInventaris = $nomorInventaris === '' ? null : $nomorInventaris;

try {
    $pdo->beginTransaction();

    if ($id) {
        // ---- MODE EDIT ----
        // Kategori/Subkategori/Peralatan TIDAK diubah (lihat form.php),
        // jadi kode_barang tidak perlu dibuat ulang.
        $stmt = $pdo->prepare(
            'UPDATE barang SET merek_id=?, lokasi_id=?, spesifikasi=?, nomor_inventaris_kantor=?,
             kondisi=?, user_last_edit=?, timestamp_last_edit=? WHERE id=?'
        );
        $stmt->execute([$merekId, $lokasiId, $spesifikasi, $nomorInventaris, $kondisi, $namaPengguna, $waktuSekarang, $id]);
        $pesan = 'Data barang berhasil diperbarui.';
    } else {
        // ---- MODE TAMBAH BARU ----
        // generateKodeBarang() memakai "SELECT ... FOR UPDATE" di dalam
        // transaction ini, supaya dua orang yang input barang sejenis
        // pada saat bersamaan tidak mendapat nomor urut yang sama.
        [$kodeBarang, $nomorUrut] = generateKodeBarang($pdo, $kategoriId, $subkategoriId, $peralatanId);

        $stmt = $pdo->prepare(
            'INSERT INTO barang
             (kode_barang, kategori_id, subkategori_id, merek_id, lokasi_id, nama_peralatan_id, nomor_urut,
              spesifikasi, nomor_inventaris_kantor, kondisi, user_last_edit, timestamp_last_edit)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $kodeBarang, $kategoriId, $subkategoriId, $merekId, $lokasiId, $peralatanId, $nomorUrut,
            $spesifikasi, $nomorInventaris, $kondisi, $namaPengguna, $waktuSekarang,
        ]);
        $pesan = "Barang baru berhasil ditambahkan dengan Kode Barang: {$kodeBarang}";
    }

    $pdo->commit();
    setFlash('sukses', $pesan);
} catch (Exception $e) {
    $pdo->rollBack();
    setFlash('gagal', 'Gagal menyimpan data barang: ' . $e->getMessage());
}

header('Location: index.php');
exit;
