<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';
requireAuth();
requireModuleAccess('transaksi');
require_once '../config/database.php';
$id = (int) ($_GET['id'] ?? 0);
$master_id = (int) ($_GET['master_id'] ?? 0);
if ($id) {
    $stmt = mysqli_prepare($connection, "DELETE FROM `detail_transaksi` WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
$success = updateMasterTotalFromDetail(
    $connection,
    'detail_transaksi',
    'subtotal',
    'transaksi_id',
    'transaksi',
    'id',
    'total_bayar',
    $master_id
);
redirect('transaksi/detail.php?id=' . $master_id);
?>
