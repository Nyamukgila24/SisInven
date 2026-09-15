<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'simpan') {
        $id = $_POST['id'] ?: null;
        $nama = trim($_POST['nama_kondisi']);
        $warna = trim($_POST['kode_warna'] ?: '#6c757d');
        if ($nama === '') {
            setFlash('gagal', 'Nama Kondisi wajib diisi.');
        } else {
            try {
                if ($id) {
                    $pdo->prepare('UPDATE kondisi_barang SET nama_kondisi=?, kode_warna=? WHERE id=?')->execute([$nama, $warna, $id]);
                    setFlash('sukses', 'Kondisi berhasil diperbarui.');
                } else {
                    $pdo->prepare('INSERT INTO kondisi_barang (nama_kondisi, kode_warna) VALUES (?,?)')->execute([$nama, $warna]);
                    setFlash('sukses', 'Kondisi baru berhasil ditambahkan.');
                }
            } catch (PDOException $e) {
                setFlash('gagal', 'Gagal menyimpan. Kemungkinan nama kondisi sudah ada.');
            }
        }
    }

    if ($aksi === 'hapus') {
        $id = $_POST['id'];
        if (masterMasihDipakai($pdo, 'kondisi_id', $id)) {
            setFlash('gagal', 'Kondisi tidak bisa dihapus karena masih dipakai oleh data barang.');
        } else {
            try {
                $pdo->prepare('DELETE FROM kondisi_barang WHERE id=?')->execute([$id]);
                setFlash('sukses', 'Kondisi berhasil dihapus.');
            } catch (PDOException $e) {
                setFlash('gagal', 'Kondisi tidak bisa dihapus karena masih berelasi dengan data lain.');
            }
        }
    }

    header('Location: kondisi.php');
    exit;
}

$daftar = $pdo->query('SELECT * FROM kondisi_barang ORDER BY id')->fetchAll();
$judulHalaman = 'Master Kondisi';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h5 class="mb-0">Master Kondisi</h5>
    <small class="text-muted">Daftar kondisi barang beserta warna badge-nya, dipilih lewat dropdown saat input barang.</small>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="bukaTambah()">
    <i class="bi bi-plus-lg"></i> Tambah Kondisi
  </button>
</div>

<div class="card p-3">
  <table class="table table-hover align-middle mb-0">
    <thead><tr><th>Nama Kondisi</th><th>Warna</th><th style="width:160px">Aksi</th></tr></thead>
    <tbody>
    <?php if (!$daftar): ?>
      <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data kondisi.</td></tr>
    <?php endif; ?>
    <?php foreach ($daftar as $row): ?>
      <tr>
        <td><?= amankan($row['nama_kondisi']) ?></td>
        <td><span class="badge" style="background-color: <?= amankan($row['kode_warna']) ?>"><?= amankan($row['kode_warna']) ?></span></td>
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
        <h5 class="modal-title" id="fJudul">Tambah Kondisi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Nama Kondisi</label>
        <input type="text" class="form-control mb-3" name="nama_kondisi" id="fNama" required placeholder="Contoh: Rusak Berat">
        <label class="form-label">Warna Badge</label><br>
        <input type="color" class="form-control form-control-color" name="kode_warna" id="fWarna" value="#6c757d">
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
  document.getElementById('fJudul').innerText = 'Tambah Kondisi';
  document.getElementById('fId').value = '';
  document.getElementById('fNama').value = '';
  document.getElementById('fWarna').value = '#6c757d';
}
function bukaEdit(row) {
  document.getElementById('fJudul').innerText = 'Ubah Kondisi';
  document.getElementById('fId').value = row.id;
  document.getElementById('fNama').value = row.nama_kondisi;
  document.getElementById('fWarna').value = row.kode_warna;
  new bootstrap.Modal(document.getElementById('modalForm')).show();
}
function hapus(id) {
  if (confirm('Hapus kondisi ini?')) {
    document.getElementById('hId').value = id;
    document.getElementById('formHapus').submit();
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>