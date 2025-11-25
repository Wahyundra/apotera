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