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

// Get transaction ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id == 0) {
    redirect('laporan_penjualan.php');
}

// Set variabel untuk template
$page_title = 'Detail Transaksi';
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

// Query transaksi
$query_transaksi = "SELECT * FROM transaksi WHERE id = $id";
$result_transaksi = mysqli_query($conn, $query_transaksi);

if (mysqli_num_rows($result_transaksi) == 0) {
    redirect('laporan_penjualan.php');
}

$transaksi = mysqli_fetch_assoc($result_transaksi);

// Query detail transaksi
$query_detail = "SELECT 
                    dt.*,
                    p.nama_produk,
                    p.kode_barang
                 FROM detail_transaksi dt
                 JOIN produk p ON dt.produk_id = p.id
                 WHERE dt.transaksi_id = $id";
$result_detail = mysqli_query($conn, $query_detail);

// Hitung data pembayaran
$diskon = $transaksi['diskon'] ?? 0;
$uang_bayar = $transaksi['uang_bayar'] ?? 0;
$kembalian = $transaksi['kembalian'] ?? 0;
$total_setelah_diskon = $transaksi['total_bayar'] - $diskon;

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
.invoice-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.invoice-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 12px 12px 0 0;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: bold;
    display: inline-block;
}

.status-lunas {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.status-belum {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.detail-row {
    padding: 15px 0;
    border-bottom: 1px solid #e9ecef;
}

.detail-row:last-child {
    border-bottom: none;
}

.total-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}

.payment-highlight {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 4px solid #28a745;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}

.payment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #dee2e6;
}

.payment-item:last-child {
    border-bottom: none;
}

.payment-label {
    color: #6c757d;
    font-size: 14px;
}

.payment-value {
    font-weight: bold;
    font-size: 18px;
}

.btn-action {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.discount-badge {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card invoice-card">
            <!-- Invoice Header -->
            <div class="invoice-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="mb-1">
                            <i class="fas fa-file-invoice me-2"></i>
                            Detail Transaksi
                        </h3>
                        <p class="mb-0 opacity-8">Nomor: <?= htmlspecialchars($transaksi['nomor_bukti']); ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="status-badge <?= $transaksi['status_bayar'] == 'LUNAS' ? 'status-lunas' : 'status-belum'; ?>">
                            <i class="fas fa-<?= $transaksi['status_bayar'] == 'LUNAS' ? 'check-circle' : 'clock'; ?> me-2"></i>
                            <?= $transaksi['status_bayar']; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Body -->
            <div class="card-body p-4">
                <!-- Informasi Transaksi -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="mb-3">Informasi Transaksi</h6>
                        <div class="detail-row">
                            <small class="text-muted">Nomor Bukti</small>
                            <p class="mb-0 font-weight-bold"><?= htmlspecialchars($transaksi['nomor_bukti']); ?></p>
                        </div>
                        <div class="detail-row">
                            <small class="text-muted">Tanggal Transaksi</small>
                            <p class="mb-0">
                                <i class="far fa-calendar me-2"></i>
                                <?= $transaksi['tanggal'] != '0000-00-00' ? date('d F Y', strtotime($transaksi['tanggal'])) : '-'; ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Ringkasan Pembayaran</h6>
                        <div class="detail-row">
                            <small class="text-muted">Subtotal</small>
                            <h5 class="mb-0">Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.'); ?></h5>
                        </div>
                        <?php if ($diskon > 0): ?>
                        <div class="detail-row">
                            <small class="text-muted">Diskon</small>
                            <h5 class="mb-0 text-danger">
                                <span class="discount-badge">
                                    <i class="fas fa-tag me-1"></i>
                                    - Rp <?= number_format($diskon, 0, ',', '.'); ?>
                                </span>
                            </h5>
                        </div>
                        <?php endif; ?>
                        <div class="detail-row">
                            <small class="text-muted">Total Pembayaran</small>
                            <h4 class="mb-0 text-primary">Rp <?= number_format($total_setelah_diskon, 0, ',', '.'); ?></h4>
                        </div>
                        <div class="detail-row">
                            <small class="text-muted">Status Pembayaran</small>
                            <p class="mb-0">
                                <span class="badge <?= $transaksi['status_bayar'] == 'LUNAS' ? 'bg-success' : 'bg-warning'; ?>">
                                    <?= $transaksi['status_bayar']; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Detail Items -->
                <h6 class="mb-3">Detail Produk</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-items-center">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder">No</th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder">Kode</th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder">Nama Produk</th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder text-center">Qty</th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder text-end">Harga</th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $total = 0;
                            while($detail = mysqli_fetch_assoc($result_detail)): 
                                $total += $detail['subtotal'];
                            ?>
                            <tr>
                                <td class="text-sm"><?= $no++; ?></td>
                                <td class="text-sm"><?= htmlspecialchars($detail['kode_barang']); ?></td>
                                <td class="text-sm font-weight-bold"><?= htmlspecialchars($detail['nama_produk']); ?></td>
                                <td class="text-sm text-center"><?= $detail['qty']; ?></td>
                                <td class="text-sm text-end">Rp <?= number_format($detail['harga'], 0, ',', '.'); ?></td>
                                <td class="text-sm text-end font-weight-bold">Rp <?= number_format($detail['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Total Section -->
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <div class="total-section">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal:</span>
                                <span class="font-weight-bold">Rp <?= number_format($total, 0, ',', '.'); ?></span>
                            </div>
                            <?php if ($diskon > 0): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Diskon:</span>
                                <span class="text-danger font-weight-bold">- Rp <?= number_format($diskon, 0, ',', '.'); ?></span>
                            </div>
                            <?php endif; ?>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <h5 class="mb-0">Total:</h5>
                                <h4 class="mb-0 text-primary">Rp <?= number_format($total_setelah_diskon, 0, ',', '.'); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="row justify-content-end mt-3">
                    <div class="col-md-5">
                        <div class="payment-highlight">
                            <h6 class="mb-3">
                                <i class="fas fa-credit-card me-2"></i>
                                Detail Pembayaran
                            </h6>
                            <div class="payment-item">
                                <span class="payment-label">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    Uang Dibayar
                                </span>
                                <span class="payment-value text-primary">
                                    Rp <?= number_format($uang_bayar, 0, ',', '.'); ?>
                                </span>
                            </div>
                            <div class="payment-item">
                                <span class="payment-label">
                                    <i class="fas fa-calculator me-2"></i>
                                    Total Bayar
                                </span>
                                <span class="payment-value text-dark">
                                    Rp <?= number_format($total_setelah_diskon, 0, ',', '.'); ?>
                                </span>
                            </div>
                            <div class="payment-item" style="background: rgba(40, 167, 69, 0.1); margin: 10px -20px -20px; padding: 15px 20px; border-radius: 0 0 8px 8px;">
                                <span class="payment-label">
                                    <i class="fas fa-hand-holding-usd me-2"></i>
                                    <strong>Kembalian</strong>
                                </span>
                                <span class="payment-value text-success" style="font-size: 22px;">
                                    Rp <?= number_format($kembalian, 0, ',', '.'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        <hr>
                        <div class="d-flex gap-2 justify-content-between flex-wrap">
                            <a href="laporan_penjualan.php" class="btn btn-secondary btn-action">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <div class="d-flex gap-2">
                                <a href="../transaksi/invoice.php?id=<?= $id; ?>" target="_blank" class="btn btn-success btn-action">
                                    <i class="fas fa-print me-2"></i>Cetak Struk
                                </a>
                                <button onclick="window.print()" class="btn btn-info btn-action">
                                    <i class="fas fa-file-pdf me-2"></i>Cetak PDF
                                </button>
                                <?php if ($transaksi['status_bayar'] == 'BELUM LUNAS'): ?>
                                <button onclick="updateStatus(<?= $id; ?>)" class="btn btn-primary btn-action">
                                    <i class="fas fa-check me-2"></i>Tandai Lunas
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus(id) {
    if (confirm('Tandai transaksi ini sebagai LUNAS?')) {
        window.location.href = 'update_status.php?id=' + id + '&status=LUNAS';
    }
}

// Print styles
const style = document.createElement('style');
style.textContent = `
    @media print {
        .sidebar, .topnav, .btn-action, hr { display: none !important; }
        .invoice-card { box-shadow: none; }
    }
`;
document.head.appendChild(style);
</script>

<?php include '../views/'.$THEME.'/footer.php'; ?>