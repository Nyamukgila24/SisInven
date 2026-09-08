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
$kondisiValid = ['Baik', 'Rusak', 'Sedang Diperbaiki'];

if (!$kategoriId || !$subkategoriId || !$peralatanId || !$merekId || !$lokasiId || $spesifikasi === '') {
    setFlash('gagal', 'Semua kolom wajib harus diisi dengan benar.');
    header('Location: ' . ($id ? "form.php?id={$id}" : 'form.php'));
    exit;
}

[$namaPengguna, $waktuSekarang] = catatJejakPerubahan();

try {
    $pdo->beginTransaction();

    if ($id) {
        // ---- MODE EDIT (satu barang) ----
        $kondisi = $_POST['kondisi'] ?? 'Baik';
        $nomorInventaris = trim($_POST['nomor_inventaris_kantor'] ?? '');
        $nomorInventaris = $nomorInventaris === '' ? null : $nomorInventaris;

        if (!in_array($kondisi, $kondisiValid, true)) {
            throw new Exception('Kondisi barang tidak valid.');
        }

        $stmt = $pdo->prepare(
            'UPDATE barang SET merek_id=?, lokasi_id=?, spesifikasi=?, nomor_inventaris_kantor=?,
             kondisi=?, user_last_edit=?, timestamp_last_edit=? WHERE id=?'
        );
        $stmt->execute([$merekId, $lokasiId, $spesifikasi, $nomorInventaris, $kondisi, $namaPengguna, $waktuSekarang, $id]);
        $pesan = 'Data barang berhasil diperbarui.';
    } else {
        // ---- MODE TAMBAH BARU (bisa lebih dari 1 sekaligus) ----
        $jumlahBarang = max(1, min(100, (int) ($_POST['jumlah_barang'] ?? 1)));

        if (!empty($_POST['kondisi_unit']) && is_array($_POST['kondisi_unit'])) {
            // Mode per-unit: kondisi & no. inventaris beda-beda tiap barang
            $daftarKondisi = $_POST['kondisi_unit'];
            $daftarNomorInventaris = $_POST['nomor_inventaris_unit'] ?? [];
        } else {
            // Mode seragam: kondisi sama untuk semua barang yang ditambah
            $kondisiTunggal = $_POST['kondisi'] ?? 'Baik';
            $nomorInventarisTunggal = trim($_POST['nomor_inventaris_kantor'] ?? '');
            $daftarKondisi = array_fill(0, $jumlahBarang, $kondisiTunggal);
            $daftarNomorInventaris = array_fill(0, $jumlahBarang, $nomorInventarisTunggal);
        }

        $daftarKodeBarang = [];

        foreach ($daftarKondisi as $i => $kondisiBaris) {
            if (!in_array($kondisiBaris, $kondisiValid, true)) {
                throw new Exception('Ada kondisi barang yang tidak valid.');
            }
            $nomorInventarisBaris = trim($daftarNomorInventaris[$i] ?? '');
            $nomorInventarisBaris = $nomorInventarisBaris === '' ? null : $nomorInventarisBaris;

            [$kodeBarang, $nomorUrut] = generateKodeBarang($pdo, $kategoriId, $subkategoriId, $peralatanId);

            $stmt = $pdo->prepare(
                'INSERT INTO barang
                 (kode_barang, kategori_id, subkategori_id, merek_id, lokasi_id, nama_peralatan_id, nomor_urut,
                  spesifikasi, nomor_inventaris_kantor, kondisi, user_last_edit, timestamp_last_edit)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $kodeBarang, $kategoriId, $subkategoriId, $merekId, $lokasiId, $peralatanId, $nomorUrut,
                $spesifikasi, $nomorInventarisBaris, $kondisiBaris, $namaPengguna, $waktuSekarang,
            ]);
            $daftarKodeBarang[] = $kodeBarang;
        }

        $jumlahDitambahkan = count($daftarKodeBarang);
        $pesan = $jumlahDitambahkan === 1
            ? "Barang baru berhasil ditambahkan dengan Kode Barang: {$daftarKodeBarang[0]}"
            : "{$jumlahDitambahkan} barang baru berhasil ditambahkan (Kode: " . implode(', ', $daftarKodeBarang) . ')';
    }

    $pdo->commit();
    setFlash('sukses', $pesan);
} catch (Exception $e) {
    $pdo->rollBack();
    setFlash('gagal', 'Gagal menyimpan data barang: ' . $e->getMessage());
}

header('Location: index.php');
exit;