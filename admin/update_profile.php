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
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    // Validasi input
    if (empty($username)) {
        $_SESSION['message'] = 'Username tidak boleh kosong';
        $_SESSION['message_type'] = 'error';
        header("Location: profile.php");
        exit;
    }
    
    // Cek apakah username sudah digunakan user lain
    $check_query = "SELECT id FROM users WHERE username = '$username' AND id != $user_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['message'] = 'Username sudah digunakan oleh user lain';
        $_SESSION['message_type'] = 'error';
        header("Location: profile.php");
        exit;
    }
    
    // Update username
    $update_query = "UPDATE users SET username = '$username' WHERE id = $user_id";
    
    if (mysqli_query($conn, $update_query)) {
        // Update session
        $_SESSION['username'] = $username;
        
        $_SESSION['message'] = 'Profile berhasil diperbarui';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Gagal memperbarui profile: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
    
    header("Location: profile.php");
    exit;
}

// Jika bukan POST, redirect ke profile
header("Location: profile.php");
exit;
?>
