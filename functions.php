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
?>