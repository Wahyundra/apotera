<?php
include_once 'config.php';
include_once 'functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $title = sanitize_input($_POST['title']);
    $message_content = sanitize_input($_POST['message']);

    if (!empty($title) && !empty($message_content)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO consultations (user_id, title, message) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $message_content]);
            $message = "Konsultasi berhasil dikirim. Admin akan segera menanggapi pertanyaan Anda.";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    } else {
        $message = "Judul dan pesan harus diisi.";
    }
}

include_once 'includes/header.php';
?>

<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-center text-dark mb-6">Konsultasi</h1>

    <p class="text-gray-600 mb-8 text-center">
        Konsultasikan keluhan kesehatan Anda kepada dokter kami atau tanyakan seputar obat dan penyakit.
    </p>

    <?php if ($message): ?>
        <div class="mb-6 p-4 <?php echo strpos($message, 'berhasil') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> rounded-md">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="POST" class="space-y-6">
            <div>
                <label for="title" class="block text-gray-700 mb-2">Judul Konsultasi</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Contoh: Sakit Kepala Berkepanjangan"
                >
            </div>

            <div>
                <label for="message" class="block text-gray-700 mb-2">Pesan</label>
                <textarea
                    id="message"
                    name="message"
                    rows="6"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Jelaskan keluhan atau pertanyaan Anda secara detail..."
                ></textarea>
            </div>

            <div class="text-center">
                <button type="submit" class="bg-primary text-white px-6 py-3 rounded-md hover:bg-green-600 transition font-medium">
                    Kirim Konsultasi
                </button>
            </div>
        </form>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-user-lock text-5xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Silakan Login Terlebih Dahulu</h3>
            <p class="text-gray-600 mb-6">Anda perlu login untuk dapat melakukan konsultasi.</p>
            <a href="login.php" class="bg-primary text-white px-6 py-3 rounded-md hover:bg-green-600 transition font-medium inline-block">Login</a>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="mt-12">
            <h2 class="text-2xl font-semibold mb-6 text-dark">Riwayat Konsultasi Anda</h2>

            <?php
            $stmt = $pdo->prepare("SELECT * FROM consultations WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
            $stmt->execute([$_SESSION['user_id']]);
            $consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($consultations) > 0):
            ?>
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
                            $replyStmt = $pdo->prepare("SELECT * FROM consultation_replies WHERE consultation_id = ? ORDER BY created_at ASC");
                            $replyStmt->execute([$consultation['id']]);
                            $replies = $replyStmt->fetchAll(PDO::FETCH_ASSOC);

                            if (count($replies) > 0):
                            ?>
                                <div class="mt-4 pl-4 border-l-2 border-primary">
                                    <h4 class="font-semibold text-dark">Balasan Admin:</h4>
                                    <?php foreach ($replies as $reply): ?>
                                        <div class="mt-2">
                                            <p class="text-gray-700"><?php echo htmlspecialchars($reply['reply']); ?></p>
                                            <p class="text-sm text-gray-500 mt-1">Admin - <?php echo date('d M Y H:i', strtotime($reply['created_at'])); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-600 text-center py-8">Anda belum memiliki riwayat konsultasi.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>