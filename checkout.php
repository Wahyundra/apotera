<?php
include_once 'config.php';
include_once 'functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$message = '';

// Handle checkout process
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $address = sanitize_input($_POST['address']);
    $phone = sanitize_input($_POST['phone']);

    // Get user's email from the database
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $email = $user['email'];

    // Validate inputs
    if (empty($address) || empty($phone)) {
        $message = "Alamat dan nomor telepon harus diisi.";
    } else {
        // Verify stock for all items in cart
        $errors = [];

        foreach ($_SESSION['cart'] as $item_id => $item) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$item['id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                $errors[] = "Produk tidak ditemukan: " . $item['name'];
            } elseif ($item['quantity'] > $product['stock']) {
                $errors[] = "Stok tidak mencukupi untuk " . $item['name'] . ". Tersedia: " . $product['stock'];
            }
        }

        if (empty($errors)) {
            // Begin transaction
            $pdo->beginTransaction();

            try {
                // Calculate total amount
                $total_amount = 0;
                foreach ($_SESSION['cart'] as $item) {
                    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
                    $stmt->execute([$item['id']]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    $total_amount += $product['price'] * $item['quantity'];
                }

                // Insert order
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, phone) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $total_amount, $address, $phone]);
                $order_id = $pdo->lastInsertId();

                // Insert order items and reduce stock
                foreach ($_SESSION['cart'] as $item) {
                    // Get product details
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                    $stmt->execute([$item['id']]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Insert order item
                    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$order_id, $product['id'], $product['name'], $product['price'], $item['quantity'], $product['price'] * $item['quantity']]);

                    // Reduce stock
                    $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$item['quantity'], $item['id']]);
                }

                // Clear cart
                $_SESSION['cart'] = [];

                $pdo->commit();
                $message = "Pembelian berhasil! Terima kasih telah berbelanja.";

                // Redirect after checkout
                header("Location: profile.php");
                exit();

            } catch (Exception $e) {
                $pdo->rollback();
                $message = "Terjadi kesalahan saat proses checkout: " . $e->getMessage();
            }
        } else {
            $message = implode("<br>", $errors);
        }
    }
}

include_once 'includes/header.php';

// Calculate cart total
$cart_total = 0;
$cart_items = [];

foreach ($_SESSION['cart'] as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $cart_total += $subtotal;
    
    $cart_items[] = [
        'name' => $item['name'],
        'price' => $item['price'],
        'quantity' => $item['quantity'],
        'subtotal' => $subtotal,
        'image_url' => $item['image_url']
    ];
}
?>

<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-center text-dark mb-8">Checkout</h1>

    <?php if ($message): ?>
        <div class="mb-6 p-4 <?php echo strpos($message, 'berhasil') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> rounded-md">
            <?php echo $message; ?>
        </div>
        <?php if (strpos($message, 'berhasil') !== false): ?>
            <div class="text-center">
                <a href="profile.php" class="bg-primary text-white px-6 py-3 rounded-md hover:bg-green-600 transition font-medium">
                    Lihat Profil
                </a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Order Summary -->
            <div class="lg:col-span-2">
                <h2 class="text-xl font-semibold text-dark mb-4">Ringkasan Pesanan</h2>
                
                <div class="space-y-4">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="flex items-center border border-gray-200 rounded-lg p-3">
                            <?php if ($item['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="w-12 h-12 object-cover rounded-md">
                            <?php else: ?>
                                <div class="w-12 h-12 bg-gray-200 flex items-center justify-center rounded-md">
                                    <i class="fas fa-pills text-lg text-gray-500"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="ml-4 flex-grow">
                                <h3 class="font-medium text-dark"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="text-gray-600 text-sm">Qty: <?php echo $item['quantity']; ?> x Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                            </div>
                            
                            <div class="text-right">
                                <p class="font-semibold text-dark">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Checkout Form -->
            <div>
                <h2 class="text-xl font-semibold text-dark mb-4">Detail Pengiriman</h2>
                
                <form method="POST">
                    <input type="hidden" name="checkout" value="1">
                    
                    <?php
                    // Get user's profile information
                    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    $user_email = $user['email'];
                    ?>

                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" readonly
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary bg-gray-100">
                    </div>

                    <div class="mb-4">
                        <label for="address" class="block text-gray-700 mb-2">Alamat Lengkap</label>
                        <textarea id="address" name="address" rows="3" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="block text-gray-700 mb-2">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    
                    
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between items-center text-lg font-bold mb-4">
                            <span>Total:</span>
                            <span class="text-primary">Rp <?php echo number_format($cart_total, 0, ',', '.'); ?></span>
                        </div>
                        
                        <button type="submit" class="w-full bg-primary text-white px-6 py-3 rounded-md hover:bg-green-600 transition font-medium">
                            Proses Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>