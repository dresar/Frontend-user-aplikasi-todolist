<?php
session_start();
require_once 'includes/functions.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Ambil data kategori menggunakan API key
$categoriesResponse = apiRequestWithKey('/key/categories');
$categories = [];
$error = '';
$success = '';

if (isset($categoriesResponse['code']) && $categoriesResponse['code'] === 200 && 
    isset($categoriesResponse['data']['success']) && $categoriesResponse['data']['success']) {
    $categories = $categoriesResponse['data']['data']['categories'] ?? [];
}

// Proses tambah kategori baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $name = $_POST['name'] ?? '';
    $color = $_POST['color'] ?? '#3b82f6'; // Default blue color
    
    if (empty($name)) {
        $error = 'Nama kategori harus diisi';
    } else {
        $categoryData = [
            'name' => $name,
            'color' => $color
        ];
        
        $response = apiRequestWithKey('/key/categories', 'POST', $categoryData);
        
        if (isset($response['code']) && $response['code'] === 201 && 
            isset($response['data']['success']) && $response['data']['success']) {
            $success = 'Kategori berhasil ditambahkan';
            // Refresh data kategori
            $categoriesResponse = apiRequestWithKey('/key/categories');
            if (isset($categoriesResponse['code']) && $categoriesResponse['code'] === 200 && 
                isset($categoriesResponse['data']['success']) && $categoriesResponse['data']['success']) {
                $categories = $categoriesResponse['data']['data']['categories'] ?? [];
            }
        } else {
            $error = $response['data']['message'] ?? 'Gagal menambahkan kategori';
        }
    }
}

// Proses hapus kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    $category_id = (int)$_POST['category_id'];
    
    $response = apiRequestWithKey("/key/categories/{$category_id}", 'DELETE');
    
    if (isset($response['code']) && ($response['code'] === 200 || $response['code'] === 204) && 
        (isset($response['data']['success']) && $response['data']['success'] || $response['code'] === 204)) {
        $success = 'Kategori berhasil dihapus';
        // Refresh data kategori
        $categoriesResponse = apiRequestWithKey('/key/categories');
        if (isset($categoriesResponse['code']) && $categoriesResponse['code'] === 200 && 
            isset($categoriesResponse['data']['success']) && $categoriesResponse['data']['success']) {
            $categories = $categoriesResponse['data']['data']['categories'] ?? [];
        }
    } else {
        $error = $response['data']['message'] ?? 'Gagal menghapus kategori';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori - Todo App</title>
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
                    <a href="categories.php" class="hover:underline font-medium">Kategori</a>
                    <a href="profile.php" class="hover:underline">Profil</a>
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
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">Tambah Kategori</h2>
                    </div>
                    <div class="p-6">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="add_category">
                            <div class="mb-4">
                                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama Kategori</label>
                                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" required>
                            </div>
                            <div class="mb-6">
                                <label for="color" class="block text-gray-700 text-sm font-bold mb-2">Warna</label>
                                <input type="color" class="h-10 w-full rounded border" id="color" name="color" value="#3b82f6">
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    <i class="fas fa-plus mr-2"></i> Tambah Kategori
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">Daftar Kategori</h2>
                    </div>
                    <div class="p-6">
                        <?php if (empty($categories)): ?>
                            <div class="bg-blue-50 text-blue-700 px-4 py-3 rounded">
                                Belum ada kategori. Tambahkan kategori baru!
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php foreach ($categories as $category): ?>
                                    <div class="border rounded-lg overflow-hidden shadow-sm">
                                        <div class="px-4 py-3 flex justify-between items-center" style="background-color: <?php echo htmlspecialchars($category['color']); ?>; color: white;">
                                            <h3 class="font-bold"><?php echo htmlspecialchars($category['name']); ?></h3>
                                            <form method="post" action="" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                                <input type="hidden" name="action" value="delete_category">
                                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                <button type="submit" class="text-white hover:text-red-200">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <div class="px-4 py-3 bg-gray-50">
                                            <div class="text-sm text-gray-600">
                                                <span class="font-medium">ID:</span> <?php echo $category['id']; ?>
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                <span class="font-medium">Warna:</span> <?php echo htmlspecialchars($category['color']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>