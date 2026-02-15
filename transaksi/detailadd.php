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

$master_id = (int)($_GET['transaksi_id'] ?? 0);
if (!$master_id) redirect('index.php');

// Cek transaksi ada dan belum lunas
$stmt = mysqli_prepare($connection, "SELECT * FROM `transaksi` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $master_id);
mysqli_stmt_execute($stmt);
$transaksi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$transaksi) {
    redirect('index.php');
}

if ($transaksi['status_bayar'] === 'LUNAS') {
    redirect("detail.php?id=$master_id");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }
    
    $produk_id = (int)($_POST['produk_id'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 0);
    
    if ($produk_id <= 0) {
        $error = "Pilih produk terlebih dahulu.";
    } elseif ($qty <= 0) {
        $error = "Quantity harus lebih dari 0.";
    }
    
    if (!$error) {
        // Get harga produk
        $stmt = mysqli_prepare($connection, "SELECT harga FROM produk WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $produk_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $produk = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$produk) {
            $error = "Produk tidak ditemukan.";
        } else {
            $harga = $produk['harga'];
            $subtotal = $harga * $qty;
            
            // Check duplicate
            $check_stmt = mysqli_prepare($connection, 
                "SELECT id FROM `detail_transaksi` WHERE `transaksi_id` = ? AND `produk_id` = ?");
            mysqli_stmt_bind_param($check_stmt, "ii", $master_id, $produk_id);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);
            
            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $error = "Produk ini sudah ada dalam transaksi. Silakan hapus item yang lama terlebih dahulu.";
            }
            mysqli_stmt_close($check_stmt);
            
            if (!$error) {
                // Insert detail
                $stmt = mysqli_prepare($connection, 
                    "INSERT INTO `detail_transaksi` (`transaksi_id`, `produk_id`, `qty`, `harga`, `subtotal`) 
                     VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iiiii", $master_id, $produk_id, $qty, $harga, $subtotal);
                
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    
                    // Update total transaksi
                    $total_stmt = mysqli_prepare($connection,
                        "SELECT SUM(subtotal) as total FROM detail_transaksi WHERE transaksi_id = ?");
                    mysqli_stmt_bind_param($total_stmt, "i", $master_id);
                    mysqli_stmt_execute($total_stmt);
                    $total_result = mysqli_stmt_get_result($total_stmt);
                    $total_row = mysqli_fetch_assoc($total_result);
                    $new_total = $total_row['total'] ?? 0;
                    mysqli_stmt_close($total_stmt);
                    
                    // Update transaksi
                    $update_stmt = mysqli_prepare($connection,
                        "UPDATE transaksi SET total_bayar = ? WHERE id = ?");
                    mysqli_stmt_bind_param($update_stmt, "ii", $new_total, $master_id);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                    
                    redirect("detail.php?id=$master_id");
                } else {
                    $error = "Gagal menyimpan item.";
                }
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<style>
.form-container {
    max-width: 600px;
    margin: 0 auto;
}

.product-preview {
    display: none;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-top: 15px;
    background: #f8f9fa;
}

.product-preview.show {
    display: block;
}

.product-preview-image {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    margin-right: 15px;
}

.product-preview-info {
    flex-grow: 1;
}

.price-preview {
    font-size: 18px;
    font-weight: bold;
    color: #28a745;
    margin-top: 10px;
}
</style>

<div class="form-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-plus-circle"></i> Tambah Item Manual</h2>
        <a href="detail.php?id=<?= $master_id ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        Menambahkan item ke Transaksi #<?= $master_id ?> - <?= htmlspecialchars($transaksi['nomor_bukti']) ?>
    </div>
    
    <?php if ($error): ?>
        <?= showAlert($error, 'danger') ?>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" id="addItemForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="transaksi_id" value="<?= $master_id ?>">
                
                <div class="mb-3">
                    <label class="form-label">Pilih Produk*</label>
                    <?php
                    echo dropdownFromTable(
                        'produk',
                        'id',
                        'nama_produk',
                        '',
                        'produk_id',
                        '-- Pilih Produk --',
                        'nama_produk'
                    ); ?>
                    <small class="text-muted">Pilih produk yang akan ditambahkan</small>
                </div>
                
                <div id="productPreview" class="product-preview"></div>
                
                <div class="mb-3">
                    <label class="form-label">Quantity (Jumlah)*</label>
                    <input type="number" 
                           name="qty" 
                           id="qtyInput"
                           class="form-control" 
                           min="1" 
                           value="1"
                           required>
                    <small class="text-muted">Masukkan jumlah produk</small>
                </div>
                
                <div id="subtotalPreview" class="price-preview"></div>
                
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="detail.php?id=<?= $master_id ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Tambah Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Preview produk saat dipilih
document.querySelector('select[name="produk_id"]').addEventListener('change', function() {
    const produkId = this.value;
    
    if (!produkId) {
        document.getElementById('productPreview').classList.remove('show');
        return;
    }
    
    // Fetch product details via AJAX
    fetch('get_product.php?id=' + produkId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const preview = document.getElementById('productPreview');
                const fotoPath = data.foto && data.foto !== '0' && data.foto !== 'default.jpg' 
                    ? '../uploads/produk/' + data.foto 
                    : '../assets/images/no-image.png';
                
                preview.innerHTML = `
                    <div class="d-flex align-items-center">
                        <img src="${fotoPath}" 
                             alt="${data.nama_produk}" 
                             class="product-preview-image"
                             onerror="this.src='../assets/images/no-image.png'">
                        <div class="product-preview-info">
                            <strong>${data.nama_produk}</strong><br>
                            <small class="text-muted">Kode: ${data.kode_barang}</small><br>
                            <span class="text-success" style="font-size: 16px; font-weight: bold;">
                                Rp ${formatNumber(data.harga)}
                            </span>
                        </div>
                    </div>
                `;
                preview.classList.add('show');
                preview.dataset.harga = data.harga;
                
                updateSubtotal();
            }
        })
        .catch(error => console.error('Error:', error));
});

// Update subtotal saat qty berubah
document.getElementById('qtyInput').addEventListener('input', updateSubtotal);

function updateSubtotal() {
    const preview = document.getElementById('productPreview');
    const harga = parseFloat(preview.dataset.harga || 0);
    const qty = parseInt(document.getElementById('qtyInput').value || 0);
    const subtotal = harga * qty;
    
    if (harga > 0 && qty > 0) {
        document.getElementById('subtotalPreview').innerHTML = `
            <i class="fas fa-calculator"></i> Subtotal: 
            <span class="text-success">Rp ${formatNumber(subtotal)}</span>
        `;
    } else {
        document.getElementById('subtotalPreview').innerHTML = '';
    }
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>