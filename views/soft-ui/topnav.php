<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
  <!-- Navbar -->
  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 border-radius-xl" id="navbarBlur" navbar-scroll="true" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); border: 1px solid rgba(0, 0, 0, 0.05); position: relative; z-index: 1000;">
    <div class="container-fluid py-1 px-3">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
          <li class="breadcrumb-item text-sm">
            <a class="text-dark" href="<?= base_url('admin/index.php'); ?>" style="opacity: 0.7; transition: opacity 0.3s;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
              </svg>
              Kasir_app
            </a>
          </li>
          <li class="breadcrumb-item text-sm text-dark active" aria-current="page"><?= $page_title ?? 'Dashboard'; ?></li>
        </ol>
        <h6 class="font-weight-bolder mb-0" style="color: #344767; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?= $page_title ?? 'Dashboard'; ?></h6>
      </nav>
      <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
        <div class="ms-md-auto pe-md-3 d-flex align-items-center">
        </div>
        <ul class="navbar-nav justify-content-end">
          <li class="nav-item d-flex align-items-center">
            <a href="<?= base_url('/transaksi/add.php'); ?>" class="btn btn-sm mb-0 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); border-radius: 8px; transition: transform 0.2s, box-shadow 0.2s;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
              </svg>
              Transaksi Baru
            </a>
          </li>
          <li class="nav-item dropdown d-flex align-items-center">
            <a href="javascript:;" class="nav-link text-body font-weight-bold px-0 dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false" style="background: rgba(102, 126, 234, 0.05); padding: 8px 12px !important; border-radius: 8px; margin-right: 8px; cursor: pointer;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <span class="d-sm-inline d-none"><?= $_SESSION['username'] ?? 'Admin'; ?></span>
              <small class="text-secondary d-block d-sm-inline">(<?= ucfirst($_SESSION['role'] ?? 'admin'); ?>)</small>
            </a>
            <ul class="dropdown-menu dropdown-menu-end px-2 py-3" aria-labelledby="dropdownUser" style="border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15); border: 1px solid rgba(0, 0, 0, 0.05); min-width: 200px; z-index: 1050;">
              <li class="mb-2">
                <a class="dropdown-item border-radius-md" href="/fash-cashier/admin/profile.php" style="transition: background 0.2s; border-radius: 8px; padding: 10px 15px;">
                  <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px;">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                      </svg>
                    </div>
                    <div>
                      <h6 class="text-sm mb-0">Profil Saya</h6>
                      <p class="text-xs text-secondary mb-0">Kelola akun</p>
                    </div>
                  </div>
                </a>
              </li>
                  <!-- LOGOUT -->
              <li>
                  <a class="dropdown-item border-radius-md d-flex align-items-center text-danger"
                    href="<?= base_url('logout.php') ?>"
                    style="border-radius:8px; padding:10px 15px;">

                      <div class="avatar avatar-sm me-3"
                          style="background:#ffe5e5;
                                  border-radius:10px; width:32px; height:32px;
                                  display:flex; align-items:center; justify-content:center;">
                          <i class="fas fa-sign-out-alt text-danger"></i>
                      </div>

                      <div>
                          <h6 class="text-sm mb-0">Logout</h6>
                          <p class="text-xs text-secondary mb-0">Keluar dari sistem</p>
                      </div>
                  </a>
              </li>
            </ul>
          <li class="nav-item d-xl-none ps-0 d-flex align-items-center">
            <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
              <div class="sidenav-toggler-inner">
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
              </div>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- End Navbar -->

<style>
/* Navbar z-index fix */
.navbar-main {
    position: relative;
    z-index: 1000 !important;
}

/* Dropdown menu z-index */
.dropdown-menu {
    z-index: 1050 !important;
}

/* Hover effects */
a[href*="transaksi/add.php"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6) !important;
}

.nav-link:hover svg {
    transform: scale(1.1);
}

.dropdown-item:hover {
    background: rgba(102, 126, 234, 0.05) !important;
}

.breadcrumb-item a:hover {
    opacity: 1 !important;
}
</style>