<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'simpan') {
        $id = $_POST['id'] ?: null;
        $kategoriId = $_POST['kategori_id'];
        $nama = trim($_POST['nama_subkategori']);

        if ($kategoriId === '' || $nama === '') {
            setFlash('gagal', 'Kategori Induk dan Nama Subkategori wajib diisi.');
        } else {
            try {
                if ($id) {
                    // Kategori induk & kode TIDAK diubah lewat edit, supaya kode
                    // barang yang sudah pernah dibuat tetap konsisten.
                    $pdo->prepare('UPDATE subkategori SET nama_subkategori=? WHERE id=?')->execute([$nama, $id]);
                    setFlash('sukses', 'Subkategori berhasil diperbarui.');
                } else {
                    $kode = nextKodeSubkategori($pdo, $kategoriId);
                    $stmt = $pdo->prepare('INSERT INTO subkategori (kategori_id, kode_subkategori, nama_subkategori) VALUES (?, ?, ?)');
                    $stmt->execute([$kategoriId, $kode, $nama]);
                    setFlash('sukses', "Subkategori baru ditambahkan dengan kode otomatis {$kode}.");
                }
            } catch (PDOException $e) {
                setFlash('gagal', 'Gagal menyimpan subkategori.');
            }
        }
    }

    if ($aksi === 'hapus') {
        $id = $_POST['id'];
        if (masterMasihDipakai($pdo, 'subkategori_id', $id)) {
            setFlash('gagal', 'Subkategori tidak bisa dihapus karena masih dipakai oleh data barang.');
        } else {
            try {
                $pdo->prepare('DELETE FROM subkategori WHERE id=?')->execute([$id]);
                setFlash('sukses', 'Subkategori berhasil dihapus.');
            } catch (PDOException $e) {
                setFlash('gagal', 'Subkategori tidak bisa dihapus karena masih berelasi dengan data lain.');
            }
        }
    }

    header('Location: subkategori.php');
    exit;
}

$daftar = $pdo->query(
    'SELECT s.*, k.nama_kategori, k.kode_kategori
     FROM subkategori s JOIN kategori k ON k.id = s.kategori_id
     ORDER BY k.kode_kategori, s.kode_subkategori'
)->fetchAll();
$daftarKategori = $pdo->query('SELECT * FROM kategori ORDER BY nama_kategori')->fetchAll();

$judulHalaman = 'Master Subkategori';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h5 class="mb-0">Master Subkategori</h5>
    <small class="text-muted">Setiap subkategori wajib terhubung ke satu Kategori Induk (mendukung dropdown bertingkat saat input barang).</small>
  </div>
  <?php if (!$daftarKategori): ?>
    <span class="text-danger small">Tambahkan Kategori terlebih dahulu.</span>
  <?php else: ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="bukaTambah()">
      <i class="bi bi-plus-lg"></i> Tambah Subkategori
    </button>
  <?php endif; ?>
</div>

<div class="card p-3">
  <table class="table table-hover align-middle mb-0">
    <thead><tr><th style="width:140px">Kategori Induk</th><th style="width:80px">Kode</th><th>Nama Subkategori</th><th style="width:160px">Aksi</th></tr></thead>
    <tbody>
    <?php if (!$daftar): ?>
      <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data subkategori.</td></tr>
    <?php endif; ?>
    <?php foreach ($daftar as $row): ?>
      <tr>
        <td><span class="badge bg-secondary"><?= amankan($row['kode_kategori']) ?></span> <?= amankan($row['nama_kategori']) ?></td>
        <td><?= amankan($row['kode_subkategori']) ?></td>
        <td><?= amankan($row['nama_subkategori']) ?></td>
        <td>
          <button class="btn btn-sm btn-outline-primary" onclick='bukaEdit(<?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" onclick="hapus(<?= (int)$row['id'] ?>)"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="modalForm" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <input type="hidden" name="aksi" value="simpan">
      <input type="hidden" name="id" id="fId">
      <div class="modal-header">
        <h5 class="modal-title" id="fJudul">Tambah Subkategori</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Kategori Induk</label>
          <select class="form-select" name="kategori_id" id="fKategori" required>
            <?php foreach ($daftarKategori as $k): ?>
              <option value="<?= $k['id'] ?>"><?= amankan($k['nama_kategori']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Nama Subkategori</label>
          <input type="text" class="form-control" name="nama_subkategori" id="fNama" required>
        </div>
        <div class="form-text" id="fKeterangan">Kode subkategori akan dibuat otomatis oleh sistem.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<form method="post" id="formHapus" class="d-none">
  <input type="hidden" name="aksi" value="hapus">
  <input type="hidden" name="id" id="hId">
</form>

<script>
function bukaTambah() {
  document.getElementById('fJudul').innerText = 'Tambah Subkategori';
  document.getElementById('fId').value = '';
  document.getElementById('fKategori').disabled = false;
  document.getElementById('fKategori').selectedIndex = 0;
  document.getElementById('fNama').value = '';
  document.getElementById('fKeterangan').innerText = 'Kode subkategori akan dibuat otomatis oleh sistem.';
}
function bukaEdit(row) {
  document.getElementById('fJudul').innerText = 'Ubah Subkategori';
  document.getElementById('fId').value = row.id;
  document.getElementById('fKategori').value = row.kategori_id;
  document.getElementById('fKategori').disabled = true; // kategori induk tidak boleh diubah
  document.getElementById('fNama').value = row.nama_subkategori;
  document.getElementById('fKeterangan').innerText = 'Kategori induk & kode #' + row.kode_subkategori + ' tidak bisa diubah agar kode barang lama tetap valid.';
  new bootstrap.Modal(document.getElementById('modalForm')).show();
}
function hapus(id) {
  if (confirm('Hapus subkategori ini?')) {
    document.getElementById('hId').value = id;
    document.getElementById('formHapus').submit();
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
