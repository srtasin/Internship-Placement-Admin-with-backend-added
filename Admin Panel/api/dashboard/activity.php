<?php
// GET api/dashboard/activity.php -> recent approvals/rejections as a simple activity feed
require __DIR__ . '/../config.php';
require_auth();

$rows = $pdo->query(
    "SELECT name, role, status FROM users WHERE status != 'pending' ORDER BY id DESC LIMIT 5"
)->fetchAll();

echo json_encode($rows);
