<?php
include_once 'config.php';
include_once 'functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $age = intval($_POST['age']);
    $cholesterol = intval($_POST['cholesterol']);
    $blood_pressure = intval($_POST['blood_pressure']);
    $smoking = $_POST['smoking'];
    $diabetes = $_POST['diabetes'];

    $score = calculateHeartRiskScore($age, $cholesterol, $blood_pressure, $smoking, $diabetes);
    $category = getHeartRiskCategory($score);

    // Save to database if user is logged in
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("INSERT INTO heart_risk_assessments (user_id, age, cholesterol, blood_pressure, smoking, diabetes, score, result) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $age, $cholesterol, $blood_pressure, $smoking, $diabetes, $score, $category]);
    }

    $message = "Nilai risiko jantung Anda adalah $score, termasuk dalam kategori: $category";
}

include_once 'includes/header.php';
?>

<div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-center text-dark mb-6">Risiko Jantung</h1>

    <p class="text-gray-600 mb-8 text-center">
        Tes ini mengevaluasi faktor-faktor risiko yang dapat mempengaruhi kesehatan jantung Anda.
    </p>

    <?php if ($message): ?>
        <div class="mb-6 p-4 bg-<?php echo strpos($message, 'Rendah') ? 'green' : (strpos($message, 'Sedang') ? 'yellow' : 'red'); ?>-100 rounded-md text-<?php echo strpos($message, 'Rendah') ? 'green' : (strpos($message, 'Sedang') ? 'yellow' : 'red'); ?>-700">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="age" class="block text-gray-700 mb-2">Umur</label>
                <input
                    type="number"
                    id="age"
                    name="age"
                    min="18"
                    max="100"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Contoh: 35"
                >
            </div>

            <div>
                <label for="cholesterol" class="block text-gray-700 mb-2">Kolesterol (mg/dL)</label>
                <input
                    type="number"
                    id="cholesterol"
                    name="cholesterol"
                    min="100"
                    max="400"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Contoh: 200"
                >
            </div>
        </div>

        <div>
            <label for="blood_pressure" class="block text-gray-700 mb-2">Tekanan Darah (mmHg)</label>
            <input
                type="number"
                id="blood_pressure"
                name="blood_pressure"
                min="80"
                max="200"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="Contoh: 120"
            >
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 mb-2">Merokok</label>
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="smoking" value="yes" required class="mr-2">
                        <span>Ya</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="smoking" value="no" class="mr-2">
                        <span>Tidak</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-gray-700 mb-2">Diabetes</label>
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="diabetes" value="yes" required class="mr-2">
                        <span>Ya</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="diabetes" value="no" class="mr-2">
                        <span>Tidak</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="bg-primary text-white px-6 py-3 rounded-md hover:bg-green-600 transition font-medium">
                Hitung Risiko
            </button>
        </div>
    </form>

    <div class="mt-8 p-6 bg-gray-50 rounded-md">
        <h2 class="text-xl font-semibold mb-4 text-dark">Interpretasi Risiko Jantung</h2>
        <ul class="space-y-2">
            <li><strong>0-3:</strong> Risiko Rendah - Faktor risiko yang rendah untuk penyakit jantung</li>
            <li><strong>4-6:</strong> Risiko Sedang - Beberapa faktor risiko yang perlu diperhatikan</li>
            <li><strong>7-10:</strong> Risiko Tinggi - Faktor risiko yang signifikan, disarankan untuk berkonsultasi dengan dokter</li>
        </ul>
        <p class="mt-4 text-red-600 font-medium">
            Peringatan: Tes ini tidak menggantikan konsultasi medis professional. Hasil hanya untuk referensi umum. Jika Anda memiliki kekhawatiran tentang kesehatan jantung Anda, segeralah berkonsultasi dengan dokter.
        </p>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>