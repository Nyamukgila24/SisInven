<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

// Filter yang dipakai bersama oleh tampilan, ekspor Excel, dan ekspor PDF
$kondisi = $_GET['kondisi'] ?? '';
$statusAset = $_GET['status_aset'] ?? ''; // 'ada' | 'tanpa' | ''

$hasil = ambilDataLaporan($pdo, $kondisi, $statusAset);

$queryString = http_build_query(['kondisi' => $kondisi, 'status_aset' => $statusAset]);

$judulHalaman = 'Laporan';
require_once __DIR__ . '/../includes/header.php';
?>

<h5 class="mb-1">Laporan & Ekspor Data</h5>
<p class="text-muted small">Saring data sesuai kebutuhan, lalu ekspor ke Excel (.csv) atau PDF (cetak lewat browser).</p>

<div class="card p-3 mb-3">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-md-4">
      <label class="form-label small mb-1">Laporan Berdasarkan Kondisi Barang</label>
      <select name="kondisi" class="form-select">
        <option value="">Semua Kondisi</option>
        <?php foreach (['Baik', 'Rusak', 'Sedang Diperbaiki'] as $k): ?>
          <option value="<?= $k ?>" <?= $kondisi === $k ? 'selected' : '' ?>><?= $k ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label small mb-1">Laporan Berdasarkan Nomor Inventaris Kantor</label>
      <select name="status_aset" class="form-select">
        <option value="">Semua Barang</option>
        <option value="ada" <?= $statusAset === 'ada' ? 'selected' : '' ?>>Punya No. Inventaris (Aset Resmi)</option>
        <option value="tanpa" <?= $statusAset === 'tanpa' ? 'selected' : '' ?>>Tanpa No. Inventaris (Non-Aset)</option>
      </select>
    </div>
    <div class="col-md-4 d-flex gap-2">
      <button class="btn btn-outline-secondary flex-fill"><i class="bi bi-funnel"></i> Terapkan Filter</button>
      <a href="index.php" class="btn btn-outline-danger">Reset</a>
    </div>
  </form>
</div>

<div class="d-flex gap-2 mb-3">
  <a href="export_excel.php?<?= $queryString ?>" class="btn btn-success">
    <i class="bi bi-file-earmark-spreadsheet"></i> Ekspor ke Excel (.csv)
  </a>
  <a href="export_pdf.php?<?= $queryString ?>" class="btn btn-danger" target="_blank">
    <i class="bi bi-file-earmark-pdf"></i> Ekspor ke PDF
  </a>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-sm table-hover mb-0">
      <thead>
        <tr>
          <th>Kode</th><th>Peralatan</th><th>Kategori</th><th>Merek</th><th>Lokasi</th>
          <th>No. Inventaris</th><th>Kondisi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$hasil): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data yang cocok dengan filter.</td></tr>
      <?php endif; ?>
      <?php foreach ($hasil as $row): ?>
        <tr>
          <td class="kode-barang"><?= amankan($row['kode_barang']) ?></td>
          <td><?= amankan($row['nama_peralatan']) ?></td>
          <td><?= amankan($row['nama_kategori']) ?></td>
          <td><?= amankan($row['nama_merek']) ?></td>
          <td><?= amankan($row['nama_ruangan']) ?></td>
          <td><?= $row['nomor_inventaris_kantor'] ? amankan($row['nomor_inventaris_kantor']) : '-' ?></td>
          <td><?= amankan($row['kondisi']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="text-muted small mt-2 mb-0">Total data sesuai filter: <?= count($hasil) ?> barang.</p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
