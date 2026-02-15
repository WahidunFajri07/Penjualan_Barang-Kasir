<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);
session_start();
require_once '../lib/auth.php';
requireAuth();

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['message'] = 'Semua field harus diisi';
        $_SESSION['message_type'] = 'error';
        header("Location: profile.php");
        exit;
    }
    
    // Validasi password baru dan konfirmasi
    if ($new_password !== $confirm_password) {
        $_SESSION['message'] = 'Password baru dan konfirmasi password tidak cocok';
        $_SESSION['message_type'] = 'error';
        header("Location: profile.php");
        exit;
    }
    
    // Validasi panjang password minimal 6 karakter
    if (strlen($new_password) < 6) {
        $_SESSION['message'] = 'Password baru minimal 6 karakter';
        $_SESSION['message_type'] = 'error';
        header("Location: profile.php");
        exit;
    }
    
    // Get current password hash
    $query = "SELECT password FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
    
    // Verify old password
    if (!password_verify($old_password, $user['password'])) {
        $_SESSION['message'] = 'Password lama tidak sesuai';
        $_SESSION['message_type'] = 'error';
        header("Location: profile.php");
        exit;
    }
    
    // Hash new password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $update_query = "UPDATE users SET password = '$new_password_hash' WHERE id = $user_id";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = 'Password berhasil diubah';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Gagal mengubah password: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
    
    header("Location: profile.php");
    exit;
}

// Jika bukan POST, redirect ke profile
header("Location: profile.php");
exit;
?>
