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

// Get dashboard statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM consultations WHERE status = 'pending'");
$pendingConsultations = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
$productCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM bmi_calculations");
$bmiCalculationsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM depression_tests");
$depressionTestsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM heart_risk_assessments");
$heartRiskCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

include_once '../includes/admin_header.php';
?>

<div class="max-w-7xl mx-auto p-6">
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700">Total Pengguna</h3>
            <p class="text-3xl font-bold text-primary mt-2"><?php echo $userCount; ?></p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700">Konsultasi Pending</h3>
            <p class="text-3xl font-bold text-yellow-600 mt-2"><?php echo $pendingConsultations; ?></p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700">Produk Obat</h3>
            <p class="text-3xl font-bold text-accent mt-2"><?php echo $productCount; ?></p>
        </div>
        
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <a href="manage-products.php" class="bg-white p-6 rounded-lg shadow-md text-center hover:shadow-lg transition">
            <i class="fas fa-pills text-3xl text-primary mb-3"></i>
            <h3 class="text-lg font-semibold text-gray-700">Kelola Produk</h3>
        </a>
        
        <a href="manage-consultations.php" class="bg-white p-6 rounded-lg shadow-md text-center hover:shadow-lg transition">
            <i class="fas fa-comments text-3xl text-secondary mb-3"></i>
            <h3 class="text-lg font-semibold text-gray-700">Kelola Konsultasi</h3>
        </a>
        
        <a href="manage-users.php" class="bg-white p-6 rounded-lg shadow-md text-center hover:shadow-lg transition">
            <i class="fas fa-users text-3xl text-accent mb-3"></i>
            <h3 class="text-lg font-semibold text-gray-700">Kelola Pengguna</h3>
        </a>
        
    </div>
    
    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Consultations -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-dark mb-4">Konsultasi Terbaru</h2>
            
            <?php
            $stmt = $pdo->query("SELECT c.*, u.username FROM consultations c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 5");
            $consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <div class="space-y-4">
                <?php if (count($consultations) > 0): ?>
                    <?php foreach ($consultations as $cons): ?>
                        <div class="border-b border-gray-200 pb-3">
                            <div class="flex justify-between">
                                <h3 class="font-medium text-dark"><?php echo htmlspecialchars($cons['title']); ?></h3>
                                <span class="text-sm text-gray-500"><?php echo date('d M', strtotime($cons['created_at'])); ?></span>
                            </div>
                            <p class="text-gray-600 text-sm mt-1">Oleh: <?php echo htmlspecialchars($cons['username']); ?></p>
                            <div class="mt-2">
                                <span class="inline-block px-2 py-1 rounded-full text-xs font-medium 
                                    <?php echo $cons['status'] === 'resolved' ? 'bg-green-100 text-green-800' : 
                                          ($cons['status'] === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'); ?>">
                                    <?php echo $cons['status'] === 'resolved' ? 'Selesai' : 
                                          ($cons['status'] === 'in_progress' ? 'Dalam Proses' : 'Menunggu'); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-600">Tidak ada konsultasi baru</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Self Health Stats -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-dark mb-4">Statistik Cek Kesehatan</h2>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">BMI Calculations</span>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full"><?php echo $bmiCalculationsCount; ?></span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Depression Tests</span>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full"><?php echo $depressionTestsCount; ?></span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Heart Risk Assessments</span>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full"><?php echo $heartRiskCount; ?></span>
                </div>
                
                <div class="mt-6">
                    <h3 class="font-medium text-dark mb-2">Akses Cepat</h3>
                    <div class="flex flex-wrap gap-2">
                        <a href="manage-bmi.php" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md text-sm hover:bg-gray-200">BMI Data</a>
                        <a href="manage-depression.php" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md text-sm hover:bg-gray-200">Depression Data</a>
                        <a href="manage-heart-risk.php" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md text-sm hover:bg-gray-200">Heart Risk Data</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/admin_footer.php'; ?>