<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();
$baseUrl = urlDasar();

$idBarang = $_GET['id'] ?? null;
$data = [
    'kategori_id' => '', 'subkategori_id' => '', 'merek_id' => '', 'lokasi_id' => '',
    'nama_peralatan_id' => '', 'nama_peralatan' => '', 'nama_merek' => '', 'foto_peralatan' => null,
    'spesifikasi' => '', 'nomor_inventaris_kantor' => '', 'kondisi_id' => '',
];
$modeEdit = false;

if ($idBarang) {
    $stmt = $pdo->prepare(
        'SELECT b.*, m.nama_merek, np.nama_peralatan FROM barang b
         JOIN merek m ON m.id = b.merek_id
         JOIN nama_peralatan np ON np.id = b.nama_peralatan_id
         WHERE b.id = ?'
    );
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
$daftarKondisi = $pdo->query('SELECT * FROM kondisi_barang ORDER BY id')->fetchAll();

$daftarSubkategoriAwal = [];
if ($data['kategori_id']) {
    $stmt = $pdo->prepare('SELECT id, nama_subkategori FROM subkategori WHERE kategori_id = ? ORDER BY nama_subkategori');
    $stmt->execute([$data['kategori_id']]);
    $daftarSubkategoriAwal = $stmt->fetchAll();
}

$dataMasterKosong = !$daftarKategori || !$daftarMerek || !$daftarLokasi || !$daftarKondisi;

$judulHalaman = $modeEdit ? 'Ubah Barang' : 'Tambah Barang';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css">
<?php
require_once __DIR__ . '/../includes/header.php';
?>

<h5 class="mb-3"><?= $modeEdit ? 'Ubah Data Barang' : 'Tambah Barang Baru' ?></h5>

<?php if ($dataMasterKosong): ?>
  <div class="alert alert-warning">
    Data master (Kategori/Subkategori/Merek/Lokasi/Kondisi) belum lengkap.
    Silakan lengkapi dulu lewat menu <a href="../master/kategori.php">Data Master</a> sebelum menambah barang.
  </div>
<?php else: ?>

<div class="card p-4">
  <form method="post" action="simpan.php" id="formBarang" enctype="multipart/form-data">
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
        <?php if ($modeEdit): ?>
          <input type="text" class="form-control" value="<?= amankan($data['nama_peralatan']) ?>" disabled>
          <input type="hidden" name="nama_peralatan_id" value="<?= $data['nama_peralatan_id'] ?>">
        <?php else: ?>
          <input type="text" name="nama_peralatan" class="form-control" list="daftarPeralatanList" required
                 placeholder="Ketik nama peralatan, contoh: Komputer">
          <datalist id="daftarPeralatanList">
            <?php foreach ($pdo->query('SELECT DISTINCT nama_peralatan FROM nama_peralatan ORDER BY nama_peralatan') as $p): ?>
              <option value="<?= amankan($p['nama_peralatan']) ?>">
            <?php endforeach; ?>
          </datalist>
          <div class="form-text">Ketik nama baru, atau pilih dari saran yang sudah pernah dipakai.</div>
        <?php endif; ?>
      </div>

      <div class="col-md-4">
        <label class="form-label">Merek</label>
        <select name="merek_id" id="selMerek" class="form-select" required>
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
        <label class="form-label">Foto Peralatan <span class="text-muted">(opsional)</span></label>
        <?php if ($modeEdit && $data['foto_peralatan']): ?>
          <div class="mb-2">
            <img src="<?= $baseUrl ?>/uploads/peralatan/<?= amankan($data['foto_peralatan']) ?>" alt="Foto" style="max-width:90px;max-height:90px;border-radius:.5rem;object-fit:cover;">
          </div>
        <?php endif; ?>
        <input type="file" name="foto_peralatan" class="form-control" accept="image/jpeg,image/png,image/webp">
        <?php if ($modeEdit && $data['foto_peralatan']): ?>
          <div class="form-text">Kosongkan jika tidak ingin mengganti foto.</div>
        <?php endif; ?>
      </div>

      <?php if (!$modeEdit): ?>
      <div class="col-md-4">
        <label class="form-label">Jumlah Barang</label>
        <input type="number" name="jumlah_barang" id="jumlahBarang" class="form-control" value="1" min="1" max="100" required>
        <div class="form-text">Barang sejenis (kategori/merek/lokasi/peralatan sama) yang masuk sekaligus. Foto berlaku sama untuk semua.</div>
      </div>
      <?php endif; ?>

      <div class="col-md-4" id="wrapKondisiSeragam">
        <label class="form-label">Kondisi Barang</label>
        <select name="kondisi_id" id="selKondisiSeragam" class="form-select" required>
          <?php foreach ($daftarKondisi as $kb): ?>
            <option value="<?= $kb['id'] ?>" <?= $data['kondisi_id'] == $kb['id'] ? 'selected' : '' ?>><?= amankan($kb['nama_kondisi']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (!$modeEdit): ?>
          <div class="form-text">
            <a href="#" id="linkPerUnit" class="d-none">Kondisinya berbeda-beda? Atur per barang</a>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-md-6" id="wrapNomorInventarisTunggal">
        <label class="form-label">Nomor Inventaris Kantor <span class="text-muted">(opsional)</span></label>
        <input type="text" name="nomor_inventaris_kantor" class="form-control" value="<?= amankan($data['nomor_inventaris_kantor']) ?>" placeholder="Kosongkan jika barang bukan aset resmi kantor">
      </div>

      <?php if (!$modeEdit): ?>
      <div class="col-12 d-none" id="wrapKondisiPerUnit">
        <label class="form-label">Kondisi & Nomor Inventaris per Barang</label>
        <table class="table table-sm align-middle">
          <thead>
            <tr><th width="20%">Barang</th><th width="40%">Kondisi</th><th width="40%">No. Inventaris (opsional)</th></tr>
          </thead>
          <tbody id="bodyKondisiPerUnit"></tbody>
        </table>
        <a href="#" id="linkKembaliSeragam" class="form-text">&larr; Pakai kondisi yang sama untuk semua</a>
      </div>
      <?php endif; ?>

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

<script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
<script>
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

// -------------------------------------------------------------
// DROPDOWN MEREK BISA DICARI (pakai Choices.js)
// -------------------------------------------------------------
if (document.querySelector('#selMerek')) {
  new Choices('#selMerek', {
    searchEnabled: true,
    itemSelectText: '',
    shouldSort: false,
    placeholder: true,
    placeholderValue: '-- Pilih Merek --',
  });
}

// -------------------------------------------------------------
// JUMLAH BARANG > 1 : tampilkan opsi atur kondisi per barang
// -------------------------------------------------------------
const daftarKondisiJs = <?= json_encode($daftarKondisi) ?>;
const jumlahInput = document.getElementById('jumlahBarang');
const linkPerUnit = document.getElementById('linkPerUnit');
const linkKembaliSeragam = document.getElementById('linkKembaliSeragam');
const wrapSeragam = document.getElementById('wrapKondisiSeragam');
const wrapNomorTunggal = document.getElementById('wrapNomorInventarisTunggal');
const wrapPerUnit = document.getElementById('wrapKondisiPerUnit');
const bodyPerUnit = document.getElementById('bodyKondisiPerUnit');

function opsiKondisiHtml() {
  return daftarKondisiJs.map(k => `<option value="${k.id}">${k.nama_kondisi}</option>`).join('');
}
function renderBarisPerUnit() {
  const jumlah = parseInt(jumlahInput.value) || 1;
  bodyPerUnit.innerHTML = '';
  for (let i = 1; i <= jumlah; i++) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>Barang #${i}</td>
      <td><select name="kondisi_unit[]" class="form-select form-select-sm" required>${opsiKondisiHtml()}</select></td>
      <td><input type="text" name="nomor_inventaris_unit[]" class="form-control form-control-sm" placeholder="Opsional"></td>
    `;
    bodyPerUnit.appendChild(tr);
  }
}
if (jumlahInput) {
  jumlahInput.addEventListener('input', function () {
    const jumlah = parseInt(this.value) || 1;
    linkPerUnit.classList.toggle('d-none', jumlah <= 1);
    if (jumlah <= 1) {
      wrapPerUnit.classList.add('d-none');
      wrapSeragam.classList.remove('d-none');
      wrapNomorTunggal.classList.remove('d-none');
    } else if (!wrapPerUnit.classList.contains('d-none')) {
      renderBarisPerUnit();
    }
  });
  linkPerUnit.addEventListener('click', function (e) {
    e.preventDefault();
    renderBarisPerUnit();
    wrapPerUnit.classList.remove('d-none');
    wrapSeragam.classList.add('d-none');
    wrapNomorTunggal.classList.add('d-none');
  });
  linkKembaliSeragam.addEventListener('click', function (e) {
    e.preventDefault();
    wrapPerUnit.classList.add('d-none');
    wrapSeragam.classList.remove('d-none');
    wrapNomorTunggal.classList.remove('d-none');
  });
}
</script>

<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>