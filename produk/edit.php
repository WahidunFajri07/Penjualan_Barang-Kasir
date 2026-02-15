<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('produk');

require_once '../config/database.php';

define('UPLOAD_DIR_PRODUK', '../uploads/produk/');

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

$stmt = mysqli_prepare($connection, "SELECT id, kode_barang, nama_produk, kategori_id, harga, foto FROM produk WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$produk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$produk) redirect('index.php');

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_barang_post = trim($_POST['kode_barang'] ?? '');
    $nama_produk_post = trim($_POST['nama_produk'] ?? '');
    $kategori_id_post = trim($_POST['kategori_id'] ?? '');
    $harga_post = trim($_POST['harga'] ?? '');

    if (empty($kode_barang_post) || empty($nama_produk_post) || empty($kategori_id_post) || empty($harga_post)) {
        $error = "Semua field wajib diisi.";
    }

    // Ambil nilai foto lama dari database (bisa NULL, '', atau nama file)
    $foto_baru = $produk['foto'];

    if (!$error) {
        // Cek apakah user mengupload file baru
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_result = handle_file_upload($_FILES['foto']);
            // Fungsi sekarang hanya mengembalikan string ('' atau nama file)
            if ($upload_result === '') {
                $error = "Upload gagal! Format harus JPG, PNG, GIF, atau WebP. Maksimal ukuran 2MB.";
            } else {
                // Hapus foto lama jika ada
                if (!empty($foto_baru) && file_exists(UPLOAD_DIR_PRODUK . $foto_baru)) {
                    unlink(UPLOAD_DIR_PRODUK . $foto_baru);
                }
                $foto_baru = $upload_result; // Simpan nama file baru
            }
        }
        // Jika tidak upload, biarkan $foto_baru = nilai lama (bisa NULL, '', atau nama file)
    }

    if (!$error) {
        $kategori_id_int = (int)$kategori_id_post;
        $harga_int = (int)$harga_post;

        // Pastikan $foto_baru adalah string (jangan biarkan NULL masuk ke bind_param)
        $foto_simpan = is_null($foto_baru) ? '' : (string)$foto_baru;

        $stmt = mysqli_prepare($connection, "UPDATE produk SET kode_barang = ?, nama_produk = ?, kategori_id = ?, harga = ?, foto = ? WHERE id = ?");
        // Urutan tipe: s, s, i, i, s, i
        mysqli_stmt_bind_param($stmt, "sssiss", 
            $kode_barang_post,
            $nama_produk_post,
            $kategori_id_int,
            $harga_int,
            $foto_simpan,  // Selalu string
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            $success = "Produk berhasil diperbarui.";
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

<h2>Edit Produk</h2>

<?php if ($error): ?>
    <?= showAlert($error, 'danger') ?>
<?php endif; ?>

<?php if ($success): ?>
    <?= showAlert($success, 'success') ?>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Kode Barang*</label>
        <input type="text" name="kode_barang" class="form-control" value="<?= htmlspecialchars($produk['kode_barang']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Nama Produk*</label>
        <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Kategori ID*</label>
        <input type="number" name="kategori_id" class="form-control" value="<?= (int)$produk['kategori_id'] ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Harga*</label>
        <input type="number" name="harga" class="form-control" value="<?= (int)$produk['harga'] ?>" required>
    </div>

    <!-- FOTO -->
    <div class="mb-3">
        <label class="form-label">Foto Produk</label>
        <input type="file" name="foto" id="fotoInput" class="form-control" accept="image/*" onchange="previewImage(event)">

        <!-- Tampilkan foto saat ini jika ada -->
        <?php if (!empty($produk['foto']) && file_exists(UPLOAD_DIR_PRODUK . $produk['foto'])): ?>
        <div class="mt-2">
            <p class="mb-1">Foto saat ini:</p>
            <img src="<?= UPLOAD_DIR_PRODUK . htmlspecialchars($produk['foto']) ?>" 
                 alt="Foto Produk" 
                 style="max-width: 300px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
        </div>
        <?php endif; ?>

        <!-- Preview gambar baru -->
        <div class="mt-2">
            <img id="imagePreview" src="#" alt="Preview Gambar Baru" 
                 style="max-width: 300px; max-height: 200px; display: none; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
        </div>

        <div class="form-text">
            <small>
                Format yang didukung: JPG, PNG, GIF, WebP. Ukuran maksimal: 2MB.<br>
                Kosongkan jika tidak ingin mengganti foto.
            </small>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Perbarui</button>
    <a href="index.php" class="btn btn-secondary">Batal</a>
</form>

<!-- JavaScript untuk preview gambar -->
<script>
function previewImage(event) {
    const reader = new FileReader();
    const preview = document.getElementById('imagePreview');

    reader.onload = function() {
        preview.src = reader.result;
        preview.style.display = 'block';
    };

    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    } else {
        preview.style.display = 'none';
        preview.src = '#';
    }
}
</script>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>