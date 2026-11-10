</div><!-- /.container -->

<footer class="text-center text-muted small py-4">
  Sistem Manajemen Inventaris Barang &copy; <?= date('Y') ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Modal Pilih Halaman -->
<div class="modal fade" id="modalPilihHalaman" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pilih Halaman</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="gridHalaman" class="d-flex flex-wrap gap-2 justify-content-center"></div>
      </div>
    </div>
  </div>
</div>

<script>
  
function bukaModalHalaman(start, end, halamanSekarang, queryDasar) {
  const container = document.getElementById('gridHalaman');
  container.innerHTML = ''; // Kosongkan dulu

  for (let i = start; i <= end; i++) {
    // Jika ini halaman yang sedang aktif, beri warna biru (btn-primary)
    // Jika bukan, beri warna putih abu-abu (btn-outline-secondary)
    const classWarna = (i === halamanSekarang) ? 'btn-primary' : 'btn-outline-secondary';
    
    // Buat tombol link
    const btn = document.createElement('a');
    btn.href = '?halaman=' + i + queryDasar;
    btn.className = 'btn ' + classWarna;
    btn.textContent = i;
    // Biar rapi, atur lebar tombol
    btn.style.width = '45px'; 
    
    container.appendChild(btn);
  }

  // Tampilkan modal Bootstrap
  new bootstrap.Modal(document.getElementById('modalPilihHalaman')).show();
}
</script>
</body>
</html>
