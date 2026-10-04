<?php
// POST api/auth/logout.php
require __DIR__ . '/../config.php';

$_SESSION = [];
session_destroy();

echo json_encode(['ok' => true]);
