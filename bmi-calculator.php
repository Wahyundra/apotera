<?php
include_once 'config.php';
include_once 'functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $height = floatval($_POST['height']);
    $weight = floatval($_POST['weight']);

    if ($height > 0 && $weight > 0) {
        $bmi = calculateBMI($weight, $height);
        $category = getBMICategory($bmi);

        // Save to database if user is logged in
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("INSERT INTO bmi_calculations (user_id, height, weight, bmi, result) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $height, $weight, $bmi, $category]);
        }

        $message = "BMI Anda adalah $bmi, termasuk dalam kategori: $category";

        // Get recommendations based on BMI category
        $recommendations = getBMIRecommendations($category);
    } else {
        $message = "Silakan masukkan tinggi dan berat badan yang valid.";
    }
}

include_once 'includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-center text-dark mb-6">Kalkulator BMI</h1>

    <p class="text-gray-600 mb-8 text-center">
        Kalkulator BMI (Body Mass Index) atau Indeks Massa Tubuh digunakan untuk mengetahui apakah berat badan Anda ideal sesuai dengan tinggi badan Anda.
    </p>

    <?php if ($message): ?>
        <div class="mb-6 p-4 bg-<?php echo strpos($message, 'Normal') ? 'green' : 'yellow'; ?>-100 rounded-md <?php echo strpos($message, 'Normal') ? 'text-green-700' : 'text-yellow-700'; ?>">
            <?php echo $message; ?>
        </div>

        <?php if (isset($recommendations)): ?>
        <div class="mb-8 p-6 bg-blue-50 rounded-md">
            <h2 class="text-xl font-semibold mb-4 text-dark">Rekomendasi untuk Anda</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-green-700 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        Yang Harus Dilakukan
                    </h3>
                    <ul class="space-y-2">
                        <?php foreach($recommendations['do'] as $do): ?>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">•</span>
                            <span><?php echo $do; ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold text-red-700 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        Yang Harus Dihindari
                    </h3>
                    <ul class="space-y-2">
                        <?php foreach($recommendations['avoid'] as $avoid): ?>
                        <li class="flex items-start">
                            <span class="text-red-500 mr-2">•</span>
                            <span><?php echo $avoid; ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="height" class="block text-gray-700 mb-2">Tinggi Badan (cm)</label>
                <input
                    type="number"
                    id="height"
                    name="height"
                    min="100"
                    max="250"
                    step="0.1"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Contoh: 170"
                >
            </div>

            <div>
                <label for="weight" class="block text-gray-700 mb-2">Berat Badan (kg)</label>
                <input
                    type="number"
                    id="weight"
                    name="weight"
                    min="30"
                    max="300"
                    step="0.1"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Contoh: 70"
                >
            </div>
        </div>

        <div class="text-center">
            <button
                type="submit"
                class="bg-primary text-white px-6 py-3 rounded-md hover:bg-green-600 transition font-medium"
            >
                Hitung BMI
            </button>
        </div>
    </form>

    <div class="mt-8 p-6 bg-gray-50 rounded-md">
        <h2 class="text-xl font-semibold mb-4 text-dark">Interpretasi BMI</h2>
        <ul class="space-y-2">
            <li class="flex items-center">
                <div class="w-4 h-4 bg-red-500 rounded-full mr-2"></div>
                <span><strong>Kurus:</strong> BMI < 18.5</span>
            </li>
            <li class="flex items-center">
                <div class="w-4 h-4 bg-green-500 rounded-full mr-2"></div>
                <span><strong>Normal:</strong> BMI 18.5 - 24.9</span>
            </li>
            <li class="flex items-center">
                <div class="w-4 h-4 bg-yellow-500 rounded-full mr-2"></div>
                <span><strong>Berlebih:</strong> BMI 25 - 29.9</span>
            </li>
            <li class="flex items-center">
                <div class="w-4 h-4 bg-red-700 rounded-full mr-2"></div>
                <span><strong>Obesitas:</strong> BMI ≥ 30</span>
            </li>
        </ul>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>