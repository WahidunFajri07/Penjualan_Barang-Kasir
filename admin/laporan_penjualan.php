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
$page_title = 'Laporan Penjualan';
$current_page = 'laporan';

// Koneksi Database
if (file_exists('../config/database.php')) {
    require_once '../config/database.php';
} else {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db = 'fashion_db';
    
    $conn = mysqli_connect($host, $user, $pass, $db);
    
    if (!$conn) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }
}

if (!isset($conn) || $conn === null) {
    die("Error: Koneksi database tidak dapat dibuat.");
}

// Filter parameters
$filter_tanggal_dari = isset($_GET['tanggal_dari']) ? $_GET['tanggal_dari'] : date('Y-m-01');
$filter_tanggal_sampai = isset($_GET['tanggal_sampai']) ? $_GET['tanggal_sampai'] : date('Y-m-d');
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'SEMUA';

// Build query dengan filter
$where_clauses = ["t.tanggal != '0000-00-00'"];

if (!empty($filter_tanggal_dari)) {
    $where_clauses[] = "DATE(t.tanggal) >= '" . mysqli_real_escape_string($conn, $filter_tanggal_dari) . "'";
}

if (!empty($filter_tanggal_sampai)) {
    $where_clauses[] = "DATE(t.tanggal) <= '" . mysqli_real_escape_string($conn, $filter_tanggal_sampai) . "'";
}

if ($filter_status !== 'SEMUA') {
    $where_clauses[] = "t.status_bayar = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
}

$where_sql = implode(' AND ', $where_clauses);

// Query untuk ringkasan
$query_ringkasan = "SELECT 
                        COUNT(*) as total_transaksi,
                        COALESCE(SUM(CASE WHEN status_bayar = 'LUNAS' THEN total_bayar ELSE 0 END), 0) as total_penjualan,
                        COALESCE(SUM(CASE WHEN status_bayar = 'LUNAS' THEN 1 ELSE 0 END), 0) as transaksi_lunas,
                        COALESCE(SUM(CASE WHEN status_bayar = 'BELUM LUNAS' THEN 1 ELSE 0 END), 0) as transaksi_belum_lunas,
                        COALESCE(SUM(CASE WHEN status_bayar = 'BELUM LUNAS' THEN total_bayar ELSE 0 END), 0) as total_piutang
                    FROM transaksi t
                    WHERE $where_sql";

$result_ringkasan = mysqli_query($conn, $query_ringkasan);
$ringkasan = mysqli_fetch_assoc($result_ringkasan);

// Query untuk detail transaksi
$query_transaksi = "SELECT 
                        t.id,
                        t.nomor_bukti,
                        t.tanggal,
                        t.total_bayar,
                        t.status_bayar,
                        COUNT(dt.id) as jumlah_item
                    FROM transaksi t
                    LEFT JOIN detail_transaksi dt ON t.id = dt.transaksi_id
                    WHERE $where_sql
                    GROUP BY t.id, t.nomor_bukti, t.tanggal, t.total_bayar, t.status_bayar
                    ORDER BY t.tanggal DESC, t.id DESC";

$result_transaksi = mysqli_query($conn, $query_transaksi);

// Query untuk produk terlaris
$query_produk_terlaris = "SELECT 
                            p.nama_produk,
                            p.kode_barang,
                            SUM(dt.qty) as total_terjual,
                            SUM(dt.subtotal) as total_pendapatan
                          FROM detail_transaksi dt
                          JOIN produk p ON dt.produk_id = p.id
                          JOIN transaksi t ON dt.transaksi_id = t.id
                          WHERE $where_sql
                          GROUP BY dt.produk_id, p.nama_produk, p.kode_barang
                          ORDER BY total_terjual DESC
                          LIMIT 10";

$result_produk_terlaris = mysqli_query($conn, $query_produk_terlaris);

// Query untuk grafik penjualan harian
$query_grafik = "SELECT 
                    DATE(t.tanggal) as tanggal,
                    COALESCE(SUM(CASE WHEN status_bayar = 'LUNAS' THEN total_bayar ELSE 0 END), 0) as total
                 FROM transaksi t
                 WHERE $where_sql
                 GROUP BY DATE(t.tanggal)
                 ORDER BY DATE(t.tanggal) ASC";

$result_grafik = mysqli_query($conn, $query_grafik);
$data_grafik = [];
$labels_grafik = [];

while($row = mysqli_fetch_assoc($result_grafik)) {
    $labels_grafik[] = date('d/m', strtotime($row['tanggal']));
    $data_grafik[] = $row['total'];
}

// Definisikan base_url dan tema
if(!defined('base_url')) {
    define('base_url', 'http://localhost/kasir_app/');
}
$THEME = 'soft-ui';
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/breadcrumb.php'; ?>

<style>
.stat-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 12px;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.filter-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.table-responsive {
    border-radius: 12px;
    overflow: hidden;
}

.badge-lunas {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
}

.badge-belum-lunas {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
}

.btn-export {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 0.5rem 1.2rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-export:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: white;
}

.product-rank {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
}

.rank-1 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.rank-2 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.rank-3 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.rank-other { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }

.chart-container {
    position: relative;
    height: 300px;
}
</style>

<!-- Filter Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card filter-card">
            <div class="card-body p-4">
                <h5 class="mb-3">
                    <i class="fas fa-filter me-2"></i>Filter Laporan
                </h5>
                <form method="GET" action="" id="filterForm">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tanggal Dari</label>
                            <input type="date" class="form-control" name="tanggal_dari" value="<?= $filter_tanggal_dari; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tanggal Sampai</label>
                            <input type="date" class="form-control" name="tanggal_sampai" value="<?= $filter_tanggal_sampai; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status Pembayaran</label>
                            <select class="form-select" name="status">
                                <option value="SEMUA" <?= $filter_status == 'SEMUA' ? 'selected' : ''; ?>>Semua Status</option>
                                <option value="LUNAS" <?= $filter_status == 'LUNAS' ? 'selected' : ''; ?>>Lunas</option>
                                <option value="BELUM LUNAS" <?= $filter_status == 'BELUM LUNAS' ? 'selected' : ''; ?>>Belum Lunas</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                                <a href="laporan_penjualan.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 opacity-8">Total Penjualan</p>
                        <h4 class="mb-0">Rp <?= number_format($ringkasan['total_penjualan'], 0, ',', '.'); ?></h4>
                    </div>
                    <div>
                        <i class="fas fa-money-bill-wave fa-3x opacity-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 opacity-8">Total Transaksi</p>
                        <h4 class="mb-0"><?= number_format($ringkasan['total_transaksi'], 0, ',', '.'); ?></h4>
                    </div>
                    <div>
                        <i class="fas fa-shopping-cart fa-3x opacity-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 opacity-8">Transaksi Lunas</p>
                        <h4 class="mb-0"><?= number_format($ringkasan['transaksi_lunas'], 0, ',', '.'); ?></h4>
                    </div>
                    <div>
                        <i class="fas fa-check-circle fa-3x opacity-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 opacity-8">Total Piutang</p>
                        <h4 class="mb-0">Rp <?= number_format($ringkasan['total_piutang'], 0, ',', '.'); ?></h4>
                    </div>
                    <div>
                        <i class="fas fa-exclamation-triangle fa-3x opacity-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart & Top Products -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header pb-0 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Grafik Penjualan Periode</h6>
                    <button class="btn btn-sm btn-export" onclick="exportData('pdf')">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </button>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header pb-0 p-3">
                <h6 class="mb-0">Produk Terlaris</h6>
                <p class="text-sm mb-0">Top 10 produk periode ini</p>
            </div>
            <div class="card-body p-3">
                <?php 
                $rank = 1;
                if (mysqli_num_rows($result_produk_terlaris) > 0):
                    while($produk = mysqli_fetch_assoc($result_produk_terlaris)): 
                        $rank_class = $rank <= 3 ? "rank-$rank" : "rank-other";
                ?>
                <div class="d-flex align-items-center mb-3 p-2 hover-bg-light rounded">
                    <div class="product-rank <?= $rank_class; ?> me-3">
                        <?= $rank; ?>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 text-sm"><?= htmlspecialchars($produk['nama_produk']); ?></h6>
                        <p class="text-xs text-secondary mb-0">
                            <?= $produk['total_terjual']; ?> terjual • Rp <?= number_format($produk['total_pendapatan'], 0, ',', '.'); ?>
                        </p>
                    </div>
                </div>
                <?php 
                        $rank++;
                    endwhile;
                else:
                ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox text-secondary" style="font-size: 48px; opacity: 0.3;"></i>
                    <p class="text-secondary mt-2">Tidak ada data produk</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Detail Transaksi Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header pb-0 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Detail Transaksi</h6>
                    <button class="btn btn-sm btn-export" onclick="exportData('excel')">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-items-center mb-0" id="transaksiTable">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-white text-xxs font-weight-bolder">No</th>
                                <th class="text-uppercase text-white text-xxs font-weight-bolder">Nomor Bukti</th>
                                <th class="text-uppercase text-white text-xxs font-weight-bolder">Tanggal</th>
                                <th class="text-uppercase text-white text-xxs font-weight-bolder">Jumlah Item</th>
                                <th class="text-uppercase text-white text-xxs font-weight-bolder">Total Bayar</th>
                                <th class="text-uppercase text-white text-xxs font-weight-bolder">Status</th>
                                <th class="text-uppercase text-white text-xxs font-weight-bolder">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($result_transaksi) > 0):
                                $no = 1;
                                while($trx = mysqli_fetch_assoc($result_transaksi)): 
                            ?>
                            <tr>
                                <td class="text-sm"><?= $no++; ?></td>
                                <td class="text-sm font-weight-bold"><?= htmlspecialchars($trx['nomor_bukti']); ?></td>
                                <td class="text-sm"><?= date('d/m/Y', strtotime($trx['tanggal'])); ?></td>
                                <td class="text-sm"><?= $trx['jumlah_item']; ?> item</td>
                                <td class="text-sm font-weight-bold">Rp <?= number_format($trx['total_bayar'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($trx['status_bayar'] == 'LUNAS'): ?>
                                        <span class="badge badge-lunas">
                                            <i class="fas fa-check-circle me-1"></i>Lunas
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-belum-lunas">
                                            <i class="fas fa-clock me-1"></i>Belum Lunas
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="detail_transaksi.php?id=<?= $trx['id']; ?>" class="btn btn-sm btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="cetak_struk.php?id=<?= $trx['id']; ?>" class="btn btn-sm btn-success" title="Cetak" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox text-secondary" style="font-size: 48px; opacity: 0.3;"></i>
                                    <p class="text-secondary mt-2">Tidak ada data transaksi</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
const ctx = document.getElementById('salesChart');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_grafik); ?>,
            datasets: [{
                label: 'Penjualan (Rp)',
                data: <?= json_encode($data_grafik); ?>,
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: '#667eea',
                borderWidth: 2,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
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

// Export functions
function exportData(type) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', type);
    window.location.href = 'export_laporan.php?' + params.toString();
}

// Print function
function printReport() {
    window.print();
}
</script>

<style>
@media print {
    .sidebar, .topnav, .filter-card, .btn-export, .btn { display: none !important; }
    .stat-card { break-inside: avoid; }
}
</style>

<?php include '../views/'.$THEME.'/footer.php'; ?>
