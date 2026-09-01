<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

// -----------------------------------------------------------------
// Semua aksi (tambah/edit/hapus/reset password) ditangani di sini.
// Semua pengguna berhak melakukan semua aksi ini (Single Role -
// Pegawai), sesuai FRD bagian 4.
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'simpan') {
        $id = $_POST['id'] ?: null;
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $namaLengkap = trim($_POST['nama_lengkap']);
        $password = $_POST['password'] ?? '';

        if ($username === '' || $email === '' || $namaLengkap === '' || (!$id && $password === '')) {
            setFlash('gagal', 'Semua kolom wajib diisi (password wajib diisi untuk pengguna baru).');
        } else {
            try {
                if ($id) {
                    if ($password !== '') {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare('UPDATE users SET username=?, email=?, nama_lengkap=?, password=? WHERE id=?');
                        $stmt->execute([$username, $email, $namaLengkap, $hash, $id]);
                    } else {
                        $stmt = $pdo->prepare('UPDATE users SET username=?, email=?, nama_lengkap=? WHERE id=?');
                        $stmt->execute([$username, $email, $namaLengkap, $id]);
                    }
                    setFlash('sukses', 'Data pengguna berhasil diperbarui.');
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO users (username, email, nama_lengkap, password) VALUES (?,?,?,?)');
                    $stmt->execute([$username, $email, $namaLengkap, $hash]);
                    setFlash('sukses', 'Pengguna baru berhasil ditambahkan.');
                }
            } catch (PDOException $e) {
                setFlash('gagal', 'Gagal menyimpan. Kemungkinan username/email sudah dipakai.');
            }
        }
    }

    if ($aksi === 'reset_password') {
        $id = $_POST['id'];
        $passwordBaru = $_POST['password_baru'] ?? '';
        if (strlen($passwordBaru) < 6) {
            setFlash('gagal', 'Password baru minimal 6 karakter.');
        } else {
            $hash = password_hash($passwordBaru, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([$hash, $id]);
            setFlash('sukses', 'Password rekan kerja berhasil direset. Sampaikan password baru kepada yang bersangkutan.');
        }
    }

    if ($aksi === 'hapus') {
        $id = $_POST['id'];
        if ((int) $id === (int) $_SESSION['user_id']) {
            setFlash('gagal', 'Anda tidak bisa menghapus akun Anda sendiri saat sedang login.');
        } else {
            $totalUser = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            if ($totalUser <= 1) {
                setFlash('gagal', 'Tidak bisa menghapus pengguna terakhir yang tersisa di sistem.');
            } else {
                $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
                setFlash('sukses', 'Pengguna berhasil dihapus.');
            }
        }
    }

    header('Location: index.php');
    exit;
}

$daftar = $pdo->query('SELECT * FROM users ORDER BY nama_lengkap')->fetchAll();
$judulHalaman = 'Manajemen Pengguna';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h5 class="mb-0">Manajemen Pengguna</h5>
    <small class="text-muted">Semua pegawai yang terdaftar memiliki hak akses yang sama untuk mengelola barang & pengguna lain.</small>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="bukaTambah()">
    <i class="bi bi-person-plus"></i> Tambah Pengguna
  </button>
</div>

<div class="card p-3">
  <table class="table table-hover align-middle mb-0">
    <thead><tr><th>Nama Lengkap</th><th>Username</th><th>Email</th><th style="width:220px">Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($daftar as $row): ?>
      <tr>
        <td><?= amankan($row['nama_lengkap']) ?> <?= (int)$row['id'] === (int)$_SESSION['user_id'] ? '<span class="badge bg-info text-dark">Anda</span>' : '' ?></td>
        <td><?= amankan($row['username']) ?></td>
        <td><?= amankan($row['email']) ?></td>
        <td class="d-flex gap-1">
          <button class="btn btn-sm btn-outline-primary" onclick='bukaEdit(<?= json_encode($row) ?>)' title="Ubah Data"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-warning" onclick='bukaReset(<?= json_encode($row) ?>)' title="Reset Password"><i class="bi bi-key"></i></button>
          <button class="btn btn-sm btn-outline-danger" onclick="hapus(<?= (int)$row['id'] ?>)" title="Hapus"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal Tambah/Edit Pengguna -->
<div class="modal fade" id="modalForm" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <input type="hidden" name="aksi" value="simpan">
      <input type="hidden" name="id" id="fId">
      <div class="modal-header">
        <h5 class="modal-title" id="fJudul">Tambah Pengguna</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" class="form-control" name="nama_lengkap" id="fNama" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" class="form-control" name="username" id="fUsername" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" id="fEmail" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password <span id="fPassKet" class="text-muted"></span></label>
          <input type="password" class="form-control" name="password" id="fPassword">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="modalReset" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <input type="hidden" name="aksi" value="reset_password">
      <input type="hidden" name="id" id="rId">
      <div class="modal-header">
        <h5 class="modal-title">Reset Password: <span id="rNama"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Dipakai saat rekan kerja lupa password. Set password baru lalu sampaikan langsung ke yang bersangkutan.</p>
        <label class="form-label">Password Baru</label>
        <input type="password" class="form-control" name="password_baru" minlength="6" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-warning">Reset Password</button>
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
  document.getElementById('fJudul').innerText = 'Tambah Pengguna';
  document.getElementById('fId').value = '';
  document.getElementById('fNama').value = '';
  document.getElementById('fUsername').value = '';
  document.getElementById('fEmail').value = '';
  document.getElementById('fPassword').value = '';
  document.getElementById('fPassword').required = true;
  document.getElementById('fPassKet').innerText = '';
}
function bukaEdit(row) {
  document.getElementById('fJudul').innerText = 'Ubah Pengguna';
  document.getElementById('fId').value = row.id;
  document.getElementById('fNama').value = row.nama_lengkap;
  document.getElementById('fUsername').value = row.username;
  document.getElementById('fEmail').value = row.email;
  document.getElementById('fPassword').value = '';
  document.getElementById('fPassword').required = false;
  document.getElementById('fPassKet').innerText = '(kosongkan jika tidak ingin mengubah password)';
  new bootstrap.Modal(document.getElementById('modalForm')).show();
}
function bukaReset(row) {
  document.getElementById('rId').value = row.id;
  document.getElementById('rNama').innerText = row.nama_lengkap;
  new bootstrap.Modal(document.getElementById('modalReset')).show();
}
function hapus(id) {
  if (confirm('Hapus pengguna ini?')) {
    document.getElementById('hId').value = id;
    document.getElementById('formHapus').submit();
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
