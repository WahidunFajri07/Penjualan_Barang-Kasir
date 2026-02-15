<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
ini_set('session.use_strict_mode', 1);
session_start();
require_once 'lib/auth.php';
require_once 'lib/functions.php';
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid request. CSRF token mismatch.');
    }
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    // jika tidak ada pilihan role maka role akan diisi dengan role: admin
    $role = $_POST['role'] ?? 'mahasiswa';
    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Validate password strength
        $passwordErrors = validatePassword($password, false); // ganti menjadi: false agar bebas membuat password
        if (!empty($passwordErrors)) {
            $error = implode('', $passwordErrors);
        } else {
            if (registerUser($username, $password, $role)) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Username already exists or registration failed.";
            }
        }
    }
}
$csrfToken = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Fash-Cashier</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary-start: #667eea;
            --primary-end: #764ba2;
            --success-start: #11998e;
            --success-end: #38ef7d;
            --info-start: #4facfe;
            --info-end: #00f2fe;
            --warning-start: #f093fb;
            --warning-end: #f5576c;
            --white: #ffffff;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-700: #495057;
            --gray-900: #212529;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f2f5 50%, #e6e9f0 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.04) 0%, transparent 50%),
                              radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.03) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .auth-card {
            border-radius: 1.5rem;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.1), 
                        0 8px 20px rgba(118, 75, 162, 0.07);
            border: 1px solid rgba(102, 126, 234, 0.1);
            overflow: hidden;
            max-width: 850px;
            margin: auto;
            backdrop-filter: blur(8px);
            background: var(--white);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .auth-card:hover {
            box-shadow: 0 20px 45px rgba(102, 126, 234, 0.13),
                        0 10px 25px rgba(118, 75, 162, 0.09);
            transform: translateY(-3px);
        }
        
        .auth-left {
            background: linear-gradient(145deg, var(--primary-start) 0%, #6b67e8 50%, var(--primary-end) 100%);
            color: var(--white);
            padding: 2.25rem 2rem;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .auth-left::before {
            content: '';
            position: absolute;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            top: -80px;
            right: -80px;
        }
        
        .auth-left::after {
            content: '';
            position: absolute;
            width: 160px;
            height: 160px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -40px;
            left: -40px;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 50%;
            animation: float 6s infinite ease-in-out;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); opacity: 0.2; }
            50% { transform: translateY(-25px) translateX(15px); opacity: 0.5; }
        }
        
        .auth-right {
            padding: 2.25rem 2rem;
            background: var(--white);
            position: relative;
        }
        
        .form-control, .form-select {
            border-radius: 0.875rem;
            border: 2px solid var(--gray-200);
            padding: 0.65rem 1rem;
            background: var(--white);
            transition: all 0.3s ease;
            font-size: 0.925rem;
            height: 44px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-start);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.12);
            background: var(--white);
            transform: translateY(-1px);
        }
        
        .form-control::placeholder {
            color: var(--gray-400);
            font-size: 0.9rem;
        }
        
        .btn-primary-soft {
            background: linear-gradient(135deg, var(--primary-start) 0%, #6b67e8 50%, var(--primary-end) 100%);
            border: none;
            border-radius: 0.875rem;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            color: var(--white);
            transition: all 0.3s ease;
            box-shadow: 0 3px 12px rgba(102, 126, 234, 0.28);
            position: relative;
            overflow: hidden;
            font-size: 0.95rem;
            height: 48px;
        }
        
        .btn-primary-soft::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary-soft:hover::before {
            left: 100%;
        }
        
        .btn-primary-soft:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(102, 126, 234, 0.35);
            background: linear-gradient(135deg, #5a6edf 0%, #6365e3 50%, #6d5dd8 100%);
        }
        
        .btn-primary-soft:active {
            transform: translateY(-1px);
        }
        
        .text-primary-gradient { 
            background: linear-gradient(135deg, var(--primary-start) 0%, var(--primary-end) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }
        
        .illustration-img {
            max-width: 100%;
            max-height: 280px;
            margin: 1.25rem auto 0;
            border-radius: 1.25rem;
            box-shadow: 0 12px 32px rgba(0,0,0,0.1);
            object-fit: cover;
            border: 3px solid rgba(255,255,255,0.35);
            position: relative;
            z-index: 1;
            transition: all 0.35s ease;
        }
        
        .illustration-img:hover {
            transform: scale(1.015);
            box-shadow: 0 16px 40px rgba(0,0,0,0.13);
        }
        
        h2 { 
            margin-bottom: 0.4rem;
            position: relative;
            z-index: 1;
            font-weight: 700;
            font-size: 1.85rem;
            letter-spacing: -0.4px;
        }
        
        h3 { 
            margin-bottom: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            font-size: 1.65rem;
        }
        
        .auth-left p {
            position: relative;
            z-index: 1;
            opacity: 0.95;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            color: var(--gray-700);
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.45rem;
        }
        
        .alert {
            border-radius: 0.875rem;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
            border: 1.5px solid #ffcccc;
            color: #c53030;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1.5px solid #86efac;
            color: #166534;
        }
        
        a.text-primary-gradient:hover {
            opacity: 0.88;
            text-decoration: underline;
        }
        
        a.text-muted:hover {
            color: var(--primary-start) !important;
        }
        
        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            margin-bottom: 1.25rem;
        }
        
        .brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary-start) 0%, var(--primary-end) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.3);
        }
        
        .brand-text {
            font-size: 1.45rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-start) 0%, var(--primary-end) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .badge-new {
            background: linear-gradient(135deg, var(--success-start) 0%, var(--success-end) 100%);
            font-size: 0.6rem;
            padding: 0.2rem 0.65rem;
            border-radius: 8px;
            margin-left: 0.4rem;
            font-weight: 600;
        }
        
        .footer-text {
            font-size: 0.825rem;
            color: var(--gray-600);
            margin-top: 1.5rem;
            text-align: center;
        }
        
        .btn-loading {
            position: relative;
            pointer-events: none;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 18px;
            height: 18px;
            margin: -9px 0 0 -9px;
            border: 2.5px solid rgba(255, 255, 255, 0.3);
            border-top-color: var(--white);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group-text {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
            background: transparent;
            border: none;
            pointer-events: none;
            font-size: 0.95rem;
        }
        
        .form-control.has-icon {
            padding-left: 2.35rem;
        }
        
        .form-select.has-icon {
            padding-left: 2.35rem;
        }
        
        .toggle-password-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--gray-500);
            font-size: 0.95rem;
            padding: 0;
            width: auto;
            height: auto;
        }
        
        .toggle-password-btn:hover {
            color: var(--primary-start);
        }
        
        .form-text {
            font-size: 0.8rem;
            color: var(--gray-600);
            margin-top: 0.25rem;
        }
        
        @media (max-width: 991.98px) {
            .auth-left {
                display: none;
            }
            
            .auth-card {
                max-width: 420px;
            }
            
            .auth-right {
                padding: 2rem 1.75rem;
            }
            
            h3 {
                font-size: 1.55rem;
                margin-bottom: 1rem;
            }
        }
        
        @media (max-width: 575.98px) {
            .auth-card {
                border-radius: 1.25rem;
            }
            
            .auth-right {
                padding: 1.75rem 1.5rem;
            }
            
            h3 {
                font-size: 1.45rem;
            }
            
            .btn-primary-soft {
                padding: 0.7rem 1.2rem;
                font-size: 0.925rem;
                height: 46px;
            }
            
            .illustration-img {
                max-height: 240px;
                border-radius: 1rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card auth-card">
                <div class="row g-0">
                    <!-- Kiri: Selamat Datang -->
                    <div class="col-lg-6 d-none d-lg-flex auth-left">
                        <div class="w-100 text-center">
                            <div class="brand-logo mb-3">
                                <div class="brand-icon">
                                    <i class="fas fa-cash-register fa-lg text-white"></i>
                                </div>
                                <span class="brand-text">Fash-Cashier</span>
                            </div>
                            
                            <h2 class="fw-bold mb-2">Bergabung Bersama Kami</h2>
                            <p class="opacity-95 mb-3">
                                <i class="fas fa-user-plus me-2"></i>
                                Daftar dan mulai kelola kasir Anda
                            </p>
                            
                            <img src="/fash-cashier/uploads/login/minimarket.jpg"
                                 alt="Minimarket Illustration"
                                 class="illustration-img"
                                 onerror="this.src='https://via.placeholder.com/350x250/f8f9fa/667eea?text=Fash-Cashier'">
                            
                            <div class="mt-3">
                                <span class="badge badge-new">
                                    <i class="fas fa-star me-1"></i>
                                    2026
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Form Register -->
                    <div class="col-lg-6 auth-right">
                        <div class="w-100">
                            <!-- Brand di mobile -->
                            <div class="d-lg-none text-center mb-3">
                                <div class="brand-logo">
                                    <div class="brand-icon">
                                        <i class="fas fa-cash-register fa-lg text-white"></i>
                                    </div>
                                    <span class="brand-text">Fash-Cashier</span>
                                </div>
                            </div>
                            
                            <h3 class="text-center fw-bold text-primary-gradient">Buat Akun Baru</h3>
                            <p class="text-center text-muted mb-3" style="font-size: 0.95rem;">Silakan lengkapi data untuk mendaftar</p>

                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?= htmlspecialchars($error) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size: 0.75rem;"></button>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?= htmlspecialchars($success) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size: 0.75rem;"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" id="registerForm">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">

                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-user me-1 text-primary"></i>
                                        Username
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" name="username" class="form-control has-icon"
                                               placeholder="Masukkan username" required autofocus>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-lock me-1 text-primary"></i>
                                        Password
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" name="password" class="form-control has-icon"
                                               placeholder="Masukkan password" required id="passwordInput">
                                        <button type="button" class="toggle-password-btn" 
                                                id="togglePassword"
                                                onclick="togglePasswordVisibility()">
                                            <i class="fas fa-eye" id="eyeIcon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Must be 8+ chars, with uppercase, lowercase, and number.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-user-tag me-1 text-primary"></i>
                                        Role
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user-tag"></i>
                                        </span>
                                        <select name="role" class="form-select has-icon">
                                            <option value="admin">Admin</option>
                                            <option value="dosen">Kasir1</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary-soft w-100 mt-2" id="registerBtn">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Daftar Sekarang
                                </button>

                                <div class="text-center mt-2">
                                    <small class="text-muted">
                                        Sudah punya akun? 
                                        <a href="login.php" class="text-primary-gradient fw-medium">
                                            <i class="fas fa-sign-in-alt me-1"></i>
                                            Masuk
                                        </a>
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-text">
                <i class="fas fa-shield-alt me-1"></i>
                © 2026. All rights reserved.
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Toggle password visibility
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}

// Add loading state to register button
document.getElementById('registerForm').addEventListener('submit', function() {
    const registerBtn = document.getElementById('registerBtn');
    registerBtn.classList.add('btn-loading');
    registerBtn.disabled = true;
    registerBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sedang Memproses...';
});

// Create floating particles
document.addEventListener('DOMContentLoaded', function() {
    const authLeft = document.querySelector('.auth-left');
    if (authLeft) {
        for (let i = 0; i < 6; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.width = Math.random() * 6 + 'px';
            particle.style.height = particle.style.width;
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 5 + 's';
            authLeft.appendChild(particle);
        }
    }
});
</script>

</body>
</html>