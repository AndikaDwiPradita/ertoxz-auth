<?php
header('Content-Type: application/json');

// File untuk log
$logFile = __DIR__ . '/../login_log.txt';
$usersFile = __DIR__ . '/../users.json';

// Baca users
if (!file_exists($usersFile)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'User data not found']);
    exit;
}
$usersData = json_decode(file_get_contents($usersFile), true);
$users = [];
foreach ($usersData['users'] as $u) {
    $users[$u['username']] = $u['password'];
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$time = date('Y-m-d H:i:s');

$success = isset($users[$username]) && $users[$username] === $password;

// Tulis log
$logEntry = "[$time] User: $username | IP: $ip | Success: " . ($success ? 'YES' : 'NO') . "\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Kirim response
if ($success) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
}

// Fungsi kirim ke Discord
function sendToDiscord($message) {
    $webhookUrl = "https://discord.com/api/webhooks/......"; // Ganti dengan webhookmu
    $data = json_encode(['content' => $message]);
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => $data
        ]
    ];
    $context = stream_context_create($options);
    file_get_contents($webhookUrl, false, $context);
}

// Di bagian setelah verifikasi:
if ($success) {
    sendToDiscord("✅ Login berhasil: **$username** dari IP $ip");
} else {
    sendToDiscord("❌ Login gagal: **$username** dari IP $ip");
}
?>
