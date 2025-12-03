<?php
// functions.php

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';
}

function redirect($path) {
    header("Location: $path");
    exit();
}

function calculateBMI($weight, $height) {
    $heightInMeters = $height / 100; // Convert cm to m
    return round($weight / ($heightInMeters * $heightInMeters), 2);
}

function getBMICategory($bmi) {
    if ($bmi < 18.5) {
        return "Kurus";
    } elseif ($bmi >= 18.5 && $bmi < 25) {
        return "Normal";
    } elseif ($bmi >= 25 && $bmi < 30) {
        return "Berlebih";
    } else {
        return "Obesitas";
    }
}

function calculateDepressionScore($answers) {
    $total = 0;
    foreach ($answers as $answer) {
        $total += $answer;
    }
    return $total;
}

function getDepressionCategory($score) {
    if ($score <= 4) {
        return "Normal";
    } elseif ($score <= 9) {
        return "Mild";
    } elseif ($score <= 14) {
        return "Moderate";
    } elseif ($score <= 19) {
        return "Severe";
    } else {
        return "Extremely Severe";
    }
}

function calculateHeartRiskScore($age, $cholesterol, $blood_pressure, $smoking, $diabetes) {
    $score = 0;

    // Age factor
    if ($age >= 40 && $age < 50) $score += 1;
    elseif ($age >= 50 && $age < 60) $score += 2;
    elseif ($age >= 60) $score += 3;

    // Cholesterol factor
    if ($cholesterol >= 200 && $cholesterol < 240) $score += 1;
    elseif ($cholesterol >= 240) $score += 2;

    // Blood pressure factor
    if ($blood_pressure >= 120 && $blood_pressure < 140) $score += 1;
    elseif ($blood_pressure >= 140) $score += 2;

    // Smoking factor
    if ($smoking === 'yes') $score += 2;

    // Diabetes factor
    if ($diabetes === 'yes') $score += 2;

    return $score;
}

function getHeartRiskCategory($score) {
    if ($score <= 3) {
        return "Risiko Rendah";
    } elseif ($score <= 6) {
        return "Risiko Sedang";
    } else {
        return "Risiko Tinggi";
    }
}

function getBMIRecommendations($category) {
    $recommendations = [
        'Kurus' => [
            'do' => [
                'Makan makanan bergizi seimbang dengan kalori yang cukup',
                'Tambahkan makanan yang kaya protein dan karbohidrat kompleks',
                'Lakukan olahraga ringan secara rutin untuk meningkatkan nafsu makan',
                'Konsultasi dengan dokter atau ahli gizi untuk rencana penambahan berat badan yang sehat'
            ],
            'avoid' => [
                'Makanan cepat saji yang tinggi lemak trans',
                'Stres berlebihan yang dapat mengurangi nafsu makan',
                'Latihan berat yang bisa mengurangi berat badan lebih lanjut',
                'Konsumsi alkohol dan rokok'
            ]
        ],
        'Normal' => [
            'do' => [
                'Pertahankan pola makan seimbang dan gizi seimbang',
                'Lakukan olahraga secara rutin minimal 150 menit per minggu',
                'Pastikan tidur cukup 7-9 jam per hari',
                'Lakukan pemeriksaan kesehatan rutin'
            ],
            'avoid' => [
                'Gaya hidup tidak sehat seperti merokok atau konsumsi alkohol',
                'Makan berlebihan saat tidak lapar',
                'Kurang aktivitas fisik',
                'Konsumsi makanan tinggi gula dan garam'
            ]
        ],
        'Berlebih' => [
            'do' => [
                'Kurangi konsumsi kalori secara bertahap dan sehat',
                'Perbanyak konsumsi sayuran, buah-buahan, dan makanan berserat',
                'Lakukan olahraga aerobik minimal 150 menit per minggu',
                'Konsultasi dengan ahli gizi untuk program penurunan berat badan yang aman'
            ],
            'avoid' => [
                'Diet ketat yang tidak sehat dan ekstrem',
                'Makanan tinggi lemak jenuh dan gula',
                'Tidak melakukan aktivitas fisik',
                'Stres yang berlebihan'
            ]
        ],
        'Obesitas' => [
            'do' => [
                'Konsultasi dengan dokter atau ahli gizi untuk rencana penurunan berat badan',
                'Lakukan perubahan gaya hidup secara bertahap dan konsisten',
                'Perbanyak konsumsi makanan rendah kalori namun bergizi',
                'Rajin berolahraga minimal 150-300 menit per minggu'
            ],
            'avoid' => [
                'Diet kilat yang tidak sehat',
                'Makanan olahan tinggi kalori',
                'Minuman manis dan alkohol',
                'Gaya hidup yang tidak aktif'
            ]
        ]
    ];

    return $recommendations[$category] ?? $recommendations['Normal'];
}

function getDepressionRecommendations($category) {
    $recommendations = [
        'Normal' => [
            'do' => [
                'Pertahankan rutinitas harian yang sehat',
                'Jaga hubungan sosial yang positif',
                'Lakukan aktivitas yang Anda nikmati',
                'Olahraga secara teratur untuk menjaga kesehatan mental'
            ],
            'avoid' => [
                'Kepribadian yang terlalu keras terhadap diri sendiri',
                'Isolasi sosial yang berkepanjangan',
                'Konsumsi alkohol dan zat terlarang',
                'Stres kronis tanpa manajemen yang tepat'
            ]
        ],
        'Mild' => [
            'do' => [
                'Berbicara dengan teman atau keluarga yang dipercaya',
                'Lakukan aktivitas fisik ringan secara rutin',
                'Jaga pola tidur yang teratur',
                'Coba teknik relaksasi seperti meditasi atau pernapasan dalam'
            ],
            'avoid' => [
                'Mengabaikan gejala yang dirasakan',
                'Penggunaan alkohol untuk mengatasi suasana hati',
                'Mengambil keputusan besar dalam keadaan tertekan',
                'Kurang tidur atau tidur berlebihan'
            ]
        ],
        'Moderate' => [
            'do' => [
                'Berkonsultasi dengan profesional kesehatan mental',
                'Terlibat dalam terapi seperti Cognitive Behavioral Therapy (CBT)',
                'Lakukan aktivitas fisik secara teratur',
                'Jaga pola makan sehat dan teratur'
            ],
            'avoid' => [
                'Menghentikan obat-obatan tanpa konsultasi dokter',
                'Menghindari kontak sosial sepenuhnya',
                'Mengambil keputusan penting tanpa pertimbangan matang',
                'Mengkonsumsi zat yang mempengaruhi mood tanpa resep'
            ]
        ],
        'Severe' => [
            'do' => [
                'Segera konsultasi dengan dokter atau psikiater',
                'Ikuti rencana pengobatan atau terapi yang disarankan profesional',
                'Minta bantuan dari keluarga atau teman terpercaya',
                'Gunakan sumber daya bantuan darurat jika diperlukan'
            ],
            'avoid' => [
                'Menyendiri terlalu lama',
                'Menghentikan obat-obatan secara tiba-tiba',
                'Mengambil keputusan penting tanpa bantuan orang lain',
                'Mengabaikan gejala yang semakin memburuk'
            ]
        ],
        'Extremely Severe' => [
            'do' => [
                'Segera cari bantuan medis darurat atau hubungi layanan krisis',
                'Tetap bersama orang yang dipercaya atau di tempat yang aman',
                'Ikuti penanganan profesional secara ketat',
                'Gunakan layanan darurat seperti hotline krisis jika diperlukan'
            ],
            'avoid' => [
                'Menyendiri saat merasa bahaya',
                'Mengakses alat yang dapat menyakiti diri sendiri',
                'Menghentikan semua bentuk perawatan medis',
                'Mengabaikan tanda bahaya atau peringatan dari orang lain'
            ]
        ]
    ];

    return $recommendations[$category] ?? $recommendations['Normal'];
}

function getHeartRiskRecommendations($category) {
    $recommendations = [
        'Risiko Rendah' => [
            'do' => [
                'Pertahankan pola makan yang sehat dan seimbang',
                'Lakukan aktivitas fisik secara teratur minimal 150 menit per minggu',
                'Lakukan pemeriksaan kesehatan rutin',
                'Jaga berat badan ideal'
            ],
            'avoid' => [
                'Merokok dan konsumsi alkohol',
                'Makanan tinggi lemak jenuh dan kolesterol',
                'Gaya hidup pasif dan tidak aktif',
                'Stres kronis tanpa manajemen yang baik'
            ]
        ],
        'Risiko Sedang' => [
            'do' => [
                'Kurangi konsumsi garam dan lemak jenuh',
                'Perbanyak konsumsi buah-buahan, sayuran, dan serat',
                'Lakukan pemeriksaan tekanan darah secara rutin',
                'Konsultasi dengan dokter untuk manajemen risiko'
            ],
            'avoid' => [
                'Merokok dalam bentuk apapun',
                'Makan berlebihan yang menyebabkan kenaikan berat badan',
                'Kurang tidur dan kurang aktivitas fisik',
                'Stres yang tidak terkelola'
            ]
        ],
        'Risiko Tinggi' => [
            'do' => [
                'Segera konsultasi dengan dokter spesialis jantung',
                'Ikuti rencana pengobatan sesuai instruksi medis',
                'Lakukan perubahan gaya hidup secara intensif',
                'Pantau tekanan darah dan kolesterol secara teratur'
            ],
            'avoid' => [
                'Merokok dan konsumsi alkohol',
                'Makanan tinggi lemak, garam, dan gula',
                'Gaya hidup tidak aktif',
                'Mengabaikan gejala yang mungkin muncul'
            ]
        ]
    ];

    return $recommendations[$category] ?? $recommendations['Risiko Rendah'];
}
?>