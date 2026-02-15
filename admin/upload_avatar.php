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
    
    // Validasi file upload
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] != 0) {
        $_SESSION['message'] = 'Tidak ada file yang diupload atau terjadi error';
        $_SESSION['message_type'] = 'error';
        header("Location: profile.php");
        exit;
    }
    
    $file = $_FILES['avatar'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // Get file extension
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Allowed extensions
    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
    
    // Validasi extension
    if (!in_array($file_ext, $allowed_extensions)) {
        $_SESSION['message'] = 'Format file tidak diizinkan. Gunakan JPG, PNG, atau GIF';
        $_SESSION['message_type'] = 'error';
        header("Location: profile.php");
        exit;
    }
    
    // Validasi ukuran file (max 2MB)
    if ($file_size > 2097152) {
        $_SESSION['message'] = 'Ukuran file terlalu besar. Maksimal 2MB';
        $_SESSION['message_type'] = 'error';
        header("Location: profile.php");
        exit;
    }
    
    // Buat nama file unik
    $new_file_name = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
    
    // Tentukan folder upload
    $upload_dir = '../uploads/users/';
    
    // Buat folder jika belum ada
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $upload_path = $upload_dir . $new_file_name;
    
    // Get old avatar untuk dihapus
    $query = "SELECT foto FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
    $old_avatar = $user['foto'];
    
    // Upload file
    if (move_uploaded_file($file_tmp, $upload_path)) {
        // Update database
        $update_query = "UPDATE users SET foto = '$new_file_name' WHERE id = $user_id";
        
        if (mysqli_query($conn, $update_query)) {
            // Hapus foto lama jika ada
            if (!empty($old_avatar) && file_exists($upload_dir . $old_avatar)) {
                unlink($upload_dir . $old_avatar);
            }
            
            $_SESSION['message'] = 'Foto profile berhasil diupload';
            $_SESSION['message_type'] = 'success';
        } else {
            // Hapus file yang baru diupload jika gagal update database
            unlink($upload_path);
            
            $_SESSION['message'] = 'Gagal menyimpan ke database: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = 'Gagal mengupload file';
        $_SESSION['message_type'] = 'error';
    }
    
    header("Location: profile.php");
    exit;
}

// Jika bukan POST, redirect ke profile
header("Location: profile.php");
exit;
?>
