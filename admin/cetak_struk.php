<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);
session_start();
require_once '../lib/auth.php';
requireAuth();

// Get transaction ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id == 0) {
    die("ID Transaksi tidak valid");
}

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
    die("Transaksi tidak ditemukan");
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

// Jika uang_bayar 0, berarti belum ada data pembayaran, gunakan total sebagai default
if ($uang_bayar == 0) {
    $uang_bayar = $total_setelah_diskon;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Struk #<?= htmlspecialchars($transaksi['nomor_bukti']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 10px;
            max-width: 300px;
            margin: 0 auto;
        }
        
        .struk {
            background: white;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .header h2 {
            font-size: 18px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header p {
            font-size: 11px;
            margin: 2px 0;
        }
        
        .info {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
        }
        
        .items {
            margin-bottom: 10px;
        }
        
        .item {
            margin-bottom: 8px;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }
        
        .totals {
            border-top: 2px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 12px;
        }
        
        .total-row.discount {
            font-weight: bold;
        }
        
        .total-row.grand {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 8px 0;
            margin-top: 8px;
        }
        
        .payment-section {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px dashed #000;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 12px;
        }
        
        .payment-row.change {
            font-size: 13px;
            font-weight: bold;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #000;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px dashed #000;
            font-size: 11px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border: 1px solid #000;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
            margin-top: 5px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            @page {
                margin: 0;
                size: 80mm auto;
            }
        }
    </style>
</head>
<body>
    <div class="struk">
        <!-- Header -->
        <div class="header">
            <h2>FASH-CASHIER</h2>
            <p>Jl. umc No. 123</p>
            <p>Telp: (021) 1234-5678</p>
            <p>Email: info@fash-cashier.com</p>
        </div>
        
        <!-- Info Transaksi -->
        <div class="info">
            <div class="info-row">
                <span>No. Bukti:</span>
                <strong><?= htmlspecialchars($transaksi['nomor_bukti']); ?></strong>
            </div>
            <div class="info-row">
                <span>Tanggal:</span>
                <span><?= $transaksi['tanggal'] != '0000-00-00' ? date('d/m/Y H:i', strtotime($transaksi['tanggal'])) : '-'; ?></span>
            </div>
            <div class="info-row">
                <span>Kasir:</span>
                <span><?= ucfirst($_SESSION['username']); ?></span>
            </div>
            <div class="info-row">
                <span>Status:</span>
                <strong><?= $transaksi['status_bayar']; ?></strong>
            </div>
        </div>
        
        <!-- Items -->
        <div class="items">
            <?php 
            $total = 0;
            while($detail = mysqli_fetch_assoc($result_detail)): 
                $total += $detail['subtotal'];
            ?>
            <div class="item">
                <div class="item-name"><?= htmlspecialchars($detail['nama_produk']); ?></div>
                <div class="item-detail">
                    <span><?= $detail['qty']; ?> x Rp <?= number_format($detail['harga'], 0, ',', '.'); ?></span>
                    <strong>Rp <?= number_format($detail['subtotal'], 0, ',', '.'); ?></strong>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp <?= number_format($total, 0, ',', '.'); ?></span>
            </div>
            
            <?php if ($diskon > 0): ?>
            <div class="total-row discount">
                <span>Diskon:</span>
                <span>- Rp <?= number_format($diskon, 0, ',', '.'); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="total-row grand">
                <span>TOTAL:</span>
                <span>Rp <?= number_format($total_setelah_diskon, 0, ',', '.'); ?></span>
            </div>
        </div>
        
        <!-- Payment Section -->
        <?php if ($transaksi['status_bayar'] == 'LUNAS'): ?>
        <div class="payment-section">
            <div class="payment-row">
                <span>Bayar:</span>
                <span>Rp <?= number_format($uang_bayar, 0, ',', '.'); ?></span>
            </div>
            <div class="payment-row change">
                <span>Kembali:</span>
                <span>Rp <?= number_format($kembalian, 0, ',', '.'); ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="footer">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
            
            <?php if ($diskon > 0): ?>
            <p style="margin-top: 10px; padding: 5px; border: 1px dashed #000;">
                <strong>Anda hemat: Rp <?= number_format($diskon, 0, ',', '.'); ?></strong>
            </p>
            <?php endif; ?>
            
            <p style="margin-top: 8px;">www.fash-cashier.com</p>
            <p style="margin-top: 15px; font-size: 10px;">
                Dicetak: <?= date('d/m/Y H:i:s'); ?>
            </p>
        </div>
    </div>
    
    <!-- Print Button -->
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; margin-right: 10px;">
            <i class="fas fa-print"></i> Cetak Struk
        </button>
        <button onclick="window.close()" style="padding: 10px 30px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
            Tutup
        </button>
    </div>
    
    <script>
        // Auto print (optional - uncomment if needed)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>