<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';
requireAuth();
requireModuleAccess('transaksi');
require_once '../config/database.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

// Get transaksi data
$stmt = mysqli_prepare($connection, "SELECT * FROM `transaksi` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$transaksi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$transaksi) redirect('index.php');

// Get detail transaksi
$stmt = mysqli_prepare($connection, 
    "SELECT dt.*, p.kode_barang, p.nama_produk 
     FROM detail_transaksi dt 
     LEFT JOIN produk p ON dt.produk_id = p.id 
     WHERE dt.transaksi_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$details = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$items = [];
while ($row = mysqli_fetch_assoc($details)) {
    $items[] = $row;
}

// Get kasir name
$kasir_name = $_SESSION['username'] ?? 'Admin';

// Hitung data pembayaran
$diskon = $transaksi['diskon'] ?? 0;
$uang_bayar = $transaksi['uang_bayar'] ?? 0;
$kembalian = $transaksi['kembalian'] ?? 0;
$total_setelah_diskon = $transaksi['total_bayar'] - $diskon;

// Jika uang_bayar 0, berarti belum ada data pembayaran, gunakan total sebagai default
if ($uang_bayar == 0) {
    $uang_bayar = $total_setelah_diskon;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $transaksi['nomor_bukti'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .receipt-container {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 10px !important;
            }
            
            @page {
                margin: 5mm;
                size: 80mm auto;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f5f5f5;
            font-family: 'Courier New', Courier, monospace;
            padding: 20px;
        }
        
        .receipt-container {
            max-width: 350px;
            margin: 0 auto;
            background: white;
            padding: 30px 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border: 2px solid #ddd;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        
        .company-info {
            font-size: 11px;
            line-height: 1.6;
            color: #333;
        }
        
        /* Divider */
        .divider {
            border-top: 1px dashed #333;
            margin: 10px 0;
        }
        
        .divider-solid {
            border-top: 2px solid #333;
            margin: 10px 0;
        }
        
        .divider-double {
            border-top: 3px double #333;
            margin: 10px 0;
        }
        
        /* Info Section */
        .info-section {
            font-size: 11px;
            line-height: 1.8;
            margin-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        .info-label {
            font-weight: bold;
        }
        
        /* Items */
        .items-section {
            margin: 10px 0;
        }
        
        .item {
            margin-bottom: 8px;
            font-size: 11px;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }
        
        /* Summary */
        .summary-section {
            margin-top: 10px;
            font-size: 11px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .summary-row.discount {
            color: #dc3545;
        }
        
        .summary-row.total {
            font-size: 13px;
            font-weight: bold;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 2px solid #333;
        }
        
        /* Payment */
        .payment-section {
            margin-top: 10px;
            font-size: 11px;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .payment-row.change {
            font-size: 12px;
            font-weight: bold;
            margin-top: 3px;
        }
        
        /* Footer */
        .footer {
            margin-top: 15px;
            font-size: 10px;
            text-align: center;
            line-height: 1.6;
        }
        
        .footer-note {
            margin: 10px 0;
            padding: 8px;
            border: 1px dashed #333;
            font-size: 9px;
        }
        
        .print-time {
            margin-top: 10px;
            font-size: 9px;
            color: #666;
        }
        
        /* Action Buttons */
        .action-buttons {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }
        
        .btn-action {
            padding: 15px 25px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.3);
        }
        
        .btn-print {
            background: #007bff;
            color: white;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .receipt-container {
                max-width: 100%;
                padding: 20px 15px;
            }
            
            .action-buttons {
                position: relative;
                bottom: auto;
                right: auto;
                margin-top: 20px;
                justify-content: center;
            }
            
            .btn-action {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">Fash-Cashier</div>
            <div class="company-info">
                Jl. Contoh No. 123<br>
                Telp: (021) 1234-5678<br>
                Email: info@Fash-Cashier.com
            </div>
        </div>
        
        <div class="divider-solid"></div>
        
        <!-- Transaction Info -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">No. Bukti:</span>
                <span><?= htmlspecialchars($transaksi['nomor_bukti']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal:</span>
                <span><?= date('d/m/Y H:i', strtotime($transaksi['tanggal'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Kasir:</span>
                <span><?= htmlspecialchars($kasir_name) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span><?= htmlspecialchars($transaksi['status_bayar']) ?></span>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Items -->
        <div class="items-section">
            <?php 
            $no = 1;
            $total_qty = 0;
            foreach ($items as $item): 
                $total_qty += $item['qty'];
            ?>
                <div class="item">
                    <div class="item-name"><?= htmlspecialchars($item['nama_produk']) ?></div>
                    <div class="item-detail">
                        <span><?= $item['qty'] ?> x Rp <?= number_format($item['harga'], 0, ',', '.') ?></span>
                        <span>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="divider-solid"></div>
        
        <!-- Summary -->
        <div class="summary-section">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.') ?></span>
            </div>
            
            <?php if ($diskon > 0): ?>
            <div class="summary-row discount">
                <span>Diskon:</span>
                <span>- Rp <?= number_format($diskon, 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            
            <div class="summary-row total">
                <span>TOTAL:</span>
                <span>Rp <?= number_format($total_setelah_diskon, 0, ',', '.') ?></span>
            </div>
        </div>
        
        <div class="divider-double"></div>
        
        <!-- Payment Details -->
        <div class="payment-section">
            <div class="payment-row">
                <span>Bayar:</span>
                <span>Rp <?= number_format($uang_bayar, 0, ',', '.') ?></span>
            </div>
            <div class="payment-row change">
                <span>Kembali:</span>
                <span>Rp <?= number_format($kembalian, 0, ',', '.') ?></span>
            </div>
        </div>
        
        <div class="divider-solid"></div>
        
        <!-- Footer Note -->
        <div class="footer">
            <div class="footer-note">
                Terima kasih atas kunjungan Anda<br>
                Barang yang sudah dibeli tidak dapat dikembalikan
            </div>
            
            <div class="print-time">
                Dicetak: <?= date('d/m/Y H:i:s') ?>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-buttons no-print">
        <button onclick="window.print()" class="btn-action btn-print">
            <i class="fas fa-print"></i> Cetak Struk
        </button>
        <a href="detail.php?id=<?= $id ?>" class="btn-action btn-back">
            <i class="fas fa-arrow-left"></i> Tutup
        </a>
    </div>
    
    <script>
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+P or Cmd+P untuk print
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            
            // ESC untuk kembali
            if (e.key === 'Escape') {
                window.location.href = 'detail.php?id=<?= $id ?>';
            }
        });
    </script>
</body>
</html>