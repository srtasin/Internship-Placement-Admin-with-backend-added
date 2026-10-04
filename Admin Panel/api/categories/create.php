<?php
// POST api/categories/create.php   { name, description }
require __DIR__ . '/../config.php';
require_auth();

$body = get_json_body();
$name = trim($body['name'] ?? '');
$description = $body['description'] ?? '';

if (!$name) {
    http_response_code(400);
    echo json_encode(['error' => 'Category name is required.']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO categories (name, description, internship_count) VALUES (?, ?, 0)');
$stmt->execute([$name, $description]);
$id = $pdo->lastInsertId();

$stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
$stmt->execute([$id]);
http_response_code(201);
echo json_encode($stmt->fetch());
