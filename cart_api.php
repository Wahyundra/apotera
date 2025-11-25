<?php
include_once 'config.php';
include_once 'functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

header('Content-Type: application/json');

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Return cart information
        $cart_count = count($_SESSION['cart']);
        $cart_total = 0;

        foreach ($_SESSION['cart'] as $item) {
            $cart_total += $item['price'] * $item['quantity'];
        }

        echo json_encode([
            'cart_count' => $cart_count,
            'cart_total' => $cart_total,
            'items' => $_SESSION['cart']
        ]);
        break;

    case 'POST':
        // Handle adding to cart
        $post_data = json_decode(file_get_contents('php://input'), true);

        if (isset($post_data['action']) && $post_data['action'] === 'add_to_cart') {
            $product_id = intval($post_data['product_id']);
            $quantity = intval($post_data['quantity']);

            if ($quantity <= 0) {
                $quantity = 1;
            }

            // Get product info to verify it exists and check stock
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                if ($quantity > $product['stock']) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Jumlah melebihi stok yang tersedia. Stok saat ini: ' . $product['stock']
                    ]);
                } else {
                    // Check if product already in cart
                    if (isset($_SESSION['cart'][$product_id])) {
                        $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
                        if ($new_quantity > $product['stock']) {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Jumlah total melebihi stok yang tersedia.'
                            ]);
                        } else {
                            $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
                            $cart_count = count($_SESSION['cart']);
                            echo json_encode([
                                'success' => true,
                                'message' => 'Produk berhasil ditambahkan ke keranjang.',
                                'cart_count' => $cart_count
                            ]);
                        }
                    } else {
                        $_SESSION['cart'][$product_id] = [
                            'id' => $product['id'],
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'quantity' => $quantity,
                            'image_url' => $product['image_url']
                        ];
                        $cart_count = count($_SESSION['cart']);
                        echo json_encode([
                            'success' => true,
                            'message' => 'Produk berhasil ditambahkan ke keranjang.',
                            'cart_count' => $cart_count
                        ]);
                    }
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan.'
                ]);
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>