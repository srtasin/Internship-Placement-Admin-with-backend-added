<?php
// POST api/auth/login.php   { email, password }
require __DIR__ . '/../config.php';

$body = get_json_body();
$email = $body['email'] ?? '';
$password = $body['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required.']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
$stmt->execute([$email]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid email or password.']);
    exit;
}

// Store the logged-in admin's id in the PHP session
$_SESSION['admin_id'] = $admin['id'];

echo json_encode([
    'id' => $admin['id'],
    'name' => $admin['name'],
    'email' => $admin['email'],
]);
