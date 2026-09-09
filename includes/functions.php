<?php
/**
 * =====================================================================
 * KUMPULAN FUNGSI BANTU
 * =====================================================================
 * Semua "logika inti" aplikasi dikumpulkan di sini supaya mudah dicari
 * dan dijelaskan. Setiap fungsi punya penjelasan singkat di atasnya.
 * =====================================================================
 */

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Mengecek apakah pengguna sudah login. Jika belum, tendang ke
 * halaman login. Panggil fungsi ini di baris paling atas setiap
 * halaman yang WAJIB login (semua halaman kecuali login.php).
 */
function wajibLogin() {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . urlDasar() . '/login.php');
        exit;
    }
}

/**
 * Mengembalikan path dasar aplikasi (folder tempat aplikasi diinstall),
 * supaya link antar halaman tetap benar walau folder instalasi
 * namanya diganti oleh pengguna (misal dari "sisinven" jadi "inventaris").
 */
function urlDasar() {
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Naik satu level jika file berada di dalam sub-folder (master/, barang/, dst.)
    $folders = ['/master', '/barang', '/user', '/laporan'];
    foreach ($folders as $f) {
        if (substr($script, -strlen($f)) === $f) {
            $script = substr($script, 0, -strlen($f));
        }
    }
    return $script === '/' ? '' : $script;
}

/**
 * Membersihkan input teks sederhana untuk ditampilkan di HTML,
 * mencegah serangan XSS.
 */
function amankan($teks) {
    return htmlspecialchars($teks ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Mencatat "siapa & kapan" pada sebuah data barang.
 * Dipanggil setiap kali barang ditambah atau diubah, sesuai kebutuhan
 * Audit Log Sederhana (kolom user_last_edit & timestamp_last_edit).
 * Mengembalikan array [nama_pengguna, waktu_sekarang].
 */
function catatJejakPerubahan() {
    return [$_SESSION['nama_lengkap'] ?? 'Tidak diketahui', date('Y-m-d H:i:s')];
}

/**
 * ---------------------------------------------------------------
 * INTI ATURAN PENGODEAN BARANG (lihat FRD bagian 3.2)
 * ---------------------------------------------------------------
 * Format kode: [Kode Kategori].[Kode Subkategori].[Kode Peralatan].[No Urut]
 * Nomor urut dihitung otomatis: barang ke berapa untuk kombinasi
 * kategori + subkategori + peralatan yang SAMA.
 *
 * Fungsi ini TIDAK boleh dipanggil dua kali untuk barang yang sama,
 * dan harus dijalankan tepat sebelum INSERT supaya nomor urutnya
 * tidak bentrok (lihat barang/simpan.php yang membungkusnya
 * dengan transaction + lock).
 * ---------------------------------------------------------------
 */
function generateKodeBarang($pdo, $kategoriId, $subkategoriId, $peralatanId) {
    // Ambil kode dari masing-masing tabel master
    $stmt = $pdo->prepare('SELECT kode_kategori FROM kategori WHERE id = ?');
    $stmt->execute([$kategoriId]);
    $kodeKategori = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT kode_subkategori FROM subkategori WHERE id = ? AND kategori_id = ?');
    $stmt->execute([$subkategoriId, $kategoriId]);
    $kodeSubkategori = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT kode_peralatan FROM nama_peralatan WHERE id = ?');
    $stmt->execute([$peralatanId]);
    $kodePeralatan = $stmt->fetchColumn();

    if ($kodeKategori === false || $kodeSubkategori === false || $kodePeralatan === false) {
        throw new Exception('Data master tidak lengkap/tidak sesuai untuk membuat kode barang.');
    }

    // Cari nomor urut berikutnya untuk kombinasi kategori+subkategori+peralatan ini
    $stmt = $pdo->prepare(
        'SELECT COALESCE(MAX(nomor_urut), 0) + 1
         FROM barang
         WHERE kategori_id = ? AND subkategori_id = ? AND nama_peralatan_id = ?
         FOR UPDATE'
    );
    $stmt->execute([$kategoriId, $subkategoriId, $peralatanId]);
    $nomorUrut = (int) $stmt->fetchColumn();

    $kodeBarang = "{$kodeKategori}.{$kodeSubkategori}.{$kodePeralatan}.{$nomorUrut}";

    return [$kodeBarang, $nomorUrut];
}

/**
 * Menghitung kode_subkategori berikutnya (otomatis, urut per kategori).
 */
function nextKodeSubkategori($pdo, $kategoriId) {
    $stmt = $pdo->prepare('SELECT COALESCE(MAX(kode_subkategori), 0) + 1 FROM subkategori WHERE kategori_id = ?');
    $stmt->execute([$kategoriId]);
    return (int) $stmt->fetchColumn();
}

/**
 * Menghitung kode_peralatan berikutnya (otomatis, urut global).
 */
function nextKodePeralatan($pdo) {
    $stmt = $pdo->query('SELECT COALESCE(MAX(kode_peralatan), 0) + 1 FROM nama_peralatan');
    return (int) $stmt->fetchColumn();
}

/**
 * Cek apakah sebuah data master masih dipakai oleh barang, supaya
 * tidak bisa dihapus sembarangan dan merusak data barang (mencegah
 * error / data yatim).
 */
function masterMasihDipakai($pdo, $kolom, $id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE {$kolom} = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Query data laporan barang dengan filter kondisi & status nomor
 * inventaris kantor. Dipakai bersama oleh laporan/index.php,
 * export_excel.php, dan export_pdf.php supaya hasilnya selalu sama.
 */
function ambilDataLaporan($pdo, $kondisi = '', $statusAset = '', $halaman = null, $perHalaman = 10) {
    $sql = 'SELECT b.kode_barang, np.nama_peralatan, k.nama_kategori, s.nama_subkategori, m.nama_merek,
                   l.nama_ruangan, b.spesifikasi, b.nomor_inventaris_kantor, b.kondisi,
                   b.user_last_edit, b.timestamp_last_edit
            FROM barang b
            JOIN kategori k ON k.id = b.kategori_id
            JOIN subkategori s ON s.id = b.subkategori_id
            JOIN merek m ON m.id = b.merek_id
            JOIN lokasi l ON l.id = b.lokasi_id
            JOIN nama_peralatan np ON np.id = b.nama_peralatan_id
            WHERE 1=1';
    $params = [];

    if ($kondisi !== '') {
        $sql .= ' AND b.kondisi = ?';
        $params[] = $kondisi;
    }
    if ($statusAset === 'ada') {
        $sql .= ' AND b.nomor_inventaris_kantor IS NOT NULL AND b.nomor_inventaris_kantor <> ""';
    } elseif ($statusAset === 'tanpa') {
        $sql .= ' AND (b.nomor_inventaris_kantor IS NULL OR b.nomor_inventaris_kantor = "")';
    }
    $sql .= ' ORDER BY b.kode_barang';

    if ($halaman !== null) {
        $sql .= ' LIMIT ' . (int) $perHalaman . ' OFFSET ' . (int) (($halaman - 1) * $perHalaman);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Menghitung total data laporan (dengan filter yang sama), dipakai
 * untuk menentukan jumlah halaman pagination.
 */
function hitungDataLaporan($pdo, $kondisi = '', $statusAset = '') {
    $sql = 'SELECT COUNT(*) FROM barang b WHERE 1=1';
    $params = [];
    if ($kondisi !== '') {
        $sql .= ' AND b.kondisi = ?';
        $params[] = $kondisi;
    }
    if ($statusAset === 'ada') {
        $sql .= ' AND b.nomor_inventaris_kantor IS NOT NULL AND b.nomor_inventaris_kantor <> ""';
    } elseif ($statusAset === 'tanpa') {
        $sql .= ' AND (b.nomor_inventaris_kantor IS NULL OR b.nomor_inventaris_kantor = "")';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

/**
 * Menampilkan navigasi pagination Bootstrap. Dipakai bersama oleh
 * barang/index.php dan laporan/index.php.
 * $paramsLain = filter/pencarian yang sedang aktif (selain 'halaman'),
 * supaya saat pindah halaman filter-nya tidak hilang.
 */
function renderPaginasi($halamanSekarang, $totalData, $perHalaman, $paramsLain = []) {
    $totalHalaman = (int) ceil($totalData / $perHalaman);
    if ($totalHalaman <= 1) return;

    $paramsLain = array_filter($paramsLain, fn($v) => $v !== '');
    echo '<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center mb-0">';
    for ($i = 1; $i <= $totalHalaman; $i++) {
        $query = http_build_query(array_merge($paramsLain, ['halaman' => $i]));
        $aktif = $i === $halamanSekarang ? ' active' : '';
        echo "<li class=\"page-item{$aktif}\"><a class=\"page-link\" href=\"?{$query}\">{$i}</a></li>";
    }
    echo '</ul></nav>';
}

/**
 * Pesan flash sederhana (notifikasi sukses/gagal) yang bertahan
 * satu kali pindah halaman saja, dipakai lewat session.
 */
function setFlash($tipe, $pesan) {
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function ambilFlash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
