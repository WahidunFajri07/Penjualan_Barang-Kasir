<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);
session_start();
require_once '../lib/auth.php';
require_once '../lib/functions.php';
requireAuth();

if (getUserRole() !== 'admin') {
    redirect('../login.php');
}

// Set variabel untuk template
$page_title = 'Dashboard Kasir';
$current_page = 'dashboard';

// Koneksi Database
require_once '../config/database.php';

// Query untuk mendapatkan total penjualan hari ini
$query_penjualan_hari_ini = "SELECT COALESCE(SUM(total_bayar), 0) as total 
                              FROM transaksi 
                              WHERE DATE(tanggal) = CURDATE() 
                              AND status_bayar = 'LUNAS'";
$result_penjualan = mysqli_query($conn, $query_penjualan_hari_ini);
$row_penjualan = mysqli_fetch_assoc($result_penjualan);
$total_penjualan_hari_ini = $row_penjualan['total'];

// Query untuk mendapatkan total transaksi hari ini
$query_transaksi_hari_ini = "SELECT COUNT(*) as total 
                              FROM transaksi 
                              WHERE DATE(tanggal) = CURDATE()";
$result_transaksi = mysqli_query($conn, $query_transaksi_hari_ini);
$row_transaksi = mysqli_fetch_assoc($result_transaksi);
$total_transaksi_hari_ini = $row_transaksi['total'];

// Query untuk mendapatkan total produk
$query_total_produk = "SELECT COUNT(*) as total FROM produk";
$result_produk = mysqli_query($conn, $query_total_produk);
$row_produk = mysqli_fetch_assoc($result_produk);
$total_produk = $row_produk['total'];

// Query untuk mendapatkan transaksi belum lunas
$query_belum_lunas = "SELECT COUNT(*) as total 
                       FROM transaksi 
                       WHERE status_bayar = 'BELUM LUNAS'";
$result_belum_lunas = mysqli_query($conn, $query_belum_lunas);
$row_belum_lunas = mysqli_fetch_assoc($result_belum_lunas);
$transaksi_belum_lunas = $row_belum_lunas['total'];

// Hitung pertumbuhan penjualan (hari ini vs kemarin)
$query_penjualan_kemarin = "SELECT COALESCE(SUM(total_bayar), 0) as total 
                            FROM transaksi 
                            WHERE DATE(tanggal) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) 
                            AND status_bayar = 'LUNAS'";
$result_kemarin = mysqli_query($conn, $query_penjualan_kemarin);
$row_kemarin = mysqli_fetch_assoc($result_kemarin);
$penjualan_kemarin = $row_kemarin['total'];

if ($penjualan_kemarin > 0) {
    $pertumbuhan_penjualan = (($total_penjualan_hari_ini - $penjualan_kemarin) / $penjualan_kemarin) * 100;
    $pertumbuhan_penjualan = number_format($pertumbuhan_penjualan, 1);
} else {
    $pertumbuhan_penjualan = $total_penjualan_hari_ini > 0 ? '+100.0' : '0.0';
}

// Hitung pertumbuhan transaksi (hari ini vs kemarin)
$query_transaksi_kemarin = "SELECT COUNT(*) as total 
                            FROM transaksi 
                            WHERE DATE(tanggal) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
$result_trx_kemarin = mysqli_query($conn, $query_transaksi_kemarin);
$row_trx_kemarin = mysqli_fetch_assoc($result_trx_kemarin);
$transaksi_kemarin = $row_trx_kemarin['total'];

if ($transaksi_kemarin > 0) {
    $pertumbuhan_transaksi = (($total_transaksi_hari_ini - $transaksi_kemarin) / $transaksi_kemarin) * 100;
    $pertumbuhan_transaksi = number_format($pertumbuhan_transaksi, 1);
} else {
    $pertumbuhan_transaksi = $total_transaksi_hari_ini > 0 ? '+100.0' : '0.0';
}

// Pertumbuhan produk (contoh: dibandingkan dengan bulan lalu)
$pertumbuhan_produk = '+5.2'; // Bisa disesuaikan dengan logika bisnis

// Query untuk produk terlaris hari ini
$query_produk_terlaris = "SELECT 
                            p.nama_produk,
                            SUM(dt.qty) as total_terjual,
                            SUM(dt.subtotal) as total_pendapatan
                          FROM detail_transaksi dt
                          JOIN produk p ON dt.produk_id = p.id
                          JOIN transaksi t ON dt.transaksi_id = t.id
                          WHERE DATE(t.tanggal) = CURDATE()
                          GROUP BY dt.produk_id, p.nama_produk
                          ORDER BY total_terjual DESC
                          LIMIT 5";
$result_top_products = mysqli_query($conn, $query_produk_terlaris);
$top_products = [];
while($row = mysqli_fetch_assoc($result_top_products)) {
    $top_products[] = [
        'nama' => $row['nama_produk'],
        'terjual' => $row['total_terjual'],
        'pendapatan' => $row['total_pendapatan']
    ];
}

// Jika tidak ada produk terlaris hari ini, ambil data keseluruhan
if (empty($top_products)) {
    $query_produk_terlaris_all = "SELECT 
                                    p.nama_produk,
                                    SUM(dt.qty) as total_terjual,
                                    SUM(dt.subtotal) as total_pendapatan
                                  FROM detail_transaksi dt
                                  JOIN produk p ON dt.produk_id = p.id
                                  GROUP BY dt.produk_id, p.nama_produk
                                  ORDER BY total_terjual DESC
                                  LIMIT 5";
    $result_top_products_all = mysqli_query($conn, $query_produk_terlaris_all);
    while($row = mysqli_fetch_assoc($result_top_products_all)) {
        $top_products[] = [
            'nama' => $row['nama_produk'],
            'terjual' => $row['total_terjual'],
            'pendapatan' => $row['total_pendapatan']
        ];
    }
}

// Query untuk data grafik penjualan 7 hari terakhir
$query_grafik_minggu_ini = "SELECT 
                              DATE(tanggal) as tgl,
                              COALESCE(SUM(total_bayar), 0) as total
                            FROM transaksi
                            WHERE DATE(tanggal) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                            AND DATE(tanggal) <= CURDATE()
                            AND status_bayar = 'LUNAS'
                            GROUP BY DATE(tanggal)
                            ORDER BY DATE(tanggal) ASC";
$result_grafik = mysqli_query($conn, $query_grafik_minggu_ini);
$data_grafik_minggu_ini = [];
$labels_grafik = [];

while($row = mysqli_fetch_assoc($result_grafik)) {
    $day_name = date('l', strtotime($row['tgl']));
    $day_indo = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu'
    ];
    $labels_grafik[] = $day_indo[$day_name];
    $data_grafik_minggu_ini[] = $row['total'];
}

// Pastikan ada data untuk 7 hari (jika tidak ada transaksi, isi dengan 0)
$today = date('Y-m-d');
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_name = date('l', strtotime($date));
    $day_indo = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu'
    ];
    
    if (!in_array($day_indo[$day_name], $labels_grafik)) {
        // Tambahkan hari yang tidak ada transaksi
    }
}

// Query untuk performa per hari dalam minggu ini
$query_performa_minggu = "SELECT 
                            DATE(tanggal) as tgl,
                            COUNT(*) as total_transaksi,
                            COALESCE(SUM(total_bayar), 0) as total_pendapatan
                          FROM transaksi
                          WHERE DATE(tanggal) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                          AND DATE(tanggal) <= CURDATE()
                          AND status_bayar = 'LUNAS'
                          GROUP BY DATE(tanggal)
                          ORDER BY DATE(tanggal) ASC";
$result_performa = mysqli_query($conn, $query_performa_minggu);
$days_performance = [];
$max_pendapatan = 0;

// Cari nilai maksimum untuk persentase
$temp_data = [];
while($row = mysqli_fetch_assoc($result_performa)) {
    $temp_data[] = $row;
    if ($row['total_pendapatan'] > $max_pendapatan) {
        $max_pendapatan = $row['total_pendapatan'];
    }
}

// Generate data 7 hari terakhir dengan data dari database
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_name = date('l', strtotime($date));
    $day_indo = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu'
    ];
    
    $found = false;
    foreach($temp_data as $data) {
        if ($data['tgl'] == $date) {
            $percentage = $max_pendapatan > 0 ? ($data['total_pendapatan'] / $max_pendapatan) * 100 : 0;
            $days_performance[] = [
                'day' => $day_indo[$day_name],
                'transaksi' => $data['total_transaksi'],
                'pendapatan' => $data['total_pendapatan'],
                'percentage' => round($percentage)
            ];
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $days_performance[] = [
            'day' => $day_indo[$day_name],
            'transaksi' => 0,
            'pendapatan' => 0,
            'percentage' => 0
        ];
    }
}

// Query untuk aktivitas terbaru
$query_aktivitas = "SELECT 
                      nomor_bukti,
                      tanggal,
                      total_bayar,
                      status_bayar,
                      TIMESTAMPDIFF(MINUTE, tanggal, NOW()) as menit_lalu
                    FROM transaksi
                    WHERE tanggal >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    AND tanggal != '0000-00-00'
                    ORDER BY tanggal DESC
                    LIMIT 4";
$result_aktivitas = mysqli_query($conn, $query_aktivitas);
$aktivitas_terbaru = [];
while($row = mysqli_fetch_assoc($result_aktivitas)) {
    $aktivitas_terbaru[] = $row;
}

// Definisikan base_url jika belum ada
if(!defined('base_url')) {
    define('base_url', 'http://localhost/fash-cashier/');
}

// Definisikan tema
$THEME = 'soft-ui';
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/breadcrumb.php'; ?>

<style>
/* Enhanced Card Animations */
.stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    overflow: hidden;
    position: relative;
    z-index: 1; /* TAMBAHKAN INI - set z-index lebih rendah */
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
    z-index: -1; /* TAMBAHKAN INI */
}

.stat-card:hover::before {
    left: 100%;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
    z-index: 2; /* UBAH DARI z-index TINGGI KE 2 */
}

.stat-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    transition: all 0.3s ease;
}

.stat-card:hover .stat-icon {
    transform: scale(1.1) rotate(5deg);
}

/* Counter Animation */
@keyframes countUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.counter {
    animation: countUp 0.6s ease-out;
}

/* Welcome Card Enhancement - DIPERBAIKI */
.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
    overflow: hidden;
    position: relative;
    margin-top: 15px;
    z-index: 1; /* TAMBAHKAN INI */
}

.welcome-card::before {
    content: '';
    position: absolute;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
    top: -100px;
    right: -100px;
    z-index: -1; /* TAMBAHKAN INI */
}

.quick-action-btn {
    background: rgba(255,255,255,0.2);
    border: 2px solid rgba(255,255,255,0.3);
    color: white;
    padding: 0.5rem 1.2rem;
    border-radius: 10px;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    position: relative;
    z-index: 2; /* UBAH DARI 3 KE 2 */
    cursor: pointer;
    display: inline-block;
    text-decoration: none;
}

.quick-action-btn:hover {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
    transform: translateY(-2px);
    color: white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Chart Card */
.chart-card {
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border-radius: 16px;
    position: relative;
    z-index: 1; /* TAMBAHKAN INI */
}

/* Top Product Card */
.product-item {
    transition: all 0.3s ease;
    border-radius: 12px;
    padding: 0.75rem;
}

.product-item:hover {
    background: #f8f9fa;
    transform: translateX(5px);
}

.product-thumbnail {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    object-fit: cover;
}

/* Activity Timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #667eea, #764ba2);
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-dot {
    position: absolute;
    left: -26px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Skeleton Loading */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Badge Enhancement */
.badge-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.badge-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.badge-gradient-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}
</style>

<!-- Welcome Card Enhanced -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card welcome-card">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="text-white mb-2">
                            <i class="fas fa-hand-wave me-2"></i>
                            Selamat Datang Kembali, <?= ucfirst($_SESSION['username']); ?>!
                        </h4>
                        <p class="text-white opacity-8 mb-3">
                            Sistem Kasir Aktif | Login sebagai <strong><?= ucfirst($_SESSION['role']); ?></strong> | 
                            <i class="far fa-clock me-1"></i><?= date('d F Y, H:i'); ?>
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="/fash-cashier/transaksi/add.php" class="quick-action-btn">
                                <i class="fas fa-cash-register me-2"></i>Transaksi Baru
                            </a>
                            <a href="/fash-cashier/admin/laporan_penjualan.php" class="quick-action-btn">
                                <i class="fas fa-file-invoice me-2"></i>Lihat Laporan
                            </a>
                            <a href="/fash-cashier/produk/index.php" class="quick-action-btn">
                                <i class="fas fa-box me-2"></i>Kelola Produk
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 text-end d-none d-lg-block">
                        <i class="fas fa-chart-line" style="font-size: 120px; opacity: 0.15; color: white;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards Enhanced -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card" style="box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold opacity-7">Penjualan Hari Ini</p>
                            <h5 class="font-weight-bolder counter mb-0">
                                Rp <?= number_format($total_penjualan_hari_ini, 0, ',', '.'); ?>
                            </h5>
                            <span class="badge badge-gradient-success mt-2">
                                <i class="fas fa-arrow-<?= strpos($pertumbuhan_penjualan, '-') === false ? 'up' : 'down'; ?> me-1"></i><?= $pertumbuhan_penjualan; ?>%
                            </span>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-money-bill-wave text-lg opacity-10 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card" style="box-shadow: 0 10px 30px rgba(56, 239, 125, 0.15);">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold opacity-7">Total Transaksi</p>
                            <h5 class="font-weight-bolder counter mb-0">
                                <?= $total_transaksi_hari_ini; ?> Transaksi
                            </h5>
                            <span class="badge badge-gradient-success mt-2">
                                <i class="fas fa-arrow-<?= strpos($pertumbuhan_transaksi, '-') === false ? 'up' : 'down'; ?> me-1"></i><?= $pertumbuhan_transaksi; ?>%
                            </span>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <i class="fas fa-shopping-cart text-lg opacity-10 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card" style="box-shadow: 0 10px 30px rgba(79, 172, 254, 0.15);">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold opacity-7">Total Produk</p>
                            <h5 class="font-weight-bolder counter mb-0">
                                <?= $total_produk; ?> Items
                            </h5>
                            <span class="badge badge-gradient-info mt-2">
                                <i class="fas fa-arrow-up me-1"></i><?= $pertumbuhan_produk; ?>%
                            </span>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-box text-lg opacity-10 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card" style="box-shadow: 0 10px 30px rgba(245, 87, 108, 0.15);">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold opacity-7">Belum Lunas</p>
                            <h5 class="font-weight-bolder counter mb-0">
                                <?= $transaksi_belum_lunas; ?> Transaksi
                            </h5>
                            <span class="badge badge-gradient-warning mt-2">
                                <i class="fas fa-exclamation-circle me-1"></i>Perlu Tindakan
                            </span>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fas fa-exclamation-triangle text-lg opacity-10 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart & Top Products Row -->
<div class="row mb-4">
    <!-- Sales Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card chart-card">
            <div class="card-header pb-0 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Grafik Penjualan Minggu Ini</h6>
                        <p class="text-sm text-secondary mb-0">7 Hari Terakhir</p>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary active">Minggu</button>
                        <button type="button" class="btn btn-sm btn-outline-primary">Bulan</button>
                        <button type="button" class="btn btn-sm btn-outline-primary">Tahun</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <canvas id="salesChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="col-lg-4 mb-4">
        <div class="card chart-card">
            <div class="card-header pb-0 p-3">
                <h6 class="mb-0">Produk Terlaris</h6>
                <p class="text-sm text-secondary mb-0">Top <?= count($top_products); ?> produk</p>
            </div>
            <div class="card-body p-3">
                <?php 
                if (!empty($top_products)):
                    foreach($top_products as $index => $product): 
                ?>
                <div class="product-item mb-2">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="stat-icon" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <span class="text-white font-weight-bold"><?= $index + 1; ?></span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 text-sm"><?= htmlspecialchars($product['nama']); ?></h6>
                            <p class="text-xs text-secondary mb-0">
                                <?= $product['terjual']; ?> terjual • Rp <?= number_format($product['pendapatan'], 0, ',', '.'); ?>
                            </p>
                        </div>
                        <div>
                            <i class="fas fa-fire text-warning"></i>
                        </div>
                    </div>
                </div>
                <?php 
                    endforeach;
                else:
                ?>
                <div class="text-center py-4">
                    <i class="fas fa-box-open text-secondary" style="font-size: 48px; opacity: 0.3;"></i>
                    <p class="text-secondary mt-2">Belum ada data penjualan</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity & Performance -->
<div class="row">
    <!-- Recent Activity -->
    <div class="col-lg-6 mb-4">
        <div class="card chart-card">
            <div class="card-header pb-0 p-3">
                <h6 class="mb-0">Aktivitas Terbaru</h6>
                <p class="text-sm text-secondary mb-0">Update real-time</p>
            </div>
            <div class="card-body p-3">
                <div class="timeline">
                    <?php 
                    if (!empty($aktivitas_terbaru)):
                        $colors = [
                            'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                            'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
                            'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                            'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'
                        ];
                        foreach($aktivitas_terbaru as $index => $aktivitas):
                            $waktu_lalu = $aktivitas['menit_lalu'];
                            if ($waktu_lalu < 60) {
                                $waktu_str = $waktu_lalu . ' menit yang lalu';
                            } else if ($waktu_lalu < 1440) {
                                $waktu_str = floor($waktu_lalu / 60) . ' jam yang lalu';
                            } else {
                                $waktu_str = floor($waktu_lalu / 1440) . ' hari yang lalu';
                            }
                    ?>
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background: <?= $colors[$index % 4]; ?>;"></div>
                        <div>
                            <h6 class="text-sm mb-1">
                                <?= $aktivitas['status_bayar'] == 'LUNAS' ? 'Pembayaran diterima' : 'Transaksi baru'; ?> 
                                #<?= htmlspecialchars($aktivitas['nomor_bukti']); ?>
                            </h6>
                            <p class="text-xs text-secondary mb-0">
                                <i class="far fa-clock me-1"></i><?= $waktu_str; ?>
                                <?php if ($aktivitas['total_bayar'] > 0): ?>
                                • Rp <?= number_format($aktivitas['total_bayar'], 0, ',', '.'); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <?php 
                        endforeach;
                    else:
                    ?>
                    <div class="text-center py-4">
                        <i class="fas fa-history text-secondary" style="font-size: 48px; opacity: 0.3;"></i>
                        <p class="text-secondary mt-2">Belum ada aktivitas hari ini</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Summary -->
    <div class="col-lg-6 mb-4">
        <div class="card chart-card">
            <div class="card-header pb-0 p-3">
                <h6 class="mb-0">Ringkasan Performa Minggu Ini</h6>
                <p class="text-sm text-secondary mb-0">7 Hari Terakhir</p>
            </div>
            <div class="card-body p-3">
                <?php
                foreach($days_performance as $day):
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="text-sm mb-0"><?= $day['day']; ?></h6>
                        <span class="text-xs text-secondary">
                            <?= $day['transaksi']; ?> transaksi
                            <?php if ($day['pendapatan'] > 0): ?>
                            • Rp <?= number_format($day['pendapatan'], 0, ',', '.'); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 10px;">
                        <div class="progress-bar" 
                             style="width: <?= $day['percentage']; ?>%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"
                             role="progressbar"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart dengan data dari database
const ctx = document.getElementById('salesChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels_grafik); ?>,
            datasets: [{
                label: 'Penjualan (Rp)',
                data: <?= json_encode($data_grafik_minggu_ini); ?>,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) {
                                return 'Rp ' + (value/1000000) + 'jt';
                            } else if (value >= 1000) {
                                return 'Rp ' + (value/1000) + 'k';
                            }
                            return 'Rp ' + value;
                        }
                    }
                }
            }
        }
    });
}

// Counter Animation
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        counter.style.opacity = '0';
        setTimeout(() => {
            counter.style.transition = 'opacity 0.6s ease-out';
            counter.style.opacity = '1';
        }, 100);
    });
});
</script>

<?php include '../views/'.$THEME.'/footer.php'; ?>