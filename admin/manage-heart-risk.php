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

// Get all heart risk assessments with user info
$stmt = $pdo->query("
    SELECT hra.*, u.username
    FROM heart_risk_assessments hra
    JOIN users u ON hra.user_id = u.id
    ORDER BY hra.created_at DESC
");
$heartRiskAssessments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_heart_risk'])) {
    $assessment_id = intval($_POST['assessment_id']);

    // Delete heart risk assessment
    $stmt = $pdo->prepare("DELETE FROM heart_risk_assessments WHERE id = ?");
    $stmt->execute([$assessment_id]);

    $message = "Data risiko jantung berhasil dihapus.";
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kolesterol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tekanan Darah</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merokok</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diabetes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($heartRiskAssessments as $assessment): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo htmlspecialchars($assessment['username']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $assessment['age']; ?> th
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $assessment['cholesterol']; ?> mg/dL
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $assessment['blood_pressure']; ?> mmHg
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $assessment['smoking'] === 'yes' ? 'Ya' : 'Tidak'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $assessment['diabetes'] === 'yes' ? 'Ya' : 'Tidak'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo $assessment['score']; ?>/10
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="<?php 
                                echo $assessment['result'] === 'Risiko Rendah' ? 'bg-green-100 text-green-800' : 
                                     ($assessment['result'] === 'Risiko Sedang' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                ?> px-2 py-1 rounded-full text-xs">
                                <?php echo htmlspecialchars($assessment['result']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo date('d M Y', strtotime($assessment['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <form method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menghapus data ini?')">
                                <input type="hidden" name="assessment_id" value="<?php echo $assessment['id']; ?>">
                                <input type="hidden" name="delete_heart_risk" value="1">
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