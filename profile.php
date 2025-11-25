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

$message = '';

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $new_username = sanitize_input($_POST['username']);
        $new_email = sanitize_input($_POST['email']);
        $current_password = $_POST['current_password'];

        // Get current user data to verify password
        $userStmt = $pdo->prepare("SELECT username, email, password FROM users WHERE id = ?");
        $userStmt->execute([$_SESSION['user_id']]);
        $currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentUser) {
            $message = "User tidak ditemukan.";
        } else {
            // Verify current password
            if (!password_verify($current_password, $currentUser['password'])) {
                $message = "Password saat ini salah.";
            } else {
                // Check if new username or email already exists (excluding current user)
                $checkStmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                $checkStmt->execute([$new_username, $new_email, $_SESSION['user_id']]);
                $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($existingUser) {
                    $message = "Username atau email sudah digunakan oleh pengguna lain.";
                } else {
                    // Update user info
                    $updateStmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    $updateStmt->execute([$new_username, $new_email, $_SESSION['user_id']]);

                    // Update session data
                    $_SESSION['username'] = $new_username;

                    $message = "Profil berhasil diperbarui.";

                    // Refresh user data
                    $userStmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
                    $userStmt->execute([$_SESSION['user_id']]);
                    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        }
    } elseif (isset($_POST['delete_account'])) {
        $delete_password = $_POST['delete_password'];

        // Get user's password to verify
        $userStmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $userStmt->execute([$_SESSION['user_id']]);
        $currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentUser) {
            $message = "User tidak ditemukan.";
        } else {
            if (!password_verify($delete_password, $currentUser['password'])) {
                $message = "Password salah. Akun tidak dihapus.";
            } else {
                // Delete user account and related data
                // First, delete related orders and order items
                $deleteOrderItemsStmt = $pdo->prepare("DELETE FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE user_id = ?)");
                $deleteOrderItemsStmt->execute([$_SESSION['user_id']]);

                $deleteOrdersStmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ?");
                $deleteOrdersStmt->execute([$_SESSION['user_id']]);

                // Then delete other related data
                $deleteStmt = $pdo->prepare("DELETE FROM consultations WHERE user_id = ?");
                $deleteStmt->execute([$_SESSION['user_id']]);

                $deleteStmt = $pdo->prepare("DELETE FROM consultation_replies WHERE admin_id = ?"); // in case user is also an admin
                $deleteStmt->execute([$_SESSION['user_id']]);

                $deleteStmt = $pdo->prepare("DELETE FROM bmi_calculations WHERE user_id = ?");
                $deleteStmt->execute([$_SESSION['user_id']]);

                $deleteStmt = $pdo->prepare("DELETE FROM depression_tests WHERE user_id = ?");
                $deleteStmt->execute([$_SESSION['user_id']]);

                $deleteStmt = $pdo->prepare("DELETE FROM heart_risk_assessments WHERE user_id = ?");
                $deleteStmt->execute([$_SESSION['user_id']]);

                $deleteStmt = $pdo->prepare("DELETE FROM ai_questions WHERE user_id = ?");
                $deleteStmt->execute([$_SESSION['user_id']]);

                // Finally, delete the user
                $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $deleteStmt->execute([$_SESSION['user_id']]);

                // Destroy session and redirect to home
                session_destroy();
                header("Location: index.php");
                exit();
            }
        }
    }
}

include_once 'includes/header.php';

// Get user's consultations and replies (if not already set)
if (!isset($user)) {
    $userStmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
}

$consultationStmt = $pdo->prepare("
    SELECT c.*,
           (SELECT COUNT(*) FROM consultation_replies cr WHERE cr.consultation_id = c.id) as reply_count
    FROM consultations c
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$consultationStmt->execute([$_SESSION['user_id']]);
$consultations = $consultationStmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's order history
$orderStmt = $pdo->prepare("
    SELECT c.*, cr.reply, cr.created_at as reply_date
    FROM consultations c
    LEFT JOIN consultation_replies cr ON c.id = cr.consultation_id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
    LIMIT 10
");
$orderStmt->execute([$_SESSION['user_id']]);
$orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-center text-dark mb-8">Profil Pengguna</h1>

    <!-- User Info Section -->
    <div class="mb-8 p-6 bg-gray-50 rounded-lg">
        <h2 class="text-xl font-semibold text-dark mb-4">Informasi Akun</h2>

        <?php if ($message): ?>
            <div class="mb-4 p-4 <?php echo strpos($message, 'berhasil') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> rounded-md">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 mb-1">Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
            </div>
            <div>
                <label class="block text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-gray-700 mb-1">Password Saat Ini</label>
                <input type="password" name="current_password" placeholder="Masukkan password saat ini"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
            </div>
            <div class="md:col-span-2 flex justify-end">
                <input type="hidden" name="update_profile" value="1">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition">
                    Update Profil
                </button>
            </div>
        </form>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-dark mb-3">Tanggal Bergabung</h3>
            <p class="bg-white p-3 rounded border"><?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
        </div>
    </div>

    <!-- Delete Account Section -->
    <div class="mb-8 p-6 bg-red-50 rounded-lg border border-red-200">
        <h2 class="text-xl font-semibold text-dark mb-4">Hapus Akun</h2>
        <p class="text-red-700 mb-4">Peringatan: Tindakan ini akan menghapus akun Anda secara permanen beserta semua data yang terkait. Tindakan ini tidak dapat dibatalkan.</p>

        <form method="POST" class="flex items-end space-x-3" onsubmit="return confirm('Anda yakin ingin menghapus akun Anda? Tindakan ini tidak dapat dibatalkan.')">
            <div class="flex-grow">
                <label class="block text-gray-700 mb-1">Konfirmasi Password</label>
                <input type="password" name="delete_password" placeholder="Masukkan password untuk menghapus akun"
                       class="w-full px-4 py-2 border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" required>
            </div>
            <div>
                <input type="hidden" name="delete_account" value="1">
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition">
                    Hapus Akun
                </button>
            </div>
        </form>
    </div>

    <!-- Consultation Replies Section -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-dark mb-4">Balasan Konsultasi dari Admin</h2>

        <?php
        // Get user's consultations
        $consultationStmt = $pdo->prepare("
            SELECT c.*
            FROM consultations c
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
        ");
        $consultationStmt->execute([$_SESSION['user_id']]);
        $consultations = $consultationStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if (count($consultations) > 0): ?>
            <div class="space-y-6">
                <?php foreach ($consultations as $consultation): ?>
                    <div class="border border-gray-200 rounded-lg p-6">
                        <div class="flex justify-between items-start">
                            <h3 class="text-lg font-semibold text-dark"><?php echo htmlspecialchars($consultation['title']); ?></h3>
                            <span class="text-sm text-gray-500"><?php echo date('d M Y', strtotime($consultation['created_at'])); ?></span>
                        </div>

                        <p class="mt-2 text-gray-700"><?php echo htmlspecialchars($consultation['message']); ?></p>

                        <div class="mt-4 flex items-center">
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                                <?php echo $consultation['status'] === 'resolved' ? 'bg-green-100 text-green-800' :
                                      ($consultation['status'] === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'); ?>">
                                <?php echo $consultation['status'] === 'resolved' ? 'Selesai' :
                                      ($consultation['status'] === 'in_progress' ? 'Dalam Proses' : 'Menunggu'); ?>
                            </span>
                        </div>

                        <?php
                        // Get replies for this consultation
                        $replyStmt = $pdo->prepare("
                            SELECT cr.*, u.username as admin_username
                            FROM consultation_replies cr
                            JOIN users u ON cr.admin_id = u.id
                            WHERE cr.consultation_id = ?
                            ORDER BY cr.created_at ASC
                        ");
                        $replyStmt->execute([$consultation['id']]);
                        $replies = $replyStmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($replies) > 0):
                        ?>
                            <div class="mt-4 pl-4 border-l-2 border-primary">
                                <h4 class="font-semibold text-dark mb-3">Balasan Admin:</h4>
                                <?php foreach ($replies as $reply): ?>
                                    <div class="mt-2 p-3 bg-gray-50 rounded">
                                        <p class="text-gray-700"><?php echo htmlspecialchars($reply['reply']); ?></p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <?php echo htmlspecialchars($reply['admin_username']); ?> -
                                            <?php echo date('d M Y H:i', strtotime($reply['created_at'])); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="mt-4 pl-4 border-l-2 border-gray-200">
                                <p class="text-gray-600">Belum ada balasan dari admin.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-600 text-center py-4">Anda belum memiliki konsultasi dengan admin.</p>
        <?php endif; ?>
    </div>

    <!-- Order History Section -->
    <div>
        <h2 class="text-xl font-semibold text-dark mb-4">Riwayat Pemesanan Obat</h2>

        <?php
        // Get user's order history
        $orderStmt = $pdo->prepare("
            SELECT o.*,
                   (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
            FROM orders o
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
            LIMIT 10
        ");
        $orderStmt->execute([$_SESSION['user_id']]);
        $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if (count($orders) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Item</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">#<?php echo $order['id']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500"><?php echo date('d M Y', strtotime($order['created_at'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?php
                                        switch($order['status']) {
                                            case 'delivered': echo 'bg-green-100 text-green-800'; break;
                                            case 'shipped': echo 'bg-blue-100 text-blue-800'; break;
                                            case 'processing': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $order['item_count']; ?> item
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600 text-center py-4">Anda belum memiliki riwayat pemesanan obat.</p>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>