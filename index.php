<?php
require_once __DIR__ . '/includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

// ============================================
// 1. QUERY DATA
// ============================================
$totalBarang = $pdo->query('SELECT COUNT(*) FROM barang')->fetchColumn();
$totalAset = $pdo->query('SELECT COUNT(*) FROM barang WHERE nomor_inventaris_kantor IS NOT NULL AND nomor_inventaris_kantor <> ""')->fetchColumn();

// Statistik Kondisi
$statistikKondisi = $pdo->query(
    'SELECT kb.nama_kondisi, kb.kode_warna, COUNT(b.id) AS jumlah
     FROM kondisi_barang kb
     LEFT JOIN barang b ON b.kondisi_id = kb.id
     GROUP BY kb.id, kb.nama_kondisi, kb.kode_warna
     ORDER BY kb.id'
)->fetchAll();

// Statistik Kategori
$statistikKategori = $pdo->query(
    'SELECT k.nama_kategori AS label, COUNT(b.id) AS jumlah
     FROM barang b
     JOIN kategori k ON k.id = b.kategori_id
     GROUP BY k.id, k.nama_kategori
     ORDER BY jumlah DESC'
)->fetchAll();

// Statistik Lokasi
$statistikLokasi = $pdo->query(
    'SELECT l.nama_ruangan AS label, COUNT(b.id) AS jumlah
     FROM barang b
     JOIN lokasi l ON l.id = b.lokasi_id
     GROUP BY l.id, l.nama_ruangan
     ORDER BY jumlah DESC'
)->fetchAll();

// Statistik Tahun Perolehan (kronologis)
$statistikTahun = $pdo->query(
    'SELECT tahun_perolehan AS label, COUNT(*) AS jumlah
     FROM barang
     WHERE tahun_perolehan IS NOT NULL
     GROUP BY tahun_perolehan
     ORDER BY tahun_perolehan ASC'
)->fetchAll();

// Barang Terakhir Diubah
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

<!-- ============================================ -->
<!-- BARIS 1: Diagram Kondisi + Diagram Aset      -->
<!-- ============================================ -->
<div class="row g-3">

  <!-- Chart Kondisi (Donut) -->
  <div class="col-md-6">
    <div class="card p-3 h-100">
      <h6 class="mb-3"><i class="bi bi-pie-chart"></i> Diagram Kondisi Barang</h6>
      <?php if ($totalBarang > 0): ?>
        <div style="position: relative; height: 260px;">
          <canvas id="chartKondisi"></canvas>
        </div>
      <?php else: ?>
        <div class="text-center text-muted py-5">
          <p class="mb-0">Belum ada data barang.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Chart Aset (Donut) -->
  <div class="col-md-6">
    <div class="card p-3 h-100">
      <h6 class="mb-3"><i class="bi bi-award"></i> Ringkasan Aset Resmi</h6>
      <?php if ($totalBarang > 0): ?>
        <div style="position: relative; height: 260px;">
          <canvas id="chartAset"></canvas>
        </div>
      <?php else: ?>
        <div class="text-center text-muted py-5">
          <p class="mb-0">Belum ada data barang.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- ============================================ -->
<!-- BARIS 2: Grafik Kategori + Grafik Lokasi     -->
<!-- ============================================ -->
<div class="row g-3 mt-1">

  <!-- Chart Kategori (Bar) -->
  <div class="col-md-6">
    <div class="card p-3 h-100">
      <h6 class="mb-3"><i class="bi bi-diagram-3"></i> Distribusi Kategori</h6>
      <?php if ($statistikKategori): ?>
        <div style="position: relative; height: 260px;">
          <canvas id="chartKategori"></canvas>
        </div>
      <?php else: ?>
        <div class="text-center text-muted py-5"><p class="mb-0">Belum ada data.</p></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Chart Lokasi (Bar) -->
  <div class="col-md-6">
    <div class="card p-3 h-100">
      <h6 class="mb-3"><i class="bi bi-geo-alt"></i> Sebaran Lokasi</h6>
      <?php if ($statistikLokasi): ?>
        <div style="position: relative; height: 260px;">
          <canvas id="chartLokasi"></canvas>
        </div>
      <?php else: ?>
        <div class="text-center text-muted py-5"><p class="mb-0">Belum ada data.</p></div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- ============================================ -->
<!-- BARIS 3: Grafik Tahun Perolehan (Full Width) -->
<!-- ============================================ -->
<div class="row g-3 mt-1">
  <div class="col-12">
    <div class="card p-3">
      <h6 class="mb-3"><i class="bi bi-calendar-event"></i> Perolehan Barang per Tahun</h6>
      <?php if ($statistikTahun): ?>
        <div style="position: relative; height: 280px;">
          <canvas id="chartTahun"></canvas>
        </div>
      <?php else: ?>
        <div class="text-center text-muted py-5"><p class="mb-0">Belum ada data.</p></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ============================================ -->
<!-- BARIS 4: Barang Terakhir Diubah (Full Width) -->
<!-- ============================================ -->
<div class="row g-3 mt-1">
  <div class="col-12">
    <div class="card p-3">
      <h6 class="mb-3"><i class="bi bi-clock-history"></i> Barang Terakhir Diubah</h6>
      <?php if (!$barangTerbaru): ?>
        <p class="text-muted mb-0">Belum ada data barang.</p>
      <?php else: ?>
        <table class="table table-sm mb-0">
          <thead>
            <tr><th>Kode</th><th>Peralatan</th><th>Kondisi</th><th>Oleh</th></tr>
          </thead>
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
</div>

<!-- ============================================ -->
<!-- SCRIPT CHART.JS                              -->
<!-- ============================================ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
  // ============================================
  // CHART 1 — KONDISI (Donut, warna dari DB)
  // ============================================
  const ctxKondisi = document.getElementById('chartKondisi');
  if (ctxKondisi) {
    const labels = <?= json_encode(array_column($statistikKondisi, 'nama_kondisi')) ?>;
    const data   = <?= json_encode(array_map('intval', array_column($statistikKondisi, 'jumlah'))) ?>;
    const colors = <?= json_encode(array_column($statistikKondisi, 'kode_warna')) ?>;
    const total  = <?= (int) $totalBarang ?>;

    new Chart(ctxKondisi, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{ data: data, backgroundColor: colors, borderColor: '#fff', borderWidth: 3 }]
      },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '68%',
        plugins: {
          legend: { position: 'right', labels: { usePointStyle: true, padding: 12, font: { size: 12 } } },
          tooltip: {
            callbacks: {
              label: function(ctx) {
                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
              }
            }
          }
        }
      },
      plugins: [{
        id: 'centerKondisi',
        afterDraw: function(chart) {
          const meta = chart.getDatasetMeta(0);
          if (!meta.data.length) return;
          const x = meta.data[0].x, y = meta.data[0].y, c = chart.ctx;
          c.save();
          c.textAlign = 'center'; c.textBaseline = 'middle';
          c.font = 'bold 30px system-ui, sans-serif'; c.fillStyle = '#212529';
          c.fillText(total, x, y - 6);
          c.font = '11px system-ui, sans-serif'; c.fillStyle = '#6c757d';
          c.fillText('Total', x, y + 18);
          c.restore();
        }
      }]
    });
  }

  // ============================================
  // CHART 2 — ASET RESMI (Donut, 2 warna tetap)
  // ============================================
  const ctxAset = document.getElementById('chartAset');
  if (ctxAset) {
    const aset    = <?= (int) $totalAset ?>;
    const nonAset = <?= (int) ($totalBarang - $totalAset) ?>;

    new Chart(ctxAset, {
      type: 'doughnut',
      data: {
        labels: ['Aset Resmi', 'Non-Aset'],
        datasets: [{
          data: [aset, nonAset],
          backgroundColor: ['#198754', '#6c757d'],
          borderColor: '#fff', borderWidth: 3
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '68%',
        plugins: {
          legend: { position: 'right', labels: { usePointStyle: true, padding: 12, font: { size: 12 } } },
          tooltip: {
            callbacks: {
              label: function(ctx) {
                const t = aset + nonAset;
                const pct = t > 0 ? ((ctx.parsed / t) * 100).toFixed(1) : 0;
                return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
              }
            }
          }
        }
      },
      plugins: [{
        id: 'centerAset',
        afterDraw: function(chart) {
          const meta = chart.getDatasetMeta(0);
          if (!meta.data.length) return;
          const x = meta.data[0].x, y = meta.data[0].y, c = chart.ctx;
          c.save();
          c.textAlign = 'center'; c.textBaseline = 'middle';
          c.font = 'bold 30px system-ui, sans-serif'; c.fillStyle = '#212529';
          c.fillText(aset, x, y - 6);
          c.font = '11px system-ui, sans-serif'; c.fillStyle = '#6c757d';
          c.fillText('Aset', x, y + 18);
          c.restore();
        }
      }]
    });
  }

  // ============================================
  // CHART 3 — KATEGORI (Bar, monoton biru tua)
  // ============================================
  const ctxKategori = document.getElementById('chartKategori');
  if (ctxKategori) {
    new Chart(ctxKategori, {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_column($statistikKategori, 'label')) ?>,
        datasets: [{
          data: <?= json_encode(array_map('intval', array_column($statistikKategori, 'jumlah'))) ?>,
          backgroundColor: '#145374',
          borderRadius: 6
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });
  }

  // ============================================
  // CHART 4 — LOKASI (Bar, monoton teal)
  // ============================================
  const ctxLokasi = document.getElementById('chartLokasi');
  if (ctxLokasi) {
    new Chart(ctxLokasi, {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_column($statistikLokasi, 'label')) ?>,
        datasets: [{
          data: <?= json_encode(array_map('intval', array_column($statistikLokasi, 'jumlah'))) ?>,
          backgroundColor: '#00A8CC',
          borderRadius: 6
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });
  }

  // ============================================
  // CHART 5 — TAHUN PEROLEHAN (Bar, monoton oranye)
  // ============================================
  const ctxTahun = document.getElementById('chartTahun');
  if (ctxTahun) {
    new Chart(ctxTahun, {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_column($statistikTahun, 'label')) ?>,
        datasets: [{
          data: <?= json_encode(array_map('intval', array_column($statistikTahun, 'jumlah'))) ?>,
          backgroundColor: '#fd7e14',
          borderRadius: 6
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });
  }
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>