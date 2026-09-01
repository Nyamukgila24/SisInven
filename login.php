<?php
require_once __DIR__ . '/includes/functions.php';

// Jika sudah login, langsung arahkan ke dasbor
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username/email dan password wajib diisi.';
    } else {
        $pdo = getKoneksi();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil: simpan data penting di session
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username/email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk - SisInven</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-wrapper">
  <div class="card login-card p-4">
    <div class="card-body">
      <div class="text-center mb-4">
        <i class="bi bi-box-seam-fill" style="font-size:2.5rem;color:#145374;"></i>
        <h4 class="mt-2 mb-0">SisInven</h4>
        <small class="text-muted">Sistem Manajemen Inventaris Barang</small>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= amankan($error) ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="mb-3">
          <label class="form-label">Username atau Email</label>
          <input type="text" name="username" class="form-control" required autofocus value="<?= amankan($_POST['username'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">
          <i class="bi bi-box-arrow-in-right"></i> Masuk
        </button>
      </form>
      <p class="text-center text-muted small mt-3 mb-0">
        Lupa password? Hubungi rekan kerja lain untuk mereset lewat menu Pengguna.
      </p>
    </div>
  </div>
</div>
</body>
</html>
