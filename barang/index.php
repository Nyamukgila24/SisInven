<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

$cari = trim($_GET['cari'] ?? '');
$filterKondisi = $_GET['kondisi'] ?? '';
$halaman = max(1, (int) ($_GET['halaman'] ?? 1));
$perHalaman = 10;

$sqlDasar = 'FROM barang b
        JOIN kategori k ON k.id = b.kategori_id
        JOIN subkategori s ON s.id = b.subkategori_id
        JOIN merek m ON m.id = b.merek_id
        JOIN lokasi l ON l.id = b.lokasi_id
        JOIN nama_peralatan np ON np.id = b.nama_peralatan_id
        WHERE 1=1';
$params = [];

if ($cari !== '') {
    $sqlDasar .= ' AND (b.kode_barang LIKE ? OR np.nama_peralatan LIKE ? OR b.spesifikasi LIKE ? OR b.nomor_inventaris_kantor LIKE ?)';
    $like = "%{$cari}%";
    array_push($params, $like, $like, $like, $like);
}
if ($filterKondisi !== '') {
    $sqlDasar .= ' AND b.kondisi = ?';
    $params[] = $filterKondisi;
}

$stmtTotal = $pdo->prepare('SELECT COUNT(*) ' . $sqlDasar);
$stmtTotal->execute($params);
$totalData = (int) $stmtTotal->fetchColumn();

$offset = ($halaman - 1) * $perHalaman;
$sql = 'SELECT b.*, k.nama_kategori, s.nama_subkategori, m.nama_merek, l.nama_ruangan, np.nama_peralatan '
     . $sqlDasar . ' ORDER BY b.id DESC LIMIT ' . $perHalaman . ' OFFSET ' . $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar = $stmt->fetchAll();

$judulHalaman = 'Data Barang';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h5 class="mb-0">Data Barang</h5>
    <small class="text-muted">Kode Barang dibuat otomatis oleh sistem sesuai Kategori, Subkategori, dan Nama Peralatan yang dipilih.</small>
  </div>
  <a href="form.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Barang</a>
</div>

<div class="card p-3 mb-3">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-md-6">
      <label class="form-label small mb-1">Cari (kode, peralatan, spesifikasi, no. inventaris)</label>
      <input type="text" name="cari" class="form-control" value="<?= amankan($cari) ?>" placeholder="Ketik kata kunci...">
    </div>
    <div class="col-md-3">
      <label class="form-label small mb-1">Kondisi</label>
      <select name="kondisi" class="form-select">
        <option value="">Semua Kondisi</option>
        <?php foreach (['Baik', 'Rusak', 'Sedang Diperbaiki'] as $k): ?>
          <option value="<?= $k ?>" <?= $filterKondisi === $k ? 'selected' : '' ?>><?= $k ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <button class="btn btn-outline-secondary flex-fill"><i class="bi bi-search"></i> Cari</button>
      <a href="index.php" class="btn btn-outline-danger">Reset</a>
    </div>
  </form>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Kode Barang</th>
          <th>Peralatan</th>
          <th>Kategori / Subkategori</th>
          <th>Merek</th>
          <th>Lokasi</th>
          <th>No. Inventaris</th>
          <th>Kondisi</th>
          <th style="width:120px">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$daftar): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data barang yang cocok.</td></tr>
      <?php endif; ?>
      <?php foreach ($daftar as $row): ?>
        <?php
          $badgeClass = ['Baik' => 'badge-baik', 'Rusak' => 'badge-rusak', 'Sedang Diperbaiki' => 'badge-diperbaiki'][$row['kondisi']];
        ?>
        <tr>
          <td class="kode-barang"><?= amankan($row['kode_barang']) ?></td>
          <td><?= amankan($row['nama_peralatan']) ?></td>
          <td><small><?= amankan($row['nama_kategori']) ?> / <?= amankan($row['nama_subkategori']) ?></small></td>
          <td><?= amankan($row['nama_merek']) ?></td>
          <td><?= amankan($row['nama_ruangan']) ?></td>
          <td><?= $row['nomor_inventaris_kantor'] ? amankan($row['nomor_inventaris_kantor']) : '<span class="text-muted">-</span>' ?></td>
          <td><span class="badge <?= $badgeClass ?>"><?= amankan($row['kondisi']) ?></span></td>
          <td>
            <a href="form.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <button class="btn btn-sm btn-outline-danger" onclick="hapus(<?= $row['id'] ?>)"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="text-muted small mt-2 mb-0">Total data sesuai filter: <?= $totalData ?> barang.</p>
  <?php renderPaginasi($halaman, $totalData, $perHalaman, ['cari' => $cari, 'kondisi' => $filterKondisi]); ?>
</div>

<form method="post" action="hapus.php" id="formHapus" class="d-none">
  <input type="hidden" name="id" id="hId">
</form>
<script>
function hapus(id) {
  if (confirm('Hapus barang ini? Kode barang yang sudah dipakai tidak akan dipakai ulang otomatis.')) {
    document.getElementById('hId').value = id;
    document.getElementById('formHapus').submit();
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>