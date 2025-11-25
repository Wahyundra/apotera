<?php
include_once 'config.php';

try {
    // Create orders table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        shipping_address TEXT NOT NULL,
        phone VARCHAR(15) NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    $pdo->exec($sql);
    echo "Orders table created successfully (or already existed).<br>";
    
    // Create order_items table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        product_name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    
    $pdo->exec($sql);
    echo "Order items table created successfully (or already existed).<br>";
    
    // Now let's update the cart handling for health store
    if (isset($_POST['add_to_cart']) && isset($_POST['redirect_to_store'])) {
        include_once 'functions.php';
        
        session_start();
        
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        
        if ($quantity <= 0) {
            $quantity = 1;
        }
        
        // Get product info to verify it exists and check stock
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $message = '';
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
                        $message = "Produk berhasil ditambahkan ke keranjang.";
                    }
                } else {
                    $_SESSION['cart'][$product_id] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'quantity' => $quantity,
                        'image_url' => $product['image_url']
                    ];
                    $message = "Produk berhasil ditambahkan ke keranjang.";
                }
            }
        } else {
            $message = "Produk tidak ditemukan.";
        }
        
        // Redirect back to health store with message
        $_SESSION['cart_message'] = $message;
        header("Location: health-store.php");
        exit();
    } else {
        echo "Database tables have been created successfully. You can now run checkout without errors.";
        echo '<br><a href="health-store.php">Go to Health Store</a>';
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>