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

// Get parameters
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

if ($id == 0 || empty($status)) {
    header("Location: laporan_penjualan.php");
    exit;
}

// Validasi status
if (!in_array($status, ['LUNAS', 'BELUM LUNAS'])) {
    header("Location: laporan_penjualan.php");
    exit;
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

// Update status
$query = "UPDATE transaksi SET status_bayar = '" . mysqli_real_escape_string($conn, $status) . "' WHERE id = $id";
$result = mysqli_query($conn, $query);

if ($result) {
    $_SESSION['message'] = "Status pembayaran berhasil diubah menjadi " . $status;
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Gagal mengubah status pembayaran";
    $_SESSION['message_type'] = "error";
}

// Redirect back
header("Location: detail_transaksi.php?id=" . $id);
exit;
?>
