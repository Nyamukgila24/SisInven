<?php
date_default_timezone_set('Asia/Jakarta');

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
 * Mencari kode peralatan (digit ke-3) yang unik untuk subkategori tertentu.
 * Kalau barang sudah ada di subkategori itu, pakai kodenya.
 * Kalau belum ada, cari nomor urut peralatan terakhir di subkategori itu, lalu +1.
 */
function getKodePeralatanSpesifik($pdo, $subkategoriId, $peralatanId, $excludeIds = []) {
    $excludeClause = '';
    if (!empty($excludeIds)) {
        // Ubah array ID jadi string: 1,2,3
        $idStr = implode(',', array_map('intval', $excludeIds));
        $excludeClause = " AND id NOT IN ($idStr) ";
    }

    // Cek apakah peralatan ini sudah ada di subkategori ini
    $stmt = $pdo->prepare("
        SELECT kode_barang FROM barang 
        WHERE subkategori_id = ? AND nama_peralatan_id = ? $excludeClause
        LIMIT 1
    ");
    $stmt->execute([$subkategoriId, $peralatanId]);
    $kodeBarangExisting = $stmt->fetchColumn();

    if ($kodeBarangExisting) {
        // Kalau sudah ada, ambil digit ke-3 dari kode barang lama (contoh: 1.3.1.5 -> ambil angka 1)
        $pecah = explode('.', $kodeBarangExisting);
        return $pecah[2] ?? '1';
    }

    // Kalau belum ada, cari angka ke-3 terbesar di subkategori ini, lalu +1
    $stmt = $pdo->prepare("
        SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(kode_barang, '.', 3), '.', -1) AS UNSIGNED)) 
        FROM barang 
        WHERE subkategori_id = ? $excludeClause
    ");
    $stmt->execute([$subkategoriId]);
    $maxKode = $stmt->fetchColumn();

    return ($maxKode === null || $maxKode == 0) ? 1 : $maxKode + 1;
}

function generateKodeBarang($pdo, $kategoriId, $subkategoriId, $peralatanId) {
    // Ambil kode dari tabel master
    $stmt = $pdo->prepare('SELECT kode_kategori FROM kategori WHERE id = ?');
    $stmt->execute([$kategoriId]);
    $kodeKategori = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT kode_subkategori FROM subkategori WHERE id = ? AND kategori_id = ?');
    $stmt->execute([$subkategoriId, $kategoriId]);
    $kodeSubkategori = $stmt->fetchColumn();

    if ($kodeKategori === false || $kodeSubkategori === false) {
        throw new Exception('Data master tidak lengkap/tidak sesuai untuk membuat kode barang.');
    }

    // DAPATKAN DIGIT KE-3 DENGAN FUNGSI BARU
    $kodePeralatan = getKodePeralatanSpesifik($pdo, $subkategoriId, $peralatanId);

    // Cari nomor urut berikutnya (digit ke-4)
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

function ambilDataLaporan($pdo, $kondisiId = '', $statusAset = '', $tipeBarang = '', $tahunPerolehan = '', $peralatanId = '', $merekId = '', $lokasiId = '', $halaman = null, $perHalaman = 10) {
    $sql = 'SELECT b.kode_barang, np.nama_peralatan, b.tipe_barang, b.tahun_perolehan,
                   k.nama_kategori, s.nama_subkategori, m.nama_merek,
                   l.nama_ruangan, b.spesifikasi, b.nomor_inventaris_kantor,
                   kb.nama_kondisi AS kondisi, kb.kode_warna,
                   b.user_last_edit, b.timestamp_last_edit
            FROM barang b
            JOIN kategori k ON k.id = b.kategori_id
            JOIN subkategori s ON s.id = b.subkategori_id
            JOIN merek m ON m.id = b.merek_id
            JOIN lokasi l ON l.id = b.lokasi_id
            JOIN nama_peralatan np ON np.id = b.nama_peralatan_id
            JOIN kondisi_barang kb ON kb.id = b.kondisi_id
            WHERE 1=1';
    $params = [];

    if ($kondisiId !== '') {
        $sql .= ' AND b.kondisi_id = ?';
        $params[] = $kondisiId;
    }
    if ($statusAset === 'ada') {
        $sql .= ' AND b.nomor_inventaris_kantor IS NOT NULL AND b.nomor_inventaris_kantor <> ""';
    } elseif ($statusAset === 'tanpa') {
        $sql .= ' AND (b.nomor_inventaris_kantor IS NULL OR b.nomor_inventaris_kantor = "")';
    }
    // ===== BARU =====
    if ($tipeBarang !== '') {
        $sql .= ' AND b.tipe_barang = ?';
        $params[] = $tipeBarang;
    }
    if ($tahunPerolehan !== '') {
        $sql .= ' AND b.tahun_perolehan = ?';
        $params[] = $tahunPerolehan;
    }
        if ($peralatanId !== '') {
        $sql .= ' AND b.nama_peralatan_id = ?';
        $params[] = $peralatanId;
    }
    if ($merekId !== '') {
        $sql .= ' AND b.merek_id = ?';
        $params[] = $merekId;
    }
    if ($lokasiId !== '') {
        $sql .= ' AND b.lokasi_id = ?';
        $params[] = $lokasiId;
    }

    $sql .= ' ORDER BY b.id DESC';

    if ($halaman !== null) {
        $sql .= ' LIMIT ' . (int) $perHalaman . ' OFFSET ' . (int) (($halaman - 1) * $perHalaman);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function hitungDataLaporan($pdo, $kondisiId = '', $statusAset = '', $tipeBarang = '', $tahunPerolehan = '', $peralatanId = '', $merekId = '', $lokasiId = '') {
    $sql = 'SELECT COUNT(*) FROM barang b WHERE 1=1';
    $params = [];

    if ($kondisiId !== '') {
        $sql .= ' AND b.kondisi_id = ?';
        $params[] = $kondisiId;
    }
    if ($statusAset === 'ada') {
        $sql .= ' AND b.nomor_inventaris_kantor IS NOT NULL AND b.nomor_inventaris_kantor <> ""';
    } elseif ($statusAset === 'tanpa') {
        $sql .= ' AND (b.nomor_inventaris_kantor IS NULL OR b.nomor_inventaris_kantor = "")';
    }
    if ($tipeBarang !== '') {
        $sql .= ' AND b.tipe_barang = ?';
        $params[] = $tipeBarang;
    }
    if ($tahunPerolehan !== '') {
        $sql .= ' AND b.tahun_perolehan = ?';
        $params[] = $tahunPerolehan;
    }
    if ($peralatanId !== '') {
        $sql .= ' AND b.nama_peralatan_id = ?';
        $params[] = $peralatanId;
    }
    if ($merekId !== '') {
        $sql .= ' AND b.merek_id = ?';
        $params[] = $merekId;
    }
    if ($lokasiId !== '') {
        $sql .= ' AND b.lokasi_id = ?';
        $params[] = $lokasiId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function ambilRingkasanBarang($pdo) {
    $daftarKondisi = $pdo->query('SELECT id, nama_kondisi FROM kondisi_barang ORDER BY id')->fetchAll();

    $sql = 'SELECT np.nama_peralatan,
                   kb.id AS kondisi_id,
                   COUNT(*) AS jumlah
            FROM barang b
            JOIN nama_peralatan np ON np.id = b.nama_peralatan_id
            JOIN kondisi_barang kb ON kb.id = b.kondisi_id
            GROUP BY np.nama_peralatan, kb.id
            ORDER BY np.nama_peralatan';
    $rows = $pdo->query($sql)->fetchAll();

    $ringkasan = [];
    foreach ($rows as $r) {
        $nama = $r['nama_peralatan'];
        if (!isset($ringkasan[$nama])) {
            $ringkasan[$nama] = ['total' => 0, 'kondisi' => []];
        }
        $ringkasan[$nama]['kondisi'][$r['kondisi_id']] = (int) $r['jumlah'];
        $ringkasan[$nama]['total'] += (int) $r['jumlah'];
    }

    return [
        'daftar_kondisi' => $daftarKondisi,
        'ringkasan'      => $ringkasan,
    ];
}

function renderPaginasi($halamanSekarang, $totalData, $perHalaman, $paramsLain = []) {
    $totalHalaman = (int) ceil($totalData / $perHalaman);
    if ($totalHalaman <= 1) return;

    // Bersihkan parameter URL
    $paramsLain = array_filter($paramsLain, fn($v) => $v !== '');
    $queryDasar = $paramsLain ? '&' . http_build_query($paramsLain) : '';

    echo '<nav class="mt-3"><ul class="pagination justify-content-center mb-0">';

    // 1. Tombol Awal (<<)
    $linkAwal = '?halaman=1' . $queryDasar;
    echo '<li class="page-item ' . ($halamanSekarang <= 1 ? 'disabled' : '') . '">';
    echo '<a class="page-link" href="' . ($halamanSekarang <= 1 ? '#' : $linkAwal) . '">&laquo;</a></li>';

    // 2. Tombol Previous (<)
    $linkPrev = '?halaman=' . ($halamanSekarang - 1) . $queryDasar;
    echo '<li class="page-item ' . ($halamanSekarang <= 1 ? 'disabled' : '') . '">';
    echo '<a class="page-link" href="' . ($halamanSekarang <= 1 ? '#' : $linkPrev) . '">&lsaquo;</a></li>';

    // 3. Logika Angka & Titik-titik
    $mulai = max(1, $halamanSekarang - 2);
    $selesai = min($totalHalaman, $halamanSekarang + 2);

    // Titik kiri (...)
    if ($mulai > 1) {
        if ($mulai > 2) {
            // Kalau jaraknya jauh, tampilkan tombol titik
            echo '<li class="page-item"><a class="page-link" href="#" onclick="bukaModalHalaman(1, ' . ($mulai - 1) . ', ' . $halamanSekarang . ', \'' . htmlspecialchars($queryDasar) . '\'); return false;">...</a></li>';
        } else {
            // Kalau cuma sisa 1 angka (misal halaman 3), langsung tampilkan angka 1
            echo '<li class="page-item"><a class="page-link" href="?halaman=1' . $queryDasar . '">1</a></li>';
        }
    }

    // Looping angka tengah
    for ($i = $mulai; $i <= $selesai; $i++) {
        $aktif = $i === $halamanSekarang ? 'active' : '';
        $link = '?halaman=' . $i . $queryDasar;
        echo '<li class="page-item ' . $aktif . '"><a class="page-link" href="' . $link . '">' . $i . '</a></li>';
    }

    // Titik kanan (...)
    if ($selesai < $totalHalaman) {
        if ($selesai < $totalHalaman - 1) {
            // Kalau jaraknya jauh, tampilkan tombol titik
            echo '<li class="page-item"><a class="page-link" href="#" onclick="bukaModalHalaman(' . ($selesai + 1) . ', ' . $totalHalaman . ', ' . $halamanSekarang . ', \'' . htmlspecialchars($queryDasar) . '\'); return false;">...</a></li>';
        } else {
            // Kalau cuma sisa 1 angka, langsung tampilkan angka terakhir
            echo '<li class="page-item"><a class="page-link" href="?halaman=' . $totalHalaman . $queryDasar . '">' . $totalHalaman . '</a></li>';
        }
    }

    // 4. Tombol Next (>)
    $linkNext = '?halaman=' . ($halamanSekarang + 1) . $queryDasar;
    echo '<li class="page-item ' . ($halamanSekarang >= $totalHalaman ? 'disabled' : '') . '">';
    echo '<a class="page-link" href="' . ($halamanSekarang >= $totalHalaman ? '#' : $linkNext) . '">&rsaquo;</a></li>';

    // 5. Tombol Akhir (>>)
    $linkAkhir = '?halaman=' . $totalHalaman . $queryDasar;
    echo '<li class="page-item ' . ($halamanSekarang >= $totalHalaman ? 'disabled' : '') . '">';
    echo '<a class="page-link" href="' . ($halamanSekarang >= $totalHalaman ? '#' : $linkAkhir) . '">&raquo;</a></li>';

    echo '</ul></nav>';
}

function setFlash($tipe, $pesan) {
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

/**
 * Cari nama_peralatan yang sudah ada (case-insensitive); kalau belum
 * ada, daftarkan otomatis dengan kode_peralatan berikutnya. Dipakai
 * karena "Nama Peralatan" sekarang diketik bebas, bukan dropdown
 * master terpisah lagi - tapi kode barang tetap butuh ID & kode urut.
 */
function cariAtauBuatPeralatan($pdo, $nama) {
    $nama = trim($nama);
    $stmt = $pdo->prepare('SELECT id FROM nama_peralatan WHERE nama_peralatan = ?');
    $stmt->execute([$nama]);
    $id = $stmt->fetchColumn();
    if ($id) return $id;

    $kodeBaru = nextKodePeralatan($pdo);
    $stmt = $pdo->prepare('INSERT INTO nama_peralatan (kode_peralatan, nama_peralatan) VALUES (?, ?)');
    $stmt->execute([$kodeBaru, $nama]);
    return $pdo->lastInsertId();
}

/**
 * Simpan file foto peralatan yang diupload ke folder uploads/peralatan/.
 * Mengembalikan nama file (untuk disimpan di kolom foto_peralatan),
 * atau null kalau tidak ada file yang diupload / gagal.
 */
function simpanFotoPeralatan() {
    if (empty($_FILES['foto_peralatan']['name']) || $_FILES['foto_peralatan']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $ekstensiDiizinkan = ['jpg', 'jpeg', 'png', 'webp'];
    $ekstensi = strtolower(pathinfo($_FILES['foto_peralatan']['name'], PATHINFO_EXTENSION));
    if (!in_array($ekstensi, $ekstensiDiizinkan, true)) return null;

    $folderTujuan = __DIR__ . '/../uploads/peralatan/';
    if (!is_dir($folderTujuan)) mkdir($folderTujuan, 0755, true);

    $namaFile = uniqid('foto_') . '.' . $ekstensi;
    move_uploaded_file($_FILES['foto_peralatan']['tmp_name'], $folderTujuan . $namaFile);
    return $namaFile;
}

function ambilFlash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
