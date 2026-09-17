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
$merekId = $_POST['merek_id'] ?? '';
$lokasiId = $_POST['lokasi_id'] ?? '';
$spesifikasi = trim($_POST['spesifikasi'] ?? '');
$tahunSekarang = (int) date('Y');

if (!$kategoriId || !$subkategoriId || !$merekId || !$lokasiId || $spesifikasi === '') {
    setFlash('gagal', 'Semua kolom wajib harus diisi dengan benar.');
    header('Location: ' . ($id ? "form.php?id={$id}" : 'form.php'));
    exit;
}

$daftarKondisiValid = $pdo->query('SELECT id FROM kondisi_barang')->fetchAll(PDO::FETCH_COLUMN);
[$namaPengguna, $waktuSekarang] = catatJejakPerubahan();
$fotoBaru = simpanFotoPeralatan();

try {
    $pdo->beginTransaction();

    if ($id) {
        // ================ MODE EDIT ================
        $kondisiId       = $_POST['kondisi_id'] ?? '';
        $tipeBarang      = trim($_POST['tipe_barang'] ?? '');
        $tahunPerolehan  = (int) ($_POST['tahun_perolehan'] ?? 0);
        $nomorInventaris = trim($_POST['nomor_inventaris_kantor'] ?? '');
        $nomorInventaris = $nomorInventaris === '' ? null : $nomorInventaris;

        if (!in_array($kondisiId, $daftarKondisiValid)) {
            throw new Exception('Kondisi barang tidak valid.');
        }
        if ($tipeBarang === '') {
            throw new Exception('Tipe Barang wajib diisi.');
        }

        if ($fotoBaru) {
            $stmt = $pdo->prepare(
                'UPDATE barang SET merek_id=?, lokasi_id=?, spesifikasi=?, nomor_inventaris_kantor=?,
                 kondisi_id=?, tipe_barang=?, tahun_perolehan=?, foto_peralatan=?,
                 user_last_edit=?, timestamp_last_edit=? WHERE id=?'
            );
            $stmt->execute([
                $merekId, $lokasiId, $spesifikasi, $nomorInventaris,
                $kondisiId, $tipeBarang, $tahunPerolehan, $fotoBaru,
                $namaPengguna, $waktuSekarang, $id,
            ]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE barang SET merek_id=?, lokasi_id=?, spesifikasi=?, nomor_inventaris_kantor=?,
                 kondisi_id=?, tipe_barang=?, tahun_perolehan=?,
                 user_last_edit=?, timestamp_last_edit=? WHERE id=?'
            );
            $stmt->execute([
                $merekId, $lokasiId, $spesifikasi, $nomorInventaris,
                $kondisiId, $tipeBarang, $tahunPerolehan,
                $namaPengguna, $waktuSekarang, $id,
            ]);
        }
        $pesan = 'Data barang berhasil diperbarui.';
    } else {
        // ================ MODE TAMBAH BARU ================
        $namaPeralatanInput = trim($_POST['nama_peralatan'] ?? '');
        if ($namaPeralatanInput === '') {
            throw new Exception('Nama Peralatan wajib diisi.');
        }
        $peralatanId = cariAtauBuatPeralatan($pdo, $namaPeralatanInput);

        $jumlahBarang = max(1, min(100, (int) ($_POST['jumlah_barang'] ?? 1)));

        // Ambil data per baris (kalau mode per-unit) atau seragam
        if (!empty($_POST['kondisi_unit']) && is_array($_POST['kondisi_unit'])) {
            $daftarKondisiBaris     = $_POST['kondisi_unit'];
            $daftarTipeBaris        = $_POST['tipe_unit'] ?? [];
            $daftarTahunBaris       = $_POST['tahun_unit'] ?? [];
            $daftarNomorInventaris  = $_POST['nomor_inventaris_unit'] ?? [];
        } else {
            $daftarKondisiBaris    = array_fill(0, $jumlahBarang, $_POST['kondisi_id'] ?? '');
            $daftarTipeBaris       = array_fill(0, $jumlahBarang, trim($_POST['tipe_barang'] ?? ''));
            $daftarTahunBaris      = array_fill(0, $jumlahBarang, (int) ($_POST['tahun_perolehan'] ?? 0));
            $daftarNomorInventaris = array_fill(0, $jumlahBarang, trim($_POST['nomor_inventaris_kantor'] ?? ''));
        }

        $daftarKodeBarang = [];

        foreach ($daftarKondisiBaris as $i => $kondisiIdBaris) {
            $tipeBaris  = trim($daftarTipeBaris[$i] ?? '');
            $tahunBaris = (int) ($daftarTahunBaris[$i] ?? 0);

            if (!in_array($kondisiIdBaris, $daftarKondisiValid)) {
                throw new Exception('Ada kondisi barang yang tidak valid.');
            }
            if ($tipeBaris === '') {
                throw new Exception('Tipe Barang baris #' . ($i + 1) . ' wajib diisi.');
            }

            $nomorInventarisBaris = trim($daftarNomorInventaris[$i] ?? '');
            $nomorInventarisBaris = $nomorInventarisBaris === '' ? null : $nomorInventarisBaris;

            [$kodeBarang, $nomorUrut] = generateKodeBarang($pdo, $kategoriId, $subkategoriId, $peralatanId);

            $stmt = $pdo->prepare(
                'INSERT INTO barang
                 (kode_barang, kategori_id, subkategori_id, merek_id, lokasi_id, nama_peralatan_id,
                  tipe_barang, tahun_perolehan, foto_peralatan, nomor_urut,
                  spesifikasi, nomor_inventaris_kantor, kondisi_id, user_last_edit, timestamp_last_edit)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $kodeBarang, $kategoriId, $subkategoriId, $merekId, $lokasiId, $peralatanId,
                $tipeBaris, $tahunBaris, $fotoBaru, $nomorUrut,
                $spesifikasi, $nomorInventarisBaris, $kondisiIdBaris, $namaPengguna, $waktuSekarang,
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