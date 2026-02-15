<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);
session_start();
require_once '../lib/auth.php';
requireAuth();

if (getUserRole() !== 'admin') {
    die("Akses ditolak");
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

// Filter parameters
$filter_tanggal_dari = isset($_GET['tanggal_dari']) ? $_GET['tanggal_dari'] : date('Y-m-01');
$filter_tanggal_sampai = isset($_GET['tanggal_sampai']) ? $_GET['tanggal_sampai'] : date('Y-m-d');
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'SEMUA';
$export_type = isset($_GET['export']) ? $_GET['export'] : 'excel';

// Build query
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

// Query data
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

// Query ringkasan
$query_ringkasan = "SELECT 
                        COUNT(*) as total_transaksi,
                        COALESCE(SUM(CASE WHEN status_bayar = 'LUNAS' THEN total_bayar ELSE 0 END), 0) as total_penjualan,
                        COALESCE(SUM(CASE WHEN status_bayar = 'LUNAS' THEN 1 ELSE 0 END), 0) as transaksi_lunas,
                        COALESCE(SUM(CASE WHEN status_bayar = 'BELUM LUNAS' THEN 1 ELSE 0 END), 0) as transaksi_belum_lunas
                    FROM transaksi t
                    WHERE $where_sql";

$result_ringkasan = mysqli_query($conn, $query_ringkasan);
$ringkasan = mysqli_fetch_assoc($result_ringkasan);

if ($export_type == 'excel') {
    // Export to Excel (CSV)
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=laporan_penjualan_' . date('Ymd_His') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // BOM untuk UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header
    fputcsv($output, ['LAPORAN PENJUALAN']);
    fputcsv($output, ['Periode: ' . date('d/m/Y', strtotime($filter_tanggal_dari)) . ' - ' . date('d/m/Y', strtotime($filter_tanggal_sampai))]);
    fputcsv($output, ['Dicetak: ' . date('d/m/Y H:i:s')]);
    fputcsv($output, []);
    
    // Ringkasan
    fputcsv($output, ['RINGKASAN']);
    fputcsv($output, ['Total Transaksi', $ringkasan['total_transaksi']]);
    fputcsv($output, ['Total Penjualan', 'Rp ' . number_format($ringkasan['total_penjualan'], 0, ',', '.')]);
    fputcsv($output, ['Transaksi Lunas', $ringkasan['transaksi_lunas']]);
    fputcsv($output, ['Transaksi Belum Lunas', $ringkasan['transaksi_belum_lunas']]);
    fputcsv($output, []);
    
    // Detail Transaksi
    fputcsv($output, ['DETAIL TRANSAKSI']);
    fputcsv($output, ['No', 'Nomor Bukti', 'Tanggal', 'Jumlah Item', 'Total Bayar', 'Status']);
    
    $no = 1;
    while($row = mysqli_fetch_assoc($result_transaksi)) {
        fputcsv($output, [
            $no++,
            $row['nomor_bukti'],
            date('d/m/Y', strtotime($row['tanggal'])),
            $row['jumlah_item'],
            'Rp ' . number_format($row['total_bayar'], 0, ',', '.'),
            $row['status_bayar']
        ]);
    }
    
    fclose($output);
    exit;
    
} elseif ($export_type == 'pdf') {
    // Export to PDF (HTML to PDF)
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Laporan Penjualan</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                margin: 20px;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 3px solid #333;
                padding-bottom: 15px;
            }
            .header h2 {
                margin: 5px 0;
                color: #333;
            }
            .info {
                margin-bottom: 20px;
            }
            .summary {
                background: #f5f5f5;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
            }
            .summary table {
                width: 100%;
            }
            .summary td {
                padding: 5px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            th {
                background: #667eea;
                color: white;
                padding: 10px;
                text-align: left;
                font-weight: bold;
            }
            td {
                padding: 8px;
                border-bottom: 1px solid #ddd;
            }
            tr:hover {
                background: #f9f9f9;
            }
            .text-right {
                text-align: right;
            }
            .badge {
                padding: 4px 8px;
                border-radius: 3px;
                font-size: 10px;
                font-weight: bold;
            }
            .badge-lunas {
                background: #38ef7d;
                color: white;
            }
            .badge-belum {
                background: #f5576c;
                color: white;
            }
            .footer {
                margin-top: 30px;
                text-align: center;
                font-size: 10px;
                color: #666;
            }
            @media print {
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h2>LAPORAN PENJUALAN</h2>
            <p>Periode: <?= date('d/m/Y', strtotime($filter_tanggal_dari)); ?> - <?= date('d/m/Y', strtotime($filter_tanggal_sampai)); ?></p>
        </div>
        
        <div class="info">
            <strong>Dicetak:</strong> <?= date('d/m/Y H:i:s'); ?><br>
            <strong>Dicetak oleh:</strong> <?= ucfirst($_SESSION['username']); ?>
        </div>
        
        <div class="summary">
            <h3 style="margin-top: 0;">Ringkasan</h3>
            <table>
                <tr>
                    <td width="50%"><strong>Total Transaksi:</strong></td>
                    <td><?= number_format($ringkasan['total_transaksi'], 0, ',', '.'); ?> transaksi</td>
                </tr>
                <tr>
                    <td><strong>Total Penjualan:</strong></td>
                    <td><strong>Rp <?= number_format($ringkasan['total_penjualan'], 0, ',', '.'); ?></strong></td>
                </tr>
                <tr>
                    <td><strong>Transaksi Lunas:</strong></td>
                    <td><?= number_format($ringkasan['transaksi_lunas'], 0, ',', '.'); ?> transaksi</td>
                </tr>
                <tr>
                    <td><strong>Transaksi Belum Lunas:</strong></td>
                    <td><?= number_format($ringkasan['transaksi_belum_lunas'], 0, ',', '.'); ?> transaksi</td>
                </tr>
            </table>
        </div>
        
        <h3>Detail Transaksi</h3>
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Nomor Bukti</th>
                    <th width="15%">Tanggal</th>
                    <th width="10%">Item</th>
                    <th width="25%" class="text-right">Total Bayar</th>
                    <th width="15%">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                mysqli_data_seek($result_transaksi, 0); // Reset pointer
                while($row = mysqli_fetch_assoc($result_transaksi)): 
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['nomor_bukti']); ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                    <td><?= $row['jumlah_item']; ?></td>
                    <td class="text-right">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                    <td>
                        <?php if ($row['status_bayar'] == 'LUNAS'): ?>
                            <span class="badge badge-lunas">LUNAS</span>
                        <?php else: ?>
                            <span class="badge badge-belum">BELUM LUNAS</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="footer">
            <p>Dokumen ini dicetak secara otomatis dari Sistem Kasir</p>
        </div>
        
        <div class="no-print" style="text-align: center; margin-top: 30px;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
                <i class="fas fa-print"></i> Cetak PDF
            </button>
            <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; margin-left: 10px;">
                Tutup
            </button>
        </div>
        
        <script>
            // Auto print on load (optional)
            // window.onload = function() { window.print(); }
        </script>
    </body>
    </html>
    <?php
    exit;
}
?>
