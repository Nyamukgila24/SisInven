<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

$idBarang = $_GET['id'] ?? null;
$data = [
    'kategori_id' => '', 'subkategori_id' => '', 'merek_id' => '', 'lokasi_id' => '',
    'nama_peralatan_id' => '', 'spesifikasi' => '', 'nomor_inventaris_kantor' => '', 'kondisi' => 'Baik',
];
$modeEdit = false;

if ($idBarang) {
    $stmt = $pdo->prepare('SELECT * FROM barang WHERE id = ?');
    $stmt->execute([$idBarang]);
    $existing = $stmt->fetch();
    if (!$existing) {
        setFlash('gagal', 'Data barang tidak ditemukan.');
        header('Location: index.php');
        exit;
    }
    $data = $existing;
    $modeEdit = true;
}

$daftarKategori = $pdo->query('SELECT * FROM kategori ORDER BY nama_kategori')->fetchAll();
$daftarMerek = $pdo->query('SELECT * FROM merek ORDER BY nama_merek')->fetchAll();
$daftarLokasi = $pdo->query('SELECT * FROM lokasi ORDER BY nama_ruangan')->fetchAll();
$daftarPeralatan = $pdo->query('SELECT * FROM nama_peralatan ORDER BY nama_peralatan')->fetchAll();

// Subkategori awal (jika mode edit / kategori sudah pernah dipilih)
$daftarSubkategoriAwal = [];
if ($data['kategori_id']) {
    $stmt = $pdo->prepare('SELECT id, nama_subkategori FROM subkategori WHERE kategori_id = ? ORDER BY nama_subkategori');
    $stmt->execute([$data['kategori_id']]);
    $daftarSubkategoriAwal = $stmt->fetchAll();
}

$dataMasterKosong = !$daftarKategori || !$daftarMerek || !$daftarLokasi || !$daftarPeralatan;

$judulHalaman = $modeEdit ? 'Ubah Barang' : 'Tambah Barang';
require_once __DIR__ . '/../includes/header.php';
?>

<h5 class="mb-3"><?= $modeEdit ? 'Ubah Data Barang' : 'Tambah Barang Baru' ?></h5>

<?php if ($dataMasterKosong): ?>
  <div class="alert alert-warning">
    Data master (Kategori/Subkategori/Merek/Lokasi/Nama Peralatan) belum lengkap.
    Silakan lengkapi dulu lewat menu <a href="../master/kategori.php">Data Master</a> sebelum menambah barang.
  </div>
<?php else: ?>

<div class="card p-4">
  <form method="post" action="simpan.php">
    <input type="hidden" name="id" value="<?= amankan($idBarang) ?>">

    <?php if ($modeEdit): ?>
      <div class="alert alert-secondary py-2">
        Kode Barang saat ini: <span class="kode-barang"><?= amankan($data['kode_barang']) ?></span>
        &mdash; Kategori, Subkategori, dan Nama Peralatan tidak bisa diubah agar kode barang tetap konsisten.
        Jika salah pilih sejak awal, hapus barang ini dan input ulang.
      </div>
    <?php endif; ?>

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Kategori</label>
        <select name="kategori_id" id="selKategori" class="form-select" required <?= $modeEdit ? 'disabled' : '' ?>>
          <option value="">-- Pilih Kategori --</option>
          <?php foreach ($daftarKategori as $k): ?>
            <option value="<?= $k['id'] ?>" <?= $data['kategori_id'] == $k['id'] ? 'selected' : '' ?>><?= amankan($k['nama_kategori']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if ($modeEdit): ?><input type="hidden" name="kategori_id" value="<?= $data['kategori_id'] ?>"><?php endif; ?>
      </div>

      <div class="col-md-4">
        <label class="form-label">Subkategori</label>
        <select name="subkategori_id" id="selSubkategori" class="form-select" required <?= $modeEdit ? 'disabled' : '' ?>>
          <option value="">-- Pilih Kategori dahulu --</option>
          <?php foreach ($daftarSubkategoriAwal as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $data['subkategori_id'] == $s['id'] ? 'selected' : '' ?>><?= amankan($s['nama_subkategori']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if ($modeEdit): ?><input type="hidden" name="subkategori_id" value="<?= $data['subkategori_id'] ?>"><?php endif; ?>
        <div class="form-text">Pilihan menyesuaikan otomatis dengan Kategori yang dipilih.</div>
      </div>

      <div class="col-md-4">
        <label class="form-label">Nama Peralatan</label>
        <select name="nama_peralatan_id" class="form-select" required <?= $modeEdit ? 'disabled' : '' ?>>
          <option value="">-- Pilih Peralatan --</option>
          <?php foreach ($daftarPeralatan as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $data['nama_peralatan_id'] == $p['id'] ? 'selected' : '' ?>><?= amankan($p['nama_peralatan']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if ($modeEdit): ?><input type="hidden" name="nama_peralatan_id" value="<?= $data['nama_peralatan_id'] ?>"><?php endif; ?>
      </div>

      <div class="col-md-4">
        <label class="form-label">Merek</label>
        <select name="merek_id" class="form-select" required>
          <option value="">-- Pilih Merek --</option>
          <?php foreach ($daftarMerek as $m): ?>
            <option value="<?= $m['id'] ?>" <?= $data['merek_id'] == $m['id'] ? 'selected' : '' ?>><?= amankan($m['nama_merek']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Lokasi / Ruangan</label>
        <select name="lokasi_id" class="form-select" required>
          <option value="">-- Pilih Lokasi --</option>
          <?php foreach ($daftarLokasi as $l): ?>
            <option value="<?= $l['id'] ?>" <?= $data['lokasi_id'] == $l['id'] ? 'selected' : '' ?>><?= amankan($l['nama_ruangan']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Kondisi Barang</label>
        <select name="kondisi" class="form-select" required>
          <?php foreach (['Baik', 'Rusak', 'Sedang Diperbaiki'] as $k): ?>
            <option value="<?= $k ?>" <?= $data['kondisi'] === $k ? 'selected' : '' ?>><?= $k ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Nomor Inventaris Kantor <span class="text-muted">(opsional)</span></label>
        <input type="text" name="nomor_inventaris_kantor" class="form-control" value="<?= amankan($data['nomor_inventaris_kantor']) ?>" placeholder="Kosongkan jika barang bukan aset resmi kantor">
      </div>

      <div class="col-12">
        <label class="form-label">Spesifikasi Barang</label>
        <textarea name="spesifikasi" class="form-control" rows="3" required placeholder="Contoh: Intel Core i5, RAM 8GB, SSD 256GB"><?= amankan($data['spesifikasi']) ?></textarea>
      </div>
    </div>

    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
      <a href="index.php" class="btn btn-outline-secondary">Batal</a>
    </div>
  </form>
</div>

<script>
// -------------------------------------------------------------
// DROPDOWN BERTINGKAT (Cascading): saat Kategori berubah, ambil
// ulang daftar Subkategori yang sesuai lewat get_subkategori.php
// -------------------------------------------------------------
const selKategori = document.getElementById('selKategori');
const selSubkategori = document.getElementById('selSubkategori');

if (selKategori && !selKategori.disabled) {
  selKategori.addEventListener('change', function () {
    const kategoriId = this.value;
    selSubkategori.innerHTML = '<option value="">Memuat...</option>';
    if (!kategoriId) {
      selSubkategori.innerHTML = '<option value="">-- Pilih Kategori dahulu --</option>';
      return;
    }
    fetch('get_subkategori.php?kategori_id=' + kategoriId)
      .then(r => r.json())
      .then(data => {
        selSubkategori.innerHTML = '<option value="">-- Pilih Subkategori --</option>';
        data.forEach(item => {
          const opt = document.createElement('option');
          opt.value = item.id;
          opt.textContent = item.nama_subkategori;
          selSubkategori.appendChild(opt);
        });
      });
  });
}
</script>

<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
