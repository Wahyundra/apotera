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

// Get all depression tests with user info
$stmt = $pdo->query("
    SELECT dt.*, u.username
    FROM depression_tests dt
    JOIN users u ON dt.user_id = u.id
    ORDER BY dt.created_at DESC
");
$depressionTests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_depression'])) {
    $test_id = intval($_POST['test_id']);

    // Delete depression test
    $stmt = $pdo->prepare("DELETE FROM depression_tests WHERE id = ?");
    $stmt->execute([$test_id]);

    $message = "Data tes depresi berhasil dihapus.";
}

include_once '../includes/admin_header.php'; ?>

<div class="max-w-7xl mx-auto p-6">
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-md">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Skor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($depressionTests as $test): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo htmlspecialchars($test['username']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $test['total_score']; ?>/27
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="<?php 
                                echo $test['result'] === 'Normal' ? 'bg-green-100 text-green-800' : 
                                     ($test['result'] === 'Mild' ? 'bg-yellow-100 text-yellow-800' : 
                                     ($test['result'] === 'Moderate' ? 'bg-orange-100 text-orange-800' : 
                                     ($test['result'] === 'Severe' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800'))); 
                                ?> px-2 py-1 rounded-full text-xs">
                                <?php echo htmlspecialchars($test['result']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo date('d M Y', strtotime($test['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <form method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menghapus data ini?')">
                                <input type="hidden" name="test_id" value="<?php echo $test['id']; ?>">
                                <input type="hidden" name="delete_depression" value="1">
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once '../includes/admin_footer.php'; ?>