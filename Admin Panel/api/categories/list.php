<?php
// GET api/categories/list.php
require __DIR__ . '/../config.php';
require_auth();

echo json_encode($pdo->query('SELECT * FROM categories ORDER BY internship_count DESC')->fetchAll());
