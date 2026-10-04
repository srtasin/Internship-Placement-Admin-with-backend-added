<?php
// GET api/deadlines/list.php
require __DIR__ . '/../config.php';
require_auth();

echo json_encode($pdo->query('SELECT * FROM deadlines ORDER BY id')->fetchAll());
