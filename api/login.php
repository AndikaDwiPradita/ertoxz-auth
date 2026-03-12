
<?php
header('Content-Type: application/json');

// Path ke file users.json
$usersFile = __DIR__ . '/../users.json';

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

if (isset($users[$username]) && $users[$username] === $password) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
}
?>
