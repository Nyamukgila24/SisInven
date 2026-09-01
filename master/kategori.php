<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

// -----------------------------------------------------------------
// PROSES: Tambah / Edit / Hapus kategori (semua dalam satu file
// supaya mudah ditelusuri: cari saja "aksi" untuk melihat semua alur).
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'simpan') {
        $id = $_POST['id'] ?: null;
        $kode = trim($_POST['kode_kategori']);
        $nama = trim($_POST['nama_kategori']);

        if ($kode === '' || $nama === '') {
            setFlash('gagal', 'Kode dan Nama Kategori wajib diisi.');
        } else {
            try {
                if ($id) {
                    $stmt = $pdo->prepare('UPDATE kategori SET kode_kategori=?, nama_kategori=? WHERE id=?');
                    $stmt->execute([$kode, $nama, $id]);
                    setFlash('sukses', 'Kategori berhasil diperbarui.');
                } else {
                    $stmt = $pdo->prepare('INSERT INTO kategori (kode_kategori, nama_kategori) VALUES (?, ?)');
                    $stmt->execute([$kode, $nama]);
                    setFlash('sukses', 'Kategori baru berhasil ditambahkan.');
                }
            } catch (PDOException $e) {
                setFlash('gagal', 'Gagal menyimpan. Kemungkinan Kode Kategori sudah dipakai. (' . $e->getMessage() . ')');
            }
        }
    }

    if ($aksi === 'hapus') {
        $id = $_POST['id'];
        // Kategori tidak boleh dihapus jika masih dipakai LANGSUNG oleh barang,
        // ATAU jika masih punya Subkategori anak (supaya tidak ada subkategori
        // yang kehilangan kategori induknya / data "yatim").
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM subkategori WHERE kategori_id = ?');
        $stmt->execute([$id]);
        $punyaSubkategori = $stmt->fetchColumn() > 0;

        if (masterMasihDipakai($pdo, 'kategori_id', $id)) {
            setFlash('gagal', 'Kategori tidak bisa dihapus karena masih dipakai oleh data barang.');
        } elseif ($punyaSubkategori) {
            setFlash('gagal', 'Kategori tidak bisa dihapus karena masih memiliki Subkategori. Hapus dulu semua Subkategori di bawahnya.');
        } else {
            try {
                $pdo->prepare('DELETE FROM kategori WHERE id=?')->execute([$id]);
                setFlash('sukses', 'Kategori berhasil dihapus.');
            } catch (PDOException $e) {
                setFlash('gagal', 'Kategori tidak bisa dihapus karena masih berelasi dengan data lain.');
            }
        }
    }

    header('Location: kategori.php');
    exit;
}

$daftar = $pdo->query('SELECT * FROM kategori ORDER BY kode_kategori')->fetchAll();

$judulHalaman = 'Master Kategori';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h5 class="mb-0">Master Kategori</h5>
    <small class="text-muted">Kategori dasar barang, contoh: Umum, Jaringan. Kode dipakai sebagai digit pertama Kode Barang.</small>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="bukaTambah()">
    <i class="bi bi-plus-lg"></i> Tambah Kategori
  </button>
</div>

<div class="card p-3">
  <table class="table table-hover align-middle mb-0">
    <thead><tr><th style="width:120px">Kode</th><th>Nama Kategori</th><th style="width:160px">Aksi</th></tr></thead>
    <tbody>
    <?php if (!$daftar): ?>
      <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data kategori.</td></tr>
    <?php endif; ?>
    <?php foreach ($daftar as $row): ?>
      <tr>
        <td><span class="badge bg-secondary"><?= amankan($row['kode_kategori']) ?></span></td>
        <td><?= amankan($row['nama_kategori']) ?></td>
        <td>
          <button class="btn btn-sm btn-outline-primary" onclick='bukaEdit(<?= json_encode($row) ?>)'>
            <i class="bi bi-pencil"></i>
          </button>
          <button class="btn btn-sm btn-outline-danger" onclick="hapus(<?= (int)$row['id'] ?>)">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="modalForm" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <input type="hidden" name="aksi" value="simpan">
      <input type="hidden" name="id" id="fId">
      <div class="modal-header">
        <h5 class="modal-title" id="fJudul">Tambah Kategori</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Kode Kategori (angka, unik)</label>
          <input type="number" class="form-control" name="kode_kategori" id="fKode" required>
          <div class="form-text">Contoh: Umum = 1, Jaringan = 2. Angka ini akan muncul sebagai digit pertama Kode Barang.</div>
        </div>
        <div class="mb-3">
          <label class="form-label">Nama Kategori</label>
          <input type="text" class="form-control" name="nama_kategori" id="fNama" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Form tersembunyi untuk hapus -->
<form method="post" id="formHapus" class="d-none">
  <input type="hidden" name="aksi" value="hapus">
  <input type="hidden" name="id" id="hId">
</form>

<script>
function bukaTambah() {
  document.getElementById('fJudul').innerText = 'Tambah Kategori';
  document.getElementById('fId').value = '';
  document.getElementById('fKode').value = '';
  document.getElementById('fNama').value = '';
}
function bukaEdit(row) {
  document.getElementById('fJudul').innerText = 'Ubah Kategori';
  document.getElementById('fId').value = row.id;
  document.getElementById('fKode').value = row.kode_kategori;
  document.getElementById('fNama').value = row.nama_kategori;
  new bootstrap.Modal(document.getElementById('modalForm')).show();
}
function hapus(id) {
  if (confirm('Hapus kategori ini? Tindakan tidak bisa dibatalkan.')) {
    document.getElementById('hId').value = id;
    document.getElementById('formHapus').submit();
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
