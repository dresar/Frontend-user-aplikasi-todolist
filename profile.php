<?php
session_start();
require_once 'includes/functions.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Ambil data user dari session
$user = $_SESSION['user'] ?? [];
$apiKey = $_SESSION['api_key'] ?? '';
$token = $_SESSION['token'] ?? '';

// Ambil data user terbaru dari API
$userResponse = apiRequestWithToken('/users/me');

if (isset($userResponse['code']) && $userResponse['code'] === 200 && 
    isset($userResponse['data']['success']) && $userResponse['data']['success']) {
    $user = $userResponse['data']['data']['user'] ?? $user;
}

$error = '';
$success = '';

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name = $_POST['name'] ?? '';
    
    if (empty($name)) {
        $error = 'Nama harus diisi';
    } else {
        $userData = [
            'name' => $name
        ];
        
        $response = apiRequestWithToken('/users/me', 'PATCH', $userData);
        
        if (isset($response['code']) && $response['code'] === 200 && 
            isset($response['data']['success']) && $response['data']['success']) {
            $success = 'Profil berhasil diperbarui';
            $user = $response['data']['data']['user'] ?? $user;
            $_SESSION['user'] = $user;
        } else {
            $error = $response['data']['message'] ?? 'Gagal memperbarui profil';
        }
    }
}

// Proses regenerate API key
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'regenerate_api_key') {
    $response = apiRequestWithToken('/users/me/api-key', 'POST');
    
    if (isset($response['code']) && $response['code'] === 200 && 
        isset($response['data']['success']) && $response['data']['success']) {
        $success = 'API Key berhasil diperbarui';
        $apiKey = $response['data']['data']['api_key'] ?? $apiKey;
        $_SESSION['api_key'] = $apiKey;
        $user = $response['data']['data']['user'] ?? $user;
        $_SESSION['user'] = $user;
    } else {
        $error = $response['data']['message'] ?? 'Gagal memperbarui API Key';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Todo App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="text-xl font-bold">Todo App</div>
                <div class="flex space-x-4">
                    <a href="dashboard.php" class="hover:underline">Dashboard</a>
                    <a href="categories.php" class="hover:underline">Kategori</a>
                    <a href="profile.php" class="hover:underline font-medium">Profil</a>
                    <a href="logout.php" class="hover:underline">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8">
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">Informasi Profil</h2>
                    </div>
                    <div class="p-6">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="mb-4">
                                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama</label>
                                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                                <input type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                                <p class="text-sm text-gray-500 mt-1">Email tidak dapat diubah</p>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">API Key</h2>
                    </div>
                    <div class="p-6">
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">API Key Anda</label>
                            <div class="flex">
                                <input type="text" class="shadow appearance-none border rounded-l w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight" value="<?php echo htmlspecialchars($apiKey); ?>" id="apiKeyField" readonly>
                                <button type="button" onclick="copyToClipboard('apiKeyField')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-r focus:outline-none focus:shadow-outline">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Gunakan API key ini untuk mengakses API</p>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Token JWT</label>
                            <div class="flex">
                                <input type="text" class="shadow appearance-none border rounded-l w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight" value="<?php echo htmlspecialchars(substr($token, 0, 20) . '...'); ?>" id="tokenField" readonly>
                                <button type="button" onclick="copyToClipboard('tokenField', '<?php echo htmlspecialchars($token); ?>')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-r focus:outline-none focus:shadow-outline">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Token JWT untuk autentikasi</p>
                        </div>
                        
                        <form method="post" action="" onsubmit="return confirm('Apakah Anda yakin ingin membuat API Key baru? API Key lama tidak akan berfungsi lagi.');">
                            <input type="hidden" name="action" value="regenerate_api_key">
                            <div class="flex justify-end">
                                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    <i class="fas fa-sync-alt mr-2"></i> Buat API Key Baru
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden mt-8">
                    <div class="bg-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">Contoh Penggunaan API</h2>
                    </div>
                    <div class="p-6">
                        <div class="bg-gray-800 text-white p-4 rounded overflow-x-auto">
                            <pre class="text-sm"><code>curl -X GET "http://localhost:3000/api/key/tasks" \
  -H "X-API-Key: <?php echo htmlspecialchars($apiKey); ?>" \
  -H "Content-Type: application/json"</code></pre>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Contoh request untuk mendapatkan daftar tugas menggunakan API Key</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function copyToClipboard(elementId, fullText = null) {
        const element = document.getElementById(elementId);
        const textToCopy = fullText || element.value;
        
        navigator.clipboard.writeText(textToCopy).then(function() {
            // Flash the background to indicate successful copy
            const originalBg = element.style.backgroundColor;
            element.style.backgroundColor = '#d1fae5';
            setTimeout(() => {
                element.style.backgroundColor = originalBg;
            }, 300);
        });
    }
    </script>
</body>
</html>