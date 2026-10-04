<?php
// PUT api/settings/update.php   { key: value, key2: value2, ... }
require __DIR__ . '/../config.php';
require_auth();

$body = get_json_body();

$upsert = $pdo->prepare(
    'INSERT INTO settings (`key`, value) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE value = VALUES(value)'
);

foreach ($body as $key => $value) {
    $upsert->execute([$key, (string) $value]);
}

$rows = $pdo->query('SELECT `key`, value FROM settings')->fetchAll();
$settings = [];
foreach ($rows as $r) {
    $settings[$r['key']] = $r['value'];
}

echo json_encode($settings);
