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

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$stmt = mysqli_prepare($connection, "SELECT id, kode_barang, nama_produk, harga, foto FROM produk WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($produk) {
    echo json_encode([
        'success' => true,
        'id' => $produk['id'],
        'kode_barang' => $produk['kode_barang'],
        'nama_produk' => $produk['nama_produk'],
        'harga' => $produk['harga'],
        'foto' => $produk['foto']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
}