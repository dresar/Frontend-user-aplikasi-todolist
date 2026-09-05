<?php
session_start();

// Cek apakah user sudah login
$isLoggedIn = isset($_SESSION['api_key']) && isset($_SESSION['token']);

// Redirect ke halaman login jika belum login
if (!$isLoggedIn) {
    header('Location: login.php');
    exit;
}

// Redirect ke dashboard jika sudah login
header('Location: dashboard.php');
exit;
?>