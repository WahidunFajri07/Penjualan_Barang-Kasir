<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('detail_transaksi');

require_once '../config/database.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

$stmt = mysqli_prepare($connection, "SELECT id, transaksi_id, produk_id, qty, harga, subtotal FROM `detail_transaksi` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$detail_transaksi = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$detail_transaksi) {
    redirect('index.php');
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaksi_id_post = trim($_POST['transaksi_id'] ?? '');
    $produk_id_post = trim($_POST['produk_id'] ?? '');
    $qty_post = trim($_POST['qty'] ?? '');
    $harga_post = trim($_POST['harga'] ?? '');
    $subtotal_post = trim($_POST['subtotal'] ?? '');
    if (empty($transaksi_id_post) || empty($produk_id_post) || empty($qty_post) || empty($harga_post) || empty($subtotal_post)) {
        $error = "Transaksi Id dan Produk Id dan Qty dan Harga dan Subtotal wajib diisi.";
    }
    if (!$error) {
        $stmt = mysqli_prepare($connection, "UPDATE `detail_transaksi` SET `transaksi_id` = ?, `produk_id` = ?, `qty` = ?, `harga` = ?, `subtotal` = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "iiiiii", $transaksi_id_post, $produk_id_post, $qty_post, $harga_post, $subtotal_post, $id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "detail_transaksi berhasil diperbarui.";
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($connection, "SELECT id, transaksi_id, produk_id, qty, harga, subtotal FROM `detail_transaksi` WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $detail_transaksi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 2000);
            </script>";
        } else {
            $error = "Gagal memperbarui: " . mysqli_error($connection);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

            <h2>Edit detail_transaksi</h2>
            <?php if ($error): ?>
                <?= showAlert($error, 'danger') ?>
            <?php endif; ?>
            <?php if ($success): ?>
                <?= showAlert($success, 'success') ?>
            <?php endif; ?>
            <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Transaksi Id*</label>
                        <input type="number" name="transaksi_id" class="form-control" value="<?= $detail_transaksi['transaksi_id'] ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Produk Id*</label>
                        <input type="number" name="produk_id" class="form-control" value="<?= $detail_transaksi['produk_id'] ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Qty*</label>
                        <input type="number" name="qty" class="form-control" value="<?= $detail_transaksi['qty'] ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga*</label>
                        <input type="number" name="harga" class="form-control" value="<?= $detail_transaksi['harga'] ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subtotal*</label>
                        <input type="number" name="subtotal" class="form-control" value="<?= $detail_transaksi['subtotal'] ?>">
                    </div>
                <button type="submit" class="btn btn-primary">Perbarui</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>


<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>
