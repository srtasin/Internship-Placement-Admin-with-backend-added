<?php
// GET api/users/pending_counts.php -> { student: 2, company: 2, coordinator: 1 }
require __DIR__ . '/../config.php';
require_auth();

$rows = $pdo->query(
    "SELECT role, COUNT(*) AS count FROM users WHERE status = 'pending' GROUP BY role"
)->fetchAll();

$counts = ['student' => 0, 'company' => 0, 'coordinator' => 0];
foreach ($rows as $r) {
    $counts[$r['role']] = (int) $r['count'];
}

echo json_encode($counts);
