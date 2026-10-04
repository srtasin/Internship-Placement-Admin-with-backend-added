<?php
// PUT api/deadlines/update.php?id=1   { title, date_label, description }
require __DIR__ . '/../config.php';
require_auth();

$id = $_GET['id'] ?? null;
$body = get_json_body();

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'id is required.']);
    exit;
}

$stmt = $pdo->prepare(
    'UPDATE deadlines SET title = ?, date_label = ?, description = ? WHERE id = ?'
);
$stmt->execute([
    $body['title'] ?? '',
    $body['date_label'] ?? '',
    $body['description'] ?? '',
    $id,
]);

if ($stmt->rowCount() === 0) {
    // rowCount is 0 both when nothing changed AND when the id doesn't exist —
    // double check existence before reporting a 404
    $check = $pdo->prepare('SELECT id FROM deadlines WHERE id = ?');
    $check->execute([$id]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Deadline not found.']);
        exit;
    }
}

$stmt = $pdo->prepare('SELECT * FROM deadlines WHERE id = ?');
$stmt->execute([$id]);
echo json_encode($stmt->fetch());
