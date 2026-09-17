<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

$kondisi        = $_GET['kondisi'] ?? '';
$statusAset     = $_GET['status_aset'] ?? '';
$tipeBarang     = $_GET['tipe'] ?? '';
$tahunPerolehan = $_GET['tahun'] ?? '';
$halaman        = max(1, (int) ($_GET['halaman'] ?? 1));
$perHalaman     = 10;

// Ambil data sesuai filter (pakai fungsi dari functions.php)
$data      = ambilDataLaporan($pdo, $kondisi, $statusAset, $tipeBarang, $tahunPerolehan, $halaman, $perHalaman);
$totalData = hitungDataLaporan($pdo, $kondisi, $statusAset, $tipeBarang, $tahunPerolehan);

// Data untuk dropdown filter
$daftarKondisi = $pdo->query('SELECT * FROM kondisi_barang ORDER BY id')->fetchAll();
$daftarTipe    = $pdo->query('SELECT DISTINCT tipe_barang FROM barang WHERE tipe_barang IS NOT NULL AND tipe_barang <> "" ORDER BY tipe_barang')->fetchAll(PDO::FETCH_COLUMN);
$daftarTahun   = $pdo->query('SELECT DISTINCT tahun_perolehan FROM barang WHERE tahun_perolehan IS NOT NULL ORDER BY tahun_perolehan DESC')->fetchAll(PDO::FETCH_COLUMN);

// Query string untuk link ekspor (biar filter ikut terbawa)
$queryEkspor = http_build_query(array_filter([
    'kondisi'     => $kondisi,
    'status_aset' => $statusAset,
    'tipe'        => $tipeBarang,
    'tahun'       => $tahunPerolehan,
], fn($v) => $v !== ''));

$judulHalaman = 'Laporan & Ekspor Data';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h5 class="mb-0">Laporan & Ekspor Data</h5>
    <small class="text-muted">
      Saring data sesuai kebutuhan, lalu ekspor ke Excel (csv) atau PDF (cetak lewat browser).
      Ekspor tetap mencakup semua data sesuai filter, bukan hanya halaman yang sedang dilihat.
    </small>
  </div>
</div>

<!-- ==================== FILTER ==================== -->
<div class="card p-3 mb-3">
  <form method="get" class="row g-2 align-items-end">

    <div class="col-md-3">
      <label class="form-label small mb-1">Kondisi Barang</label>
      <select name="kondisi" class="form-select">
        <option value="">Semua Kondisi</option>
        <?php foreach ($daftarKondisi as $kb): ?>
          <option value="<?= $kb['id'] ?>" <?= $kondisi == $kb['id'] ? 'selected' : '' ?>>
            <?= amankan($kb['nama_kondisi']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label small mb-1">Tipe Barang</label>
      <select name="tipe" class="form-select">
        <option value="">Semua Tipe</option>
        <?php foreach ($daftarTipe as $t): ?>
          <option value="<?= amankan($t) ?>" <?= $tipeBarang === $t ? 'selected' : '' ?>>
            <?= amankan($t) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-2">
      <label class="form-label small mb-1">Tahun Perolehan</label>
      <select name="tahun" class="form-select">
        <option value="">Semua Tahun</option>
        <?php foreach ($daftarTahun as $th): ?>
          <option value="<?= $th ?>" <?= $tahunPerolehan == $th ? 'selected' : '' ?>>
            <?= amankan($th) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-2">
      <label class="form-label small mb-1">Nomor Inventaris Kantor</label>
      <select name="status_aset" class="form-select">
        <option value="">Semua Barang</option>
        <option value="ada"   <?= $statusAset === 'ada'   ? 'selected' : '' ?>>Punya No. Inventaris</option>
        <option value="tanpa" <?= $statusAset === 'tanpa' ? 'selected' : '' ?>>Tanpa No. Inventaris</option>
      </select>
    </div>

    <div class="col-md-2 d-flex gap-2">
      <button class="btn btn-outline-secondary flex-fill">
        <i class="bi bi-funnel"></i> Terapkan Filter
      </button>
      <a href="index.php" class="btn btn-outline-danger">Reset</a>
    </div>

  </form>
</div>

<!-- ==================== TOMBOL EKSPOR ==================== -->
<div class="d-flex gap-2 mb-3 flex-wrap">
  <a href="export_excel.php<?= $queryEkspor ? '?' . $queryEkspor : '' ?>" class="btn btn-success">
    <i class="bi bi-file-earmark-excel"></i> Ekspor ke Excel (csv)
  </a>
  <a href="export_pdf.php<?= $queryEkspor ? '?' . $queryEkspor : '' ?>" target="_blank" class="btn btn-danger">
    <i class="bi bi-file-earmark-pdf"></i> Ekspor ke PDF
  </a>
</div>

<!-- ==================== TABEL HASIL ==================== -->
<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Peralatan</th>
          <th>Tipe</th>
          <th>Tahun</th>
          <th>Kategori</th>
          <th>Merek</th>
          <th>Lokasi</th>
          <th>No. Inventaris</th>
          <th>Kondisi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$data): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data yang cocok dengan filter.</td></tr>
        <?php endif; ?>
        <?php foreach ($data as $row): ?>
          <tr>
            <td class="kode-barang"><?= amankan($row['kode_barang']) ?></td>
            <td><?= amankan($row['nama_peralatan']) ?></td>
            <td><?= amankan($row['tipe_barang']) ?></td>
            <td><?= amankan($row['tahun_perolehan']) ?></td>
            <td><small><?= amankan($row['nama_kategori']) ?> / <?= amankan($row['nama_subkategori']) ?></small></td>
            <td><?= amankan($row['nama_merek']) ?></td>
            <td><?= amankan($row['nama_ruangan']) ?></td>
            <td>
              <?= $row['nomor_inventaris_kantor']
                    ? amankan($row['nomor_inventaris_kantor'])
                    : '<span class="text-muted">-</span>' ?>
            </td>
            <td>
              <span class="badge" style="background-color: <?= amankan($row['kode_warna']) ?>">
                <?= amankan($row['kondisi']) ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <p class="text-muted small mt-2 mb-0">Total data sesuai filter: <?= $totalData ?> barang.</p>

  <?php renderPaginasi($halaman, $totalData, $perHalaman, [
      'kondisi'     => $kondisi,
      'status_aset' => $statusAset,
      'tipe'        => $tipeBarang,
      'tahun'       => $tahunPerolehan,
  ]); ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>