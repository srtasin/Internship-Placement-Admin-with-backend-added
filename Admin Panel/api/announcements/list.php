<?php
// GET api/announcements/list.php
require __DIR__ . '/../config.php';
require_auth();

echo json_encode($pdo->query('SELECT * FROM announcements ORDER BY created_at DESC, id DESC')->fetchAll());
