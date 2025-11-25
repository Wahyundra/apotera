<?php
include_once 'config.php';
include_once 'functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set no container for this page to allow full-width sections
$no_container = true;

// Check for cart messages
$cart_message = '';
if (isset($_SESSION['cart_message'])) {
    $cart_message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']); // Clear the message after displaying
}

include_once 'includes/header.php';
?>

<style>
    .hero-section {
        background: linear-gradient(135deg, #10B981 0%, #3B82F6 100%);
        min-height: 500px;
        display: flex;
        align-items: center;
    }

    .feature-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container mx-auto px-4 py-16">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 text-white mb-10 md:mb-0">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Layanan Kesehatan Terlengkap</h1>
                <p class="text-xl mb-8">Konsultasi dokter, pembelian obat, dan cek kesehatan mandiri hanya dalam satu platform.</p>

                <div class="flex flex-wrap gap-4">
                    <a href="consultation.php" class="bg-white text-primary px-6 py-3 rounded-md font-semibold hover:bg-gray-100 transition">
                        Konsultasi Sekarang
                    </a>
                    <a href="health-store.php" class="bg-transparent border-2 border-white text-white px-6 py-3 rounded-md font-semibold hover:bg-white hover:text-primary transition">
                        Beli Obat
                    </a>
                </div>
            </div>

            <div class="md:w-1/2 flex justify-center">
                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-8 max-w-md w-full">
                    <h2 class="text-2xl font-bold text-white mb-6">Cek Kesehatan Mandiri</h2>

                    <div class="space-y-4">
                        <a href="bmi-calculator.php" class="block bg-white text-dark p-4 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-center">
                                <i class="fas fa-calculator text-2xl text-primary mr-4"></i>
                                <span class="font-medium">Kalkulator BMI</span>
                            </div>
                        </a>

                        <a href="depression-test.php" class="block bg-white text-dark p-4 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-center">
                                <i class="fas fa-brain text-2xl text-primary mr-4"></i>
                                <span class="font-medium">Cek Depresi</span>
                            </div>
                        </a>

                        <a href="heart-risk.php" class="block bg-white text-dark p-4 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-center">
                                <i class="fas fa-heartbeat text-2xl text-primary mr-4"></i>
                                <span class="font-medium">Risiko Jantung</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-dark mb-4">Layanan Kami</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Platform Doktera menyediakan berbagai layanan kesehatan untuk membantu Anda menjaga kesehatan dengan mudah dan aman.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 justify-items-center">
            <!-- Feature 1 -->
            <div class="feature-card bg-white p-6 rounded-xl shadow-md border border-gray-100">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-user-md text-primary text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-dark mb-3">Konsultasi</h3>
                <p class="text-gray-600">Konsultasikan keluhan kesehatan atau seputar obat Anda secara online.</p>
            </div>

            <!-- Feature 2 -->
            <div class="feature-card bg-white p-6 rounded-xl shadow-md border border-gray-100">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-pills text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-dark mb-3">Toko Kesehatan</h3>
                <p class="text-gray-600">Temukan berbagai obat dan produk kesehatan dengan harga terjangkau.</p>
            </div>

            <!-- Feature 3 -->
            <div class="feature-card bg-white p-6 rounded-xl shadow-md border border-gray-100">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-heartbeat text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-dark mb-3">Cek Kesehatan</h3>
                <p class="text-gray-600">Lakukan cek kesehatan mandiri dengan berbagai alat bantu yang tersedia.</p>
            </div>

        </div>
    </div>
</section>

<?php if ($cart_message): ?>
    <div class="max-w-4xl mx-auto mb-6 px-4">
        <div class="p-4 <?php echo strpos($cart_message, 'berhasil') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> rounded-md">
            <?php echo $cart_message; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Popular Products Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-10">
            <h2 class="text-3xl font-bold text-dark">Produk Terlaris</h2>
            <a href="health-store.php" class="text-primary hover:underline font-medium">Lihat Semua ></a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php
            // Get popular products
            $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 4");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($products as $product):
            ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <?php if ($product['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-pills text-5xl text-gray-400"></i>
                        </div>
                    <?php endif; ?>

                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-dark mb-1"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gray-600 text-sm mb-3"><?php echo htmlspecialchars(substr($product['description'], 0, 80)) . (strlen($product['description']) > 80 ? '...' : ''); ?></p>

                        <div class="flex justify-between items-center">
                            <span class="text-primary font-bold">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>

                            <?php if ($product['stock'] > 0): ?>
                                <span class="text-green-600 text-sm">Tersedia</span>
                            <?php else: ?>
                                <span class="text-red-600 text-sm">Habis</span>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form method="POST" action="cart.php" class="w-full">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="add_to_cart" value="1">
                                    <input type="hidden" name="redirect_to_store" value="1">
                                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-md hover:bg-green-600 transition text-sm">
                                        <i class="fas fa-shopping-cart mr-1"></i> Beli Sekarang
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="w-full block bg-primary text-white py-2 rounded-md hover:bg-green-600 transition text-center text-sm">
                                    <i class="fas fa-shopping-cart mr-1"></i> Beli Sekarang
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-dark mb-4">Mengapa Memilih Apotera</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Kami menyediakan layanan kesehatan terpadu yang mengutamakan kemudahan, kenyamanan, dan keamanan Anda.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div class="text-center">
                <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-shield-alt text-primary text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-dark mb-3">Aman & Terpercaya</h3>
                <p class="text-gray-600">Semua produk dan layanan kami telah diverifikasi oleh tenaga medis profesional.</p>
            </div>

            <div class="text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-hand-holding-heart text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-dark mb-3">Mudah Digunakan</h3>
                <p class="text-gray-600">Antarmuka yang ramah pengguna membuat pengalaman kesehatan Anda lebih menyenangkan.</p>
            </div>

            <div class="text-center">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-headset text-blue-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-dark mb-3">Layanan 24/7</h3>
                <p class="text-gray-600">Tim kami siap membantu Anda kapan saja, di mana saja.</p>
            </div>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>