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

if (isset($categoriesResponse['code']) && $categoriesResponse['code'] === 200 && 
    isset($categoriesResponse['data']['success']) && $categoriesResponse['data']['success']) {
    $categories = $categoriesResponse['data']['data']['categories'] ?? [];
}

// Ambil data tugas menggunakan API key
$tasksResponse = apiRequestWithKey('/key/tasks');
$tasks = [];
$pagination = null;

if (isset($tasksResponse['code']) && $tasksResponse['code'] === 200 && 
    isset($tasksResponse['data']['success']) && $tasksResponse['data']['success']) {
    $tasks = $tasksResponse['data']['data']['tasks'] ?? [];
    $pagination = $tasksResponse['data']['pagination'] ?? null;
}

// Proses tambah tugas baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_task') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    
    $taskData = [
        'title' => $title,
        'description' => $description,
        'priority' => $priority,
        'category_id' => $category_id,
        'due_date' => $due_date
    ];
    
    $response = apiRequestWithKey('/key/tasks', 'POST', $taskData);
    
    // Redirect untuk refresh halaman
    header('Location: dashboard.php');
    exit;
}

// Proses update status tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $task_id = (int)$_POST['task_id'];
    $status = $_POST['status'];
    
    $response = apiRequestWithKey("/key/tasks/{$task_id}/status", 'PATCH', [
        'status' => $status
    ]);
    
    // Redirect untuk refresh halaman
    header('Location: dashboard.php');
    exit;
}

// Proses hapus tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_task') {
    $task_id = (int)$_POST['task_id'];
    
    $response = apiRequestWithKey("/key/tasks/{$task_id}", 'DELETE');
    
    // Redirect untuk refresh halaman
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Todo App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="text-xl font-bold">Todo App</div>
                <div class="flex space-x-4">
                    <a href="dashboard.php" class="hover:underline font-medium">Dashboard</a>
                    <a href="categories.php" class="hover:underline">Kategori</a>
                    <a href="profile.php" class="hover:underline">Profil</a>
                    <a href="logout.php" class="hover:underline">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white">Tambah Tugas Baru</h2>
                </div>
                <div class="p-6">
                    <form method="post" action="">
                        <input type="hidden" name="action" value="add_task">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div class="md:col-span-2">
                                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Judul</label>
                                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="title" name="title" required>
                            </div>
                            <div>
                                <label for="priority" class="block text-gray-700 text-sm font-bold mb-2">Prioritas</label>
                                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="priority" name="priority">
                                    <option value="low">Rendah</option>
                                    <option value="medium" selected>Sedang</option>
                                    <option value="high">Tinggi</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div class="md:col-span-2">
                                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
                                <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Kategori</label>
                                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="category_id" name="category_id">
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="due_date" class="block text-gray-700 text-sm font-bold mb-2">Tenggat Waktu</label>
                                    <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="due_date" name="due_date">
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                <i class="fas fa-plus mr-2"></i> Tambah Tugas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">Daftar Tugas</h2>
                </div>
                <div class="p-6">
                    <?php if (empty($tasks)): ?>
                        <div class="bg-blue-50 text-blue-700 px-4 py-3 rounded">
                            Belum ada tugas. Tambahkan tugas baru!
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left">Judul</th>
                                        <th class="py-3 px-4 text-left">Kategori</th>
                                        <th class="py-3 px-4 text-left">Prioritas</th>
                                        <th class="py-3 px-4 text-left">Status</th>
                                        <th class="py-3 px-4 text-left">Tenggat</th>
                                        <th class="py-3 px-4 text-left">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($tasks as $task): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4">
                                                <div class="font-medium"><?php echo htmlspecialchars($task['title']); ?></div>
                                                <?php if (!empty($task['description'])): ?>
                                                    <div class="text-sm text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars($task['description']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3 px-4">
                                                <?php if (!empty($task['category'])): ?>
                                                    <span class="px-2 py-1 rounded text-xs font-medium" style="background-color: <?php echo htmlspecialchars($task['category']['color']); ?>; color: white;">
                                                        <?php echo htmlspecialchars($task['category']['name']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-gray-400">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3 px-4">
                                                <?php 
                                                $priorityClass = 'bg-blue-100 text-blue-800';
                                                $priorityText = 'Sedang';
                                                
                                                if ($task['priority'] === 'high') {
                                                    $priorityClass = 'bg-red-100 text-red-800';
                                                    $priorityText = 'Tinggi';
                                                } elseif ($task['priority'] === 'low') {
                                                    $priorityClass = 'bg-green-100 text-green-800';
                                                    $priorityText = 'Rendah';
                                                }
                                                ?>
                                                <span class="px-2 py-1 rounded text-xs font-medium <?php echo $priorityClass; ?>">
                                                    <?php echo $priorityText; ?>
                                                </span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <form method="post" action="" class="inline">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                    <input type="hidden" name="status" value="<?php echo $task['status'] === 'pending' ? 'completed' : 'pending'; ?>">
                                                    <button type="submit" class="px-2 py-1 rounded text-xs font-medium <?php echo $task['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                        <?php echo $task['status'] === 'completed' ? 'Selesai' : 'Pending'; ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="py-3 px-4">
                                                <?php 
                                                if (!empty($task['due_date'])) {
                                                    echo date('d M Y', strtotime($task['due_date']));
                                                } else {
                                                    echo '<span class="text-gray-400">-</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="flex space-x-2">
                                                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="post" action="" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');">
                                                        <input type="hidden" name="action" value="delete_task">
                                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>