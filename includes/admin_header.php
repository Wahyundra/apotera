<?php
// Include config and functions (which handles database connection)
// Determine the correct path based on where the file is included from
if (file_exists('config.php')) {
    include_once 'config.php';
    include_once 'functions.php';
} elseif (file_exists('../config.php')) {
    include_once '../config.php';
    include_once '../functions.php';
} elseif (file_exists('../../config.php')) {
    include_once '../../config.php';
    include_once '../../functions.php';
} else {
    die('Configuration files not found. Please check your file paths.');
}

// Start session (only if not already started)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Apotera</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                        secondary: '#3B82F6',
                        accent: '#8B5CF6',
                        dark: '#1F2937',
                        light: '#F9FAFB'
                    }
                }
            }
        }
    </script>
    <style>
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .main-content {
            flex: 1;
            margin-top: 100px; /* Account for navbar height */
        }
        .navbar {
            background-color: #1F2937;
            color: white;
            height: 80px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        .navbar-menu {
            display: flex;
            align-items: center;
        }
        .nav-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #D1D5DB;
            text-decoration: none;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
            margin: 0 5px;
            border-radius: 5px;
        }
        .nav-item:hover, .nav-item.active {
            background-color: #374151;
            color: white;
            border-bottom: 3px solid #10B981;
        }
        .nav-text {
            margin-left: 8px;
        }
        .dropdown {
            position: relative;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #374151;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 60;
            border-radius: 5px;
            padding: 10px 0;
        }
        .dropdown-menu .nav-item {
            display: block;
            padding: 8px 20px;
            border: none;
            border-radius: 0;
        }
        .dropdown-menu .nav-item:hover {
            background-color: #4B5563;
            border: none;
        }
        .mobile-menu-btn {
            display: none;
        }
        @media (max-width: 1024px) {
            .navbar-menu {
                display: none;
                position: absolute;
                top: 80px;
                left: 0;
                right: 0;
                background-color: #1F2937;
                flex-direction: column;
                align-items: flex-start;
                padding: 20px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .navbar-menu.show {
                display: flex;
            }
            .nav-item {
                width: 100%;
                margin: 5px 0;
            }
            .dropdown-menu {
                position: static;
                width: 100%;
                margin-left: 20px;
            }
            .mobile-menu-btn {
                display: block;
            }
            .main-content {
                margin-top: 80px;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="navbar">
        <div class="flex items-center justify-between w-full px-4">
            <div class="flex items-center">
                <div class="navbar-brand">
                    <i class="fas fa-heartbeat text-primary text-2xl"></i>
                    <h1 class="text-xl font-bold ml-2">Apotera Admin</h1>
                </div>

                <div class="navbar-menu ml-10">
                    <!-- Dashboard -->
                    <a href="index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>

                    <!-- User Management -->
                    <a href="manage-users.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'manage-users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Kelola Pengguna</span>
                    </a>

                    <!-- Consultation Management -->
                    <a href="manage-consultations.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'manage-consultations.php' ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i>
                        <span class="nav-text">Konsultasi</span>
                    </a>

                    <!-- Health Assessments -->
                    <div class="dropdown">
                        <a href="#" class="nav-item">
                            <i class="fas fa-heartbeat"></i>
                            <span class="nav-text">Cek Kesehatan</span>
                            <i class="fas fa-chevron-down ml-1"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="manage-bmi.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'manage-bmi.php' ? 'active' : ''; ?>">
                                <i class="fas fa-calculator"></i>
                                <span class="nav-text">BMI</span>
                            </a>
                            <a href="manage-depression.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'manage-depression.php' ? 'active' : ''; ?>">
                                <i class="fas fa-brain"></i>
                                <span class="nav-text">Depresi</span>
                            </a>
                            <a href="manage-heart-risk.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'manage-heart-risk.php' ? 'active' : ''; ?>">
                                <i class="fas fa-heart"></i>
                                <span class="nav-text">Risiko Jantung</span>
                            </a>
                        </div>
                    </div>

                    <!-- Product Management -->
                    <a href="manage-products.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'manage-products.php' ? 'active' : ''; ?>">
                        <i class="fas fa-pills"></i>
                        <span class="nav-text">Produk Obat</span>
                    </a>

                    <!-- Order Management -->
                    <a href="manage-orders.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'manage-orders.php' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="nav-text">Pesanan</span>
                    </a>

                    <!-- Admin Info -->
                    <div class="flex items-center ml-6 border-l border-gray-600 pl-6">
                        <span class="text-gray-300 mr-4">Halo, Admin</span>
                        <a href="../logout.php" class="bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 transition text-sm">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>

            <button id="mobile-menu-btn" class="mobile-menu-btn text-white">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-content transition-all duration-300">
        <!-- Top Navigation Bar -->
        <header class="bg-white shadow-md">
            <div class="flex justify-between items-center p-4">
                <div>
                    <h1 class="text-xl font-semibold text-dark">
                        <?php
                        // Get page title based on current file
                        $page = basename($_SERVER['PHP_SELF']);
                        switch($page) {
                            case 'index.php': echo 'Dashboard'; break;
                            case 'manage-users.php': echo 'Kelola Pengguna'; break;
                            case 'manage-consultations.php': echo 'Kelola Konsultasi'; break;
                            case 'manage-bmi.php': echo 'Kelola Data BMI'; break;
                            case 'manage-depression.php': echo 'Kelola Data Depresi'; break;
                            case 'manage-heart-risk.php': echo 'Kelola Data Risiko Jantung'; break;
                            case 'manage-products.php': echo 'Kelola Produk'; break;
                            case 'manage-orders.php': echo 'Kelola Pesanan'; break;
                            default: echo 'Admin Panel';
                        }
                        ?>
                    </h1>
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Selamat Bekerja!</span>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-6">