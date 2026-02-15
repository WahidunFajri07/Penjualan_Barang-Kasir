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

// MODE 1: Checkout dari Cart (tidak ada ID)
if (!$id) {
    // Cek apakah ada cart
    if (empty($_SESSION['cart'])) {
        redirect('add.php');
    }
    
    $error = $success = '';
    
    // Handle form submit untuk save transaksi
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_transaksi'])) {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF token.');
        }
        
        $nomor_bukti = trim($_POST['nomor_bukti'] ?? '');
        $tanggal = trim($_POST['tanggal'] ?? '');
        $diskon = (float)($_POST['diskon'] ?? 0);
        $uang_bayar = (float)($_POST['uang_bayar'] ?? 0);
        
        if (empty($nomor_bukti) || empty($tanggal)) {
            $error = "Nomor bukti dan tanggal wajib diisi.";
        }
        
        if (!$error && empty($_SESSION['cart'])) {
            $error = "Keranjang kosong. Silakan pilih produk terlebih dahulu.";
        }
        
        if (!$error) {
            // Start transaction
            mysqli_begin_transaction($connection);
            
            try {
                // Hitung total
                $total_bayar = 0;
                $cart_items = [];
                
                foreach ($_SESSION['cart'] as $produk_id => $qty) {
                    $stmt = mysqli_prepare($connection, "SELECT id, nama_produk, harga FROM produk WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "i", $produk_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $produk = mysqli_fetch_assoc($result);
                    mysqli_stmt_close($stmt);
                    
                    if ($produk) {
                        $subtotal = $produk['harga'] * $qty;
                        $total_bayar += $subtotal;
                        $cart_items[] = [
                            'produk_id' => $produk_id,
                            'nama_produk' => $produk['nama_produk'],
                            'qty' => $qty,
                            'harga' => $produk['harga'],
                            'subtotal' => $subtotal
                        ];
                    }
                }
                
                // Hitung total setelah diskon
                $total_setelah_diskon = $total_bayar - $diskon;
                $kembalian = $uang_bayar - $total_setelah_diskon;
                
                // Validasi uang bayar
                if ($uang_bayar < $total_setelah_diskon) {
                    throw new Exception("Uang bayar tidak mencukupi!");
                }
                
                $status_bayar = 'LUNAS';
                $stmt = mysqli_prepare($connection, 
                    "INSERT INTO `transaksi` (`nomor_bukti`, `tanggal`, `total_bayar`, `diskon`, `uang_bayar`, `kembalian`, `status_bayar`) VALUES (?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssdddds", $nomor_bukti, $tanggal, $total_bayar, $diskon, $uang_bayar, $kembalian, $status_bayar);
                mysqli_stmt_execute($stmt);
                $transaksi_id = mysqli_insert_id($connection);
                mysqli_stmt_close($stmt);
                
                // Insert detail transaksi
                $stmt_detail = mysqli_prepare($connection,
                    "INSERT INTO `detail_transaksi` (`transaksi_id`, `produk_id`, `qty`, `harga`, `subtotal`) VALUES (?, ?, ?, ?, ?)");
                
                foreach ($cart_items as $item) {
                    mysqli_stmt_bind_param($stmt_detail, "iiiii", 
                        $transaksi_id, 
                        $item['produk_id'], 
                        $item['qty'], 
                        $item['harga'], 
                        $item['subtotal']
                    );
                    mysqli_stmt_execute($stmt_detail);
                }
                mysqli_stmt_close($stmt_detail);
                
                // Commit transaction
                mysqli_commit($connection);
                
                // Clear cart
                unset($_SESSION['cart']);
                
                redirect("/fash-cashier/transaksi/detail.php?id=$transaksi_id&success=1");
                
            } catch (Exception $e) {
                mysqli_rollback($connection);
                $error = "Gagal menyimpan transaksi: " . $e->getMessage();
            }
        }
    }
    
    // Load produk dari cart untuk ditampilkan
    $cart_items = [];
    $total_bayar = 0;
    
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $produk_id => $qty) {
            $stmt = mysqli_prepare($connection, "SELECT id, kode_barang, nama_produk, harga, foto FROM produk WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $produk_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $produk = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            if ($produk) {
                $subtotal = $produk['harga'] * $qty;
                $total_bayar += $subtotal;
                $cart_items[] = [
                    'produk_id' => $produk_id,
                    'kode_barang' => $produk['kode_barang'],
                    'nama_produk' => $produk['nama_produk'],
                    'foto' => $produk['foto'],
                    'qty' => $qty,
                    'harga' => $produk['harga'],
                    'subtotal' => $subtotal
                ];
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
    .checkout-container {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .cart-item {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background: white;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .cart-item-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        background: #f8f9fa;
    }
    
    .cart-item-info {
        flex-grow: 1;
    }
    
    .cart-item-title {
        font-weight: bold;
        font-size: 16px;
        margin-bottom: 5px;
    }
    
    .cart-item-code {
        font-size: 12px;
        color: #666;
    }
    
    .cart-item-price {
        text-align: right;
    }
    
    .cart-item-qty {
        font-size: 14px;
        color: #666;
    }
    
    .cart-item-subtotal {
        font-size: 18px;
        font-weight: bold;
        color: #28a745;
    }
    
    .total-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 16px;
    }
    
    .total-row.grand-total {
        font-size: 24px;
        font-weight: bold;
        color: #28a745;
        padding-top: 15px;
        border-top: 2px solid #dee2e6;
    }
    
    .form-section {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .btn-remove-item {
        color: #dc3545;
        cursor: pointer;
        font-size: 20px;
    }
    
    .btn-remove-item:hover {
        color: #a71d2a;
    }
    </style>
    
    <div class="checkout-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-shopping-cart"></i> Checkout Transaksi</h2>
            <a href="add.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali Belanja
            </a>
        </div>
        
        <?php if ($error): ?>
            <?= showAlert($error, 'danger') ?>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                Keranjang Anda kosong. Silakan <a href="add.php">pilih produk</a> terlebih dahulu.
            </div>
        <?php else: ?>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Periksa kembali pesanan Anda, lalu lengkapi form di bawah untuk menyimpan transaksi.
            </div>
            
            <!-- Daftar Item di Cart -->
            <h5 class="mb-3"><i class="fas fa-list"></i> Daftar Produk (<?= count($cart_items) ?> item)</h5>
            
            <?php foreach ($cart_items as $item): 
                $foto_path = '../uploads/produk/' . $item['foto'];
                if (!file_exists($foto_path) || $item['foto'] == 'default.jpg' || $item['foto'] == '0') {
                    $foto_path = '../assets/images/no-image.png';
                }
            ?>
                <div class="cart-item">
                    <img src="<?= htmlspecialchars($foto_path) ?>" 
                         alt="<?= htmlspecialchars($item['nama_produk']) ?>" 
                         class="cart-item-image"
                         onerror="this.src='../assets/images/no-image.png'">
                    
                    <div class="cart-item-info">
                        <div class="cart-item-title"><?= htmlspecialchars($item['nama_produk']) ?></div>
                        <div class="cart-item-code">
                            <i class="fas fa-barcode"></i> <?= htmlspecialchars($item['kode_barang']) ?>
                        </div>
                    </div>
                    
                    <div class="cart-item-price">
                        <div class="cart-item-qty">
                            <?= $item['qty'] ?> × Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                        </div>
                        <div class="cart-item-subtotal">
                            Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Total Section -->
            <div class="total-section">
                <div class="total-row">
                    <span>Total Item:</span>
                    <span><strong><?= array_sum(array_column($cart_items, 'qty')) ?></strong></span>
                </div>
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span id="subtotal-display">Rp <?= number_format($total_bayar, 0, ',', '.') ?></span>
                </div>
                <div class="total-row">
                    <span>Diskon:</span>
                    <span id="diskon-display">Rp 0</span>
                </div>
                <div class="total-row grand-total">
                    <span>Total Bayar:</span>
                    <span id="total-display">Rp <?= number_format($total_bayar, 0, ',', '.') ?></span>
                </div>
            </div>
            
            <!-- Form Transaksi -->
            <div class="form-section">
                <h5 class="mb-3"><i class="fas fa-file-invoice"></i> Informasi Transaksi</h5>
                
                <form method="POST" id="checkoutForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="save_transaksi" value="1">
                    <input type="hidden" id="subtotal-value" value="<?= $total_bayar ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor Bukti*</label>
                            <input type="text" 
                                   name="nomor_bukti" 
                                   class="form-control" 
                                   value="TRX-<?= date('Ymd') ?>-<?= str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) ?>"
                                   required>
                            <small class="text-muted">Nomor bukti transaksi</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Transaksi*</label>
                            <input type="date" 
                                   name="tanggal" 
                                   class="form-control" 
                                   value="<?= date('Y-m-d') ?>" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Diskon (Rp)</label>
                            <input type="number" 
                                   name="diskon" 
                                   id="diskon-input"
                                   class="form-control" 
                                   value="0"
                                   min="0"
                                   step="1000">
                            <small class="text-muted">Potongan harga</small>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Uang Bayar (Rp)*</label>
                            <input type="number" 
                                   name="uang_bayar" 
                                   id="uang-bayar-input"
                                   class="form-control" 
                                   value="<?= $total_bayar ?>"
                                   min="0"
                                   step="1000"
                                   required>
                            <small class="text-muted">Nominal yang dibayarkan</small>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kembalian (Rp)</label>
                            <input type="text" 
                                   id="kembalian-display"
                                   class="form-control" 
                                   value="Rp 0"
                                   readonly
                                   style="background: #e9ecef; font-weight: bold; color: #28a745;">
                            <small class="text-muted">Uang kembali</small>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="add.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                            <i class="fas fa-save"></i> Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
            
        <?php endif; ?>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const subtotalValue = parseFloat(document.getElementById('subtotal-value').value);
        const diskonInput = document.getElementById('diskon-input');
        const uangBayarInput = document.getElementById('uang-bayar-input');
        const diskonDisplay = document.getElementById('diskon-display');
        const totalDisplay = document.getElementById('total-display');
        const kembalianDisplay = document.getElementById('kembalian-display');
        const submitBtn = document.getElementById('submitBtn');
        
        function formatRupiah(angka) {
            return 'Rp ' + angka.toLocaleString('id-ID');
        }
        
        function hitungTotal() {
            const diskon = parseFloat(diskonInput.value) || 0;
            const uangBayar = parseFloat(uangBayarInput.value) || 0;
            
            // Validasi diskon tidak boleh lebih dari subtotal
            if (diskon > subtotalValue) {
                diskonInput.value = subtotalValue;
                alert('Diskon tidak boleh lebih dari total belanja!');
                return;
            }
            
            const totalSetelahDiskon = subtotalValue - diskon;
            const kembalian = uangBayar - totalSetelahDiskon;
            
            // Update display
            diskonDisplay.textContent = formatRupiah(diskon);
            totalDisplay.textContent = formatRupiah(totalSetelahDiskon);
            kembalianDisplay.value = formatRupiah(kembalian >= 0 ? kembalian : 0);
            
            // Update uang bayar minimal
            uangBayarInput.min = totalSetelahDiskon;
            
            // Validasi tombol submit
            if (uangBayar < totalSetelahDiskon) {
                kembalianDisplay.style.color = '#dc3545';
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Uang Bayar Kurang';
            } else {
                kembalianDisplay.style.color = '#28a745';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Transaksi';
            }
        }
        
        // Event listeners
        diskonInput.addEventListener('input', hitungTotal);
        uangBayarInput.addEventListener('input', hitungTotal);
        
        // Initial calculation
        hitungTotal();
    });
    </script>
    
    <?php include '../views/'.$THEME.'/lower_block.php'; ?>
    <?php include '../views/'.$THEME.'/footer.php'; ?>
    <?php
    exit;
}

// MODE 2: View Detail Transaksi (ada ID)
$stmt = mysqli_prepare($connection, "SELECT * FROM `transaksi` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$transaksi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$transaksi) redirect('index.php');

$details = mysqli_query($connection, "SELECT dt.*, p.kode_barang, p.nama_produk, p.foto 
    FROM `detail_transaksi` dt 
    LEFT JOIN `produk` p ON dt.produk_id = p.id 
    WHERE dt.`transaksi_id` = $id");

$success_msg = isset($_GET['success']) ? 'Transaksi berhasil disimpan!' : '';

// Hitung total setelah diskon
$diskon = $transaksi['diskon'] ?? 0;
$uang_bayar = $transaksi['uang_bayar'] ?? 0;
$kembalian = $transaksi['kembalian'] ?? 0;
$total_setelah_diskon = $transaksi['total_bayar'] - $diskon;
?>
<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<style>
.detail-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.info-row {
    display: flex;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: bold;
    width: 180px;
    color: #666;
}

.info-value {
    flex-grow: 1;
}

.status-badge {
    font-size: 14px;
    padding: 5px 15px;
}

.item-table img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.payment-highlight {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 4px solid #28a745;
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
}
</style>

<?php if ($success_msg): ?>
    <div class="alert alert-success alert-dismissible fade show" style="position: relative;">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="alert-heading mb-2">
                    <i class="fas fa-check-circle"></i> Transaksi Berhasil Disimpan!
                </h5>
                <p class="mb-0">
                    Transaksi telah berhasil disimpan dengan nomor: <strong><?= htmlspecialchars($transaksi['nomor_bukti']) ?></strong>
                </p>
            </div>
            <div>
                <a href="invoice.php?id=<?= $id ?>" class="btn btn-primary" target="_blank">
                    <i class="fas fa-print"></i> Cetak Invoice
                </a>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-invoice"></i> Detail Transaksi #<?= $transaksi['id'] ?></h2>
    <div class="d-flex gap-2">
        <a href="invoice.php?id=<?= $id ?>" class="btn btn-primary" target="_blank">
            <i class="fas fa-print"></i> Cetak Invoice
        </a>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Informasi Transaksi -->
<div class="detail-card">
    <h5 class="mb-3"><i class="fas fa-info-circle"></i> Informasi Transaksi</h5>
    
    <div class="info-row">
        <div class="info-label">Nomor Bukti:</div>
        <div class="info-value"><?= htmlspecialchars($transaksi['nomor_bukti']) ?></div>
    </div>
    
    <div class="info-row">
        <div class="info-label">Tanggal:</div>
        <div class="info-value"><?= date('d/m/Y', strtotime($transaksi['tanggal'])) ?></div>
    </div>
    
    <div class="info-row">
        <div class="info-label">Subtotal:</div>
        <div class="info-value">
            <strong>Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.') ?></strong>
        </div>
    </div>
    
    <?php if ($diskon > 0): ?>
    <div class="info-row">
        <div class="info-label">Diskon:</div>
        <div class="info-value">
            <strong class="text-danger">- Rp <?= number_format($diskon, 0, ',', '.') ?></strong>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="info-row">
        <div class="info-label">Total Bayar:</div>
        <div class="info-value">
            <strong class="text-success" style="font-size: 20px;">
                Rp <?= number_format($total_setelah_diskon, 0, ',', '.') ?>
            </strong>
        </div>
    </div>
    
    <div class="info-row">
        <div class="info-label">Status Bayar:</div>
        <div class="info-value">
            <span class="badge status-badge bg-<?= $transaksi['status_bayar'] === 'LUNAS' ? 'success' : 'warning' ?>">
                <?= htmlspecialchars($transaksi['status_bayar']) ?>
            </span>
        </div>
    </div>
    
    <!-- Pembayaran Detail -->
    <div class="payment-highlight">
        <div class="row">
            <div class="col-md-4">
                <small class="text-muted d-block">Uang Dibayar</small>
                <strong class="text-primary" style="font-size: 18px;">
                    Rp <?= number_format($uang_bayar, 0, ',', '.') ?>
                </strong>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Total Bayar</small>
                <strong class="text-dark" style="font-size: 18px;">
                    Rp <?= number_format($total_setelah_diskon, 0, ',', '.') ?>
                </strong>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Kembalian</small>
                <strong class="text-success" style="font-size: 18px;">
                    Rp <?= number_format($kembalian, 0, ',', '.') ?>
                </strong>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Item -->
<div class="detail-card">
    <h5 class="mb-3"><i class="fas fa-list"></i> Daftar Item</h5>
    
    <?php if (mysqli_num_rows($details) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover item-table">
                <thead class="table-light">
                    <tr>
                        <th width="80">Gambar</th>
                        <th>Kode</th>
                        <th>Nama Produk</th>
                        <th width="80">Qty</th>
                        <th width="120">Harga</th>
                        <th width="120">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_qty = 0;
                    while ($detail = mysqli_fetch_assoc($details)): 
                        $total_qty += $detail['qty'];
                        $foto_path = '../uploads/produk/' . $detail['foto'];
                        if (!file_exists($foto_path) || $detail['foto'] == 'default.jpg' || $detail['foto'] == '0') {
                            $foto_path = '../assets/images/no-image.png';
                        }
                    ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($foto_path) ?>" 
                                     alt="<?= htmlspecialchars($detail['nama_produk']) ?>"
                                     onerror="this.src='../assets/images/no-image.png'">
                            </td>
                            <td><?= htmlspecialchars($detail['kode_barang']) ?></td>
                            <td><?= htmlspecialchars($detail['nama_produk']) ?></td>
                            <td class="text-center"><strong><?= $detail['qty'] ?></strong></td>
                            <td class="text-end">Rp <?= number_format($detail['harga'], 0, ',', '.') ?></td>
                            <td class="text-end"><strong>Rp <?= number_format($detail['subtotal'], 0, ',', '.') ?></strong></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <th class="text-center"><?= $total_qty ?></th>
                        <th></th>
                        <th class="text-end text-success">
                            Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.') ?>
                        </th>
                    </tr>
                    <?php if ($diskon > 0): ?>
                    <tr>
                        <th colspan="5" class="text-end text-danger">Diskon:</th>
                        <th class="text-end text-danger">
                            - Rp <?= number_format($diskon, 0, ',', '.') ?>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="5" class="text-end">Grand Total:</th>
                        <th class="text-end text-success" style="font-size: 18px;">
                            Rp <?= number_format($total_setelah_diskon, 0, ',', '.') ?>
                        </th>
                    </tr>
                    <?php endif; ?>
                </tfoot>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Belum ada item dalam transaksi ini.
        </div>
    <?php endif; ?>
</div>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>