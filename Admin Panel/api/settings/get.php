<?php
// GET api/settings/get.php -> { system_name: "...", session_timeout: "30", ... }
require __DIR__ . '/../config.php';
require_auth();

$rows = $pdo->query('SELECT `key`, value FROM settings')->fetchAll();
$settings = [];
foreach ($rows as $r) {
    $settings[$r['key']] = $r['value'];
}

echo json_encode($settings);
