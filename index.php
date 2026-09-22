<?php
require_once __DIR__ . '/includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

$totalBarang = $pdo->query('SELECT COUNT(*) FROM barang')->fetchColumn();
$totalAset = $pdo->query('SELECT COUNT(*) FROM barang WHERE nomor_inventaris_kantor IS NOT NULL AND nomor_inventaris_kantor <> ""')->fetchColumn();

$statistikKondisi = $pdo->query(
    'SELECT kb.nama_kondisi, kb.kode_warna, COUNT(b.id) AS jumlah
     FROM kondisi_barang kb
     LEFT JOIN barang b ON b.kondisi_id = kb.id
     GROUP BY kb.id, kb.nama_kondisi, kb.kode_warna
     ORDER BY kb.id'
)->fetchAll();

$chartLabels = array_column($statistikKondisi, 'nama_kondisi');
$chartData   = array_map('intval', array_column($statistikKondisi, 'jumlah'));
$chartColors = array_column($statistikKondisi, 'kode_warna');

$barangTerbaru = $pdo->query(
    'SELECT b.kode_barang, np.nama_peralatan, kb.nama_kondisi, kb.kode_warna, b.user_last_edit, b.timestamp_last_edit
     FROM barang b
     JOIN nama_peralatan np ON np.id = b.nama_peralatan_id
     JOIN kondisi_barang kb ON kb.id = b.kondisi_id
     ORDER BY b.id DESC LIMIT 5'
)->fetchAll();

$judulHalaman = 'Dasbor';
require_once __DIR__ . '/includes/header.php';
?>

<h4 class="mb-4">Selamat datang, <?= amankan($_SESSION['nama_lengkap']) ?> 👋</h4>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card dashboard-stat p-3 text-center h-100 d-flex flex-column justify-content-center">
      <div class="display-3 fw-bold"><?= (int) $totalBarang ?></div>
      <div class="text-muted small">Total Barang</div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card p-3 h-100">
      <h6 class="mb-3"><i class="bi bi-pie-chart"></i> Diagram Kondisi Barang</h6>
      <?php if ($totalBarang > 0): ?>
        <div style="position: relative; height: 240px;">
          <canvas id="chartKondisi"></canvas>
        </div>
      <?php else: ?>
        <div class="text-center text-muted py-5">
          <i class="bi bi-pie-chart" style="font-size: 3rem; opacity: 0.3;"></i>
          <p class="mt-2 mb-0">Belum ada data barang.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-3 mt-1">
  <div class="col-md-6">
    <div class="card p-3 h-100">
      <h6 class="mb-3"><i class="bi bi-clock-history"></i> Barang Terakhir Diubah</h6>
      <?php if (!$barangTerbaru): ?>
        <p class="text-muted mb-0">Belum ada data barang.</p>
      <?php else: ?>
        <table class="table table-sm mb-0">
          <thead><tr><th>Kode</th><th>Peralatan</th><th>Kondisi</th><th>Oleh</th></tr></thead>
          <tbody>
          <?php foreach ($barangTerbaru as $b): ?>
            <tr>
              <td class="kode-barang"><?= amankan($b['kode_barang']) ?></td>
              <td><?= amankan($b['nama_peralatan']) ?></td>
              <td><span class="badge" style="background-color: <?= amankan($b['kode_warna']) ?>"><?= amankan($b['nama_kondisi']) ?></span></td>
              <td><small><?= amankan($b['user_last_edit']) ?><br><?= amankan($b['timestamp_last_edit']) ?></small></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3 h-100">
      <h6 class="mb-3"><i class="bi bi-award"></i> Ringkasan Aset Resmi</h6>
      <p class="mb-1">Barang dengan Nomor Inventaris Kantor (aset resmi): <strong><?= (int) $totalAset ?></strong></p>
      <p class="mb-3">Barang tanpa Nomor Inventaris Kantor (non-aset): <strong><?= (int) ($totalBarang - $totalAset) ?></strong></p>
      <a href="laporan/index.php" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-file-earmark-bar-graph"></i> Buka Menu Laporan
      </a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
  const canvas = document.getElementById('chartKondisi');
  if (!canvas) return;

  const labels = <?= json_encode($chartLabels) ?>;
  const data   = <?= json_encode($chartData) ?>;
  const colors = <?= json_encode($chartColors) ?>;
  const total  = <?= (int) $totalBarang ?>;

  new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: data,
        backgroundColor: colors,
        borderColor: '#fff',
        borderWidth: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '68%',
      plugins: {
        legend: {
          position: 'right',
          labels: {
            usePointStyle: true,
            padding: 16,
            font: { size: 13 }
          }
        },
        tooltip: {
          callbacks: {
            label: function(ctx) {
              const val = ctx.parsed;
              const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
              return ` ${ctx.label}: ${val} (${pct}%)`;
            }
          }
        }
      }
    },
    plugins: [{
      id: 'centerText',
      afterDraw: function(chart) {
        const meta = chart.getDatasetMeta(0);
        if (!meta.data.length) return;
        const x = meta.data[0].x;
        const y = meta.data[0].y;
        const ctx = chart.ctx;
        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        ctx.font = 'bold 34px system-ui, -apple-system, sans-serif';
        ctx.fillStyle = '#212529';
        ctx.fillText(total, x, y - 8);

        ctx.font = '12px system-ui, -apple-system, sans-serif';
        ctx.fillStyle = '#6c757d';
        ctx.fillText('Total', x, y + 20);

        ctx.restore();
      }
    }]
  });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>