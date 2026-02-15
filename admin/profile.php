<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);
session_start();
require_once '../lib/auth.php';
require_once '../lib/functions.php';
requireAuth();

// Set variabel untuk template
$page_title = 'Profile Saya';
$current_page = 'profile';

// Koneksi Database
if (file_exists('../config/database.php')) {
    require_once '../config/database.php';
} else {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db = 'fashion_db';
    
    $conn = mysqli_connect($host, $user, $pass, $db);
    
    if (!$conn) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }
}

if (!isset($conn) || $conn === null) {
    die("Error: Koneksi database tidak dapat dibuat.");
}

// Get user ID dari session
$user_id = $_SESSION['user_id'];

// Query data user
$query_user = "SELECT * FROM users WHERE id = $user_id";
$result_user = mysqli_query($conn, $query_user);
$user_data = mysqli_fetch_assoc($result_user);

// Query statistik user (contoh: total transaksi yang dibuat user ini)
$query_stats = "SELECT 
                    COUNT(*) as total_transaksi,
                    COALESCE(SUM(total_bayar), 0) as total_penjualan
                FROM transaksi 
                WHERE DATE(tanggal) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$result_stats = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Handle messages
$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Definisikan base_url dan tema
if(!defined('base_url')) {
    define('base_url', 'http://localhost/fash-cashier/');
}
$THEME = 'soft-ui';
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/breadcrumb.php'; ?>

<style>
.profile-card {
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-top: 15px;
}

.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px 30px;
    text-align: center;
    position: relative;
}

.profile-header::before {
    content: '';
    position: absolute;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
    top: -100px;
    right: -100px;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 5px solid white;
    object-fit: cover;
    margin: 0 auto 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    position: relative;
    z-index: 1;
}

.profile-name {
    color: white;
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 5px;
    position: relative;
    z-index: 1;
}

.profile-role {
    color: rgba(255,255,255,0.9);
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    z-index: 1;
}

.stat-box {
    background: white;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.stat-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.stat-number {
    font-size: 28px;
    font-weight: bold;
    color: #667eea;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 13px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-group {
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.info-group:hover {
    background: #e9ecef;
}

.info-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.info-value {
    font-size: 16px;
    color: #2d3748;
    font-weight: 500;
}

.btn-edit {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 12px 30px;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

.activity-item {
    padding: 15px;
    border-left: 3px solid #667eea;
    margin-bottom: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.activity-time {
    font-size: 11px;
    color: #6c757d;
}

.activity-text {
    font-size: 14px;
    color: #2d3748;
    margin-top: 5px;
}

.change-avatar-btn {
    position: absolute;
    bottom: 0;
    right: 50%;
    transform: translateX(50%);
    background: white;
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.change-avatar-btn:hover {
    background: #667eea;
    color: white;
    transform: translateX(50%) scale(1.1);
}

.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(5px);
}

.modal-content {
    background: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 16px;
    max-width: 600px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 30px;
    border-radius: 16px 16px 0 0;
}

.modal-body {
    padding: 30px;
}

.close {
    color: white;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

.close:hover {
    transform: scale(1.2);
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    font-weight: 500;
    margin-bottom: 8px;
    color: #2d3748;
}

.form-control {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 10px 15px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.alert {
    border-radius: 10px;
    border: none;
    padding: 15px 20px;
    margin-bottom: 20px;
}

.alert-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.alert-danger {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}
</style>

<?php if ($message): ?>
<div class="alert alert-<?= $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
    <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
    <?= $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Profile Card -->
    <div class="col-lg-4 mb-4">
        <div class="card profile-card">
            <div class="profile-header">
                <div style="position: relative; display: inline-block;">
                    <img src="<?= !empty($user_data['foto']) ? '../uploads/users/' . $user_data['foto'] : '../assets/img/default-avatar.png'; ?>" 
                         alt="Avatar" 
                         class="profile-avatar"
                         id="profileAvatar">
                    <button class="change-avatar-btn" onclick="openAvatarModal()">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                <h4 class="profile-name"><?= htmlspecialchars($user_data['username']); ?></h4>
                <p class="profile-role">
                    <i class="fas fa-user-shield me-2"></i>
                    <?= htmlspecialchars($user_data['role']); ?>
                </p>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-2">
                    <button class="btn btn-edit" onclick="openEditModal()">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </button>
                    <button class="btn btn-outline-primary" onclick="openPasswordModal()">
                        <i class="fas fa-lock me-2"></i>Ganti Password
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="card mt-4">
            <div class="card-body p-4">
                <h6 class="mb-3">Statistik 30 Hari Terakhir</h6>
                <div class="stat-box mb-3">
                    <div class="stat-number"><?= number_format($stats['total_transaksi'], 0, ',', '.'); ?></div>
                    <div class="stat-label">Total Transaksi</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">Rp <?= number_format($stats['total_penjualan'], 0, ',', '.'); ?></div>
                    <div class="stat-label">Total Penjualan</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info & Activity -->
    <div class="col-lg-8 mb-4">
        <!-- Personal Information -->
        <div class="card profile-card mb-4">
            <div class="card-header p-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h6 class="mb-0 text-white">
                    <i class="fas fa-user me-2"></i>Informasi Personal
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-group">
                            <div class="info-label">Username</div>
                            <div class="info-value">
                                <i class="fas fa-user-circle me-2 text-primary"></i>
                                <?= htmlspecialchars($user_data['username']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <div class="info-label">Role</div>
                            <div class="info-value">
                                <i class="fas fa-shield-alt me-2 text-success"></i>
                                <?= htmlspecialchars($user_data['role']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <div class="info-label">Terdaftar Sejak</div>
                            <div class="info-value">
                                <i class="fas fa-calendar me-2 text-info"></i>
                                <?= date('d F Y', strtotime($user_data['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <div class="info-label">Status Akun</div>
                            <div class="info-value">
                                <i class="fas fa-check-circle me-2 text-success"></i>
                                Aktif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card profile-card">
            <div class="card-header p-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h6 class="mb-0 text-white">
                    <i class="fas fa-history me-2"></i>Aktivitas Terbaru
                </h6>
            </div>
            <div class="card-body p-4">
                <?php
                // Query aktivitas terbaru
                $query_activity = "SELECT * FROM transaksi 
                                  WHERE tanggal != '0000-00-00'
                                  ORDER BY tanggal DESC, id DESC 
                                  LIMIT 5";
                $result_activity = mysqli_query($conn, $query_activity);
                
                if (mysqli_num_rows($result_activity) > 0):
                    while($activity = mysqli_fetch_assoc($result_activity)):
                        $time_ago = time() - strtotime($activity['tanggal']);
                        if ($time_ago < 3600) {
                            $time_text = floor($time_ago / 60) . ' menit yang lalu';
                        } elseif ($time_ago < 86400) {
                            $time_text = floor($time_ago / 3600) . ' jam yang lalu';
                        } else {
                            $time_text = floor($time_ago / 86400) . ' hari yang lalu';
                        }
                ?>
                <div class="activity-item">
                    <div class="activity-time">
                        <i class="far fa-clock me-1"></i><?= $time_text; ?>
                    </div>
                    <div class="activity-text">
                        <strong>Transaksi <?= htmlspecialchars($activity['nomor_bukti']); ?></strong> 
                        - <?= $activity['status_bayar']; ?>
                        <span class="float-end text-primary font-weight-bold">
                            Rp <?= number_format($activity['total_bayar'], 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox text-secondary" style="font-size: 48px; opacity: 0.3;"></i>
                    <p class="text-secondary mt-2">Belum ada aktivitas</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Profile -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="mb-0">
                <i class="fas fa-edit me-2"></i>Edit Profile
            </h5>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="update_profile.php" id="editForm">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user_data['username']); ?>" required>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-edit">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ganti Password -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="mb-0">
                <i class="fas fa-lock me-2"></i>Ganti Password
            </h5>
            <span class="close" onclick="closePasswordModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="change_password.php" id="passwordForm">
                <div class="form-group">
                    <label class="form-label">Password Lama</label>
                    <input type="password" class="form-control" name="old_password" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <input type="password" class="form-control" name="new_password" id="new_password" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" onclick="closePasswordModal()">Batal</button>
                    <button type="submit" class="btn btn-edit">
                        <i class="fas fa-key me-2"></i>Ganti Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Upload Avatar -->
<div id="avatarModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="mb-0">
                <i class="fas fa-camera me-2"></i>Ganti Foto Profile
            </h5>
            <span class="close" onclick="closeAvatarModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="upload_avatar.php" enctype="multipart/form-data" id="avatarForm">
                <div class="form-group text-center">
                    <img src="<?= !empty($user_data['foto']) ? '../uploads/users/' . $user_data['foto'] : '../assets/img/default-avatar.png'; ?>" 
                         alt="Preview" 
                         id="avatarPreview"
                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 20px;">
                </div>
                <div class="form-group">
                    <label class="form-label">Pilih Foto</label>
                    <input type="file" class="form-control" name="avatar" accept="image/*" onchange="previewAvatar(event)" required>
                    <small class="text-muted">Format: JPG, PNG, GIF (Max 2MB)</small>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" onclick="closeAvatarModal()">Batal</button>
                    <button type="submit" class="btn btn-edit">
                        <i class="fas fa-upload me-2"></i>Upload Foto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modal functions
function openEditModal() {
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function openPasswordModal() {
    document.getElementById('passwordModal').style.display = 'block';
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
}

function openAvatarModal() {
    document.getElementById('avatarModal').style.display = 'block';
}

function closeAvatarModal() {
    document.getElementById('avatarModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Preview avatar
function previewAvatar(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}

// Validate password form
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Password baru dan konfirmasi password tidak cocok!');
    }
});
</script>

<?php include '../views/'.$THEME.'/footer.php'; ?>