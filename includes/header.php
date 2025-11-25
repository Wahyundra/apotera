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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apotera - Konsultasi & Penjualan Obat</title>
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
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="page-wrapper">
        <!-- Header/Navbar -->
        <header class="bg-white shadow-md fixed top-0 left-0 right-0 z-50">
            <div class="container mx-auto px-4">
                <nav class="flex justify-between items-center py-4">
                    <!-- Logo -->
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-heartbeat text-primary text-2xl"></i>
                        <a href="index.php" class="text-2xl font-bold text-dark">Apotera</a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden md:flex items-center space-x-8">
                        <!-- Self Health Check Dropdown -->
                        <div class="relative dropdown">
                            <button class="flex items-center space-x-1 text-gray-700 hover:text-primary">
                                <span>Cek Kesehatan Mandiri</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>

                            <div class="dropdown-menu absolute hidden bg-white shadow-lg rounded-md mt-2 py-2 w-48 z-10">
                                <a href="bmi-calculator.php" class="block px-4 py-2 text-gray-700 hover:bg-primary hover:text-white">Kalkulator BMI</a>
                                <a href="depression-test.php" class="block px-4 py-2 text-gray-700 hover:bg-primary hover:text-white">Cek Depresi</a>
                                <a href="heart-risk.php" class="block px-4 py-2 text-gray-700 hover:bg-primary hover:text-white">Risiko Jantung</a>
                            </div>
                        </div>

                        <a href="consultation.php" class="text-gray-700 hover:text-primary">Konsultasi</a>
                        <a href="health-store.php" class="text-gray-700 hover:text-primary">Toko Kesehatan</a>
                    </div>

                    <!-- Auth Buttons -->
                    <div class="flex items-center space-x-4">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <span class="text-gray-700">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="admin/index.php" class="bg-accent text-white px-4 py-2 rounded-md hover:bg-purple-700 transition">Admin Panel</a>
                            <?php else: ?>
                                <a href="profile.php" class="bg-secondary text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">Profil</a>
                                <!-- Cart Dropdown -->
                                <div class="relative" id="cart-dropdown">
                                    <a href="cart.php" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition relative">
                                        <i class="fas fa-shopping-cart mr-1"></i> Keranjang
                                        <?php
                                        $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                                        if ($cart_count > 0): ?>
                                            <span class="cart-count absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                                <?php echo $cart_count; ?>
                                            </span>
                                        <?php endif; ?>
                                    </a>

                                    <!-- Cart Preview Dropdown -->
                                    <div class="cart-preview absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-2 hidden z-50 border border-gray-200">
                                        <div class="px-4 py-2 border-b border-gray-200">
                                            <h3 class="font-semibold text-dark">Keranjang Anda</h3>
                                        </div>
                                        <div class="max-h-64 overflow-y-auto">
                                            <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                                                <?php
                                                $total = 0;
                                                foreach ($_SESSION['cart'] as $item):
                                                    $subtotal = $item['price'] * $item['quantity'];
                                                    $total += $subtotal;
                                                ?>
                                                    <div class="flex items-center p-3 border-b border-gray-100 hover:bg-gray-50">
                                                        <?php if ($item['image_url']): ?>
                                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                                 class="w-10 h-10 object-cover rounded">
                                                        <?php else: ?>
                                                            <div class="w-10 h-10 bg-gray-200 flex items-center justify-center rounded">
                                                                <i class="fas fa-pills text-gray-500"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="ml-3 flex-grow">
                                                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars(substr($item['name'], 0, 30)) . (strlen($item['name']) > 30 ? '...' : ''); ?></p>
                                                            <p class="text-xs text-gray-500"><?php echo $item['quantity']; ?> x Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                                                        </div>
                                                        <div class="text-right">
                                                            <p class="text-sm font-semibold text-gray-900">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></p>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="p-4 text-center">
                                                    <i class="fas fa-shopping-cart text-3xl text-gray-300 mb-2"></i>
                                                    <p class="text-gray-500">Keranjang kosong</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                                            <div class="p-3 border-t border-gray-200">
                                                <div class="flex justify-between font-semibold">
                                                    <span>Total:</span>
                                                    <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                                                </div>
                                                <a href="cart.php" class="block mt-2 bg-primary text-white text-center py-2 rounded-md hover:bg-green-600 transition">
                                                    Lihat Keranjang
                                                </a>
                                                <a href="checkout.php" class="block mt-2 bg-secondary text-white text-center py-2 rounded-md hover:bg-blue-600 transition">
                                                    Checkout
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition">Logout</a>
                        <?php else: ?>
                            <a href="login.php" class="text-gray-700 hover:text-primary">Login</a>
                            <a href="register.php" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition">Daftar</a>
                        <?php endif; ?>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button id="mobile-menu-button" class="text-gray-700">
                            <i class="fas fa-bars text-2xl"></i>
                        </button>
                    </div>
                </nav>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden bg-white py-2 px-4">
                <div class="flex flex-col space-y-3">
                    <!-- Self Health Check Mobile -->
                    <div>
                        <button id="mobile-dropdown-button" class="flex justify-between items-center w-full text-gray-700">
                            <span>Cek Kesehatan Mandiri</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div id="mobile-dropdown-content" class="hidden ml-4 mt-2 space-y-2">
                            <a href="bmi-calculator.php" class="block text-gray-700 hover:text-primary">Kalkulator BMI</a>
                            <a href="depression-test.php" class="block text-gray-700 hover:text-primary">Cek Depresi</a>
                            <a href="heart-risk.php" class="block text-gray-700 hover:text-primary">Risiko Jantung</a>
                        </div>
                    </div>

                    <a href="consultation.php" class="text-gray-700 hover:text-primary">Konsultasi</a>
                    <a href="health-store.php" class="text-gray-700 hover:text-primary">Toko Kesehatan</a>
                </div>
            </div>
        </header>

        <main class="main-content pt-16">
            <!-- Main content will be inserted here -->
            <?php if (!isset($no_container)): ?>
                <div class="container mx-auto px-4 py-8">
            <?php endif; ?>

            <!-- Content will be added by individual pages -->
            <?php if (!isset($no_container)): ?>
                </div>
            <?php endif; ?>
        </main>