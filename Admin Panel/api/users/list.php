<?php
// GET api/users/list.php?role=student
require __DIR__ . '/../config.php';
require_auth();

$role = $_GET['role'] ?? null;

if ($role) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE role = ? ORDER BY id');
    $stmt->execute([$role]);
} else {
    $stmt = $pdo->query('SELECT * FROM users ORDER BY id');
}

echo json_encode($stmt->fetchAll());
