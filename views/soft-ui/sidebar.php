<?php
$current_page = $current_page ?? 'dashboard';
$role = $_SESSION['role'] ?? 'admin';
$username = $_SESSION['username'] ?? 'User';

// Mapping halaman untuk active state yang akurat
$page_mapping = [
    'dashboard' => ['admin/index.php', 'index.php'],
    'transaksi_baru' => ['transaksi/add.php', 'add.php'],
    'riwayat_transaksi' => ['transaksi/index.php', 'transaksi.php'],
    'produk' => ['produk/index.php', 'produk.php', 'produk/add.php', 'produk/edit.php'],
    'kategori' => ['kategori/index.php', 'kategori.php'],
    'laporan_penjualan' => ['detail_transaksi/index.php', 'laporan.php'],
    'profile' => ['admin/profile.php', 'profile.php'],
    'logout' => ['logout.php']
];

// Deteksi active page secara akurat
function isActivePage($current_page_var, $mapping, $request_uri) {
    $request = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (isset($mapping[$current_page_var])) {
        return in_array($request, $mapping[$current_page_var]);
    }
    return false;
}
?>

<!-- Minimalist White Sidebar -->
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-white"
       id="sidenav-main"
       style="box-shadow: 0 4px 20px rgba(0,0,0,0.08); height: calc(100vh - 24px); transition: all 0.3s ease;">

  <!-- Sidebar Header - Clean Branding -->
  <div class="sidenav-header position-relative mb-4">
    <!-- Close button for mobile -->
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-xl-none d-block"
       id="iconSidenav" style="z-index: 1000;"></i>
    
    <a class="navbar-brand m-0 d-flex align-items-center py-2" href="<?= base_url('admin/index.php'); ?>">
      <div class="icon icon-shape icon-sm bg-gradient-primary shadow text-center me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 12px;">
        <i class="fas fa-cash-register text-white text-lg"></i>
      </div>
      <span class="font-weight-bold text-dark" style="font-size: 1.25rem;">Fash-Cashier</span>
    </a>
  </div>

  <!-- Clean Divider -->
  <hr class="horizontal dark mt-0 mb-3" style="opacity: 0.1;">

  <!-- Scrollable Navigation -->
  <div class="collapse navbar-collapse w-auto h-auto"
       id="sidenav-collapse-main"
       style="height: calc(100% - 220px); overflow-y: auto; overflow-x: hidden;">

    <ul class="navbar-nav">
      
      <!-- ========== DASHBOARD ========== -->
      <li class="nav-item mb-1">
        <a class="nav-link <?= ($current_page == 'dashboard') ? 'active bg-light' : ''; ?>" 
           href="<?= base_url('admin/index.php'); ?>"
           style="border-radius: 10px; padding: 0.85rem 1.5rem; margin: 0 1rem; transition: all 0.25s ease;">
          <div class="d-flex align-items-center">
            <div class="icon icon-shape icon-xs bg-gradient-primary shadow-sm text-center me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 8px;">
              <i class="fas fa-home text-white text-sm"></i>
            </div>
            <span class="nav-link-text font-weight-normal text-dark">Dashboard</span>
          </div>
          <?php if($current_page == 'dashboard'): ?>
          <span class="position-absolute start-0 top-1 bottom-1 w-1 rounded-end bg-gradient-primary"></span>
          <?php endif; ?>
        </a>
      </li>

      <!-- ========== TRANSAKSI SECTION ========== -->
      <li class="nav-item mt-2 mb-1">
        <div class="ps-4 ms-1">
          <h6 class="text-uppercase text-xs font-weight-bolder opacity-6 mb-0" style="letter-spacing: 0.5px; color: #6c757d;">
            Transaksi
          </h6>
        </div>
      </li>

      <li class="nav-item mb-1">
        <a class="nav-link <?= ($current_page == 'transaksi_baru') ? 'active bg-light' : ''; ?>" 
           href="<?= base_url('transaksi/add.php'); ?>"
           style="border-radius: 10px; padding: 0.85rem 1.5rem; margin: 0 1rem; transition: all 0.25s ease;">
          <div class="d-flex align-items-center">
            <div class="icon icon-shape icon-xs bg-gradient-success shadow-sm text-center me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 8px;">
              <i class="fas fa-plus text-white text-sm"></i>
            </div>
            <span class="nav-link-text font-weight-normal text-dark">Transaksi Baru</span>
          </div>
          <?php if($current_page == 'transaksi_baru'): ?>
          <span class="position-absolute start-0 top-1 bottom-1 w-1 rounded-end bg-gradient-success"></span>
          <?php endif; ?>
        </a>
      </li>

      <li class="nav-item mb-1">
        <a class="nav-link <?= ($current_page == 'riwayat_transaksi') ? 'active bg-light' : ''; ?>" 
           href="<?= base_url('transaksi/index.php'); ?>"
           style="border-radius: 10px; padding: 0.85rem 1.5rem; margin: 0 1rem; transition: all 0.25s ease;">
          <div class="d-flex align-items-center">
            <div class="icon icon-shape icon-xs bg-gradient-info shadow-sm text-center me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 8px;">
              <i class="fas fa-history text-white text-sm"></i>
            </div>
            <span class="nav-link-text font-weight-normal text-dark">Riwayat Transaksi</span>
          </div>
          <?php if($current_page == 'riwayat_transaksi'): ?>
          <span class="position-absolute start-0 top-1 bottom-1 w-1 rounded-end bg-gradient-info"></span>
          <?php endif; ?>
        </a>
      </li>

      <!-- ========== MASTER DATA SECTION ========== -->
      <li class="nav-item mt-2 mb-1">
        <div class="ps-4 ms-1">
          <h6 class="text-uppercase text-xs font-weight-bolder opacity-6 mb-0" style="letter-spacing: 0.5px; color: #6c757d;">
            Master Data
          </h6>
        </div>
      </li>

      <li class="nav-item mb-1">
        <a class="nav-link <?= ($current_page == 'produk') ? 'active bg-light' : ''; ?>" 
           href="<?= base_url('produk/index.php'); ?>"
           style="border-radius: 10px; padding: 0.85rem 1.5rem; margin: 0 1rem; transition: all 0.25s ease;">
          <div class="d-flex align-items-center">
            <div class="icon icon-shape icon-xs bg-gradient-warning shadow-sm text-center me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 8px;">
              <i class="fas fa-box text-white text-sm"></i>
            </div>
            <span class="nav-link-text font-weight-normal text-dark">Produk</span>
          </div>
          <?php if($current_page == 'produk'): ?>
          <span class="position-absolute start-0 top-1 bottom-1 w-1 rounded-end bg-gradient-warning"></span>
          <?php endif; ?>
        </a>
      </li>

      <!-- ========== LAPORAN SECTION ========== -->
      <li class="nav-item mt-2 mb-1">
        <div class="ps-4 ms-1">
          <h6 class="text-uppercase text-xs font-weight-bolder opacity-6 mb-0" style="letter-spacing: 0.5px; color: #6c757d;">
            Laporan
          </h6>
        </div>
      </li>

      <li class="nav-item mb-1">
        <a class="nav-link <?= ($current_page == 'laporan_penjualan') ? 'active bg-light' : ''; ?>" 
           href="<?= base_url('/admin/laporan_penjualan.php'); ?>"
           style="border-radius: 10px; padding: 0.85rem 1.5rem; margin: 0 1rem; transition: all 0.25s ease;">
          <div class="d-flex align-items-center">
            <div class="icon icon-shape icon-xs bg-gradient-info shadow-sm text-center me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 8px;">
              <i class="fas fa-file-invoice text-white text-sm"></i>
            </div>
            <span class="nav-link-text font-weight-normal text-dark">Laporan Penjualan</span>
          </div>
          <?php if($current_page == 'laporan_penjualan'): ?>
          <span class="position-absolute start-0 top-1 bottom-1 w-1 rounded-end bg-gradient-info"></span>
          <?php endif; ?>
        </a>
      </li>

      <!-- ========== PENGATURAN SECTION ========== -->
      <?php if($role == 'admin'): ?>
      <li class="nav-item mt-2 mb-1">
        <div class="ps-4 ms-1">
          <h6 class="text-uppercase text-xs font-weight-bolder opacity-6 mb-0" style="letter-spacing: 0.5px; color: #6c757d;">
            Pengaturan
          </h6>
        </div>
      </li>

      <li class="nav-item mb-1">
        <a class="nav-link <?= ($current_page == 'profile') ? 'active bg-light' : ''; ?>" 
          href="<?= base_url('/admin/profile.php'); ?>"
          style="border-radius: 10px; padding: 0.85rem 1.5rem; margin: 0 1rem; transition: all 0.25s ease;">

          <div class="d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-center bg-dark text-white shadow me-3"
                style="width:32px;height:32px;border-radius:8px;">
              <i class="fas fa-user-cog"></i>
            </div>

            <span class="fw-normal text-dark">Profile & Setting</span>
          </div>

        </a>
      </li>
      <?php endif; ?>

      <!-- ========== AKUN SECTION ========== -->
      <li class="nav-item mt-2 mb-1">
        <div class="ps-4 ms-1">
          <h6 class="text-uppercase text-xs font-weight-bolder opacity-6 mb-0" style="letter-spacing: 0.5px; color: #6c757d;">
            Akun
          </h6>
        </div>
      </li>

      <li class="nav-item mb-1">
        <a class="nav-link <?= ($current_page == 'logout') ? 'active bg-light' : ''; ?>" 
          href="<?= base_url('logout.php'); ?>"
          style="border-radius:10px;padding:0.85rem 1.5rem;margin:0 1rem;transition:all .25s"
          onclick="return confirm('Apakah Anda yakin ingin keluar?');">

          <div class="d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-center bg-danger text-white shadow me-3"
                style="width:32px;height:32px;border-radius:8px;">
              <i class="fas fa-sign-out-alt"></i>
            </div>

            <span class="fw-normal text-dark">Keluar</span>
          </div>

        </a>
      </li>

    </ul>
  </div>

  <!-- ========== USER PROFILE CARD ========== -->
  <div class="sidenav-footer mx-3 mt-auto mb-3">
    <div class="card border-radius-xl p-3 bg-gray-100" style="border: 1px solid #e9ecef;">
      <div class="d-flex align-items-center">
        <div class="avatar avatar-sm bg-gradient-primary text-white text-center me-3 d-flex align-items-center justify-content-center border-radius-lg" style="width: 36px; height: 36px;">
          <span class="font-weight-bold"><?= strtoupper(substr($username, 0, 1)); ?></span>
        </div>
        <div class="flex-grow-1">
          <h6 class="mb-0 text-sm font-weight-bold text-dark"><?= ucfirst($username); ?></h6>
          <small class="text-xs opacity-7 text-capitalize"><?= $role; ?></small>
        </div>
        <span class="badge badge-xs bg-success rounded-circle" style="width: 8px; height: 8px;"></span>
      </div>
    </div>
  </div>

</aside>

<!-- ========== OVERLAY UNTUK MOBILE ========== -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
/* Clean White Sidebar */
#sidenav-main {
  background-color: #ffffff !important;
  border-right: 1px solid #e9ecef;
  z-index: 1038 !important;
  position: fixed !important;
}

/* Scrollbar Minimalis */
#sidenav-main .navbar-collapse::-webkit-scrollbar {
  width: 4px;
}
#sidenav-main .navbar-collapse::-webkit-scrollbar-track {
  background: #f8f9fa;
  border-radius: 4px;
}
#sidenav-main .navbar-collapse::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 4px;
}
#sidenav-main .navbar-collapse::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}

/* Hover Effects - Subtle */
#sidenav-main .nav-link {
  color: #495057 !important;
}
#sidenav-main .nav-link:hover {
  background-color: #f8f9fa !important;
  transform: translateX(4px);
}
#sidenav-main .nav-link:hover .icon {
  transform: scale(1.05);
}

/* Active State - Persistent & Clear */
#sidenav-main .nav-link.active {
  background-color: #f8f9ff !important;
  color: #495057 !important;
  font-weight: 600 !important;
  position: relative;
}
#sidenav-main .nav-link.active .icon {
  transform: scale(1.1);
}

/* Section Headers - Jarak lebih rapi */
#sidenav-main .nav-item.mt-2 {
  position: relative;
}
#sidenav-main .nav-item.mt-2::before {
  content: '';
  display: block;
  width: calc(100% - 2rem);
  height: 1px;
  margin: 0.5rem auto 0.5rem;
  background-color: #e9ecef;
}

/* Icons */
#sidenav-main .icon {
  transition: all 0.25s ease;
}

/* ========== RESPONSIVE SIDEBAR ========== */
@media (max-width: 1199.98px) {
  #sidenav-main {
    position: fixed !important;
    top: 16px !important;
    left: 16px !important;
    bottom: 16px !important;
    width: calc(100% - 32px) !important;
    max-width: 280px !important;
    transform: translateX(-110%) !important;
    opacity: 0 !important;
    visibility: hidden !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    margin-left: 0 !important;
    z-index: 9999 !important;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2) !important;
    border-radius: 16px !important;
  }
  
  #sidenav-main.show {
    transform: translateX(0) !important;
    opacity: 1 !important;
    visibility: visible !important;
  }
  
  /* Overlay ketika sidebar terbuka */
  .sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9998;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease;
  }
  
  .sidebar-overlay.show {
    display: block;
    opacity: 1;
  }
  
  /* Adjust main content when sidebar is open */
  body.sidebar-open {
    overflow: hidden;
  }
  
  /* Close button styling */
  #iconSidenav {
    transition: all 0.3s ease;
  }
  
  #iconSidenav:hover {
    opacity: 1 !important;
    transform: rotate(90deg);
  }
}

@media (max-width: 576px) {
  #sidenav-main {
    width: calc(100% - 32px) !important;
    max-width: none !important;
    border-radius: 12px !important;
    top: 8px !important;
    bottom: 8px !important;
    left: 8px !important;
  }
  
  #sidenav-main .sidenav-footer {
    display: block !important;
  }
}

/* ========== HAMBURGER ANIMATION ========== */
body.sidebar-open #navbarSidebarToggler .sidenav-toggler-line:nth-child(1) {
  transform: translateY(8px) rotate(45deg);
}

body.sidebar-open #navbarSidebarToggler .sidenav-toggler-line:nth-child(2) {
  opacity: 0;
}

body.sidebar-open #navbarSidebarToggler .sidenav-toggler-line:nth-child(3) {
  transform: translateY(-8px) rotate(-45deg);
}

/* Fix untuk active state yang persisten */
body:not(.g-sidenav-pinned) #sidenav-main {
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
</style>

<script>
// Sidebar Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
  const sidenav = document.getElementById('sidenav-main');
  const iconSidenav = document.getElementById('iconSidenav');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  const body = document.body;
  
  // Fungsi untuk membuka sidebar
  function openSidebar() {
    sidenav.classList.add('show');
    sidebarOverlay.classList.add('show');
    body.classList.add('sidebar-open');
    
    // Update hamburger button state di navbar
    const navbarToggler = document.getElementById('navbarSidebarToggler');
    if (navbarToggler) {
      navbarToggler.setAttribute('aria-expanded', 'true');
      navbarToggler.classList.add('active');
    }
  }
  
  // Fungsi untuk menutup sidebar
  function closeSidebar() {
    sidenav.classList.remove('show');
    sidebarOverlay.classList.remove('show');
    body.classList.remove('sidebar-open');
    
    // Update hamburger button state di navbar
    const navbarToggler = document.getElementById('navbarSidebarToggler');
    if (navbarToggler) {
      navbarToggler.setAttribute('aria-expanded', 'false');
      navbarToggler.classList.remove('active');
    }
  }
  
  // Event listener untuk close button di sidebar
  if (iconSidenav) {
    iconSidenav.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      closeSidebar();
    });
  }
  
  // Event listener untuk overlay
  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', function() {
      closeSidebar();
    });
  }
  
  // Event listener untuk ESC key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && sidenav.classList.contains('show')) {
      closeSidebar();
    }
  });
  
  // Event delegation untuk link di sidebar (opsional)
  sidenav.addEventListener('click', function(e) {
    if (e.target.closest('.nav-link') && window.innerWidth <= 1199.98) {
      // Tutup sidebar setelah klik link di mobile
      setTimeout(closeSidebar, 300);
    }
  });
  
  // Auto close sidebar pada resize ke desktop
  let resizeTimer;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
      if (window.innerWidth > 1199.98 && sidenav.classList.contains('show')) {
        closeSidebar();
      }
    }, 250);
  });
  
  // ========== AUTOMATIC HAMBURGER MENU DETECTION ==========
  // Fungsi untuk menghubungkan hamburger di navbar dengan sidebar
  function connectNavbarHamburger() {
    // Cari semua elemen hamburger di navbar
    const navbarHamburgers = [
      document.getElementById('navbarSidebarToggler'),
      document.getElementById('iconNavbarSidenav'),
      document.querySelector('.navbar-toggler')
    ];
    
    navbarHamburgers.forEach(hamburger => {
      if (hamburger) {
        // Hapus event listener lama jika ada
        const newHamburger = hamburger.cloneNode(true);
        hamburger.parentNode.replaceChild(newHamburger, hamburger);
        
        // Tambah event listener baru
        newHamburger.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          if (sidenav.classList.contains('show')) {
            closeSidebar();
          } else {
            openSidebar();
          }
        });
      }
    });
  }
  
  // Panggil fungsi koneksi hamburger
  connectNavbarHamburger();
  
  // Pastikan navbar sudah dimuat (jika navbar dimuat secara terpisah)
  setTimeout(connectNavbarHamburger, 500);
  
  // Active page detection (existing code)
  const currentPath = window.location.pathname.split('/').pop();
  const pageMap = {
    'index.php': 'dashboard',
    'add.php': 'transaksi_baru',
    'transaksi.php': 'riwayat_transaksi',
    'produk.php': 'produk',
    'produk/index.php': 'produk',
    'produk/add.php': 'produk',
    'produk/edit.php': 'produk',
    'laporan.php': 'laporan_penjualan',
    'detail_transaksi/index.php': 'laporan_penjualan',
    'profile.php': 'profile'
  };
  
  Object.entries(pageMap).forEach(([path, pageKey]) => {
    if (currentPath.includes(path) || window.location.href.includes(path)) {
      document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active', 'bg-light');
        link.querySelector('.position-absolute')?.remove();
      });
      
      const activeLink = document.querySelector(`.nav-link[href*="${path}"]`);
      if (activeLink) {
        activeLink.classList.add('active', 'bg-light');
        
        if (!activeLink.querySelector('.position-absolute')) {
          const indicator = document.createElement('span');
          indicator.className = 'position-absolute start-0 top-1 bottom-1 w-1 rounded-end';
          
          if (pageKey === 'dashboard') indicator.style.background = 'linear-gradient(180deg, #667eea 0%, #764ba2 100%)';
          else if (pageKey === 'transaksi_baru') indicator.style.background = 'linear-gradient(180deg, #11998e 0%, #38ef7d 100%)';
          else if (pageKey === 'riwayat_transaksi' || pageKey === 'laporan_penjualan') indicator.style.background = 'linear-gradient(180deg, #4facfe 0%, #00f2fe 100%)';
          else if (pageKey === 'produk') indicator.style.background = 'linear-gradient(180deg, #f093fb 0%, #f5576c 100%)';
          else if (pageKey === 'profile') indicator.style.background = 'linear-gradient(180deg, #343a40 0%, #6c757d 100%)';
          else if (pageKey === 'logout') indicator.style.background = 'linear-gradient(180deg, #dc3545 0%, #c82333 100%)';
          
          activeLink.appendChild(indicator);
        }
      }
    }
  });
});

// Global function untuk toggle sidebar dari mana saja
window.toggleSidebar = function() {
  const sidenav = document.getElementById('sidenav-main');
  if (sidenav.classList.contains('show')) {
    window.closeSidebar();
  } else {
    window.openSidebar();
  }
};

// Ekspos fungsi ke global scope
window.openSidebar = function() {
  const sidenav = document.getElementById('sidenav-main');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  const body = document.body;
  
  if (sidenav && sidebarOverlay) {
    sidenav.classList.add('show');
    sidebarOverlay.classList.add('show');
    body.classList.add('sidebar-open');
  }
};

window.closeSidebar = function() {
  const sidenav = document.getElementById('sidenav-main');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  const body = document.body;
  
  if (sidenav && sidebarOverlay) {
    sidenav.classList.remove('show');
    sidebarOverlay.classList.remove('show');
    body.classList.remove('sidebar-open');
  }
};
</script>