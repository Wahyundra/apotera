<?php
include_once 'config.php';
include_once 'functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get products from database
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique categories
$categoryStmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Filter by category if specified
if (isset($_GET['category']) && $_GET['category'] !== 'all') {
    $category = $_GET['category'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? ORDER BY created_at DESC");
    $stmt->execute([$category]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check for cart messages
$cart_message = '';
if (isset($_SESSION['cart_message'])) {
    $cart_message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']); // Clear the message after displaying
}

include_once 'includes/header.php';
?>

<div class="max-w-7xl mx-auto">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-dark">Toko Kesehatan</h1>
        <p class="text-gray-600 mt-2">Temukan obat dan produk kesehatan sesuai kebutuhan Anda</p>
    </div>

    <?php if ($cart_message): ?>
        <div class="max-w-4xl mx-auto mb-6 p-4 <?php echo strpos($cart_message, 'berhasil') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> rounded-md">
            <?php echo $cart_message; ?>
        </div>
    <?php endif; ?>

    <!-- Category Filter -->
    <div class="mb-8">
        <div class="flex flex-wrap justify-center gap-2">
            <a href="?category=all" class="px-4 py-2 rounded-full bg-gray-200 text-gray-700 hover:bg-primary hover:text-white transition">Semua</a>
            <?php foreach ($categories as $cat): ?>
                <a href="?category=<?php echo urlencode($cat['category']); ?>"
                   class="px-4 py-2 rounded-full bg-gray-200 text-gray-700 hover:bg-primary hover:text-white transition">
                    <?php echo htmlspecialchars($cat['category']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
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
                                <?php if ($product['stock'] > 0): ?>
                                    <form method="POST" action="cart.php" class="w-full">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="add_to_cart" value="1">
                                        <input type="hidden" name="redirect_to_store" value="1">
                                        <button type="submit" class="w-full bg-primary text-white py-2 rounded-md hover:bg-green-600 transition text-sm">
                                            <i class="fas fa-shopping-cart mr-1"></i> Tambah ke Keranjang
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="w-full bg-gray-400 text-white py-2 rounded-md text-sm" disabled>
                                        <i class="fas fa-times mr-1"></i> Habis
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="w-full block bg-primary text-white py-2 rounded-md hover:bg-green-600 transition text-center text-sm">
                                    <i class="fas fa-shopping-cart mr-1"></i> Beli Sekarang
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-12">
                <i class="fas fa-capsules text-5xl text-gray-400 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Produk Tidak Ditemukan</h3>
                <p class="text-gray-600">Kategori yang Anda pilih tidak memiliki produk saat ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>