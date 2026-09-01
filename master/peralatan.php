<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'simpan') {
        $id = $_POST['id'] ?: null;
        $nama = trim($_POST['nama_peralatan']);
        if ($nama === '') {
            setFlash('gagal', 'Nama Peralatan wajib diisi.');
        } else {
            try {
                if ($id) {
                    // Kode peralatan TIDAK ikut diubah saat edit, supaya kode barang
                    // yang sudah pernah dibuat sebelumnya tetap konsisten.
                    $pdo->prepare('UPDATE nama_peralatan SET nama_peralatan=? WHERE id=?')->execute([$nama, $id]);
                    setFlash('sukses', 'Nama Peralatan berhasil diperbarui.');
                } else {
                    $kode = nextKodePeralatan($pdo);
                    $pdo->prepare('INSERT INTO nama_peralatan (kode_peralatan, nama_peralatan) VALUES (?, ?)')->execute([$kode, $nama]);
                    setFlash('sukses', "Peralatan baru ditambahkan dengan kode otomatis {$kode}.");
                }
            } catch (PDOException $e) {
                setFlash('gagal', 'Gagal menyimpan. Kemungkinan nama peralatan sudah ada.');
            }
        }
    }

    if ($aksi === 'hapus') {
        $id = $_POST['id'];
        if (masterMasihDipakai($pdo, 'nama_peralatan_id', $id)) {
            setFlash('gagal', 'Peralatan tidak bisa dihapus karena masih dipakai oleh data barang.');
        } else {
            try {
                $pdo->prepare('DELETE FROM nama_peralatan WHERE id=?')->execute([$id]);
                setFlash('sukses', 'Nama Peralatan berhasil dihapus.');
            } catch (PDOException $e) {
                setFlash('gagal', 'Nama Peralatan tidak bisa dihapus karena masih berelasi dengan data lain.');
            }
        }
    }

    header('Location: peralatan.php');
    exit;
}

$daftar = $pdo->query('SELECT * FROM nama_peralatan ORDER BY kode_peralatan')->fetchAll();
$judulHalaman = 'Master Nama Peralatan';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h5 class="mb-0">Master Nama Peralatan</h5>
    <small class="text-muted">Standardisasi nama peralatan. Kode dibuat otomatis oleh sistem dan dipakai sebagai bagian dari Kode Barang.</small>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="bukaTambah()">
    <i class="bi bi-plus-lg"></i> Tambah Peralatan
  </button>
</div>

<div class="card p-3">
  <table class="table table-hover align-middle mb-0">
    <thead><tr><th style="width:100px">Kode</th><th>Nama Peralatan</th><th style="width:160px">Aksi</th></tr></thead>
    <tbody>
    <?php if (!$daftar): ?>
      <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data peralatan.</td></tr>
    <?php endif; ?>
    <?php foreach ($daftar as $row): ?>
      <tr>
        <td><span class="badge bg-secondary"><?= amankan($row['kode_peralatan']) ?></span></td>
        <td><?= amankan($row['nama_peralatan']) ?></td>
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
        <h5 class="modal-title" id="fJudul">Tambah Peralatan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Nama Peralatan</label>
        <input type="text" class="form-control" name="nama_peralatan" id="fNama" required placeholder="Contoh: Komputer">
        <div class="form-text" id="fKeterangan"></div>
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
  document.getElementById('fJudul').innerText = 'Tambah Peralatan';
  document.getElementById('fId').value = '';
  document.getElementById('fNama').value = '';
  document.getElementById('fKeterangan').innerText = 'Kode akan dibuat otomatis oleh sistem.';
}
function bukaEdit(row) {
  document.getElementById('fJudul').innerText = 'Ubah Peralatan';
  document.getElementById('fId').value = row.id;
  document.getElementById('fNama').value = row.nama_peralatan;
  document.getElementById('fKeterangan').innerText = 'Kode #' + row.kode_peralatan + ' tidak berubah agar kode barang lama tetap valid.';
  new bootstrap.Modal(document.getElementById('modalForm')).show();
}
function hapus(id) {
  if (confirm('Hapus peralatan ini?')) {
    document.getElementById('hId').value = id;
    document.getElementById('formHapus').submit();
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
