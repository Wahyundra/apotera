<?php
include_once '../config.php';
include_once '../functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Check if required tables exist
$tablesExist = true;
try {
    $pdo->query("SELECT 1 FROM orders LIMIT 1");
    $pdo->query("SELECT 1 FROM order_items LIMIT 1");
} catch (PDOException $e) {
    $tablesExist = false;
}

if (!$tablesExist) {
    // Redirect to table creation page
    header("Location: create_order_tables.php");
    exit();
}

// Handle AJAX request for order items
if (isset($_GET['action']) && $_GET['action'] === 'get_order_items' && isset($_GET['order_id'])) {
    header('Content-Type: application/json');

    $order_id = intval($_GET['order_id']);

    try {
        // Get order items
        $stmt = $pdo->prepare("
            SELECT product_name, price, quantity, subtotal
            FROM order_items
            WHERE order_id = ?
        ");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'items' => $items
        ]);
        exit();
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit();
    }
}

include_once '../includes/admin_header.php';

$message = '';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($new_status, $valid_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $message = "Status pesanan berhasil diperbarui.";
    } else {
        $message = "Status tidak valid.";
    }
}

// Get all orders with user info and item count
$stmt = $pdo->query("
    SELECT o.*, u.username,
           (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-dark">Kelola Pesanan</h1>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-md">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">#<?php echo $order['id']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo htmlspecialchars($order['username']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $order['item_count']; ?> item
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="
                                <?php 
                                switch($order['status']) {
                                    case 'delivered': echo 'bg-green-100 text-green-800'; break;
                                    case 'shipped': echo 'bg-blue-100 text-blue-800'; break;
                                    case 'processing': echo 'bg-yellow-100 text-yellow-800'; break;
                                    case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800';
                                }
                                ?> px-2 py-1 rounded-full text-xs">
                                <?php 
                                switch($order['status']) {
                                    case 'delivered': echo 'Dikirim'; break;
                                    case 'shipped': echo 'Dalam Pengiriman'; break;
                                    case 'processing': echo 'Diproses'; break;
                                    case 'cancelled': echo 'Dibatalkan'; break;
                                    default: echo 'Menunggu';
                                }
                                ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <a href="#order-<?php echo $order['id']; ?>" 
                               onclick="openOrderDetails(<?php echo $order['id']; ?>, 
                                   '<?php echo addslashes(htmlspecialchars($order['username'])); ?>', 
                                   '<?php echo date('d M Y H:i', strtotime($order['created_at'])); ?>', 
                                   <?php echo $order['total_amount']; ?>, 
                                   '<?php echo $order['status']; ?>', 
                                   '<?php echo addslashes(htmlspecialchars($order['shipping_address'])); ?>', 
                                   '<?php echo addslashes(htmlspecialchars($order['phone'])); ?>', 
                                   '<?php echo addslashes(htmlspecialchars($order['notes'])); ?>')"
                               class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#order-<?php echo $order['id']; ?>" 
                               onclick="openStatusModal(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')"
                               class="text-yellow-600 hover:text-yellow-900">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Order Details Modal -->
<div id="order-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 id="order-modal-title" class="text-lg font-semibold text-dark">Detail Pesanan</h3>
                <button onclick="closeOrderModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="order-details" class="mt-5">
                <!-- Order details will be populated by JavaScript -->
            </div>

            <div class="mt-6 flex justify-end">
                <button onclick="closeOrderModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="status-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-40 mx-auto p-5 border w-11/12 md:w-1/3 lg:w-1/4 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-semibold text-yellow-600">Update Status Pesanan</h3>
                <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="status-form" method="POST" class="mt-5 space-y-4">
                <input type="hidden" id="status-order-id" name="order_id" value="">
                <input type="hidden" name="update_status" value="1">

                <div>
                    <label for="status-select" class="block text-gray-700 mb-2">Status Baru</label>
                    <select id="status-select" name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="pending">Menunggu</option>
                        <option value="processing">Diproses</option>
                        <option value="shipped">Dalam Pengiriman</option>
                        <option value="delivered">Dikirim</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openOrderDetails(orderId, username, date, total, status, address, phone, notes) {
        // Get order items via AJAX to the same page
        fetch('?action=get_order_items&order_id=' + orderId)
            .then(response => response.json())
            .then(data => {
                let itemsHtml = '<div class="mb-4"><h4 class="font-semibold text-dark">Detail Pesanan</h4>';
                itemsHtml += '<div class="grid grid-cols-2 gap-2 text-sm mb-3">';
                itemsHtml += `<div><strong>ID Pesanan:</strong> #${orderId}</div>`;
                itemsHtml += `<div><strong>Pengguna:</strong> ${username}</div>`;
                itemsHtml += `<div><strong>Tanggal:</strong> ${date}</div>`;
                itemsHtml += `<div><strong>Total:</strong> Rp ${total.toLocaleString('id-ID')}</div>`;
                itemsHtml += `<div><strong>Status:</strong> ${status}</div>`;
                itemsHtml += `<div><strong>Telepon:</strong> ${phone}</div>`;
                itemsHtml += '</div>';

                itemsHtml += '<div class="mb-3"><strong>Alamat Pengiriman:</strong><br>';
                itemsHtml += `<span class="text-gray-700">${address}</span></div>`;

                if (notes) {
                    itemsHtml += '<div class="mb-4"><strong>Catatan:</strong><br>';
                    itemsHtml += `<span class="text-gray-700">${notes}</span></div>`;
                }

                itemsHtml += '<h5 class="font-semibold text-dark mt-3">Item Pesanan:</h5>';
                itemsHtml += '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead><tr>';
                itemsHtml += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>';
                itemsHtml += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>';
                itemsHtml += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>';
                itemsHtml += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>';
                itemsHtml += '</tr></thead><tbody>';

                if (data.success && data.items) {
                    data.items.forEach(item => {
                        itemsHtml += '<tr class="border-b">';
                        itemsHtml += `<td class="px-4 py-2">${item.product_name}</td>`;
                        itemsHtml += `<td class="px-4 py-2">Rp ${parseFloat(item.price).toLocaleString('id-ID', {maximumFractionDigits: 0})}</td>`;
                        itemsHtml += `<td class="px-4 py-2">${item.quantity}</td>`;
                        itemsHtml += `<td class="px-4 py-2">Rp ${parseFloat(item.subtotal).toLocaleString('id-ID', {maximumFractionDigits: 0})}</td>`;
                        itemsHtml += '</tr>';
                    });
                } else {
                    itemsHtml += '<tr><td colspan="4" class="px-4 py-2 text-center text-red-500">Gagal memuat item pesanan</td></tr>';
                }

                itemsHtml += '</tbody></table></div></div>';

                document.getElementById('order-details').innerHTML = itemsHtml;
                document.getElementById('order-modal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('order-details').innerHTML = '<p class="text-red-500">Gagal memuat detail pesanan</p>';
                document.getElementById('order-modal').classList.remove('hidden');
            });
    }

    function closeOrderModal() {
        document.getElementById('order-modal').classList.add('hidden');
    }

    function openStatusModal(orderId, currentStatus) {
        document.getElementById('status-order-id').value = orderId;
        document.getElementById('status-select').value = currentStatus;
        document.getElementById('status-modal').classList.remove('hidden');
    }

    function closeStatusModal() {
        document.getElementById('status-modal').classList.add('hidden');
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const orderModal = document.getElementById('order-modal');
        const statusModal = document.getElementById('status-modal');

        if (event.target === orderModal) {
            closeOrderModal();
        }

        if (event.target === statusModal) {
            closeStatusModal();
        }
    }
</script>

<?php include_once '../includes/admin_footer.php'; ?>