<?php
// POST api/announcements/create.php   { title, description, audience }
require __DIR__ . '/../config.php';
require_auth();

$body = get_json_body();
$title = trim($body['title'] ?? '');
$description = $body['description'] ?? '';
$audience = $body['audience'] ?? 'All Users';

if (!$title) {
    http_response_code(400);
    echo json_encode(['error' => 'Title is required.']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO announcements (title, description, audience) VALUES (?, ?, ?)');
$stmt->execute([$title, $description, $audience]);
$id = $pdo->lastInsertId();

$stmt = $pdo->prepare('SELECT * FROM announcements WHERE id = ?');
$stmt->execute([$id]);
http_response_code(201);
echo json_encode($stmt->fetch());
