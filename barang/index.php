<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();
$baseUrl = urlDasar();

$cari = trim($_GET['cari'] ?? '');
$filterKondisi = $_GET['kondisi'] ?? '';
$filterTahun = $_GET['tahun'] ?? '';
$halaman = max(1, (int) ($_GET['halaman'] ?? 1));
$perHalaman = 10;

$sqlDasar = 'FROM barang b
        JOIN kategori k ON k.id = b.kategori_id
        JOIN subkategori s ON s.id = b.subkategori_id
        JOIN merek m ON m.id = b.merek_id
        JOIN lokasi l ON l.id = b.lokasi_id
        JOIN nama_peralatan np ON np.id = b.nama_peralatan_id
        JOIN kondisi_barang kb ON kb.id = b.kondisi_id
        WHERE 1=1';
$params = [];

if ($cari !== '') {
    $sqlDasar .= ' AND (b.kode_barang LIKE ? OR np.nama_peralatan LIKE ? OR b.spesifikasi LIKE ?
                        OR b.nomor_inventaris_kantor LIKE ? OR b.tipe_barang LIKE ?)';
    $like = "%{$cari}%";
    array_push($params, $like, $like, $like, $like, $like);
}
if ($filterKondisi !== '') {
    $sqlDasar .= ' AND b.kondisi_id = ?';
    $params[] = $filterKondisi;
}
if ($filterTahun !== '') {
    $sqlDasar .= ' AND b.tahun_perolehan = ?';
    $params[] = $filterTahun;
}

$stmtTotal = $pdo->prepare('SELECT COUNT(*) ' . $sqlDasar);
$stmtTotal->execute($params);
$totalData = (int) $stmtTotal->fetchColumn();

$offset = ($halaman - 1) * $perHalaman;
$sql = 'SELECT b.*, k.nama_kategori, s.nama_subkategori, m.nama_merek, l.nama_ruangan, np.nama_peralatan,
               kb.nama_kondisi, kb.kode_warna '
     . $sqlDasar . ' ORDER BY b.id DESC LIMIT ' . $perHalaman . ' OFFSET ' . $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar = $stmt->fetchAll();

$daftarKondisi = $pdo->query('SELECT * FROM kondisi_barang ORDER BY id')->fetchAll();
$daftarTahun = $pdo->query('SELECT DISTINCT tahun_perolehan FROM barang WHERE tahun_perolehan IS NOT NULL ORDER BY tahun_perolehan DESC')->fetchAll(PDO::FETCH_COLUMN);

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
    <div class="col-md-4">
      <label class="form-label small mb-1">Cari (kode, peralatan, tipe, spesifikasi, no. inventaris)</label>
      <input type="text" name="cari" class="form-control" value="<?= amankan($cari) ?>" placeholder="Ketik kata kunci...">
    </div>
    <div class="col-md-3">
      <label class="form-label small mb-1">Kondisi</label>
      <select name="kondisi" class="form-select">
        <option value="">Semua Kondisi</option>
        <?php foreach ($daftarKondisi as $kb): ?>
          <option value="<?= $kb['id'] ?>" <?= $filterKondisi == $kb['id'] ? 'selected' : '' ?>><?= amankan($kb['nama_kondisi']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small mb-1">Tahun</label>
      <select name="tahun" class="form-select">
        <option value="">Semua</option>
        <?php foreach ($daftarTahun as $th): ?>
          <option value="<?= $th ?>" <?= $filterTahun == $th ? 'selected' : '' ?>><?= amankan($th) ?></option>
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
          <th>Tipe</th>
          <th>Tahun</th>
          <th>Lokasi</th>
          <th>No. Inventaris</th>
          <th>Kondisi</th>
          <th style="width:140px; white-space:nowrap;">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$daftar): ?>
        <tr><td colspan="10" class="text-center text-muted py-4">Tidak ada data barang yang cocok.</td></tr>
      <?php endif; ?>
      <?php foreach ($daftar as $row): ?>
        <tr>
          <td class="kode-barang"><?= amankan($row['kode_barang']) ?></td>
          <td><?= amankan($row['nama_peralatan']) ?></td>
          <td><small><?= amankan($row['nama_kategori']) ?> / <?= amankan($row['nama_subkategori']) ?></small></td>
          <td><?= amankan($row['nama_merek']) ?></td>
          <td><?= amankan($row['tipe_barang']) ?></td>
          <td><?= amankan($row['tahun_perolehan']) ?></td>
          <td><?= amankan($row['nama_ruangan']) ?></td>
          <td><?= $row['nomor_inventaris_kantor'] ? amankan($row['nomor_inventaris_kantor']) : '<span class="text-muted">-</span>' ?></td>
          <td><span class="badge" style="background-color: <?= amankan($row['kode_warna']) ?>"><?= amankan($row['nama_kondisi']) ?></span></td>
          <td>
            <button class="btn btn-sm btn-outline-info" onclick='bukaDetail(<?= json_encode($row) ?>)' title="Lihat Detail">
            <i class="bi bi-eye"></i>
            </button>
            <a href="form.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ubah"><i class="bi bi-pencil"></i></a>
            <button class="btn btn-sm btn-outline-danger" onclick="hapus(<?= $row['id'] ?>)" title="Hapus"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="text-muted small mt-2 mb-0">Total data sesuai filter: <?= $totalData ?> barang.</p>
  <?php renderPaginasi($halaman, $totalData, $perHalaman, ['cari' => $cari, 'kondisi' => $filterKondisi, 'tahun' => $filterTahun]); ?>
</div>

<form method="post" action="hapus.php" id="formHapus" class="d-none">
  <input type="hidden" name="id" id="hId">
</form>
<script>
  
function hapus(id) {
  if (confirm('Hapus barang ini?')) {
    document.getElementById('hId').value = id;
    document.getElementById('formHapus').submit();
  }
}

function bukaDetail(row) {
  document.getElementById('dKode').textContent      = row.kode_barang || '-';
  document.getElementById('dPeralatan').textContent = row.nama_peralatan || '-';
  document.getElementById('dKategori').textContent  = (row.nama_kategori || '-') + ' / ' + (row.nama_subkategori || '-');
  document.getElementById('dMerek').textContent     = row.nama_merek || '-';
  document.getElementById('dTipe').textContent      = row.tipe_barang || '-';
  document.getElementById('dTahun').textContent     = row.tahun_perolehan || '-';
  document.getElementById('dLokasi').textContent    = row.nama_ruangan || '-';
  document.getElementById('dNoInv').textContent     = row.nomor_inventaris_kantor || '-';
  document.getElementById('dSpesifikasi').textContent = row.spesifikasi || '-';

  document.getElementById('dKondisi').innerHTML =
    '<span class="badge" style="background-color:' + (row.kode_warna || '#6c757d') + '">' +
    (row.nama_kondisi || '-') + '</span>';

  document.getElementById('dEdit').innerHTML =
    (row.user_last_edit || '-') + '<br><small class="text-muted">' +
    (row.timestamp_last_edit || '-') + '</small>';

  const img    = document.getElementById('dFoto');
  const kosong = document.getElementById('dFotoKosong');
  if (row.foto_peralatan) {
    img.src = '<?= $baseUrl ?>/uploads/peralatan/' + row.foto_peralatan;
    img.classList.remove('d-none');
    kosong.classList.add('d-none');
  } else {
    img.src = '';
    img.classList.add('d-none');
    kosong.classList.remove('d-none');
  }

  new bootstrap.Modal(document.getElementById('modalDetail')).show();
}
</script>

<div class="modal fade" id="modalDetail" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-info-circle"></i> Detail Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">

          <!-- ===== KIRI: Foto (50%) ===== -->
          <div class="col-md-6">
            <div id="dFotoWrap" class="text-center">
              <img id="dFoto" src="" alt="Foto Barang"
                  class="img-fluid rounded d-none w-100"
                  style="max-height: 500px; object-fit: contain;">
              <div id="dFotoKosong" class="text-muted py-5">
                <i class="bi bi-image" style="font-size: 4rem; opacity: 0.3;"></i>
                <p class="mt-2 mb-0">Tidak ada foto</p>
              </div>
            </div>
          </div>

          <!-- ===== KANAN: Info Detail (50%) ===== -->
          <div class="col-md-6">
            <table class="table table-sm mb-0">
              <tbody>
                <tr><th width="40%">Kode Barang</th><td id="dKode" class="kode-barang"></td></tr>
                <tr><th>Nama Peralatan</th><td id="dPeralatan"></td></tr>
                <tr><th>Kategori / Sub</th><td id="dKategori"></td></tr>
                <tr><th>Merek</th><td id="dMerek"></td></tr>
                <tr><th>Tipe Barang</th><td id="dTipe"></td></tr>
                <tr><th>Tahun Perolehan</th><td id="dTahun"></td></tr>
                <tr><th>Lokasi / Ruangan</th><td id="dLokasi"></td></tr>
                <tr><th>No. Inventaris</th><td id="dNoInv"></td></tr>
                <tr><th>Kondisi</th><td id="dKondisi"></td></tr>
                <tr><th>Spesifikasi</th><td id="dSpesifikasi"></td></tr>
                <tr><th>Terakhir Diubah</th><td id="dEdit"></td></tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>