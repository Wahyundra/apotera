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

// Get all BMI calculations with user info
$stmt = $pdo->query("
    SELECT bc.*, u.username
    FROM bmi_calculations bc
    JOIN users u ON bc.user_id = u.id
    ORDER BY bc.created_at DESC
");
$bmiCalculations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_bmi'])) {
    $bmi_id = intval($_POST['bmi_id']);

    // Delete BMI calculation
    $stmt = $pdo->prepare("DELETE FROM bmi_calculations WHERE id = ?");
    $stmt->execute([$bmi_id]);

    $message = "Data BMI berhasil dihapus.";
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tinggi (cm)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berat (kg)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BMI</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($bmiCalculations as $bmi): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo htmlspecialchars($bmi['username']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $bmi['height']; ?> cm
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $bmi['weight']; ?> kg
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $bmi['bmi']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="<?php 
                                echo $bmi['result'] === 'Normal' ? 'bg-green-100 text-green-800' : 
                                     ($bmi['result'] === 'Kurus' ? 'bg-yellow-100 text-yellow-800' : 
                                     ($bmi['result'] === 'Berlebih' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')); 
                                ?> px-2 py-1 rounded-full text-xs">
                                <?php echo htmlspecialchars($bmi['result']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo date('d M Y', strtotime($bmi['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <form method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menghapus data ini?')">
                                <input type="hidden" name="bmi_id" value="<?php echo $bmi['id']; ?>">
                                <input type="hidden" name="delete_bmi" value="1">
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