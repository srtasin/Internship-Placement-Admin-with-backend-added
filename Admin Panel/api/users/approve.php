<?php
// POST api/users/approve.php?id=3
require __DIR__ . '/../config.php';
require_auth();

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'id is required.']);
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
$stmt->execute([$id]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found.']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
echo json_encode($stmt->fetch());
