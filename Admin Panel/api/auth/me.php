<?php
// GET api/auth/me.php -> current logged-in admin, or 401
require __DIR__ . '/../config.php';
require_auth();

$stmt = $pdo->prepare('SELECT id, name, email FROM admins WHERE id = ?');
$stmt->execute([$_SESSION['admin_id']]);
echo json_encode($stmt->fetch());
