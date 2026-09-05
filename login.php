<?php
session_start();
require_once 'includes/functions.php';

$error = '';

// Jika user sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi';
    } else {
        // Request ke API untuk login
        $apiBaseUrl = "http://localhost:3000/api";
        $url = $apiBaseUrl . "/users/login";
        
        $curl = curl_init();
        
        $data = [
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
        
        if ($httpCode === 200 && isset($responseData['success']) && $responseData['success']) {
            // Simpan API key dan token di session
            $apiKey = $responseData['data']['user']['api_key'];
            $token = $responseData['data']['token'];
            $userData = $responseData['data']['user'];
            
            saveAuthData($apiKey, $token, $userData);
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $responseData['message'] ?? 'Login gagal';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Todo App</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-blue-600 px-6 py-4">
            <h2 class="text-2xl font-bold text-white">Login</h2>
        </div>
        
        <div class="p-6">
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" id="email" name="email" required 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" id="password" name="password" required 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                        Login
                    </button>
                </div>
            </form>
            
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600">Belum punya akun? <a href="register.php" class="text-blue-600 hover:text-blue-800">Daftar disini</a></p>
                <p class="text-sm text-gray-600 mt-2">Login sebagai <a href="admin_login.php" class="text-purple-600 hover:text-purple-800">Administrator</a></p>
            </div>
        </div>
    </div>
</body>
</html>