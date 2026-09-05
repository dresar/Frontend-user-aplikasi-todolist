<?php
// Fungsi untuk menyimpan API key dan token di session
function saveAuthData($apiKey, $token, $userData) {
    $_SESSION['api_key'] = $apiKey;
    $_SESSION['token'] = $token;
    $_SESSION['user'] = $userData;
}

// Fungsi untuk menyimpan API key dan token admin di session
function saveAdminAuthData($apiKey, $token, $adminData) {
    $_SESSION['admin_api_key'] = $apiKey;
    $_SESSION['admin_token'] = $token;
    $_SESSION['admin'] = $adminData;
}

// Fungsi untuk mengecek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['api_key']) && isset($_SESSION['token']);
}

// Fungsi untuk mengecek apakah admin sudah login
function isAdminLoggedIn() {
    return isset($_SESSION['admin_api_key']) && isset($_SESSION['admin_token']);
}

// Fungsi untuk melakukan request ke API dengan API key
function apiRequestWithKey($endpoint, $method = 'GET', $data = null) {
    $apiBaseUrl = "http://localhost:3000/api";
    $url = $apiBaseUrl . $endpoint;
    
    $curl = curl_init();
    
    $headers = [
        "X-API-Key: " . $_SESSION['api_key'],
        "Content-Type: application/json"
    ];
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method
    ];
    
    if ($data && ($method === 'POST' || $method === 'PUT' || $method === 'PATCH')) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($curl, $options);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);
    
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Fungsi untuk melakukan request ke API dengan token JWT
function apiRequestWithToken($endpoint, $method = 'GET', $data = null) {
    $apiBaseUrl = "http://localhost:3000/api";
    $url = $apiBaseUrl . $endpoint;
    
    $curl = curl_init();
    
    $headers = [
        "Authorization: Bearer " . $_SESSION['token'],
        "Content-Type: application/json"
    ];
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method
    ];
    
    if ($data && ($method === 'POST' || $method === 'PUT' || $method === 'PATCH')) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($curl, $options);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);
    
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Fungsi untuk melakukan request ke API dengan API key admin
function apiRequestWithAdminKey($endpoint, $method = 'GET', $data = null) {
    $apiBaseUrl = "http://localhost:3000/api";
    $url = $apiBaseUrl . $endpoint;
    
    $curl = curl_init();
    
    $headers = [
        "X-API-Key: " . $_SESSION['admin_api_key'],
        "Content-Type: application/json"
    ];
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method
    ];
    
    if ($data && ($method === 'POST' || $method === 'PUT' || $method === 'PATCH')) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($curl, $options);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);
    
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Fungsi untuk melakukan request ke API dengan token JWT admin
function apiRequestWithAdminToken($endpoint, $method = 'GET', $data = null) {
    $apiBaseUrl = "http://localhost:3000/api";
    $url = $apiBaseUrl . $endpoint;
    
    $curl = curl_init();
    
    $headers = [
        "Authorization: Bearer " . $_SESSION['admin_token'],
        "Content-Type: application/json"
    ];
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method
    ];
    
    if ($data && ($method === 'POST' || $method === 'PUT' || $method === 'PATCH')) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($curl, $options);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);
    
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}
?>