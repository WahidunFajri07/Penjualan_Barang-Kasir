<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Fash-Cashier - Sistem Kasir Modern untuk Bisnis Anda">
  <meta name="author" content="Fash-Cashier Team">
  <meta name="theme-color" content="#667eea">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  
  <!-- Open Graph Meta Tags -->
  <meta property="og:title" content="Fash-Cashier - <?= $page_title ?? 'Sistem Kasir'; ?>">
  <meta property="og:description" content="Sistem Kasir Modern untuk Bisnis Anda">
  <meta property="og:type" content="website">
  
  <!-- Preconnect & Preload untuk Performance -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" as="style">
  <link rel="preload" href="<?= base_url('assets/soft-ui/css/soft-ui-dashboard.css?v=1.1.0'); ?>" as="style">
  
  <!-- Icons -->
  <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/soft-ui/img/apple-icon.png'); ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/soft-ui/img/favicon.png'); ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/soft-ui/img/favicon.png'); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">


  
  <title>
    📊 Fash-Cashier - <?= $page_title ?? 'Sistema Kasir Modern'; ?>
  </title>
  
  <!-- Google Fonts - Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Nucleo Icons -->
  <link href="https://demos.creative-tim.com/soft-ui-dashboard/assets/css/nucleo-icons.css" rel="stylesheet">
  <link href="https://demos.creative-tim.com/soft-ui-dashboard/assets/css/nucleo-svg.css" rel="stylesheet">
  
  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  
  <!-- Main CSS -->
  <link id="pagestyle" href="<?= base_url('assets/soft-ui/css/soft-ui-dashboard.css?v=1.1.0'); ?>" rel="stylesheet">
  
  <!-- Custom CSS - Enhanced -->
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      --transition-slow: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      --transition-fast: all 0.2s ease;
      --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
      --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
      --shadow-lg: 0 8px 32px rgba(0,0,0,0.15);
      --shadow-xl: 0 20px 40px rgba(0,0,0,0.2);
    }

    /* Smooth Scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }
    
    ::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg, #667eea, #764ba2);
      border-radius: 10px;
      transition: var(--transition-fast);
    }
    
    ::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(135deg, #764ba2, #667eea);
    }

    /* Body Enhancement */
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      transition: var(--transition-slow);
      overflow-x: hidden;
      background-color: #f8f9fa !important;
    }
    
    body.g-sidenav-show {
      animation: fadeIn 0.5s ease-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Custom Cursor Pointer */
    .cursor-pointer { 
      cursor: pointer; 
      transition: var(--transition-fast);
    }
    
    .cursor-pointer:hover {
      opacity: 0.85;
    }

    /* Enhanced Notification Badge */
    .notification-badge {
      position: absolute;
      top: -6px;
      right: -6px;
      font-size: 0.65rem;
      font-weight: 700;
      padding: 2px 6px;
      border-radius: 10px;
      background: var(--warning-gradient);
      color: white;
      box-shadow: var(--shadow-md);
      animation: pulse 2s infinite;
      min-width: 18px;
      text-align: center;
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.1); opacity: 0.8; }
    }

    /* Quick Action Button Enhancement */
    .quick-action-btn {
      transition: var(--transition-slow);
      border-radius: 12px;
      position: relative;
      overflow: hidden;
      box-shadow: var(--shadow-sm);
    }
    
    .quick-action-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }
    
    .quick-action-btn:hover::before {
      left: 100%;
    }
    
    .quick-action-btn:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
    }
    
    .quick-action-btn:active {
      transform: translateY(-2px);
    }

    /* Loading States */
    .loading {
      position: relative;
      overflow: hidden;
    }
    
    .loading::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
      to { left: 100%; }
    }

    /* Enhanced Cards */
    .card {
      border: none;
      border-radius: 16px !important;
      box-shadow: var(--shadow-md);
      transition: var(--transition-slow);
      overflow: hidden;
    }
    
    .card:hover {
      box-shadow: var(--shadow-lg);
    }

    /* Gradient Text */
    .gradient-text {
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Custom Scrollbar for Tables */
    .table-responsive {
      scrollbar-width: thin;
      scrollbar-color: #667eea #f1f1f1;
    }

    /* Button Enhancement */
    .btn {
      border-radius: 10px !important;
      transition: var(--transition-slow);
      text-transform: capitalize;
      font-weight: 500;
      padding: 0.625rem 1.5rem;
      box-shadow: var(--shadow-sm);
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }
    
    .btn:active {
      transform: translateY(0);
    }
    
    .btn-primary {
      background: var(--primary-gradient) !important;
      border: none !important;
    }
    
    .btn-success {
      background: var(--success-gradient) !important;
      border: none !important;
    }
    
    .btn-info {
      background: var(--info-gradient) !important;
      border: none !important;
    }
    
    .btn-warning {
      background: var(--warning-gradient) !important;
      border: none !important;
    }

    /* Form Controls Enhancement */
    .form-control, .form-select {
      border-radius: 10px !important;
      border: 1px solid #e9ecef !important;
      padding: 0.625rem 1rem !important;
      transition: var(--transition-fast);
    }
    
    .form-control:focus, .form-select:focus {
      border-color: #667eea !important;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
      transform: translateY(-1px);
    }

    /* Table Enhancement */
    .table {
      border-radius: 12px;
      overflow: hidden;
    }
    
    .table thead th {
      background: var(--primary-gradient);
      color: white;
      font-weight: 600;
      border: none;
    }
    
    .table tbody tr {
      transition: var(--transition-fast);
    }
    
    .table tbody tr:hover {
      background-color: rgba(102, 126, 234, 0.05);
      transform: translateX(5px);
    }

    /* Sidebar Enhancement */
    .sidenav {
      box-shadow: var(--shadow-lg);
      border-radius: 0 16px 16px 0 !important;
    }
    
    .sidenav .nav-link {
      transition: var(--transition-fast);
      border-radius: 10px !important;
      margin: 4px 10px !important;
    }
    
    .sidenav .nav-link:hover {
      background: rgba(102, 126, 234, 0.1) !important;
      transform: translateX(5px);
    }
    
    .sidenav .nav-link.active {
      background: var(--primary-gradient) !important;
      color: white !important;
    }

    /* Navbar Enhancement */
    .navbar {
      box-shadow: var(--shadow-md);
      backdrop-filter: blur(10px);
      background-color: rgba(255, 255, 255, 0.95) !important;
    }
    
    .navbar-blur {
      background-color: rgba(255, 255, 255, 0.9) !important;
    }

    /* Badge Enhancement */
    .badge {
      border-radius: 8px !important;
      padding: 0.4em 0.8em !important;
      font-weight: 600;
    }

    /* Modal Enhancement */
    .modal-content {
      border-radius: 16px !important;
      border: none;
      box-shadow: var(--shadow-xl);
    }
    
    .modal-header {
      border-radius: 16px 16px 0 0 !important;
      border-bottom: none !important;
      padding: 1.5rem;
    }
    
    .modal-footer {
      border-top: none !important;
      padding: 1.5rem;
    }

    /* Toast Notification Enhancement */
    .toast {
      border-radius: 12px !important;
      box-shadow: var(--shadow-lg);
      border: none;
      backdrop-filter: blur(10px);
    }

    /* Progress Bar Enhancement */
    .progress {
      border-radius: 10px !important;
      overflow: hidden;
      height: 10px;
    }
    
    .progress-bar {
      transition: width 0.6s ease !important;
    }

    /* Dropdown Enhancement */
    .dropdown-menu {
      border-radius: 12px !important;
      box-shadow: var(--shadow-lg);
      border: none;
      padding: 0.5rem 0;
      animation: dropdownSlide 0.3s ease-out;
      margin-top: 8px !important;
    }
    
    @keyframes dropdownSlide {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .dropdown-item {
      transition: var(--transition-fast);
      padding: 0.625rem 1.5rem !important;
    }
    
    .dropdown-item:hover {
      background-color: rgba(102, 126, 234, 0.1);
      transform: translateX(5px);
    }

    /* Input Group Enhancement */
    .input-group-text {
      border-radius: 10px 0 0 10px !important;
      background-color: #f8f9fa;
      border-right: none !important;
    }

    /* Breadcrumb Enhancement */
    .breadcrumb {
      background: transparent !important;
      padding: 0 !important;
    }
    
    .breadcrumb-item a {
      color: #6c757d;
      transition: var(--transition-fast);
    }
    
    .breadcrumb-item a:hover {
      color: #667eea;
    }
    
    .breadcrumb-item.active {
      color: #667eea !important;
      font-weight: 600;
    }

    /* Responsive Enhancement */
    @media (max-width: 768px) {
      .card {
        border-radius: 12px !important;
      }
      
      .btn {
        padding: 0.5rem 1rem;
      }
      
      .quick-action-btn {
        width: 100%;
        margin-bottom: 0.5rem;
      }
    }

    /* Print Styles */
    @media print {
      .no-print {
        display: none !important;
      }
      
      body {
        background-color: white !important;
      }
      
      .card {
        box-shadow: none !important;
        border: 1px solid #dee2e6;
      }
    }

    /* Dark Mode Support (Optional) */
    @media (prefers-color-scheme: dark) {
      body {
        background-color: #1a1a1a !important;
        color: #f8f9fa !important;
      }
    }

    /* Skeleton Loading */
    .skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: skeleton-loading 1.5s infinite;
    }
    
    @keyframes skeleton-loading {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    /* Custom Animation Classes */
    .fade-in {
      animation: fadeIn 0.6s ease-out forwards;
    }
    
    .fade-in-up {
      animation: fadeInUp 0.6s ease-out forwards;
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Enhanced Focus States */
    *:focus-visible {
      outline: 2px solid #667eea !important;
      outline-offset: 2px !important;
    }

    /* Custom Utilities */
    .text-gradient {
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .bg-gradient-primary {
      background: var(--primary-gradient) !important;
    }
    
    .bg-gradient-success {
      background: var(--success-gradient) !important;
    }
    
    .bg-gradient-info {
      background: var(--info-gradient) !important;
    }
    
    .bg-gradient-warning {
      background: var(--warning-gradient) !important;
    }

    /* Smooth Page Transitions */
    .page-transition {
      opacity: 1;
      transition: opacity 0.3s ease;
    }
    
    .page-transition.fade-out {
      opacity: 0;
    }

    /* Enhanced Link Styles */
    a {
      transition: var(--transition-fast);
      text-decoration: none;
    }
    
    a:hover {
      text-decoration: none;
    }
  </style>
</head>

<body class="g-sidenav-show bg-gray-100">