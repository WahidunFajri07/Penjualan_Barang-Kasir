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
$stmt = mysqli_prepare($connection, "SELECT * FROM `transaksi` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$transaksi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$transaksi) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) die('Invalid CSRF token.');
    $nomor_bukti_post = trim($_POST['nomor_bukti'] ?? '');
    $tanggal_post = trim($_POST['tanggal'] ?? '');
    $total_bayar_post = trim($_POST['total_bayar'] ?? '');
    $status_bayar_post = trim($_POST['status_bayar'] ?? '');
    if (empty($nomor_bukti_post) || empty($tanggal_post) || empty($total_bayar_post)) {
        $error = "Semua field wajib diisi.";
    }
    if (!$error) {
        $stmt = mysqli_prepare($connection, "UPDATE `transaksi` SET `nomor_bukti` = ?, `tanggal` = ?, `total_bayar` = ?, `status_bayar` = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssisi", $nomor_bukti_post, $tanggal_post, $total_bayar_post, $status_bayar_post, $id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($connection, "SELECT * FROM `transaksi` WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $transaksi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        } else {
            $error = "Gagal memperbarui transaksi.";
        }
        mysqli_stmt_close($stmt);
    }
}
$csrfToken = generateCSRFToken();
?>
<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>
<h2>Edit Transaksi</h2>
<?php if ($error): ?>
<?= showAlert($error, 'danger') ?>
<?php endif; ?>
<form method="POST">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <div class="mb-3">
<label class="form-label">Nomor Bukti*</label>
<input type="text" name="nomor_bukti" class="form-control" value="<?= htmlspecialchars($transaksi['nomor_bukti']) ?>" required>
</div>
                <div class="mb-3">
<label class="form-label">Tanggal*</label>
<input type="date" name="tanggal" class="form-control" value="<?= $transaksi['tanggal'] ?>" required>
</div>
                <div class="mb-3">
<label class="form-label">Total Bayar*</label>
<input type="number" name="total_bayar" class="form-control" value="<?= $transaksi['total_bayar'] ?>" required>
</div>
                <div class="mb-3">
<label class="form-label">Status Bayar</label>
<select name="status_bayar" class="form-select">

</select>
</div>
<button type="submit" class="btn btn-primary">Perbarui</button>
<a href="index.php" class="btn btn-secondary">Batal</a>
</form>
<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>
