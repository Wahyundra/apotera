<?php
include_once 'config.php';
include_once 'functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $answers = [];
    for ($i = 1; $i <= 9; $i++) {
        $answers[$i] = intval($_POST["q$i"]);
    }

    // Calculate total score
    $totalScore = calculateDepressionScore($answers);
    $category = getDepressionCategory($totalScore);

    // Save to database if user is logged in
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("INSERT INTO depression_tests (user_id, q1, q2, q3, q4, q5, q6, q7, q8, q9, total_score, result) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $answers[1], $answers[2], $answers[3], $answers[4], $answers[5], $answers[6], $answers[7], $answers[8], $answers[9], $totalScore, $category]);
    }

    $message = "Skor depresi Anda adalah $totalScore, termasuk dalam kategori: $category";
}

include_once 'includes/header.php';
?>

<div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-center text-dark mb-6">Cek Depresi</h1>

    <p class="text-gray-600 mb-8 text-center">
        Tes ini terdiri dari 9 pertanyaan untuk menilai tingkat depresi Anda. Skor menggunakan skala 0-3 untuk setiap pertanyaan.
    </p>

    <?php if ($message): ?>
        <div class="mb-6 p-4 bg-<?php echo strpos($message, 'Normal') ? 'green' : (strpos($message, 'Mild') ? 'yellow' : 'red'); ?>-100 rounded-md text-<?php echo strpos($message, 'Normal') ? 'green' : (strpos($message, 'Mild') ? 'yellow' : 'red'); ?>-700">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-8">
        <?php
        $questions = [
            1 => "Saya merasa sedih atau murung",
            2 => "Saya kehilangan minat dalam aktivitas yang biasanya saya nikmati",
            3 => "Saya merasa lelah sepanjang waktu",
            4 => "Saya merasa tidak berharga atau bersalah",
            5 => "Saya kesulitan tidur atau tidur terlalu banyak",
            6 => "Saya merasa tidak bersemangat atau tidak berdaya",
            7 => "Saya mengalami perubahan nafsu makan",
            8 => "Saya merasa cemas atau gelisah",
            9 => "Saya memiliki pikiran untuk menyakiti diri sendiri"
        ];
        ?>

        <?php for ($i = 1; $i <= 9; $i++): ?>
            <div class="p-4 border border-gray-200 rounded-md">
                <p class="font-medium text-gray-800 mb-3"><?php echo $i . ". " . $questions[$i]; ?></p>

                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center">
                        <input type="radio" name="q<?php echo $i; ?>" value="0" required class="mr-2">
                        <span>0 - Tidak sama sekali</span>
                    </label>

                    <label class="flex items-center">
                        <input type="radio" name="q<?php echo $i; ?>" value="1" class="mr-2">
                        <span>1 - Beberapa hari</span>
                    </label>

                    <label class="flex items-center">
                        <input type="radio" name="q<?php echo $i; ?>" value="2" class="mr-2">
                        <span>2 - Lebih dari setengah hari</span>
                    </label>

                    <label class="flex items-center">
                        <input type="radio" name="q<?php echo $i; ?>" value="3" class="mr-2">
                        <span>3 - Hampir setiap hari</span>
                    </label>
                </div>
            </div>
        <?php endfor; ?>

        <div class="text-center">
            <button type="submit" class="bg-primary text-white px-6 py-3 rounded-md hover:bg-green-600 transition font-medium">
                Submit
            </button>
        </div>
    </form>

    <div class="mt-8 p-6 bg-gray-50 rounded-md">
        <h2 class="text-xl font-semibold mb-4 text-dark">Interpretasi Skor Depresi</h2>
        <ul class="space-y-2">
            <li><strong>0-4:</strong> Normal - Tidak ada gejala depresi yang signifikan</li>
            <li><strong>5-9:</strong> Mild - Gejala depresi ringan</li>
            <li><strong>10-14:</strong> Moderate - Gejala depresi sedang</li>
            <li><strong>15-19:</strong> Severe - Gejala depresi berat</li>
            <li><strong>20-27:</strong> Extremely Severe - Gejala depresi sangat berat</li>
        </ul>
        <p class="mt-4 text-red-600 font-medium">
            Peringatan: Tes ini bukan diagnosis medis. Jika Anda memiliki pikiran untuk menyakiti diri sendiri, segera hubungi profesional kesehatan mental atau layanan darurat.
        </p>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>