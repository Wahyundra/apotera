<?php
include_once 'config.php';
include_once 'functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];

    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $message = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Apotera</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                        secondary: '#3B82F6',
                        accent: '#8B5CF6',
                        dark: '#1F2937',
                        light: '#F9FAFB'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-lg w-full bg-white p-10 rounded-2xl shadow-lg">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <i class="fas fa-heartbeat text-primary text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-dark">Selamat Datang</h1>
            <p class="text-gray-600 mt-2">Silakan login ke akun Anda</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-md">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-8">
            <div>
                <label for="username" class="block text-gray-700 mb-3 text-lg">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    class="w-full px-5 py-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-lg"
                    placeholder="Masukkan username Anda"
                >
            </div>

            <div>
                <label for="password" class="block text-gray-700 mb-3 text-lg">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full px-5 py-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-lg"
                    placeholder="Masukkan password Anda"
                >
            </div>

            <div class="text-center">
                <button type="submit" class="w-full bg-primary text-white py-4 rounded-lg hover:bg-green-600 transition font-medium text-lg">
                    Login
                </button>
            </div>
        </form>

        <div class="mt-8 text-center">
            <p class="text-gray-600">Belum punya akun? <a href="register.php" class="text-primary hover:underline font-medium">Daftar di sini</a></p>
            <p class="text-gray-600 mt-4"><a href="index.php" class="text-gray-500 hover:underline">Kembali ke Beranda</a></p>
        </div>
    </div>
</body>
</html>