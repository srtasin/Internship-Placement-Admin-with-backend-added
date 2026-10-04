<?php
// DELETE api/announcements/delete.php?id=3
require __DIR__ . '/../config.php';
require_auth();

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'id is required.']);
    exit;
}

$stmt = $pdo->prepare('DELETE FROM announcements WHERE id = ?');
$stmt->execute([$id]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Announcement not found.']);
    exit;
}

echo json_encode(['ok' => true]);
