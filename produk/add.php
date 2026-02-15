<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('produk');

require_once '../config/database.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_barang = trim($_POST['kode_barang'] ?? '');
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $kategori_id = trim($_POST['kategori_id'] ?? '');
    $harga = trim($_POST['harga'] ?? '');

    if (empty($kode_barang) || empty($nama_produk) || empty($kategori_id) || empty($harga)) {
        $error = "Semua field wajib diisi.";
    }

    $foto_filename = '';
    if (!$error) {
        // Wajib upload foto
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
            $error = "Foto wajib diupload.";
        } else {
            $foto_filename = handle_file_upload($_FILES['foto']);
            if ($foto_filename === false) {
                $error = "Upload gagal! Format: JPG/PNG/GIF/WebP, maks 2MB.";
            }
        }
    }

    if (!$error) {
        $stmt = mysqli_prepare($connection, "INSERT INTO produk (kode_barang, nama_produk, kategori_id, harga, foto) VALUES (?, ?, ?, ?, ?)");
        $kategori_id = (int)$kategori_id;
        $harga = (int)$harga;
        mysqli_stmt_bind_param($stmt, "sssis", $kode_barang, $nama_produk, $kategori_id, $harga, $foto_filename);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Produk berhasil ditambahkan.";
            echo "<script>setTimeout(() => window.location.href='index.php', 2000);</script>";
        } else {
            $error = "Gagal menyimpan: " . mysqli_error($connection);
            // Hapus file jika gagal
            if ($foto_filename && file_exists(__DIR__ . '/../uploads/produk/' . $foto_filename)) {
                unlink(__DIR__ . '/../uploads/produk/' . $foto_filename);
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<h2>Tambah Produk</h2>

<?php if ($error): ?>
    <?= showAlert($error, 'danger') ?>
<?php endif; ?>

<?php if ($success): ?>
    <?= showAlert($success, 'success') ?>
    <a href="index.php" class="btn btn-secondary">Kembali ke Daftar</a>
<?php else: ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Kode Barang*</label>
            <input type="text" name="kode_barang" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Produk*</label>
            <input type="text" name="nama_produk" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Kategori ID*</label>
            <input type="number" name="kategori_id" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Harga*</label>
            <input type="number" name="harga" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Foto*</label>
            <input type="file" name="foto" id="fotoInput" class="form-control" accept="image/*" required onchange="previewImage(event)">
            <div class="mt-2">
                <img id="imagePreview" src="#" alt="Preview" style="max-width:300px; max-height:200px; display:none; border:1px solid #ddd; border-radius:4px; padding:5px;">
            </div>
            <div class="form-text">
                <small>Format: JPG, PNG, GIF, WebP. Maksimal 2MB.</small>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>

    <script>
    function previewImage(event) {
        const reader = new FileReader();
        const preview = document.getElementById('imagePreview');
        reader.onload = () => {
            preview.src = reader.result;
            preview.style.display = 'block';
        };
        if (event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
    }
    </script>
<?php endif; ?>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>