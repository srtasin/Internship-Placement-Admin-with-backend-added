<?php
// GET api/dashboard/stats.php
require __DIR__ . '/../config.php';
require_auth();

$totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$totalCompanies = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='company'")->fetchColumn();
$totalCoordinators = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='coordinator'")->fetchColumn();
$pendingApprovals = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE status='pending'")->fetchColumn();
$totalAnnouncements = (int) $pdo->query("SELECT COUNT(*) FROM announcements")->fetchColumn();

echo json_encode([
    'totalStudents' => $totalStudents,
    'totalCompanies' => $totalCompanies,
    'totalCoordinators' => $totalCoordinators,
    'pendingApprovals' => $pendingApprovals,
    'totalAnnouncements' => $totalAnnouncements,
]);
