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

$message = '';

// Handle consultation replies
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply'])) {
    $consultation_id = intval($_POST['consultation_id']);
    $reply = sanitize_input($_POST['reply']);

    $stmt = $pdo->prepare("INSERT INTO consultation_replies (consultation_id, admin_id, reply) VALUES (?, ?, ?)");
    $stmt->execute([$consultation_id, $_SESSION['user_id'], $reply]);

    // Update consultation status to 'resolved' when admin replies
    $stmt = $pdo->prepare("UPDATE consultations SET status = 'resolved' WHERE id = ?");
    $stmt->execute([$consultation_id]);

    $message = "Balasan berhasil dikirim.";
}

// Handle consultation deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_consultation'])) {
    $consultation_id = intval($_POST['consultation_id']);

    // Begin transaction
    try {
        $pdo->beginTransaction();

        // Delete all replies related to this consultation
        $stmt = $pdo->prepare("DELETE FROM consultation_replies WHERE consultation_id = ?");
        $stmt->execute([$consultation_id]);

        // Delete the consultation itself
        $stmt = $pdo->prepare("DELETE FROM consultations WHERE id = ? AND status = 'resolved'");
        $stmt->execute([$consultation_id]);

        // Check if any rows were affected
        if ($stmt->rowCount() > 0) {
            $message = "Konsultasi berhasil dihapus.";
        } else {
            $message = "Konsultasi tidak ditemukan atau belum selesai.";
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollback();
        $message = "Terjadi kesalahan saat menghapus konsultasi: " . $e->getMessage();
    }
}

// Get all consultations
$stmt = $pdo->query("SELECT c.*, u.username FROM consultations c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC");
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once '../includes/admin_header.php'; ?>

<div class="max-w-7xl mx-auto p-6">
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-md">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php foreach ($consultations as $consultation): ?>
            <div class="border-b border-gray-200 p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-dark"><?php echo htmlspecialchars($consultation['title']); ?></h3>
                        <div class="mt-1 flex items-center text-sm text-gray-600">
                            <span>Oleh: <?php echo htmlspecialchars($consultation['username']); ?></span>
                            <span class="mx-2">•</span>
                            <span><?php echo date('d M Y H:i', strtotime($consultation['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                            <?php echo $consultation['status'] === 'resolved' ? 'bg-green-100 text-green-800' :
                                  ($consultation['status'] === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'); ?>">
                            <?php echo $consultation['status'] === 'resolved' ? 'Selesai' :
                                  ($consultation['status'] === 'in_progress' ? 'Dalam Proses' : 'Menunggu'); ?>
                        </span>

                        <?php if ($consultation['status'] === 'resolved'): ?>
                            <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus konsultasi ini?')">
                                <input type="hidden" name="consultation_id" value="<?php echo $consultation['id']; ?>">
                                <input type="hidden" name="delete_consultation" value="1">
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <p class="mt-4 text-gray-700"><?php echo htmlspecialchars($consultation['message']); ?></p>
                
                <!-- Replies Section -->
                <?php
                $replyStmt = $pdo->prepare("SELECT cr.*, u.username FROM consultation_replies cr JOIN users u ON cr.admin_id = u.id WHERE cr.consultation_id = ? ORDER BY cr.created_at ASC");
                $replyStmt->execute([$consultation['id']]);
                $replies = $replyStmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <?php if (count($replies) > 0): ?>
                    <div class="mt-6 pl-6 border-l-2 border-primary">
                        <h4 class="font-semibold text-dark mb-3">Balasan Admin:</h4>
                        <?php foreach ($replies as $reply): ?>
                            <div class="mb-4 p-4 bg-gray-50 rounded-md">
                                <div class="flex items-center text-sm text-gray-600 mb-2">
                                    <span><?php echo htmlspecialchars($reply['username']); ?></span>
                                    <span class="mx-2">•</span>
                                    <span><?php echo date('d M Y H:i', strtotime($reply['created_at'])); ?></span>
                                </div>
                                <p><?php echo htmlspecialchars($reply['reply']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Reply Form -->
                <div class="mt-6">
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="consultation_id" value="<?php echo $consultation['id']; ?>">

                        <div>
                            <label for="reply_<?php echo $consultation['id']; ?>" class="block text-gray-700 mb-2">Balas Konsultasi</label>
                            <textarea
                                id="reply_<?php echo $consultation['id']; ?>"
                                name="reply"
                                rows="3"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="Tulis balasan untuk pengguna..."
                            ></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition">
                                Kirim Balasan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include_once '../includes/admin_footer.php'; ?>