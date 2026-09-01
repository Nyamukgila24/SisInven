<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'simpan') {
        $id = $_POST['id'] ?: null;
        $nama = trim($_POST['nama_merek']);
        if ($nama === '') {
            setFlash('gagal', 'Nama Merek wajib diisi.');
        } else {
            try {
                if ($id) {
                    $pdo->prepare('UPDATE merek SET nama_merek=? WHERE id=?')->execute([$nama, $id]);
                    setFlash('sukses', 'Merek berhasil diperbarui.');
                } else {
                    $pdo->prepare('INSERT INTO merek (nama_merek) VALUES (?)')->execute([$nama]);
                    setFlash('sukses', 'Merek baru berhasil ditambahkan.');
                }
            } catch (PDOException $e) {
                setFlash('gagal', 'Gagal menyimpan. Kemungkinan nama merek sudah ada.');
            }
        }
    }

    if ($aksi === 'hapus') {
        $id = $_POST['id'];
        if (masterMasihDipakai($pdo, 'merek_id', $id)) {
            setFlash('gagal', 'Merek tidak bisa dihapus karena masih dipakai oleh data barang.');
        } else {
            try {
                $pdo->prepare('DELETE FROM merek WHERE id=?')->execute([$id]);
                setFlash('sukses', 'Merek berhasil dihapus.');
            } catch (PDOException $e) {
                setFlash('gagal', 'Merek tidak bisa dihapus karena masih berelasi dengan data lain.');
            }
        }
    }

    header('Location: merek.php');
    exit;
}

$daftar = $pdo->query('SELECT * FROM merek ORDER BY nama_merek')->fetchAll();
$judulHalaman = 'Master Merek';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h5 class="mb-0">Master Merek</h5>
    <small class="text-muted">Daftar merek terstandardisasi, dipilih lewat dropdown saat input barang (tidak diketik manual).</small>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="bukaTambah()">
    <i class="bi bi-plus-lg"></i> Tambah Merek
  </button>
</div>

<div class="card p-3">
  <table class="table table-hover align-middle mb-0">
    <thead><tr><th>Nama Merek</th><th style="width:160px">Aksi</th></tr></thead>
    <tbody>
    <?php if (!$daftar): ?>
      <tr><td colspan="2" class="text-center text-muted py-4">Belum ada data merek.</td></tr>
    <?php endif; ?>
    <?php foreach ($daftar as $row): ?>
      <tr>
        <td><?= amankan($row['nama_merek']) ?></td>
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
        <h5 class="modal-title" id="fJudul">Tambah Merek</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Nama Merek</label>
        <input type="text" class="form-control" name="nama_merek" id="fNama" required>
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
  document.getElementById('fJudul').innerText = 'Tambah Merek';
  document.getElementById('fId').value = '';
  document.getElementById('fNama').value = '';
}
function bukaEdit(row) {
  document.getElementById('fJudul').innerText = 'Ubah Merek';
  document.getElementById('fId').value = row.id;
  document.getElementById('fNama').value = row.nama_merek;
  new bootstrap.Modal(document.getElementById('modalForm')).show();
}
function hapus(id) {
  if (confirm('Hapus merek ini?')) {
    document.getElementById('hId').value = id;
    document.getElementById('formHapus').submit();
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
