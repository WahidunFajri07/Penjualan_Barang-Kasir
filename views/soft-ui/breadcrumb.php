<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mt-0">
  <div class="d-flex justify-content-between align-items-center">
    <div class="d-flex">
      <?php if(isset($_GET['action']) && $_GET['action'] == 'new'): ?>
        <button class="btn btn-sm btn-success me-2" onclick="simpanTransaksi()">
          <i class="ni ni-check-bold me-1"></i> Simpan Transaksi
        </button>
      <?php endif; ?>
    </div>
  </div>
</nav>
<!-- End Breadcrumb -->