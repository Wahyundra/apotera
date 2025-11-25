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

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = '';

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);

        if ($quantity <= 0) {
            $quantity = 1;
        }

        // Get product info to verify it exists and check stock
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            if ($quantity > $product['stock']) {
                $message = "Jumlah melebihi stok yang tersedia. Stok saat ini: " . $product['stock'];
            } else {
                // Check if product already in cart
                if (isset($_SESSION['cart'][$product_id])) {
                    $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
                    if ($new_quantity > $product['stock']) {
                        $message = "Jumlah total melebihi stok yang tersedia.";
                    } else {
                        $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
                    }
                } else {
                    $_SESSION['cart'][$product_id] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'quantity' => $quantity,
                        'image_url' => $product['image_url']
                    ];
                }

                if (empty($message)) {
                    $message = "Produk berhasil ditambahkan ke keranjang.";
                }
            }
        } else {
            $message = "Produk tidak ditemukan.";
        }

        // If it's an AJAX request, return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => empty($message) || strpos($message, 'berhasil') !== false,
                'message' => $message,
                'cart_count' => count($_SESSION['cart'])
            ]);
            exit;
        } else {
            // For direct form submissions, redirect back to health store
            if (isset($_POST['redirect_to_store']) && $_POST['redirect_to_store'] === '1') {
                $_SESSION['cart_message'] = $message;
                header("Location: health-store.php");
                exit;
            }
        }
    }
    elseif (isset($_POST['remove_item'])) {
        $product_id = intval($_POST['product_id']);
        unset($_SESSION['cart'][$product_id]);
        $message = "Produk berhasil dihapus dari keranjang.";
    }
    elseif (isset($_POST['update_quantity'])) {
        $product_id = intval($_POST['product_id']);
        $new_quantity = intval($_POST['quantity']);

        if ($new_quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            // Get product info to check stock
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product && $new_quantity > $product['stock']) {
                $message = "Jumlah melebihi stok yang tersedia.";
            } else {
                $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
                $message = "Kuantitas berhasil diperbarui.";
            }
        }
    }
}

include_once 'includes/header.php';

// Calculate cart total
$cart_total = 0;
$cart_count = 0;
?>

<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-center text-dark mb-8">Keranjang Belanja</h1>

    <?php if ($message): ?>
        <div class="mb-6 p-4 <?php echo strpos($message, 'berhasil') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> rounded-md">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['cart'])): ?>
        <div class="space-y-6">
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <?php
                $subtotal = $item['price'] * $item['quantity'];
                $cart_total += $subtotal;
                $cart_count += $item['quantity'];
                ?>
                <div class="flex items-center border border-gray-200 rounded-lg p-4">
                    <?php if ($item['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="w-16 h-16 object-cover rounded-md">
                    <?php else: ?>
                        <div class="w-16 h-16 bg-gray-200 flex items-center justify-center rounded-md">
                            <i class="fas fa-pills text-xl text-gray-500"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="ml-4 flex-grow">
                        <h3 class="font-semibold text-dark"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p class="text-gray-600">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <form method="POST" class="flex items-center">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <span class="mr-2">Jumlah:</span>
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                   min="1" max="99" class="w-16 px-2 py-1 border rounded-md text-center">
                            <input type="hidden" name="update_quantity" value="1">
                            <button type="submit" class="ml-2 bg-primary text-white px-3 py-1 rounded-md hover:bg-green-600">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </form>
                        
                        <form method="POST" class="ml-2 inline">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="remove_item" value="1">
                            <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600"
                                    onclick="return confirm('Yakin ingin menghapus produk dari keranjang?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="ml-4 text-right">
                        <p class="font-semibold text-dark">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="border-t border-gray-200 pt-6 mt-6">
                <div class="flex justify-between items-center text-lg font-bold">
                    <span>Total:</span>
                    <span class="text-primary">Rp <?php echo number_format($cart_total, 0, ',', '.'); ?></span>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <a href="checkout.php" class="bg-primary text-white px-6 py-3 rounded-md hover:bg-green-600 transition font-medium">
                        Checkout
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-shopping-cart text-5xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Keranjang Belanja Kosong</h3>
            <p class="text-gray-600 mb-6">Tambahkan produk dari toko kesehatan ke keranjang Anda.</p>
            <a href="health-store.php" class="bg-primary text-white px-6 py-3 rounded-md hover:bg-green-600 transition font-medium inline-block">
                Lihat Produk
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>