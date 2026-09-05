<?php
session_start();
require_once 'includes/functions.php';

$error = '';
$success = '';

// Jika user sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($name) || empty($email) || empty($password) || empty($password_confirm)) {
        $error = 'Semua field harus diisi';
    } elseif ($password !== $password_confirm) {
        $error = 'Password dan konfirmasi password tidak sama';
    } else {
        // Request ke API untuk registrasi
        $apiBaseUrl = "http://localhost:3000/api";
        $url = $apiBaseUrl . "/users/register";
        
        $curl = curl_init();
        
        $data = [
            'name' => $name,
            'email' => $email,
            'password' => $password
        ];
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ]
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        $responseData = json_decode($response, true);
        
        if ($httpCode === 201 && isset($responseData['success']) && $responseData['success']) {
            $success = 'Registrasi berhasil! Silakan login.';
        } else {
            $error = $responseData['message'] ?? 'Registrasi gagal';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Todo App</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center py-12">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-blue-600 px-6 py-4">
            <h2 class="text-2xl font-bold text-white">Register</h2>
        </div>
        
        <div class="p-6">
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success; ?>
                    <p class="mt-2"><a href="login.php" class="text-blue-600 hover:text-blue-800 underline">Klik disini untuk login</a></p>
                </div>
            <?php else: ?>
                <form method="post" action="">
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama</label>
                        <input type="text" id="name" name="name" required 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <input type="email" id="email" name="email" required 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                        <input type="password" id="password" name="password" required 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    
                    <div class="mb-6">
                        <label for="password_confirm" class="block text-gray-700 text-sm font-bold mb-2">Konfirmasi Password</label>
                        <input type="password" id="password_confirm" name="password_confirm" required 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                            Register
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600">Sudah punya akun? <a href="login.php" class="text-blue-600 hover:text-blue-800">Login disini</a></p>
            </div>
        </div>
    </div>
</body>
</html>