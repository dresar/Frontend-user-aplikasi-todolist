<?php
session_start();
require_once 'includes/functions.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Cek apakah ada ID tugas
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$task_id = (int)$_GET['id'];
$error = '';
$success = '';

// Ambil data kategori menggunakan API key
$categoriesResponse = apiRequestWithKey('/key/categories');
$categories = [];

if (isset($categoriesResponse['code']) && $categoriesResponse['code'] === 200 && 
    isset($categoriesResponse['data']['success']) && $categoriesResponse['data']['success']) {
    $categories = $categoriesResponse['data']['data']['categories'] ?? [];
}

// Ambil data tugas berdasarkan ID
$taskResponse = apiRequestWithKey("/key/tasks/{$task_id}");
$task = null;

if (isset($taskResponse['code']) && $taskResponse['code'] === 200 && 
    isset($taskResponse['data']['success']) && $taskResponse['data']['success']) {
    $task = $taskResponse['data']['data']['task'] ?? null;
} else {
    $error = 'Tugas tidak ditemukan';
}

// Proses update tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_task') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $status = $_POST['status'] ?? 'pending';
    
    if (empty($title)) {
        $error = 'Judul tugas harus diisi';
    } else {
        $taskData = [
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'category_id' => $category_id,
            'due_date' => $due_date,
            'status' => $status
        ];
        
        $response = apiRequestWithKey("/key/tasks/{$task_id}", 'PUT', $taskData);
        
        if (isset($response['code']) && $response['code'] === 200 && 
            isset($response['data']['success']) && $response['data']['success']) {
            $success = 'Tugas berhasil diperbarui';
            $task = $response['data']['data']['task'] ?? $task;
        } else {
            $error = $response['data']['message'] ?? 'Gagal memperbarui tugas';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas - Todo App</title>
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
                <?php if ($error === 'Tugas tidak ditemukan'): ?>
                    <p class="mt-2"><a href="dashboard.php" class="text-blue-600 hover:text-blue-800 underline">Kembali ke Dashboard</a></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
                <p class="mt-2"><a href="dashboard.php" class="text-blue-600 hover:text-blue-800 underline">Kembali ke Dashboard</a></p>
            </div>
        <?php endif; ?>
        
        <?php if ($task): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white">Edit Tugas</h2>
                    <a href="dashboard.php" class="text-white hover:text-blue-200">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
                <div class="p-6">
                    <form method="post" action="">
                        <input type="hidden" name="action" value="update_task">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div class="md:col-span-2">
                                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Judul</label>
                                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                            </div>
                            <div>
                                <label for="priority" class="block text-gray-700 text-sm font-bold mb-2">Prioritas</label>
                                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="priority" name="priority">
                                    <option value="low" <?php echo $task['priority'] === 'low' ? 'selected' : ''; ?>>Rendah</option>
                                    <option value="medium" <?php echo $task['priority'] === 'medium' ? 'selected' : ''; ?>>Sedang</option>
                                    <option value="high" <?php echo $task['priority'] === 'high' ? 'selected' : ''; ?>>Tinggi</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div class="md:col-span-2">
                                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
                                <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="description" name="description" rows="3"><?php echo htmlspecialchars($task['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Kategori</label>
                                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="category_id" name="category_id">
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo isset($task['category_id']) && $task['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="due_date" class="block text-gray-700 text-sm font-bold mb-2">Tenggat Waktu</label>
                                    <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="due_date" name="due_date" value="<?php echo $task['due_date'] ?? ''; ?>">
                                </div>
                                <div>
                                    <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="status" name="status">
                                        <option value="pending" <?php echo $task['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="completed" <?php echo $task['status'] === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <a href="dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Batal
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                <i class="fas fa-save mr-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>