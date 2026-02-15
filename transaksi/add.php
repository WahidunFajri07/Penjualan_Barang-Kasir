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

// Inisialisasi session keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle AJAX request untuk update cart
if (isset($_POST['action']) && isset($_POST['produk_id'])) {
    header('Content-Type: application/json');
    $produk_id = (int)$_POST['produk_id'];
    
    if ($_POST['action'] == 'add') {
        // Tambah quantity
        if (isset($_SESSION['cart'][$produk_id])) {
            $_SESSION['cart'][$produk_id]++;
        } else {
            $_SESSION['cart'][$produk_id] = 1;
        }
    } elseif ($_POST['action'] == 'remove') {
        // Kurangi quantity
        if (isset($_SESSION['cart'][$produk_id])) {
            $_SESSION['cart'][$produk_id]--;
            if ($_SESSION['cart'][$produk_id] <= 0) {
                unset($_SESSION['cart'][$produk_id]);
            }
        }
    } elseif ($_POST['action'] == 'delete') {
        // Hapus item dari cart
        unset($_SESSION['cart'][$produk_id]);
    }
    
    // Hitung total items dan subtotal
    $total_items = array_sum($_SESSION['cart']);
    $subtotal = 0;
    
    if (!empty($_SESSION['cart'])) {
        $ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = mysqli_prepare($connection, "SELECT id, harga FROM produk WHERE id IN ($placeholders)");
        
        $types = str_repeat('i', count($ids));
        mysqli_stmt_bind_param($stmt, $types, ...$ids);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $subtotal += $row['harga'] * $_SESSION['cart'][$row['id']];
        }
        mysqli_stmt_close($stmt);
    }
    
    echo json_encode([
        'success' => true, 
        'total_items' => $total_items,
        'subtotal' => $subtotal
    ]);
    exit;
}

// Get semua produk
$result = mysqli_query($connection, "SELECT * FROM `produk` ORDER BY nama_produk ASC");

// Hitung total items di cart dan subtotal
$total_items = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = mysqli_prepare($connection, "SELECT id, harga FROM produk WHERE id IN ($placeholders)");
    
    $types = str_repeat('i', count($ids));
    mysqli_stmt_bind_param($stmt, $types, ...$ids);
    mysqli_stmt_execute($stmt);
    $result_calc = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result_calc)) {
        $subtotal += $row['harga'] * $_SESSION['cart'][$row['id']];
    }
    mysqli_stmt_close($stmt);
}
?>
<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<style>
.product-card {
    border: 1px solid #ddd;
    border-radius: 10px; /* ditambah dari 8px */
    padding: 18px; /* ditambah dari 15px */
    margin-bottom: 20px;
    transition: all 0.3s ease;
    background: white;
    height: auto; /* diubah dari 55% ke auto untuk lebih fleksibel */
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05); /* tambah shadow ringan */
}

.product-card:hover {
    box-shadow: 0 6px 16px rgba(0,0,0,0.12); /* diperbesar dari 4px 12px */
    transform: translateY(-3px); /* diperbesar dari -2px */
    border-color: #ccc; /* border lebih gelap saat hover */
}

.product-image {
    width: 100%;
    height: 180px; /* dikurangi dari 200px */
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 18px; /* ditambah dari 15px */
    background: #f8f9fa;
    padding: 5px; /* tambah padding agar gambar tidak mentok */
}

.product-info {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.product-title {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 10px; /* ditambah dari 8px */
    color: #333;
    min-height: 44px; /* ditambah dari 40px */
    line-height: 1.4; /* tambah line-height untuk readability */
}

.product-code {
    font-size: 12px;
    color: #666;
    margin-bottom: 8px; /* ditambah dari 5px */
    padding: 3px 8px; /* tambah padding */
    background-color: #f9f9f9; /* tambah background subtle */
    border-radius: 4px;
    display: inline-block;
    width: fit-content;
}

.product-price {
    font-size: 19px; /* ditambah dari 18px */
    font-weight: bold;
    color: #28a745;
    margin-bottom: 20px; /* ditambah dari 15px */
    margin-top: auto; /* push ke bawah agar konsisten */
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 12px; /* ditambah dari 10px */
    justify-content: center;
    margin-top: 10px; /* tambah margin atas */
    padding-top: 15px; /* tambah padding atas */
    border-top: 1px solid #eee; /* tambah separator */
}

.btn-quantity {
    width: 38px; /* dikurangi dari 40px */
    height: 38px; /* dikurangi dari 40px */
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* tambah shadow */
}

.btn-minus {
    background: #dc3545;
    color: white;
}

.btn-plus {
    background: #28a745;
    color: white;
}

.btn-quantity:hover:not(:disabled) {
    opacity: 0.9; /* dikurangi dari 0.8 */
    transform: scale(1.08); /* dikurangi dari 1.1 */
    box-shadow: 0 3px 6px rgba(0,0,0,0.15); /* shadow lebih saat hover */
}

.btn-quantity:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.quantity-display {
    font-size: 18px; /* dikurangi dari 20px */
    font-weight: bold;
    min-width: 36px; /* dikurangi dari 40px */
    text-align: center;
    color: #333;
    padding: 5px 8px; /* tambah padding */
    background: #f8f9fa;
    border-radius: 6px;
}

.cart-summary {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    padding: 18px 24px; /* ditambah dari 15px 20px */
    box-shadow: 0 -3px 15px rgba(0,0,0,0.12); /* diperbesar */
    z-index: 1000;
    display: none;
    border-top: 1px solid #eee; /* tambah border atas */
}

.cart-summary.show {
    display: block;
    animation: slideUp 0.3s ease; /* tambah animasi */
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}

.cart-summary-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 36px; /* ditambah dari 32px */
}

.cart-info {
    display: flex;
    flex-direction: row;
    gap: 28px; /* ditambah dari 24px */
    align-items: center;
}

.cart-info-item {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    min-width: 120px; /* tambah min-width untuk konsistensi */
}

.cart-info-label {
    font-size: 13px; /* ditambah dari 12px */
    color: #666;
    line-height: 1.3; /* ditambah dari 1.2 */
    margin-bottom: 2px; /* tambah margin bawah */
}

.cart-info-value {
    font-size: 18px; /* ditambah dari 17px */
    font-weight: bold;
    color: #333;
}
/* Optional: supaya tombol nggak terlalu gede */
.cart-summary .btn-lg {
    padding: 10px 18px;
    font-size: 16px;
}


.search-box {
    margin-bottom: 20px;
}

.search-box input {
    border-radius: 25px;
    padding: 10px 20px;
}

@media (max-width: 768px) {
    .cart-summary-content {
        flex-direction: column;
        gap: 15px;
    }
    
    .cart-info {
        gap: 20px;
        width: 100%;
        justify-content: center;
    }
    
    .product-card {
        margin-bottom: 15px;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-shopping-cart"></i> Tambah Transaksi Baru</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> 
    <strong>Cara Belanja:</strong> Pilih produk yang ingin dibeli dengan menekan tombol <strong class="text-success">+</strong>. 
    Setelah selesai memilih, klik tombol <strong class="text-primary">Lanjut ke Checkout</strong> di bagian bawah.
</div>

<!-- Search Box -->
<div class="search-box">
    <input type="text" 
           id="searchProduct" 
           class="form-control" 
           placeholder="🔍 Cari produk berdasarkan nama atau kode barang...">
</div>

<?php if (mysqli_num_rows($result) > 0): ?>
    <div class="row" id="productList">
        <?php while ($row = mysqli_fetch_assoc($result)): 
            $produk_id = $row['id'];
            $qty = isset($_SESSION['cart'][$produk_id]) ? $_SESSION['cart'][$produk_id] : 0;
            
            // Path foto produk
            $foto_path = '../uploads/produk/' . $row['foto'];
            if (!file_exists($foto_path) || $row['foto'] == 'default.jpg' || $row['foto'] == '0') {
                $foto_path = '../assets/images/no-image.png';
            }
        ?>
            <div class="col-lg-3 col-md-4 col-sm-6 product-item" 
                 data-name="<?= strtolower(htmlspecialchars($row['nama_produk'])) ?>"
                 data-code="<?= strtolower(htmlspecialchars($row['kode_barang'])) ?>">
                <div class="product-card">
                    <img src="<?= htmlspecialchars($foto_path) ?>" 
                         alt="<?= htmlspecialchars($row['nama_produk']) ?>" 
                         class="product-image"
                         onerror="this.src='../assets/images/no-image.png'">
                    
                    <div class="product-info">
                        <div class="product-code">
                            <i class="fas fa-barcode"></i> <?= htmlspecialchars($row['kode_barang']) ?>
                        </div>
                        <div class="product-title"><?= htmlspecialchars($row['nama_produk']) ?></div>
                        <div class="product-price">
                            <i class="fas fa-tag"></i> Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                        </div>
                    </div>
                    
                    <div class="quantity-control">
                        <button class="btn-quantity btn-minus" 
                                onclick="updateCart(<?= $produk_id ?>, 'remove')"
                                <?= $qty == 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity-display" id="qty-<?= $produk_id ?>"><?= $qty ?></span>
                        <button class="btn-quantity btn-plus" 
                                onclick="updateCart(<?= $produk_id ?>, 'add')">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> Belum ada produk tersedia. 
        Silakan tambahkan produk terlebih dahulu di menu Produk.
    </div>
<?php endif; ?>

<!-- Cart Summary (Fixed Bottom) -->
<div class="cart-summary <?= $total_items > 0 ? 'show' : '' ?>" id="cartSummary">
    <div class="cart-summary-content">
        <div class="cart-info">
            <div class="cart-info-item">
                <span class="cart-info-label">Total Item</span>
                <span class="cart-info-value" id="totalItems"><?= $total_items ?></span>
            </div>
            <div class="cart-info-item">
                <span class="cart-info-label">Subtotal</span>
                <span class="cart-info-value text-success" id="subtotalAmount">
                    Rp <?= number_format($subtotal, 0, ',', '.') ?>
                </span>
            </div>
        </div>
        <div>
            <a href="detail.php" class="btn btn-primary btn-lg">
                <i class="fas fa-check-circle"></i> Lanjut ke Checkout
            </a>
        </div>
    </div>
</div>

<!-- Add padding bottom when cart summary is shown -->
<div style="height: <?= $total_items > 0 ? '100px' : '0' ?>"></div>

<script>
function updateCart(produkId, action) {
    fetch('add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=${action}&produk_id=${produkId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update quantity display
            const qtyElement = document.getElementById('qty-' + produkId);
            const currentQty = parseInt(qtyElement.textContent);
            
            let newQty = currentQty;
            if (action == 'add') {
                newQty = currentQty + 1;
            } else if (action == 'remove') {
                newQty = Math.max(0, currentQty - 1);
            }
            qtyElement.textContent = newQty;
            
            // Update total items
            document.getElementById('totalItems').textContent = data.total_items;
            
            // Update subtotal
            document.getElementById('subtotalAmount').textContent = 
                'Rp ' + formatNumber(data.subtotal);
            
            // Show/hide cart summary
            const cartSummary = document.getElementById('cartSummary');
            if (data.total_items > 0) {
                cartSummary.classList.add('show');
            } else {
                cartSummary.classList.remove('show');
            }
            
            // Enable/disable minus button
            const minusBtn = document.querySelector(
                `button[onclick*="${produkId}"][onclick*="remove"]`
            );
            minusBtn.disabled = newQty === 0;
            
            // Add animation effect
            qtyElement.style.transform = 'scale(1.3)';
            qtyElement.style.color = '#28a745';
            setTimeout(() => {
                qtyElement.style.transform = 'scale(1)';
                qtyElement.style.color = '#333';
            }, 200);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan. Silakan coba lagi.');
    });
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Search functionality
document.getElementById('searchProduct').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const productItems = document.querySelectorAll('.product-item');
    
    productItems.forEach(item => {
        const name = item.getAttribute('data-name');
        const code = item.getAttribute('data-code');
        
        if (name.includes(searchTerm) || code.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>