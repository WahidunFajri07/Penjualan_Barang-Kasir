<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('produk');

require_once '../config/database.php';

// Sesuai PDF: gunakan konstanta UPLOAD_DIR_PRODUK
define('UPLOAD_DIR_PRODUK', '/fash-cashier/uploads/produk/');

$result = mysqli_query($connection, "SELECT * FROM produk ORDER BY id DESC");
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Daftar Produk</h2>
    <a href="add.php" class="btn btn-primary">+ Tambah Produk</a>
</div>

<?php if (mysqli_num_rows($result) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Foto</th>
                    <th>Kode Barang</th>
                    <th>Nama Produk</th>
                    <th>Kategori ID</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)): 
                    $foto = $row['foto'];
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <?php if (!empty($foto) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/fash-cashier/uploads/produk/' . $foto)): ?>
                                <img src="<?= UPLOAD_DIR_PRODUK . htmlspecialchars($foto) ?>"
                                     width="60"
                                     style="border-radius:6px; border:1px solid #ddd;"
                                     onerror="this.style.display='none'">
                            <?php else: ?>
                                <em>-</em>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['kode_barang']) ?></td>
                        <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                        <td><?= htmlspecialchars($row['kategori_id']) ?></td>
                        <td>Rp <?= number_format((int)$row['harga'], 0, ',', '.') ?></td>
                        <td>
                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus produk ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">Belum ada data produk.</div>
<?php endif; ?>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>