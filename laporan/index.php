<?php
require_once __DIR__ . '/../includes/functions.php';
wajibLogin();
$pdo = getKoneksi();

$kondisi        = $_GET['kondisi'] ?? '';
$statusAset     = $_GET['status_aset'] ?? '';
$tipeBarang     = $_GET['tipe'] ?? '';
$tahunPerolehan = $_GET['tahun'] ?? '';
$peralatanId    = $_GET['peralatan'] ?? '';
$merekId        = $_GET['merek'] ?? '';
$lokasiId       = $_GET['lokasi'] ?? '';
$halaman        = max(1, (int) ($_GET['halaman'] ?? 1));
$perHalaman     = 10;

// Ambil data sesuai filter (pakai fungsi dari functions.php)
$data      = ambilDataLaporan($pdo, $kondisi, $statusAset, $tipeBarang, $tahunPerolehan, $peralatanId, $merekId, $lokasiId, $halaman, $perHalaman);
$totalData = hitungDataLaporan($pdo, $kondisi, $statusAset, $tipeBarang, $tahunPerolehan, $peralatanId, $merekId, $lokasiId);

// Data untuk dropdown filter
$daftarKondisi = $pdo->query('SELECT * FROM kondisi_barang ORDER BY id')->fetchAll();
$daftarTipe    = $pdo->query('SELECT DISTINCT tipe_barang FROM barang WHERE tipe_barang IS NOT NULL AND tipe_barang <> "" ORDER BY tipe_barang')->fetchAll(PDO::FETCH_COLUMN);
$daftarTahun   = $pdo->query('SELECT DISTINCT tahun_perolehan FROM barang WHERE tahun_perolehan IS NOT NULL ORDER BY tahun_perolehan DESC')->fetchAll(PDO::FETCH_COLUMN);
$daftarPeralatan = $pdo->query('SELECT id, nama_peralatan FROM nama_peralatan ORDER BY nama_peralatan')->fetchAll();
$daftarMerek     = $pdo->query('SELECT id, nama_merek FROM merek ORDER BY nama_merek')->fetchAll();
$daftarLokasi    = $pdo->query('SELECT id, nama_ruangan FROM lokasi ORDER BY nama_ruangan')->fetchAll();

$dataRingkasan = ambilRingkasanBarang($pdo);
// Query string untuk link ekspor (biar filter ikut terbawa)
$queryEkspor = http_build_query(array_filter([
    'kondisi'     => $kondisi,
    'status_aset' => $statusAset,
    'tipe'        => $tipeBarang,
    'tahun'       => $tahunPerolehan,
    'peralatan'   => $peralatanId,
    'merek'       => $merekId,
    'lokasi'      => $lokasiId,
], fn($v) => $v !== ''));

$judulHalaman = 'Laporan & Ekspor Data';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h5 class="mb-0">Laporan & Ekspor Data</h5>
    <small class="text-muted">
      Saring data sesuai kebutuhan, lalu ekspor ke Excel (csv) atau PDF (cetak lewat browser).
      Ekspor tetap mencakup semua data sesuai filter, bukan hanya halaman yang sedang dilihat.
    </small>
  </div>
</div>

<!--Filter ges-->
<div class="card p-3 mb-3">
  <form method="get" class="row g-2">

    <div class="col-md-3">
      <label class="form-label small mb-1">Kondisi Barang</label>
      <select name="kondisi" class="form-select">
        <option value="">Semua Kondisi</option>
        <?php foreach ($daftarKondisi as $kb): ?>
          <option value="<?= $kb['id'] ?>" <?= $kondisi == $kb['id'] ? 'selected' : '' ?>>
            <?= amankan($kb['nama_kondisi']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label small mb-1">Lokasi / Ruangan</label>
      <select name="lokasi" class="form-select">
        <option value="">Semua Lokasi</option>
        <?php foreach ($daftarLokasi as $l): ?>
          <option value="<?= $l['id'] ?>" <?= $lokasiId == $l['id'] ? 'selected' : '' ?>>
            <?= amankan($l['nama_ruangan']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label small mb-1">Tahun Perolehan</label>
      <select name="tahun" class="form-select">
        <option value="">Semua Tahun</option>
        <?php foreach ($daftarTahun as $th): ?>
          <option value="<?= $th ?>" <?= $tahunPerolehan == $th ? 'selected' : '' ?>>
            <?= amankan($th) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label small mb-1">Nomor Inventaris Kantor</label>
      <select name="status_aset" class="form-select">
        <option value="">Semua Barang</option>
        <option value="ada"   <?= $statusAset === 'ada'   ? 'selected' : '' ?>>Punya No. Inventaris</option>
        <option value="tanpa" <?= $statusAset === 'tanpa' ? 'selected' : '' ?>>Tanpa No. Inventaris</option>
      </select>
    </div>

    <!-- ===== BARIS 2 ===== -->
    <div class="col-md-3">
      <label class="form-label small mb-1">Nama Peralatan</label>
      <select name="peralatan" id="selPeralatan" class="form-select">
        <option value="">Semua Peralatan</option>
        <?php foreach ($daftarPeralatan as $p): ?>
          <option value="<?= $p['id'] ?>" <?= $peralatanId == $p['id'] ? 'selected' : '' ?>>
            <?= amankan($p['nama_peralatan']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label small mb-1">Merek</label>
      <select name="merek" id="selMerek" class="form-select">
        <option value="">Semua Merek</option>
        <?php foreach ($daftarMerek as $m): ?>
          <option value="<?= $m['id'] ?>" <?= $merekId == $m['id'] ? 'selected' : '' ?>>
            <?= amankan($m['nama_merek']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="form-text small" id="infoMerek"></div>
    </div>

    <div class="col-md-3">
      <label class="form-label small mb-1">Tipe Barang</label>
      <select name="tipe" id="selTipe" class="form-select">
        <option value="">Semua Tipe</option>
        <?php foreach ($daftarTipe as $t): ?>
          <option value="<?= amankan($t) ?>" <?= $tipeBarang === $t ? 'selected' : '' ?>>
            <?= amankan($t) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="form-text small" id="infoTipe"></div>
    </div>

    <div class="col-md-3 d-flex gap-2 align-items-end">
      <button class="btn btn-outline-secondary flex-fill">
        <i class="bi bi-funnel"></i> Terapkan Filter
      </button>
      <a href="index.php" class="btn btn-outline-danger">Reset</a>
    </div>

  </form>
</div>

<div class="d-flex gap-2 mb-3 flex-wrap">
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRingkasan">
    <i class="bi bi-bar-chart-line"></i> Ringkasan
  </button>
  <a href="export_excel.php<?= $queryEkspor ? '?' . $queryEkspor : '' ?>" class="btn btn-success">
    <i class="bi bi-file-earmark-excel"></i> Ekspor ke Excel (csv)
  </a>
  <a href="export_pdf.php<?= $queryEkspor ? '?' . $queryEkspor : '' ?>" target="_blank" class="btn btn-danger">
    <i class="bi bi-file-earmark-pdf"></i> Ekspor ke PDF
  </a>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Peralatan</th>
          <th>Tipe</th>
          <th>Tahun</th>
          <th>Kategori</th>
          <th>Merek</th>
          <th>Lokasi</th>
          <th>No. Inventaris</th>
          <th>Kondisi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$data): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data yang cocok dengan filter.</td></tr>
        <?php endif; ?>
        <?php foreach ($data as $row): ?>
          <tr>
            <td class="kode-barang"><?= amankan($row['kode_barang']) ?></td>
            <td><?= amankan($row['nama_peralatan']) ?></td>
            <td><?= amankan($row['tipe_barang']) ?></td>
            <td><?= amankan($row['tahun_perolehan']) ?></td>
            <td><small><?= amankan($row['nama_kategori']) ?> / <?= amankan($row['nama_subkategori']) ?></small></td>
            <td><?= amankan($row['nama_merek']) ?></td>
            <td><?= amankan($row['nama_ruangan']) ?></td>
            <td>
              <?= $row['nomor_inventaris_kantor']
                    ? amankan($row['nomor_inventaris_kantor'])
                    : '<span class="text-muted">-</span>' ?>
            </td>
            <td>
              <span class="badge" style="background-color: <?= amankan($row['kode_warna']) ?>">
                <?= amankan($row['kondisi']) ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <p class="text-muted small mt-2 mb-0">Total data sesuai filter: <?= $totalData ?> barang.</p>

  <?php renderPaginasi($halaman, $totalData, $perHalaman, [
      'kondisi'     => $kondisi,
      'status_aset' => $statusAset,
      'tipe'        => $tipeBarang,
      'tahun'       => $tahunPerolehan,
      'peralatan'   => $peralatanId,
      'merek'       => $merekId,
      'lokasi'      => $lokasiId,
  ]); ?>
</div>

<script>
const selPeralatan = document.getElementById('selPeralatan');
const selMerek     = document.getElementById('selMerek');
const selTipe      = document.getElementById('selTipe');
const infoMerek    = document.getElementById('infoMerek');
const infoTipe     = document.getElementById('infoTipe');

async function refreshOpsi(resetMerek, resetTipe) {
  const peralatan = selPeralatan.value;
  const merek     = resetMerek ? '' : selMerek.value;
  const merekTerpilih = resetMerek ? '' : selMerek.value;
  const tipeTerpilih  = resetTipe  ? '' : selTipe.value;

  selMerek.disabled = true;
  selTipe.disabled  = true;
  selMerek.innerHTML = '<option value="">Memuat...</option>';
  selTipe.innerHTML  = '<option value="">Memuat...</option>';

  try {
    const res = await fetch(
      `get_opsi_filter.php?peralatan=${encodeURIComponent(peralatan)}&merek=${encodeURIComponent(merek)}`
    );
    const data = await res.json();

    selMerek.innerHTML = '<option value="">Semua Merek</option>';
    data.merek.forEach(m => {
      const opt = document.createElement('option');
      opt.value = m.id;
      opt.textContent = m.nama_merek;
      if (String(m.id) === String(merekTerpilih)) opt.selected = true;
      selMerek.appendChild(opt);
    });

    selTipe.innerHTML = '<option value="">Semua Tipe</option>';
    data.tipe.forEach(t => {
      const opt = document.createElement('option');
      opt.value = t;
      opt.textContent = t;
      if (t === tipeTerpilih) opt.selected = true;
      selTipe.appendChild(opt);
    });

  } catch (err) {
    console.error('Gagal memuat opsi filter:', err);
    selMerek.innerHTML = '<option value="">Semua Merek</option>';
    selTipe.innerHTML  = '<option value="">Semua Tipe</option>';
  } finally {
    selMerek.disabled = false;
    selTipe.disabled  = false;
    updateInfoText();
  }
}

function updateInfoText() {
  const peralatanText = selPeralatan.options[selPeralatan.selectedIndex]?.text || '';
  const merekText     = selMerek.options[selMerek.selectedIndex]?.text || '';

  if (selPeralatan.value) {
    infoMerek.textContent = `Hanya merek dengan peralatan "${peralatanText}"`;
  } else {
    infoMerek.textContent = '';
  }

  if (selPeralatan.value && selMerek.value) {
    infoTipe.textContent = `Hanya tipe "${merekText} ${peralatanText}"`;
  } else if (selPeralatan.value) {
    infoTipe.textContent = `Hanya tipe untuk peralatan "${peralatanText}"`;
  } else if (selMerek.value) {
    infoTipe.textContent = `Hanya tipe merek "${merekText}"`;
  } else {
    infoTipe.textContent = '';
  }
}

selPeralatan.addEventListener('change', () => {
  refreshOpsi(true, true);
});

selMerek.addEventListener('change', () => {
  refreshOpsi(false, true);
});

document.addEventListener('DOMContentLoaded', () => {
  if (selPeralatan.value) {
    refreshOpsi(false, false); 
  } else {
    updateInfoText();
  }
});
</script>
<div class="modal fade" id="modalRingkasan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-bar-chart-line"></i> Ringkasan Barang
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <?php if (!$dataRingkasan['ringkasan']): ?>
          <p class="text-center text-muted mb-0">Belum ada data barang.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Nama Barang</th>
                  <th class="text-center">Jumlah Barang</th>
                  <?php foreach ($dataRingkasan['daftar_kondisi'] as $k): ?>
                    <th class="text-center"><?= amankan($k['nama_kondisi']) ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($dataRingkasan['ringkasan'] as $nama => $info): ?>
                  <tr>
                    <td><?= amankan($nama) ?></td>
                    <td class="text-center fw-semibold"><?= $info['total'] ?></td>
                    <?php foreach ($dataRingkasan['daftar_kondisi'] as $k): ?>
                      <td class="text-center"><?= $info['kondisi'][$k['id']] ?? 0 ?></td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <div class="modal-footer mt-3">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <a href="export_ringkasan_excel.php" class="btn btn-success">
          <i class="bi bi-file-earmark-excel"></i> Ekspor Excel
        </a>
        <a href="export_ringkasan_pdf.php" target="_blank" class="btn btn-danger">
          <i class="bi bi-file-earmark-pdf"></i> Ekspor PDF
        </a>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>