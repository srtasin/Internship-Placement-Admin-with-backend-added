<?php
// api/config.php
// Shared database connection + session startup for every API endpoint.
// Every other .php file in api/ starts with: require 'config.php'; (or '../config.php')

// ---- XAMPP MySQL defaults ----
// A fresh XAMPP install has a MySQL user "root" with NO password.
// If you set a root password in phpMyAdmin, put it below.
define('DB_HOST', 'localhost');
define('DB_NAME', 'interntrack');
define('DB_USER', 'root');
define('DB_PASS', '');

// ---- Connect with PDO ----
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// ---- Sessions (PHP's built-in session, stored server-side) ----
session_start();

// ---- CORS / JSON defaults ----
// Only needed if you ever open the front-end from a different origin than
// the API (e.g. a different port). Same-origin XAMPP setups don't need this,
// but it's harmless to leave on.
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---- Helper: read JSON body for POST/PUT requests ----
function get_json_body() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// ---- Helper: require a logged-in admin session, or stop with 401 ----
function require_auth() {
    if (empty($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated. Please log in.']);
        exit;
    }
}
